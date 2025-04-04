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

    // Get recipe ID from URL
    $recipeId = isset($_GET['id']) ? intval($_GET['id']) : 1;

    // Fetch recipe details
    $recipeQuery = "SELECT recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps FROM RECIPES WHERE recipeID = ?";
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

    mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['recipeName']); ?></title>
    <link rel="stylesheet" href="styles.css">
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
            <?php endif; ?>
        </div>
    </div>

    <!-- Recipe Content -->
    <div class="container">
        <div class="recipe-header">
            <button class="back-button" onclick="goBack()">&larr;</button>
            <h1><?php echo htmlspecialchars($recipe['recipeName']); ?></h1>
            <button id="bookmarkBtn" class="bookmark-btn">Bookmark</button>
        </div>
        <p><?php echo htmlspecialchars($recipe['recipeDescription']); ?></p>

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

    <script>
        function goBack() {
            window.location.href = "RecipeList.php"; // Redirect to Recipe List
        }
    </script>

</body>
</html>
