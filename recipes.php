<?php 

include "./dbinfo.inc"; // Database connection

function SignUp($connection, $userName, $userPassword) {
    // Check if the username already exists
    $stmt = $connection->prepare("SELECT userID FROM USERS WHERE userName = ?");
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        return "Error: Username already exists.";
    }
    
    // Hash the password
    $hashedPassword = password_hash($userPassword, PASSWORD_BCRYPT);
    
    // Insert the new user
    $stmt = $connection->prepare("INSERT INTO USERS (userName, userPassword) VALUES (?, ?)");
    $stmt->bind_param("ss", $userName, $hashedPassword);
    
    if ($stmt->execute()) {
        return "User registered successfully.";
    } else {
        return "Error: Could not register user.";
    }
}

function Login($connection, $userName, $userPassword) {
    // Retrieve user data
    $stmt = $connection->prepare("SELECT userID, userPassword FROM USERS WHERE userName = ?");
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 0) {
        return "Error: Username not found.";
    }
    
    $stmt->bind_result($userId, $hashedPassword);
    $stmt->fetch();
    
    // Verify password
    if (password_verify($userPassword, $hashedPassword)) {
        // Start session and store user ID
        session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $userName;
        return "Login successful.";
    } else {
        return "Error: Incorrect password.";
    }
}

function AddIngredient($connection, $ingredientName, $ingredientUnit) {
    $stmt = $connection->prepare("INSERT INTO INGREDIENTS (ingredientName, ingredientUnit) VALUES (?, ?)");
    $stmt->bind_param("ss", $ingredientName, $ingredientUnit);
    return $stmt->execute();
}

function BookmarkRecipe($connection, $userId, $recipeId) {
    $stmt = $connection->prepare("INSERT INTO BOOKMARKS (userID, recipeID) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $recipeId);
    return $stmt->execute();
}

function GetBookmarkedRecipes($connection, $userId) {
    $stmt = $connection->prepare("SELECT RECIPES.* FROM RECIPES INNER JOIN BOOKMARKS ON RECIPES.recipeID = BOOKMARKS.recipeID WHERE BOOKMARKS.userID = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function GetUserRecipes($connection, $userName) {
    $stmt = $connection->prepare("SELECT * FROM RECIPES WHERE userName = ?");
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function GetRandomRecipe($connection) {
    $sql = "SELECT recipeID, recipeName, recipeDescription, recipeImage FROM RECIPES ORDER BY RAND() LIMIT 1";
    
    if ($stmt = $connection->prepare($sql)) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        } else {
            // Execution failed
            error_log("Statement execution failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Prepare failed
        error_log("Statement preparation failed: " . $connection->error);
    }

    return null;
}

function AddRecipeIngredient($connection, $recipeId, $ingredientId, $ingredientAmount) {
    $stmt = $connection->prepare("INSERT INTO RECIPE_INGREDIENTS (recipeID, ingredientID, ingredientAmount) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $recipeId, $ingredientId, $ingredientAmount);
    return $stmt->execute();
}

// Function to check if a table exists in the database
function TableExists($tableName, $connection, $dbName) {
    $t = mysqli_real_escape_string($connection, $tableName);
    $d = mysqli_real_escape_string($connection, $dbName);

    $checktable = mysqli_query($connection,
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");

    return mysqli_num_rows($checktable) > 0;
}

// Function to create USERS table if it does not exist
function VerifyUser($connection, $dbName) {
    if (!TableExists("USERS", $connection, $dbName)) {
        $query = "CREATE TABLE USERS (
            userID INT AUTO_INCREMENT PRIMARY KEY,
            userName VARCHAR(50) NOT NULL UNIQUE,
            userPassword VARCHAR(255) NOT NULL,
            userBookmarkedRecipes TEXT
        )";
        mysqli_query($connection, $query);
    }
}

// Function to create RECIPES table if it does not exist
function VerifyRecipe($connection, $dbName) {
    if (!TableExists("RECIPES", $connection, $dbName)) {
        $query = "CREATE TABLE RECIPES (
            recipeID INT AUTO_INCREMENT PRIMARY KEY,
            recipeName VARCHAR(100) NOT NULL,
            recipeDescription TEXT NOT NULL,
            recipeImage VARCHAR(255) NOT NULL,
            recipeTime INT NOT NULL,
            recipeSteps TEXT NOT NULL,
            userName VARCHAR(50) NOT NULL,
            recipeIngredients TEXT NOT NULL,
            FOREIGN KEY (userName) REFERENCES USERS(userName) ON DELETE CASCADE
        )";
        mysqli_query($connection, $query);
    }
}

// Function to create INGREDIENTS table if it does not exist
function VerifyIngredient($connection, $dbName) {
    if (!TableExists("INGREDIENTS", $connection, $dbName)) {
        $query = "CREATE TABLE INGREDIENTS (
            ingredientID INT AUTO_INCREMENT PRIMARY KEY,
            ingredientName VARCHAR(50) NOT NULL,
            ingredientUnit VARCHAR(50) NOT NULL
        )";
        mysqli_query($connection, $query);
    }
}

// Function that pulls all the recipes from the database
function GetAllRecipes($connection) {
    $sql = "SELECT recipeID, recipeName, recipeDescription, recipeImage, recipeTime FROM RECIPES";
    
    if ($stmt = $connection->prepare($sql)) {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            }
        } else {
            // Execution failed
            error_log("GetAllRecipes() execution failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Preparation failed
        error_log("GetAllRecipes() preparation failed: " . $connection->error);
    }

    return []; // Return empty array if nothing found or error occurred
}

?>

