<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe List</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS file -->
    <script>
        function bookmarkRecipe() {
            alert("Recipe saved!");
        }

        function goToRecipePage(recipeId) {
            // Navigate to the recipe page with the specific recipe ID
            // Replace 'RecipePage.html' with the actual URL structure for your recipe pages
            window.location.href = `RecipePage.php?id=${recipeId}`;
        }

        // Function to dynamically display recipes
        function displayRecipes() {
            const recipeList = document.querySelector(".recipe-list");
            recipeList.innerHTML = ""; // Clear existing content

            recipes.forEach(recipe => {
                const recipeDiv = document.createElement("div");
                recipeDiv.classList.add("recipe");

                recipeDiv.innerHTML = `
                    <a href="javascript:void(0);" onclick="goToRecipePage(${recipe.id})" class="recipe-link">
                        <div class="recipe-box">
                            <div class="recipe-thumbnail">
                                <img src="${recipe.thumbnail}" alt="${recipe.name} Thumbnail">
                            </div>
                            <div class="recipe-details">
                                <h3 class="recipe-title">${recipe.name}</h3>
                                <p class="recipe-description">${recipe.description}</p>
                                <p class="recipe-cooking-time"><strong>Cooking Time:</strong> ${recipe.cookingTime}</p>
                            </div>
                            <div class="recipe-actions">
                                <button class="bookmark-btn" onclick="event.stopPropagation(); bookmarkRecipe()">Bookmark</button>
                            </div>
                        </div>
                    </a>
                `;

                recipeList.appendChild(recipeDiv);
            });
        }

        // Example recipes array (replace this with data fetched from your database or API)
        const recipes = [
            {
                id: 1,
                name: "Spaghetti Bolognese",
                description: "A classic Italian pasta dish with rich meat sauce.",
                cookingTime: "45 minutes",
                thumbnail: "spaghetti.jpg"
            },
            {
                id: 2,
                name: "Chicken Curry",
                description: "A flavorful curry with tender chicken pieces.",
                cookingTime: "60 minutes",
                thumbnail: "chicken-curry.jpg"
            },
            {
                id: 3,
                name: "Vegetable Stir Fry",
                description: "A quick and healthy stir fry with fresh vegetables.",
                cookingTime: "20 minutes",
                thumbnail: "stir-fry.jpg"
            }
        ];

        // Call the function to display recipes when the page loads
        document.addEventListener("DOMContentLoaded", displayRecipes);
    </script>
</head>
<body>
    <div class="navbar">
        <a href="Index.php" class="navbar-title">
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
    <div class="container">
        <div class="search-bar">
            <input type="text" placeholder="Search for recipes..." class="search-input">
            <button class="search-btn">Search</button>
        </div>
        <div class="links">
        <?php if (isset($_SESSION['username'])): ?>
            <button class="nav-btn" onclick="window.location.href='CreateRecipe.html'">Create Recipe</button>
            <button class="nav-btn" onclick="window.location.href='BookmarkedRecipes.html'">View Bookmarked Recipes</button>
        <?php endif; ?>
        </div>
        <div class="recipe-list">
            <!-- Recipes will be dynamically loaded here -->
        </div>
    </div>
</body>
</html>