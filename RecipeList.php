<?php
    include "./dbinfo.inc"; // Database connection
    include "./recipes.php"; // Include the recipes functions
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Establish database connection
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
    if (mysqli_connect_errno()) {
        die(json_encode(["error" => "Failed to connect to MySQL: " . mysqli_connect_error()]));
    }
    mysqli_select_db($connection, DB_DATABASE);

    // Get recipes from database
    $recipes = GetAllRecipes($connection);

    // Handle signup/login POST requests
    $signUpName = isset($_POST['signupUsername']) ? mysqli_real_escape_string($connection, $_POST['signupUsername']) : '';
    $signUPPassword = isset($_POST['signupPassword']) ? mysqli_real_escape_string($connection, $_POST['signupPassword']) : '';

    $loginName = isset($_POST['loginUsername']) ? mysqli_real_escape_string($connection, $_POST['loginUsername']) : '';
    $loginPassword = isset($_POST['loginPassword']) ? mysqli_real_escape_string($connection, $_POST['loginPassword']) : '';

    if (strlen($signUpName) || strlen($signUPPassword)) {
        $signUpResult = SignUp($connection, $signUpName, $signUPPassword);
        echo "<script>alert('$signUpResult');</script>";
    }

    if (strlen($loginName) || strlen($loginPassword)) {
        $loginResult = Login($connection, $loginName, $loginPassword);
        echo "<script>alert('$loginResult');</script>";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
      }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe List</title>
    <link rel="stylesheet" href="styles.css">

    <script>
        const recipes = <?php echo json_encode($recipes); ?>;
        const isLoggedIn = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;

        function goToRecipePage(recipeId) {
            window.location.href = `RecipePage.php?id=${recipeId}`;
        }

        function bookmarkRecipe(recipeId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "bookmarkRecipe.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("Recipe bookmarked successfully!");
                }
            };
            xhr.send("recipeId=" + recipeId);
        }

        function displayRecipes() {
            const recipeList = document.querySelector(".recipe-list");
            recipeList.innerHTML = "";

            recipes.forEach(recipe => {
                const recipeDiv = document.createElement("div");
                recipeDiv.classList.add("recipe");

                recipeDiv.innerHTML = `
                    <a href="javascript:void(0);" onclick="goToRecipePage(${recipe.recipeID})" class="recipe-link">
                        <div class="recipe-box">
                            <div class="recipe-thumbnail">
                                <img src="${recipe.recipeImage}" alt="${recipe.recipeName} Thumbnail">
                            </div>
                            <div class="recipe-details">
                                <h3 class="recipe-title">${recipe.recipeName}</h3>
                                <p class="recipe-description">${recipe.recipeDescription}</p>
                                <p class="recipe-cooking-time"><strong>Cooking Time:</strong> ${recipe.recipeTime} minutes</p>
                            </div>
                            <div class="recipe-actions">
                                ${isLoggedIn ? `<button class="bookmark-btn" onclick="event.stopPropagation(); bookmarkRecipe(${recipe.recipeID})">Bookmark</button>` : ""}
                            </div>
                        </div>
                    </a>
                `;
                recipeList.appendChild(recipeDiv);
            });
        }

        function searchRecipes() {
            const searchInput = document.querySelector(".search-input").value.toLowerCase();
            const filteredRecipes = recipes.filter(recipe =>
                recipe.recipeName.toLowerCase().includes(searchInput) ||
                recipe.recipeDescription.toLowerCase().includes(searchInput)
            );
            displayFilteredRecipes(filteredRecipes);
        }

        function displayFilteredRecipes(filteredRecipes) {
            const recipeList = document.querySelector(".recipe-list");
            recipeList.innerHTML = "";

            if (filteredRecipes.length === 0) {
                recipeList.innerHTML = "<p>No recipes found.</p>";
                return;
            }

            filteredRecipes.forEach(recipe => {
                const recipeDiv = document.createElement("div");
                recipeDiv.classList.add("recipe");

                recipeDiv.innerHTML = `
                    <a href="javascript:void(0);" onclick="goToRecipePage(${recipe.recipeID})" class="recipe-link">
                        <div class="recipe-box">
                            <div class="recipe-thumbnail">
                                <img src="${recipe.recipeImage}" alt="${recipe.recipeName} Thumbnail">
                            </div>
                            <div class="recipe-details">
                                <h3 class="recipe-title">${recipe.recipeName}</h3>
                                <p class="recipe-description">${recipe.recipeDescription}</p>
                                <p class="recipe-cooking-time"><strong>Cooking Time:</strong> ${recipe.recipeTime} minutes</p>
                            </div>
                            <div class="recipe-actions">
                                ${isLoggedIn ? `<button class="bookmark-btn" onclick="event.stopPropagation(); bookmarkRecipe(${recipe.recipeID})">Bookmark</button>` : ""}
                            </div>
                        </div>
                    </a>
                `;
                recipeList.appendChild(recipeDiv);
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            displayRecipes();
            document.querySelector(".search-input").addEventListener("input", searchRecipes);
        });

        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
            document.getElementById('popupOverlay').style.display = 'block';
        }

        function closePopup() {
            let popups = document.querySelectorAll('.popup');
            popups.forEach(popup => popup.style.display = 'none');
            document.getElementById('popupOverlay').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="navbar-title">
            <h1>Online Cookbook</h1>
        </a>
        <div class="nav-links">
            <?php if (!isset($_SESSION['username'])): ?>
                <button onclick="openPopup('loginPopup')">Login</button>
                <button onclick="openPopup('signupPopup')">Signup</button>
            <?php else: ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="search-bar">
            <input type="text" placeholder="Search for recipes..." class="search-input">
        </div>
        <div class="links">
            <?php if (isset($_SESSION['username'])): ?>
                <button class="nav-btn" onclick="window.location.href='CreateRecipe.php'">Create Recipe</button>
                <button class="nav-btn" onclick="window.location.href='BookmarkedRecipes.php'">View Bookmarked Recipes</button>
            <?php endif; ?>
        </div>
        <div class="recipe-list">
            <!-- Recipes will be loaded dynamically -->
        </div>
    </div>

    <!-- Overlay -->
    <div class="popup-overlay" id="popupOverlay" onclick="closePopup()"></div>

    <!-- Signup Popup -->
    <div id="signupPopup" class="popup">
        <span class="close" onclick="closePopup()">&times;</span>
        <h2>Signup</h2>
        <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
            <input type="text" name="signupUsername" placeholder="Username" required>
            <input type="password" name="signupPassword" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
    </div>

    <!-- Login Popup -->
    <div id="loginPopup" class="popup">
        <span class="close" onclick="closePopup()">&times;</span>
        <h2>Login</h2>
        <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="POST">
            <input type="text" name="loginUsername" placeholder="Username" required>
            <input type="password" name="loginPassword" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>

    <script src="scripts.js"></script>
</body>
</html>