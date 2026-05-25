<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':       getProducts();     break;
    case 'single':     getProduct();      break;
    case 'categories': getCategories();   break;
    case 'search':     searchProducts();  break;
    case 'add':        addProduct();      break;
    case 'update':     updateProduct();   break;
    case 'delete':     deleteProduct();   break;
    default:           jsonResponse(['error' => 'Invalid action'], 400);
}

function getProducts() {
    $conn = getConnection();
    $cat  = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    $where = $cat ? "WHERE p.category_id = $cat" : "";

    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            $where 
            ORDER BY p.created_at DESC";

    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) $products[] = $row;
    jsonResponse(['products' => $products]);
}

function getProduct() {
    $conn = getConnection();
    $id   = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID required'], 400);

    $result = $conn->query("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = $id");
    if ($result->num_rows === 0) jsonResponse(['error' => 'Product not found'], 404);
    jsonResponse(['product' => $result->fetch_assoc()]);
}

function getCategories() {
    $conn   = getConnection();
    $result = $conn->query("SELECT * FROM categories");
    $cats   = [];
    while ($row = $result->fetch_assoc()) $cats[] = $row;
    jsonResponse(['categories' => $cats]);
}

function searchProducts() {
    $conn = getConnection();
    $q    = sanitize($conn, $_GET['q'] ?? '');
    if (!$q) jsonResponse(['products' => []]);

    $result = $conn->query("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.name LIKE '%$q%' OR p.description LIKE '%$q%'");
    $products = [];
    while ($row = $result->fetch_assoc()) $products[] = $row;
    jsonResponse(['products' => $products]);
}

function addProduct() {
    if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 403);
    $conn  = getConnection();
    $name  = sanitize($conn, $_POST['name'] ?? '');
    $desc  = sanitize($conn, $_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $cat   = (int)($_POST['category_id'] ?? 0);
    $rx    = isset($_POST['requires_prescription']) ? 1 : 0;

    if (!$name || !$price) jsonResponse(['error' => 'Name and price required'], 400);

    $conn->query("INSERT INTO products (category_id, name, description, price, stock, requires_prescription) 
                  VALUES ($cat, '$name', '$desc', $price, $stock, $rx)");
    jsonResponse(['success' => true, 'message' => 'Product added']);
}

function updateProduct() {
    if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 403);
    $conn  = getConnection();
    $id    = (int)($_POST['id'] ?? 0);
    $name  = sanitize($conn, $_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $desc  = sanitize($conn, $_POST['description'] ?? '');

    $conn->query("UPDATE products SET name='$name', description='$desc', price=$price, stock=$stock WHERE id=$id");
    jsonResponse(['success' => true, 'message' => 'Product updated']);
}

function deleteProduct() {
    if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 403);
    $conn = getConnection();
    $id   = (int)($_GET['id'] ?? 0);
    $conn->query("DELETE FROM products WHERE id=$id");
    jsonResponse(['success' => true, 'message' => 'Product deleted']);
}
?>
