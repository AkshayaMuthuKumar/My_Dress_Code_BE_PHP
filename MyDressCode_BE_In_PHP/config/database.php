<?php

// Database connection configuration
$host = 'localhost:3307';
$user = 'root';
$password = ''; // Replace with your MySQL password
$database = 'MyDressCode';

// Create a connection to the MySQL database
$connection = mysqli_connect($host, $user, $password, $database);

// Set the character set to utf8mb4 for better compatibility with Unicode
mysqli_set_charset($connection, 'utf8mb4');

// Check if the connection was successful
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Queries to create tables
$usersTable = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15) NOT NULL UNIQUE,
    isAdmin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

$categoriesTable = "
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id VARCHAR(10) NOT NULL UNIQUE,
    category VARCHAR(255) NOT NULL,
    subcategory VARCHAR(255) NOT NULL,
    discount DECIMAL(5, 2) DEFAULT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

$productTable = "
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id VARCHAR(255) NOT NULL,
    product_id VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    subcategory VARCHAR(255) NOT NULL,
    size VARCHAR(50) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    image VARCHAR(255),
    originalAmount DECIMAL(10, 2),
    discountAmount DECIMAL(10, 2),
    stock INT,
    description VARCHAR(255),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) 
);";

$reviewTable = "
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,              
    productId VARCHAR(255) NOT NULL,                         
    name VARCHAR(255) NOT NULL,                     
    email VARCHAR(255) NOT NULL,                    
    purchaseDate DATE NOT NULL,                     
    experience VARCHAR(255) NOT NULL,              
    rating INT CHECK (rating >= 1 AND rating <= 5), 
    review TEXT,                                   
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (productId) REFERENCES products(product_id) ON DELETE CASCADE
);";

$wishlistTable = "
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    product_id VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    price DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE (user_id, product_id)
);";

$addToCartTable = "
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    product_id VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    quantity INT DEFAULT 1,
    price DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    UNIQUE (user_id, product_id)
);";

// Array of all table queries
$tableQueries = [
    $usersTable,
    $categoriesTable,
    $productTable,
    $reviewTable,
    $wishlistTable,
    $addToCartTable
];

// Execute each query to create the tables
foreach ($tableQueries as $tableQuery) {
    if (mysqli_query($connection, $tableQuery)) {
        echo "Table created successfully.<br>";
    } else {
        echo "Error creating table: " . mysqli_error($connection) . "<br>";
    }
}

// Close the connection
mysqli_close($connection);
?>
