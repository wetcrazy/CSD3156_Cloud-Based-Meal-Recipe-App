CREATE DATABASE IF NOT EXISTS recipe;

USE recipe;

-- Create the USERS table
CREATE TABLE IF NOT EXISTS USERS (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    userName VARCHAR(50) NOT NULL UNIQUE,
    userPassword VARCHAR(255) NOT NULL,
    userBookmarkedRecipes TEXT
);

-- Create the RECIPES table
CREATE TABLE IF NOT EXISTS RECIPES (
    recipeID INT AUTO_INCREMENT PRIMARY KEY,
    recipeName VARCHAR(100) NOT NULL,
    recipeDescription TEXT NOT NULL,
    recipeImage VARCHAR(255) NOT NULL,
    recipeTime INT NOT NULL,
    recipeSteps TEXT NOT NULL,
    userName VARCHAR(50) NOT NULL,
    recipeIngredients TEXT NOT NULL,
    FOREIGN KEY (userName) REFERENCES USERS(userName) ON DELETE CASCADE
);

-- Create the INGREDIENTS table
CREATE TABLE IF NOT EXISTS INGREDIENTS (
    ingredientID INT AUTO_INCREMENT PRIMARY KEY,
    ingredientName VARCHAR(50) NOT NULL,
    ingredientUnit VARCHAR(50) NOT NULL
);
