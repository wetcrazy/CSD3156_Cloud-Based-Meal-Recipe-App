const express = require('express');
const mysql = require('mysql2');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(cors());

const SECRET_KEY = "your_secret_key"; // Change this to a secure key

// MySQL Database Connection
const db = mysql.createConnection({
    host: 'database-1.c9bnuoglnn21.us-east-1.rds.amazonaws.com', // Change to 'localhost' if running MySQL locally
    user: 'admin',
    password: 'testtest',
    database: 'recipe'
});

db.connect(err => {
    if (err) {
        console.error('Database connection failed:', err);
        process.exit(1);
    }
    console.log('Connected to MySQL');
});

// Helper function: Verify JWT token middleware
function authenticateToken(req, res, next) {
    const token = req.headers['authorization'];
    if (!token) return res.sendStatus(401);
    
    jwt.verify(token, SECRET_KEY, (err, user) => {
        if (err) return res.sendStatus(403);
        req.user = user;
        next();
    });
}

// **1. Signup**
app.post('/signup', async (req, res) => {
    const { username, password } = req.body;

    db.query('SELECT * FROM USERS WHERE userName = ?', [username], async (err, results) => {
        if (err) return res.status(500).json({ message: "Database error" });
        if (results.length > 0) return res.status(400).json({ message: "Username already taken" });

        const hashedPassword = await bcrypt.hash(password, 10);
        db.query('INSERT INTO USERS (userName, userPassword) VALUES (?, ?)', [username, hashedPassword], (err) => {
            if (err) return res.status(500).json({ message: "Signup failed" });
            res.json({ message: "Signup successful" });
        });
    });
});

// **2. Login**
app.post('/login', (req, res) => {
    const { username, password } = req.body;

    db.query('SELECT * FROM USERS WHERE userName = ?', [username], async (err, results) => {
        if (err) return res.status(500).json({ message: "Database error" });
        if (results.length === 0) return res.status(400).json({ message: "User not found" });

        const user = results[0];
        const validPassword = await bcrypt.compare(password, user.userPassword);

        if (!validPassword) return res.status(401).json({ message: "Incorrect password" });

        const token = jwt.sign({ username: user.userName }, SECRET_KEY, { expiresIn: '1h' });
        res.json({ message: "Login successful", token });
    });
});

// **3. Bookmark Recipe**
app.post('/bookmark', authenticateToken, (req, res) => {
    const { recipeID } = req.body;
    const username = req.user.username;

    db.query('SELECT userBookmarkedRecipes FROM USERS WHERE userName = ?', [username], (err, results) => {
        if (err) return res.status(500).json({ message: "Database error" });

        let bookmarks = results[0].userBookmarkedRecipes ? JSON.parse(results[0].userBookmarkedRecipes) : [];
        if (!bookmarks.includes(recipeID)) {
            bookmarks.push(recipeID);
        }

        db.query('UPDATE USERS SET userBookmarkedRecipes = ? WHERE userName = ?', [JSON.stringify(bookmarks), username], (err) => {
            if (err) return res.status(500).json({ message: "Bookmarking failed" });
            res.json({ message: "Recipe bookmarked successfully" });
        });
    });
});

// **4. Create Recipe**
app.post('/recipe', authenticateToken, (req, res) => {
    const { recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps, recipeIngredients } = req.body;
    const username = req.user.username;

    db.query('INSERT INTO RECIPES (recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps, userName, recipeIngredients) VALUES (?, ?, ?, ?, ?, ?, ?)', 
    [recipeName, recipeDescription, recipeImage, recipeTime, recipeSteps, username, JSON.stringify(recipeIngredients)], (err) => {
        if (err) return res.status(500).json({ message: "Recipe creation failed" });
        res.json({ message: "Recipe created successfully" });
    });
});

// **5. Create Ingredient (Only If It Doesn't Exist)**
app.post('/ingredient', authenticateToken, (req, res) => {
    const { ingredientName, ingredientUnit } = req.body;

    db.query('SELECT * FROM INGREDIENTS WHERE ingredientName = ?', [ingredientName], (err, results) => {
        if (err) return res.status(500).json({ message: "Database error" });

        if (results.length > 0) {
            return res.json({ message: "Ingredient already exists" });
        }

        db.query('INSERT INTO INGREDIENTS (ingredientName, ingredientUnit) VALUES (?, ?)', [ingredientName, ingredientUnit], (err) => {
            if (err) return res.status(500).json({ message: "Failed to add ingredient" });
            res.json({ message: "Ingredient added successfully" });
        });
    });
});

// **Start Server**
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server running on http://your-ec2-public-ip:${PORT}`);
});
