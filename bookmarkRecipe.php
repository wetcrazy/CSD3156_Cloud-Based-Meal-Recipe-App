<?php
    include "./dbinfo.inc"; // Database connection details
    include "./recipes.php"; // Include the recipes.php file with BookmarkRecipe function

    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "You need to log in first.";
        exit;
    }

    $connection = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($connection->connect_error) {
        echo "Database connection failed.";
        exit;
    }

    $userId = $_SESSION['user_id']; // Get user ID from the session
    $recipeId = isset($_POST['recipeId']) ? intval($_POST['recipeId']) : 0;

    if ($recipeId > 0) {
        // Call the BookmarkRecipe function from recipes.php
        if (BookmarkRecipe($connection, $userId, $recipeId)) {
            echo "Recipe bookmarked successfully!";
        } else {
            echo "Failed to bookmark the recipe.";
        }
    } else {
        echo "Invalid Recipe ID.";
    }

    $connection->close();
?>