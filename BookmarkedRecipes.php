<?php
include "./dbinfo.inc";
include "./recipes.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("<p>Please log in to view your bookmarked recipes.</p>");
}

$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_errno()) {
    die(json_encode(["error" => "Failed to connect to MySQL: " . mysqli_connect_error()]));
}

$userId = $_SESSION['user_id'];

// Fetch bookmarked recipes for the user
$bookmarkedQuery = "
    SELECT r.recipeID, r.recipeName, r.recipeDescription, r.recipeImage, r.recipeTime
    FROM BOOKMARKS b
    JOIN RECIPES r ON b.recipeID = r.recipeID
    WHERE b.userID = ?
";
$stmt = mysqli_prepare($connection, $bookmarkedQuery);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$recipes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recipes[] = $row;
}

// Handle signup/login
$signUpName = $_POST['signupUsername'] ?? '';
$signUPPassword = $_POST['signupPassword'] ?? '';
$loginName = $_POST['loginUsername'] ?? '';
$loginPassword = $_POST['loginPassword'] ?? '';

if (!empty($signUpName) || !empty($signUPPassword)) {
    $signUpResult = SignUp($connection, $signUpName, $signUPPassword);
    echo "<script>alert('$signUpResult');</script>";
}
if (!empty($loginName) || !empty($loginPassword)) {
    $loginResult = Login($connection, $loginName, $loginPassword);
    echo "<script>alert('$loginResult');</script>";
}

mysqli_close($connection);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmarked Recipes</title>
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

        function displayRecipes(recipeArray) {
            const recipeList = document.querySelector(".recipe-list");
            recipeList.innerHTML = "";

            if (recipeArray.length === 0) {
                recipeList.innerHTML = "<p>No bookmarked recipes found.</p>";
                return;
            }

            recipeArray.forEach(recipe => {
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
            const filtered = recipes.filter(recipe =>
                recipe.recipeName.toLowerCase().includes(searchInput) ||
                recipe.recipeDescription.toLowerCase().includes(searchInput)
            );
            displayRecipes(filtered);
        }

        document.addEventListener("DOMContentLoaded", () => {
            displayRecipes(recipes);
            document.querySelector(".search-input").addEventListener("input", searchRecipes);
        });
    </script>
</head>
<body>
    <div class="navbar">
        <a href="index.php" class="navbar-title"><h1>Online Cookbook</h1></a>
        <div class="nav-links">
            <?php if (!isset($_SESSION['username'])): ?>
                <button onclick="openPopup('loginPopup')">Login</button>
                <button onclick="openPopup('signupPopup')">Signup</button>
            <?php else: ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <h2>My Bookmarked Recipes</h2>
        <div class="search-bar">
            <input type="text" placeholder="Search your bookmarks..." class="search-input">
        </div>
        <div class="links">
            <?php if (isset($_SESSION['username'])): ?>
                <button class="nav-btn" onclick="window.location.href='CreateRecipe.php'">Create Recipe</button>
                <button class="nav-btn" onclick="window.location.href='RecipeList.php'">All Recipes</button>
            <?php endif; ?>
        </div>
        <div class="recipe-list">
            <!-- Recipes will be dynamically loaded here -->
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
