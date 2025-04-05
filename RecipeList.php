<?php
include "./dbinfo.inc";
include "./recipes.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if (mysqli_connect_errno()) {
    echo "<p>Failed to connect to MySQL: " . mysqli_connect_error() . "</p>";
}
mysqli_select_db($connection, DB_DATABASE);

// Get all recipes
$recipes = GetAllRecipes($connection);

// Handle login/signup
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Recipe List</title>
  <link rel="stylesheet" href="styles.css" />
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

<!-- Search and Navigation -->
<div class="container">
  <div class="search-bar">
    <input type="text" placeholder="Search for recipes..." class="search-input">
  </div>
  <div class="links">
    <?php if (isset($_SESSION['username'])): ?>
      <button class="nav-btn" onclick="window.location.href='CreateRecipe.php'">Create Recipe</button>
      <button class="nav-btn" onclick="window.location.href='BookmarkedRecipes.php'">View Bookmarked Recipes</button>
    <?php endif; ?>
  </div>
  <div class="recipe-list">
    <!-- Recipes will be loaded dynamically -->
  </div>
</div>

<!-- Overlay for popups -->
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

  function displayRecipes() {
    const recipeList = document.querySelector(".recipe-list");
    recipeList.innerHTML = "";

    recipes.forEach(recipe => {
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
    const filteredRecipes = recipes.filter(recipe =>
      recipe.recipeName.toLowerCase().includes(searchInput) ||
      recipe.recipeDescription.toLowerCase().includes(searchInput)
    );
    displayFilteredRecipes(filteredRecipes);
  }

  function displayFilteredRecipes(filteredRecipes) {
    const recipeList = document.querySelector(".recipe-list");
    recipeList.innerHTML = "";

    if (filteredRecipes.length === 0) {
      recipeList.innerHTML = "<p>No recipes found.</p>";
      return;
    }

    filteredRecipes.forEach(recipe => {
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

  document.addEventListener("DOMContentLoaded", () => {
    displayRecipes();
    document.querySelector(".search-input").addEventListener("input", searchRecipes);
  });
</script>

<script src="scripts.js"></script>

<?php mysqli_close($connection); ?>
</body>
</html>