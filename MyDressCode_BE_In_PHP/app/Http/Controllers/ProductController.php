<?php
include('../config/database.php'); // Adjust the path as needed

function generateCategoryId($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) AS count FROM categories');
    $row = $stmt->fetch();
    $count = $row['count'] + 1; // Increment by 1 for the new ID
    return 'CAT' . str_pad($count, 2, '0', STR_PAD_LEFT); // Generate ID like CAT01, CAT02, etc.
}

function generateProductId($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) AS count FROM products');
    $row = $stmt->fetch();
    $count = $row['count'] + 1; // Increment by 1 for the new ID
    return 'MDC' . str_pad($count, 2, '0', STR_PAD_LEFT); // Generate ID like MDC01, MDC02, etc.
}

// File upload logic using PHP's $_FILES
$target_dir = "uploads/";
$imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
if($check === false) {
    echo json_encode(['message' => 'File is not an image.']);
    exit; // Exit if the file is not an image
}

// Check if file already exists
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
if (file_exists($target_file)) {
    echo json_encode(['message' => 'Sorry, file already exists.']);
    exit;
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo json_encode(['message' => 'Sorry, your file is too large.']);
    exit;
}

// Allow certain file formats
if(!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
    echo json_encode(['message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.']);
    exit;
}

// Attempt to upload file
if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo json_encode(['message' => 'Sorry, there was an error uploading your file.']);
    exit;
}

// Proceed with adding category
function addCategory($pdo) {
    global $target_file; // Use the global variable for the target file
    try {
        $category = $_POST['category'];
        $subcategory = $_POST['subcategory'];
        $discount = isset($_POST['discount']) ? $_POST['discount'] : null;

        if (!$category || !$subcategory) {
            http_response_code(400);
            echo json_encode(['message' => 'Category and subcategory are required']);
            return;
        }

        $image = $target_file;
        $categoryId = generateCategoryId($pdo);
        $discountValue = $discount == '' || $discount === null ? null : $discount;

        $sql = 'INSERT INTO categories (category_id, category, subcategory, discount, image) VALUES (?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoryId, $category, $subcategory, $discountValue, $image]);

        echo json_encode(['message' => 'Category added successfully', 'categoryId' => $categoryId]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Error adding category']);
    }
}

function getCategory($pdo) {
    try {
        // Fetch distinct categories
        $stmt = $pdo->query('SELECT DISTINCT category FROM categories');
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Fetch distinct subcategories
        $stmt = $pdo->query('SELECT DISTINCT subcategory FROM categories');
        $subcategories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Return the result in a JSON response
        echo json_encode([
            'categories' => $categories,
            'subcategories' => $subcategories
        ]);
    } catch (Exception $e) {
        error_log('Error fetching categories: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Server error while fetching categories']);
    }
}

function addProduct($pdo) {
    global $target_file; // Use the global variable for the target file
    $name = $_POST['name'] ?? '';
    $subcategory = $_POST['subcategory'] ?? '';
    $categoryId = $_POST['categoryId'] ?? '';
    $size = $_POST['size'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $originalAmount = $_POST['originalAmount'] ?? 0;
    $discountAmount = $_POST['discountAmount'] ?? null;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';

    if (!isset($_FILES["fileToUpload"])) {
        http_response_code(400);
        echo json_encode(['message' => 'Image file is required']);
        return;
    }

    $image = $target_file;
    $productId = generateProductId($pdo);

    try {
        // Fetch the category name from the categories table using the provided categoryId
        $stmt = $pdo->prepare('SELECT category FROM categories WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        $categoryRow = $stmt->fetch();

        if (!$categoryRow) {
            http_response_code(404);
            echo json_encode(['message' => 'Category not found']);
            return;
        }

        $categoryName = $categoryRow['category'];

        // Insert the product into the products table
        $sql = 'INSERT INTO products (category_id, product_id, category, name, subcategory, size, brand, image, originalAmount, discountAmount, stock, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoryId, $productId, $categoryName, $name, $subcategory, $size, $brand, $image, $originalAmount, $discountAmount, $stock, $description]);

        echo json_encode(['message' => 'Product added successfully!', 'productId' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        error_log('Error adding product: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['message' => 'Error adding product', 'error' => $e->getMessage()]);
    }
}

// Other functions such as getCategoryIdBySubcategory, getTopCategories, etc., would follow the same pattern.
// You can continue converting the rest of the code similarly.
?>
