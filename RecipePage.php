<?php
    include "./dbinfo.inc"; // Ensure this file contains DB credentials
    include "./recipes.php"; // Include the recipes functions

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
    // $recipeQuery = GetRecipe($connection, $_GET['id']);
    // if (!$recipeQuery) {
    //     die("<p>Receipe not found.</p>");
    // }

    // Fetch ingredients using 
    //$ingredients = GetIngredients($connection, $_GET['id']);

    //$instructions = GetInstructions($connection, $_GET['id']);

    mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['name']); ?></title>
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
            <h1><?php echo htmlspecialchars($recipe['name']); ?></h1>
            <button id="bookmarkBtn" class="bookmark-btn">Bookmark</button>
        </div>
        <p><?php echo htmlspecialchars($recipe['description']); ?></p>

        <!-- Recipe Image -->
        <div class="recipe-images">
            <img src="<?php echo htmlspecialchars($recipe['image_path']); ?>" alt="Recipe Image">
        </div>

        <!-- Ingredients Section -->
        <div class="recipe-section">
            <h2>Ingredients</h2>
            <ul>
                <?php foreach ($ingredients as $ingredient): ?>
                    <li><?php echo htmlspecialchars($ingredient); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Instructions Section -->
        <div class="recipe-section">
            <h2>Instructions</h2>
            <ol>
                <?php foreach ($instructions as $step): ?>
                    <li><?php echo htmlspecialchars($step); ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>

    <script>
        function goBack() {
            window.location.href = "RecipeList.php"; // Redirect to Recipe List
        }
    </script>
</body>
</html>
