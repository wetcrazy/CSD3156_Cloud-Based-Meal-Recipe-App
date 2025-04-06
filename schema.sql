USE foodrecipe_db1;

-- Create the USERS table
CREATE TABLE IF NOT EXISTS USERS (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    userName VARCHAR(50) NOT NULL UNIQUE,
    userPassword VARCHAR(255) NOT NULL
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
    FOREIGN KEY (userName) REFERENCES USERS(userName) ON DELETE CASCADE
);

-- Create the INGREDIENTS table
CREATE TABLE IF NOT EXISTS INGREDIENTS (
    ingredientID INT AUTO_INCREMENT PRIMARY KEY,
    ingredientName VARCHAR(50) NOT NULL,
    ingredientUnit VARCHAR(50) NOT NULL
);

-- Create the BOOKMARKS table to link USERS to RECIPES they bookmarked
CREATE TABLE IF NOT EXISTS BOOKMARKS (
    bookmarkID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    recipeID INT,
    FOREIGN KEY (userID) REFERENCES USERS(userID) ON DELETE CASCADE,
    FOREIGN KEY (recipeID) REFERENCES RECIPES(recipeID) ON DELETE CASCADE
);

-- Create the RECIPE_INGREDIENTS table to link RECIPES with INGREDIENTS and the quantity used
CREATE TABLE IF NOT EXISTS RECIPE_INGREDIENTS (
    recipeID INT,
    ingredientID INT,
    ingredientAmount DECIMAL(10, 2),
    PRIMARY KEY (recipeID, ingredientID),
    FOREIGN KEY (recipeID) REFERENCES RECIPES(recipeID) ON DELETE CASCADE,
    FOREIGN KEY (ingredientID) REFERENCES INGREDIENTS(ingredientID) ON DELETE CASCADE
);

-- Insert Users
INSERT INTO USERS (userName, userPassword) 
VALUES 
    ('john_doe', 'hashed_password1'),
    ('jane_smith', 'hashed_password2'),
    ('sam_brown', 'hashed_password3');

-- Insert Ingredients
INSERT INTO INGREDIENTS (ingredientName, ingredientUnit) 
VALUES 
    ('Sugar', 'grams'),
    ('Flour', 'grams'),
    ('Eggs', 'pieces'),
    ('Butter', 'grams'),
    ('Salt', 'grams');

-- Insert Recipes
INSERT INTO RECIPES (recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps, userName) 
VALUES 
    ('Chocolate Cake', 'A rich and moist chocolate cake.', 'https://foodrecipe-bucket-1.s3.amazonaws.com/public/chocolate_cake.jpg', 60, '1. Preheat oven. 2. Mix ingredients. 3. Bake for 45 minutes.', 'john_doe'),
    ('Pancakes', 'Fluffy and delicious pancakes.', 'https://foodrecipe-bucket-1.s3.amazonaws.com/public/pancakes.jpg', 30, '1. Mix ingredients. 2. Cook on skillet. 3. Serve with syrup.', 'jane_smith'),
    ('Scrambled Eggs', 'Quick and easy scrambled eggs.', 'https://foodrecipe-bucket-1.s3.amazonaws.com/public/scrambled_eggs.jpg', 10, '1. Whisk eggs. 2. Cook in pan. 3. Serve with toast.', 'sam_brown');

-- Insert Bookmarks with correct `recipeID` values
INSERT INTO BOOKMARKS (userID, recipeID) 
VALUES 
    (1, 4),  -- john_doe bookmarks Chocolate Cake (recipeID 4)
    (2, 5),  -- jane_smith bookmarks Pancakes (recipeID 5)
    (3, 6);  -- sam_brown bookmarks Scrambled Eggs (recipeID 6)

-- Insert Recipe Ingredients with correct `ingredientID` values
INSERT INTO RECIPE_INGREDIENTS (recipeID, ingredientID, ingredientAmount) 
VALUES 
    (4, 1, 200.00),  -- Chocolate Cake requires 200g of Sugar (ingredientID 1)
    (4, 2, 250.00),  -- Chocolate Cake requires 250g of Flour (ingredientID 2)
    (4, 4, 150.00),  -- Chocolate Cake requires 150g of Butter (ingredientID 4)
    (5, 1, 100.00),  -- Pancakes require 100g of Sugar (ingredientID 1)
    (5, 2, 150.00),  -- Pancakes require 150g of Flour (ingredientID 2)
    (5, 3, 2.00),    -- Pancakes require 2 Eggs (ingredientID 3)
    (6, 3, 3.00),    -- Scrambled Eggs require 3 Eggs (ingredientID 3)
    (6, 4, 50.00);   -- Scrambled Eggs require 50g of Butter (ingredientID 4)
