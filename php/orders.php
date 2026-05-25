<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add_to_cart':    addToCart();     break;
    case 'get_cart':       getCart();       break;
    case 'update_cart':    updateCart();    break;
    case 'remove_cart':    removeFromCart();break;
    case 'clear_cart':     clearCart();     break;
    case 'place_order':    placeOrder();    break;
    case 'my_orders':      myOrders();      break;
    case 'all_orders':     allOrders();     break;
    case 'update_status':  updateStatus();  break;
    default: jsonResponse(['error' => 'Invalid action'], 400);
}

function addToCart() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Please login first'], 401);
    $conn       = getConnection();
    $user_id    = (int)$_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty        = (int)($_POST['quantity'] ?? 1);

    // Check stock
    $prod = $conn->query("SELECT stock FROM products WHERE id=$product_id")->fetch_assoc();
    if (!$prod || $prod['stock'] < $qty) {
        jsonResponse(['error' => 'Insufficient stock'], 400);
    }

    // Check if already in cart
    $existing = $conn->query("SELECT id, quantity FROM cart WHERE user_id=$user_id AND product_id=$product_id");
    if ($existing->num_rows > 0) {
        $row    = $existing->fetch_assoc();
        $newQty = $row['quantity'] + $qty;
        $conn->query("UPDATE cart SET quantity=$newQty WHERE id={$row['id']}");
    } else {
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $qty)");
    }
    jsonResponse(['success' => true, 'message' => 'Added to cart']);
}

function getCart() {
    if (!isLoggedIn()) jsonResponse(['cart' => [], 'total' => 0]);
    $conn    = getConnection();
    $user_id = (int)$_SESSION['user_id'];

    $result = $conn->query("SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image_url, p.requires_prescription
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = $user_id");
    $cart  = [];
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total          += $row['subtotal'];
        $cart[]          = $row;
    }
    jsonResponse(['cart' => $cart, 'total' => $total, 'count' => count($cart)]);
}

function updateCart() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Login required'], 401);
    $conn    = getConnection();
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $qty     = (int)($_POST['quantity'] ?? 1);

    if ($qty <= 0) {
        $conn->query("DELETE FROM cart WHERE id=$cart_id AND user_id={$_SESSION['user_id']}");
    } else {
        $conn->query("UPDATE cart SET quantity=$qty WHERE id=$cart_id AND user_id={$_SESSION['user_id']}");
    }
    jsonResponse(['success' => true]);
}

function removeFromCart() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Login required'], 401);
    $conn    = getConnection();
    $cart_id = (int)($_GET['cart_id'] ?? 0);
    $conn->query("DELETE FROM cart WHERE id=$cart_id AND user_id={$_SESSION['user_id']}");
    jsonResponse(['success' => true]);
}

function clearCart() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Login required'], 401);
    $conn = getConnection();
    $conn->query("DELETE FROM cart WHERE user_id={$_SESSION['user_id']}");
    jsonResponse(['success' => true]);
}

function placeOrder() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Login required'], 401);
    $conn    = getConnection();
    $user_id = (int)$_SESSION['user_id'];
    $address = sanitize($conn, $_POST['address'] ?? '');

    if (!$address) jsonResponse(['error' => 'Delivery address required'], 400);

    // Get cart
    $cartResult = $conn->query("SELECT c.quantity, p.id, p.price, p.stock, p.requires_prescription
                                FROM cart c JOIN products p ON c.product_id = p.id 
                                WHERE c.user_id = $user_id");
    if ($cartResult->num_rows === 0) jsonResponse(['error' => 'Cart is empty'], 400);

    $items = [];
    $total = 0;
    $needsRx = false;
    while ($row = $cartResult->fetch_assoc()) {
        if ($row['stock'] < $row['quantity']) {
            jsonResponse(['error' => "Insufficient stock for a product"], 400);
        }
        if ($row['requires_prescription']) $needsRx = true;
        $items[] = $row;
        $total  += $row['price'] * $row['quantity'];
    }

    // Create order
    $conn->query("INSERT INTO orders (user_id, total_amount, delivery_address) VALUES ($user_id, $total, '$address')");
    $order_id = $conn->insert_id;

    // Insert order items & reduce stock
    foreach ($items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                      VALUES ($order_id, {$item['id']}, {$item['quantity']}, {$item['price']})");
        $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}");
    }

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    jsonResponse([
        'success'  => true,
        'order_id' => $order_id,
        'total'    => $total,
        'message'  => $needsRx ? 'Order placed! Please upload prescription within 24 hours.' : 'Order placed successfully!'
    ]);
}

function myOrders() {
    if (!isLoggedIn()) jsonResponse(['error' => 'Login required'], 401);
    $conn    = getConnection();
    $user_id = (int)$_SESSION['user_id'];

    $result = $conn->query("SELECT o.*, 
                            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                            FROM orders o WHERE o.user_id = $user_id ORDER BY o.created_at DESC");
    $orders = [];
    while ($row = $result->fetch_assoc()) $orders[] = $row;
    jsonResponse(['orders' => $orders]);
}

function allOrders() {
    if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 403);
    $conn   = getConnection();
    $result = $conn->query("SELECT o.*, u.full_name, u.email,
                            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                            FROM orders o JOIN users u ON o.user_id = u.id 
                            ORDER BY o.created_at DESC");
    $orders = [];
    while ($row = $result->fetch_assoc()) $orders[] = $row;
    jsonResponse(['orders' => $orders]);
}

function updateStatus() {
    if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 403);
    $conn     = getConnection();
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status   = sanitize($conn, $_POST['status'] ?? '');
    $conn->query("UPDATE orders SET status='$status' WHERE id=$order_id");
    jsonResponse(['success' => true]);
}
?>
