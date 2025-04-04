<?php 
  include "./dbinfo.inc"; 
  include "./recipes.php"; // Include the recipes functions

  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Online Cookbook</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php
    $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

    if (mysqli_connect_errno()) {
        echo "<p>Failed to connect to MySQL: " . mysqli_connect_error() . "</p>";
    }

    $database = mysqli_select_db($connection, DB_DATABASE);

    $featuredRecipe = null;
    // $randomRecipeQuery = "SELECT * FROM RECIPES ORDER BY RAND() LIMIT 1";
    // $result = mysqli_query($connection, $randomRecipeQuery);
    // if ($result && mysqli_num_rows($result) > 0) {
    //     $featuredRecipe = mysqli_fetch_assoc($result);
    // }
    $featuredRecipe = GetRandomRecipe($connection);

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

<!-- Hero Section -->
<div class="hero">
  <h1>Discover & Share Your Favorite Recipes!</h1>
  <a href="RecipeList.php"><button class="discover-button">Discover Now!</button></a>
</div>

<!-- Featured Recipe -->
<section class="recipes">
  <h2>Featured Recipe</h2>
  <?php if ($featuredRecipe): ?>
    <div class="featured-recipe">
      <div class="recipe-card" onclick="window.location.href='RecipePage.php?id=<?php echo $featuredRecipe['recipeID']; ?>'">
        <img src="<?php echo htmlspecialchars($featuredRecipe['recipeImage']); ?>" alt="<?php echo htmlspecialchars($featuredRecipe['recipeName']); ?>">
        <h3><?php echo htmlspecialchars($featuredRecipe['recipeName']); ?></h3>
        <p><?php echo htmlspecialchars($featuredRecipe['recipeDescription']); ?></p>
      </div>
    </div>
  <?php else: ?>
    <p>No recipes available to feature.</p>
  <?php endif; ?>
</section>

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

<script src="scripts.js"></script>
<script>
  let recipeId;

  window.onload = fetchRandomRecipe;

  function goToRecipePage() {
    if (recipeId) {
      window.location.href = `RecipePage.php?id=${recipeId}`;
    }
  }
</script>

<?php
  /* Close DB connection */
  mysqli_close($connection);
?>

</body>
</html>
