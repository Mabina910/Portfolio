<?php
session_start();
require_once 'db_connect.php';


if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id']) || empty($data['name']) || empty($data['email'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid data']));
}

$stmt = $conn->prepare("UPDATE users SET 
    full_name = ?, 
    email = ?, 
    phone_number = ?, 
    gender = ? 
    WHERE id = ?");
$stmt->bind_param("ssssi", 
    $data['name'],
    $data['email'],
    $data['phone'],
    $data['gender'],
    $data['id']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>