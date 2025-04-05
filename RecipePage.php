<?php
    include "./dbinfo.inc"; // Ensure this file contains DB credentials
    include "./recipes.php"; // Include helper functions

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Establish database connection
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if (!$connection) {
        die("<p>Failed to connect to MySQL: " . mysqli_connect_error() . "</p>");
    }
    
    $database = mysqli_select_db($connection, DB_DATABASE);

    $userId = $_SESSION['user_id']; // Get user ID from the session

    // Get recipe ID from URL
    $recipeId = isset($_GET['id']) ? intval($_GET['id']) : 1;

    // Fetch recipe details
    $recipeQuery = "SELECT recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps, userName FROM RECIPES WHERE recipeID = ?";
    $stmt = mysqli_prepare($connection, $recipeQuery);
    mysqli_stmt_bind_param($stmt, "i", $recipeId);
    mysqli_stmt_execute($stmt);
    $recipeResult = mysqli_stmt_get_result($stmt);
    $recipe = mysqli_fetch_assoc($recipeResult);

    if (!$recipe) {
        die("<p>Recipe not found.</p>");
    }

    // Fetch ingredients
    $ingredientQuery = "
        SELECT i.ingredientName, ri.ingredientAmount, i.ingredientUnit
        FROM RECIPE_INGREDIENTS ri
        JOIN INGREDIENTS i ON ri.ingredientID = i.ingredientID
        WHERE ri.recipeID = ?";
    $stmt = mysqli_prepare($connection, $ingredientQuery);
    mysqli_stmt_bind_param($stmt, "i", $recipeId);
    mysqli_stmt_execute($stmt);
    $ingredientResult = mysqli_stmt_get_result($stmt);
    $ingredients = [];
    while ($row = mysqli_fetch_assoc($ingredientResult)) {
        $ingredients[] = $row;
    }

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

    mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['recipeName']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <script>
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

    <!-- Navbar -->
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

    

    <!-- Recipe Content -->
    <div class="container">
        <div class="recipe-header">
            <button class="back-button" onclick="goBack()">&larr;</button>
            <h1><?php echo htmlspecialchars($recipe['recipeName']); ?></h1>
            <?php if (isset($_SESSION['username'])): ?>
                <button class="bookmark-btn" onclick="bookmarkRecipe(<?php echo $recipeId; ?>)">Bookmark</button>
            <?php endif; ?>
        </div>
        <p><?php echo htmlspecialchars($recipe['recipeDescription']); ?></p>
        <p class="recipe-author">Created by: <strong><?php echo htmlspecialchars($recipe['userName']); ?></strong></p>

        <!-- Recipe Image -->
        <div class="recipe-images">
            <img src="<?php echo htmlspecialchars($recipe['recipeImage']); ?>" alt="Recipe Image">
        </div>

        <!-- Ingredients Section -->
        <div class="recipe-section">
            <h2>Ingredients</h2>
            <ul>
                <?php foreach ($ingredients as $ingredient): ?>
                    <li><?php echo htmlspecialchars($ingredient['ingredientAmount'] . ' ' . $ingredient['ingredientUnit'] . ' of ' . $ingredient['ingredientName']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Instructions Section -->
        <div class="recipe-section">
            <h2>Instructions</h2>
            <p><?php echo nl2br(htmlspecialchars($recipe['recipeSteps'])); ?></p>
        </div>
    </div>

        <!-- Signup Popup -->
    <div id="signupPopup" class="popup">
    <span class="close" onclick="closePopup()">&times;</span>
    <h2>Signup</h2>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
        <input type="text" name="signupUsername" placeholder="Username" required>
        <input type="password" name="signupPassword" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>
    </div>

    <!-- Login Popup -->
    <div id="loginPopup" class="popup">
    <span class="close" onclick="closePopup()">&times;</span>
    <h2>Login</h2>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
        <input type="text" name="loginUsername" placeholder="Username" required>
        <input type="password" name="loginPassword" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    </div>

    <script src="script.js"></script>

    <script>
        function goBack() {
            window.location.href = "RecipeList.php"; // Redirect to Recipe List
        }

        function bookmarkRecipe(recipeId) {
            // AJAX call to bookmark the recipe
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
    </script>

</body>
</html>
