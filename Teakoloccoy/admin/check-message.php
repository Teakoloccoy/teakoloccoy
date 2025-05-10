<?php
include_once '../connection/Sessionstart.php';

if (!isset($_SESSION['username'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Unauthorized');
}

include_once '../connection/dbConnection.php';

$query = "SELECT COUNT(*) as unread_count 
          FROM messages 
          WHERE receiver_id = ? AND notification = 1";
          
$stmt = $connect->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode(['unread_count' => $count['unread_count']]);
?> 