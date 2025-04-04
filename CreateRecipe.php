<?php
include "./dbinfo.inc"; 
session_start();

$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if (mysqli_connect_errno()) {
    die("<p>Failed to connect to MySQL: " . mysqli_connect_error() . "</p>");
}
mysqli_select_db($connection, DB_DATABASE);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION['username'])) {
    $recipeName = mysqli_real_escape_string($connection, $_POST['recipeName']);
    $recipeDescription = mysqli_real_escape_string($connection, $_POST['recipeDescription']);
    $recipeTime = (int) $_POST['recipeTimeTaken'];
    $recipeSteps = mysqli_real_escape_string($connection, $_POST['recipeSteps']);
    $userName = $_SESSION['username'];

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['recipeImage']) && $_FILES['recipeImage']['error'] === UPLOAD_ERR_OK) {
      $imagePath = 'images/placeholder.jpg';
    }

    // Insert into RECIPES table
    $insertRecipe = "INSERT INTO RECIPES (recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps, userName)
                     VALUES ('$recipeName', '$recipeDescription', '$imagePath', $recipeTime, '$recipeSteps', '$userName')";

    if (mysqli_query($connection, $insertRecipe)) {
        $recipeID = mysqli_insert_id($connection);

        // Handle ingredients
        $ingredientIDs = $_POST['ingredient_ids'];
        $quantities = $_POST['quantities'];

        for ($i = 0; $i < count($ingredientIDs); $i++) {
            $ingredientID = (int) $ingredientIDs[$i];
            $amount = floatval($quantities[$i]);

            $insertIngredient = "INSERT INTO RECIPE_INGREDIENTS (recipeID, ingredientID, ingredientAmount)
                                 VALUES ($recipeID, $ingredientID, $amount)";
            mysqli_query($connection, $insertIngredient);
        }

        echo "<script>alert('Recipe created successfully!');</script>";
    } else {
        echo "<script>alert('Error saving recipe.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Recipe</title>
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
      <button onclick="location.href='index.php'">Login</button>
    <?php else: ?>
      <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
      <a href="logout.php"><button>Logout</button></a>
    <?php endif; ?>
  </div>
</div>

<?php if (isset($_SESSION['username'])): ?>
  <div class="create-recipe-form">
    <form action="CreateRecipe.php" method="POST" enctype="multipart/form-data">
      <label for="recipeName">Recipe Name:</label>
      <input type="text" name="recipeName" required>

      <label for="recipeDescription">Description:</label>
      <textarea name="recipeDescription" rows="3" required></textarea>

      <label for="recipeTimeTaken">Time Taken (minutes):</label>
      <input type="text" name="recipeTimeTaken" required>

      <label for="recipeSteps">Steps:</label>
      <textarea name="recipeSteps" rows="6" required></textarea>

      <label>Ingredients & Quantities:</label>
      <div class="ingredient-list" id="ingredient-list">
        <div class="ingredient-group">
          <select name="ingredient_ids[]" required>
            <option value="">-- Select Ingredient --</option>
            <?php
              $result = mysqli_query($connection, "SELECT ingredientID, ingredientName, ingredientUnit FROM INGREDIENTS");
              while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['ingredientID']}'>{$row['ingredientName']} ({$row['ingredientUnit']})</option>";
              }
            ?>
          </select>
          <input type="text" name="quantities[]" placeholder="e.g. 2.5" required>
          <button type="button" class="remove-btn" onclick="removeIngredient(this)">Remove</button>
        </div>
      </div>

      <button type="button" class="submit-btn" onclick="addIngredient()">+ Add Ingredient</button>

      <label for="recipeImage">Upload Image:</label>
      <input type="file" name="recipeImage" accept="image/*" required>

      <button type="submit" class="submit-btn">Create Recipe</button>
    </form>
  </div>
<?php else: ?>
  <div class="create-recipe-form">
    <p><strong>You must be logged in to create a recipe.</strong></p>
    <a href="index.php"><button class="submit-btn">Go to Home / Login</button></a>
  </div>
<?php endif; ?>

<script>
function addIngredient() {
  const list = document.getElementById('ingredient-list');
  const newGroup = document.createElement('div');
  newGroup.className = 'ingredient-group';

  const dropdown = `
    <select name="ingredient_ids[]" required>
      <option value="">-- Select Ingredient --</option>
      <?php
        $result = mysqli_query($connection, "SELECT ingredientID, ingredientName, ingredientUnit FROM INGREDIENTS");
        while ($row = mysqli_fetch_assoc($result)) {
          echo "<option value='{$row['ingredientID']}'>{$row['ingredientName']} ({$row['ingredientUnit']})</option>";
        }
      ?>
    </select>`;

  newGroup.innerHTML = `
    ${dropdown}
    <input type="text" name="quantities[]" placeholder="e.g. 1.5" required>
    <button type="button" class="remove-btn" onclick="removeIngredient(this)">Remove</button>
  `;
  list.appendChild(newGroup);
}

function removeIngredient(btn) {
  btn.parentElement.remove();
}
</script>

<?php mysqli_close($connection); ?>
</body>
</html>
