<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isAdmin()) jsonResponse(['error' => 'Unauthorized'], 403);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'users':
        $conn   = getConnection();
        $result = $conn->query("SELECT id, full_name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
        $users  = [];
        while ($row = $result->fetch_assoc()) $users[] = $row;
        jsonResponse(['users' => $users]);
        break;
    case 'messages':
        $conn   = getConnection();
        $result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $msgs   = [];
        while ($row = $result->fetch_assoc()) $msgs[] = $row;
        jsonResponse(['messages' => $msgs]);
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
?>
