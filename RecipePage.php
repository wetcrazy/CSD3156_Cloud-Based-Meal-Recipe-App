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
    <title>Recipe Page</title>
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
            <h1 id="recipe-title">Loading...</h1>
            <button id="bookmarkBtn" class="bookmark-btn">Bookmark</button>
        </div>
        <p id="recipe-description">Fetching recipe details...</p>

        <!-- Recipe Image -->
        <div class="recipe-images">
            <img id="recipe-image" src="" alt="Recipe Image">
        </div>

        <!-- Ingredients Section -->
        <div class="recipe-section">
            <h2>Ingredients</h2>
            <ul id="ingredients-list"></ul>
        </div>

        <!-- Instructions Section -->
        <div class="recipe-section">
            <h2>Instructions</h2>
            <ol id="instructions-list"></ol>
        </div>
    </div>

    <!-- Overlay for popups -->
    <div class="popup-overlay" id="popupOverlay" onclick="closePopup()"></div>

    <!-- Login Popup -->
    <div id="loginPopup" class="popup">
        <span class="close" onclick="closePopup()">&times;</span>
        <h2>Login</h2>
        <input type="text" id="loginUsername" placeholder="Username">
        <input type="password" id="loginPassword" placeholder="Password">
        <button onclick="login()">Login</button>
    </div>

    <!-- Signup Popup -->
    <div id="signupPopup" class="popup">
        <span class="close" onclick="closePopup()">&times;</span>
        <h2>Signup</h2>
        <input type="text" id="signupUsername" placeholder="Username">
        <input type="password" id="signupPassword" placeholder="Password">
        <button onclick="signup()">Sign Up</button>
    </div>

    <script src="scripts.js"></script>
    <script>
        const bookmarkBtn = document.getElementById("bookmarkBtn");
        const recipeTitle = document.getElementById("recipe-title");
        const recipeDescription = document.getElementById("recipe-description");
        const recipeImage = document.getElementById("recipe-image");
        const ingredientsList = document.getElementById("ingredients-list");
        const instructionsList = document.getElementById("instructions-list");

        async function fetchRecipe(recipeId) {
            try {
                // Fetch recipe from AWS RDS backend API
                const response = await fetch(`http://3.86.67.125/api/recipes/${recipeId}`); // Replace with actual backend URL
                const data = await response.json();

                // Update UI with fetched data
                recipeTitle.textContent = data.name;
                recipeDescription.textContent = data.description;
                recipeImage.src = data.image;
                recipeImage.alt = data.name;

                ingredientsList.innerHTML = data.ingredients.map(ingredient => `<li>${ingredient}</li>`).join('');
                instructionsList.innerHTML = data.instructions.map(step => `<li>${step}</li>`).join('');
            } catch (error) {
                console.error("Error fetching recipe:", error);
            }
        }

        function goBack() {
           window.location.href = "RecipeList.php"; // Redirect to Recipe List
        }

        // Example: Fetch recipe with ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const recipeId = urlParams.get('id') || 1;
        fetchRecipe(recipeId); // Fetch recipe on page load
    </script>

</body>
</html>
