<?php
require 'config/database.php';
require 'vendor/autoload.php'; // For JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateUserId() {
    global $conn;
    $query = "SELECT COUNT(*) AS count FROM users";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    return 'UID' . str_pad($count, 2, '0', STR_PAD_LEFT);
}

function signupUser() {
    global $conn;

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $phone_number = $_POST['phone_number'];

    // Check if user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['message' => 'This Mobile number has already been registered']);
        return;
    }

    $user_id = generateUserId();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (user_id, username, email, password, phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $user_id, $username, $email, $hashedPassword, $phone_number);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'User registered successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error during signup']);
    }
}

function loginUser() {
    global $conn;

    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ?");
    $stmt->bind_param("s", $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid credentials']);
        return;
    }

    $userData = $result->fetch_assoc();
    if (!password_verify($password, $userData['password'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid credentials']);
        return;
    }

    $payload = [
        'id' => $userData['id'],
        'isAdmin' => $userData['isAdmin']
    ];

    $token = JWT::encode($payload, getenv('JWT_SECRET'), 'HS256');

    echo json_encode([
        'username' => $userData['username'],
        'user_id' => $userData['user_id'],
        'token' => $token,
        'message' => 'Login successful'
    ]);
}

function updateUserRole($userId) {
    global $conn;

    $isAdmin = $_POST['isAdmin'];

    $stmt = $conn->prepare("UPDATE users SET isAdmin = ? WHERE user_id = ?");
    $stmt->bind_param("is", $isAdmin, $userId);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['message' => 'User role updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Internal server error']);
    }
}

function getUsers() {
    global $conn;

    $query = "SELECT id, user_id, username, email, isAdmin, phone_number FROM users";
    $result = $conn->query($query);

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    http_response_code(200);
    echo json_encode($users);
}

function getCurrentUser() {
    global $conn;

    $userId = $_GET['id'];  // Assuming user ID is passed via query parameters

    $stmt = $conn->prepare("SELECT id, user_id, username, email, isAdmin FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['message' => 'User not found']);
        return;
    }

    $user = $result->fetch_assoc();
    echo json_encode($user);
}

function getUserWishlist($userId) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT w.*, p.image 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.product_id 
        WHERE w.user_id = ?
    ");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $wishlistItems = [];
    while ($row = $result->fetch_assoc()) {
        $row['image'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . str_replace('\\', '/', $row['image']);
        $wishlistItems[] = $row;
    }

    http_response_code(200);
    echo json_encode($wishlistItems);
}

function getUserCart($userId) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT c.*, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $cartItems = [];
    while ($row = $result->fetch_assoc()) {
        $row['image'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . str_replace('\\', '/', $row['image']);
        $cartItems[] = $row;
    }

    http_response_code(200);
    echo json_encode($cartItems);
}

function toggleCartItem($userId) {
    global $conn;

    $productId = $_POST['productId'];
    $quantity = $_POST['quantity'];
    $product_name = $_POST['product_name'];
    $image = $_POST['image'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ss", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ss", $userId, $productId);
        $stmt->execute();
        echo json_encode(['message' => 'Item removed from cart', 'isAdded' => false]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, product_name, image, price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $userId, $productId, $quantity, $product_name, $image, $price);
        $stmt->execute();
        echo json_encode(['message' => 'Item added to cart', 'isAdded' => true]);
    }
}

function toggleWishlistItem($userId) {
    global $conn;

    $productId = $_POST['productId'];
    $product_name = $_POST['product_name'];
    $image = $_POST['image'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ss", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ss", $userId, $productId);
        $stmt->execute();
        echo json_encode(['message' => 'Item removed from wishlist', 'isAdded' => false]);
    } else {
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id, product_name, image, price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $userId, $productId, $product_name, $image, $price);
        $stmt->execute();
        echo json_encode(['message' => 'Item added to wishlist', 'isAdded' => true]);
    }
}
?>
