<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        register();
        break;
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'check':
        checkSession();
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

function register() {
    $conn = getConnection();
    $name  = sanitize($conn, $_POST['full_name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $phone = sanitize($conn, $_POST['phone'] ?? '');

    if (!$name || !$email || !$pass) {
        jsonResponse(['error' => 'All fields are required'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Invalid email format'], 400);
    }
    if (strlen($pass) < 6) {
        jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
    }

    // Check duplicate
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        jsonResponse(['error' => 'Email already registered'], 400);
    }

    $hashed = password_hash($pass, PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (full_name, email, password, phone) VALUES ('$name','$email','$hashed','$phone')");

    jsonResponse(['success' => true, 'message' => 'Registration successful! Please login.']);
}

function login() {
    $conn  = getConnection();
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        jsonResponse(['error' => 'Email and password required'], 400);
    }

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows === 0) {
        jsonResponse(['error' => 'Invalid email or password'], 401);
    }

    $user = $result->fetch_assoc();
    if (!password_verify($pass, $user['password'])) {
        jsonResponse(['error' => 'Invalid email or password'], 401);
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];

    jsonResponse([
        'success' => true,
        'user' => [
            'id'   => $user['id'],
            'name' => $user['full_name'],
            'role' => $user['role']
        ]
    ]);
}

function logout() {
    session_destroy();
    jsonResponse(['success' => true, 'message' => 'Logged out']);
}

function checkSession() {
    if (isLoggedIn()) {
        jsonResponse([
            'loggedIn' => true,
            'user' => [
                'id'   => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'role' => $_SESSION['role']
            ]
        ]);
    } else {
        jsonResponse(['loggedIn' => false]);
    }
}
?>
