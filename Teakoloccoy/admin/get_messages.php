<?php
include_once '../connection/Sessionstart.php';

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 1) {
    exit('Unauthorized');
}

include_once '../connection/dbConnection.php';

$adminId = $_SESSION['user_id'];
$selectedUserId = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : null;

if (!$selectedUserId) {
    exit('No user selected');
}

$messages = [];

// Mark messages as read when viewing them
$updateStmt = $connect->prepare("UPDATE messages SET notification = 0 WHERE sender_id = ? AND receiver_id = ?");
$updateStmt->bind_param("ii", $selectedUserId, $adminId);
$updateStmt->execute();
$updateStmt->close();

// Fetch messages with selected user
$messageQuery = "SELECT m.*, u.Username as sender_name 
                FROM messages m 
                JOIN users u ON m.sender_id = u.user_id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) 
                ORDER BY m.sent_at ASC";
$stmt = $connect->prepare($messageQuery);
$stmt->bind_param("iiii", $adminId, $selectedUserId, $selectedUserId, $adminId);
$stmt->execute();
$result = $stmt->get_result();
while ($message = $result->fetch_assoc()) {
    $messages[] = $message;
}
$stmt->close();
?>

<div class="message-container" id="messageContainer">
    <?php if (count($messages) > 0): ?>
        <?php foreach($messages as $message): ?>
        <div class="message <?php echo $message['sender_id'] == $adminId ? 'sent' : 'received'; ?>">
            <div class="message-content">
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
            <div class="message-time">
                <?php echo date('M d, Y h:i A', strtotime($message['sent_at'])); ?>
                <span class="sender-name">
                    <?php echo $message['sender_id'] == $adminId ? 'You' : htmlspecialchars($message['sender_name']); ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>No messages yet.</div>
    <?php endif; ?>
</div> 