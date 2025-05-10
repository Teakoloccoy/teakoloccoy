<?php
include_once '../connection/Sessionstart.php';

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 0) {
    exit('Unauthorized');
}

include_once '../connection/dbConnection.php';

$currentUserId = $_SESSION['user_id'];
$adminId = 7; // Set to the correct admin user_id
$messages = [];

// Fetch messages between user and admin
$messageQuery = "SELECT m.*, u.Username as sender_name 
                FROM messages m 
                JOIN users u ON m.sender_id = u.user_id 
                WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) 
                ORDER BY m.sent_at ASC";
$stmt = $connect->prepare($messageQuery);
$stmt->bind_param("iiii", $currentUserId, $adminId, $adminId, $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
while ($message = $result->fetch_assoc()) {
    $messages[] = $message;
}
$stmt->close();
?>

<div class="message-container" id="messageContainer">
    <?php foreach($messages as $message): ?>
    <div class="message <?php echo $message['sender_id'] == $currentUserId ? 'sent' : 'received'; ?>">
        <div class="message-content">
            <?php echo htmlspecialchars($message['message']); ?>
        </div>
        <div class="message-time">
            <?php echo date('M d, Y h:i A', strtotime($message['sent_at'])); ?>
            <span class="sender-name">
                <?php echo $message['sender_id'] == $currentUserId ? 'You' : 'Admin'; ?>
            </span>
        </div>
        <?php if ($message['sender_id'] == $currentUserId): ?>
            <span class="message-status">
                <?php if ($message['notification'] == 0): ?>
                    Seen
                <?php else: ?>
                    Unread
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div> 