<?php
include_once '../connection/Sessionstart.php';

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../php/login.php");
    exit();
}

include_once '../connection/dbConnection.php';

$adminId = $_SESSION['user_id'];
$selectedUserId = null;
if (isset($_POST['receiver_id'])) {
    $selectedUserId = intval($_POST['receiver_id']);
} elseif (isset($_GET['receiver_id'])) {
    $selectedUserId = intval($_GET['receiver_id']);
}
$messages = [];
$users = [];

// Get all users (not admins) with unread message count
$userQuery = "SELECT u.user_id, u.Username, 
              (SELECT COUNT(*) FROM messages m 
               WHERE m.sender_id = u.user_id 
               AND m.receiver_id = ? 
               AND m.notification = 1) as unread_count 
              FROM users u 
              WHERE u.isAdmin = 0";
$stmt = $connect->prepare($userQuery);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
while ($user = $result->fetch_assoc()) {
    $users[] = $user;
}
$stmt->close();

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message']) && $selectedUserId) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $connect->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at, notification) VALUES (?, ?, ?, NOW(), 1)");
        $stmt->bind_param("iis", $adminId, $selectedUserId, $message);
        $stmt->execute();
        $stmt->close();
        
        // Mark messages as read when admin sends a message
        $updateStmt = $connect->prepare("UPDATE messages SET notification = 0 WHERE sender_id = ? AND receiver_id = ?");
        $updateStmt->bind_param("ii", $selectedUserId, $adminId);
        $updateStmt->execute();
        $updateStmt->close();
        // Redirect to prevent form resubmission and retain selected user
        header("Location: message.php?receiver_id=$selectedUserId");
        exit();
    }
}

// Mark messages as read when viewing them
if ($selectedUserId) {
    $updateStmt = $connect->prepare("UPDATE messages SET notification = 0 WHERE sender_id = ? AND receiver_id = ?");
    $updateStmt->bind_param("ii", $selectedUserId, $adminId);
    $updateStmt->execute();
    $updateStmt->close();
}

// Fetch messages with selected user
if ($selectedUserId) {
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages - Teakoloccoy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/admin.css">
    <style>
        .message-container {
            max-height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            max-width: 80%;
        }
        .message.sent {
            background-color: #cd853f;
            color: white;
            margin-left: auto;
        }
        .message.received {
            background-color: #e9ecef;
            margin-right: auto;
        }
        .message-time {
            font-size: 0.8em;
            color: #6c757d;
            margin-top: 5px;
        }
        .message-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-list {
            max-height: 500px;
            overflow-y: auto;
        }
        .user-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            position: relative;
            color: #222 !important;
            text-decoration: none !important;
        }
        .user-item:hover {
            background-color: #f8f9fa;
        }
        .user-item.active {
            background-color: #cd853f;
            color: white !important;
        }
        .unread-badge {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        /* Remove underline and blue color from usernames */
        .user-item,
        .user-item:focus,
        .user-item:active,
        .user-item:visited {
            color: #222 !important;
            text-decoration: none !important;
        }
    </style>
</head>
<body style="background: url('../admin/images/cafebg.jpg') no-repeat center center fixed; background-size: cover;">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #c88339 !important;">
        <div class="container">
            <a class="navbar-brand" href="#">Teakoloccoy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                        <a class="nav-link" href="../php/index.php">Homepage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/menu-admin.php">Edit Menu</a>
                    </li>
                    <?php if($_SESSION['isAdmin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="../admin/admin.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/message.php"> Messages </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/maintenance-admin.php"> Maintenance</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="user-list">
                            <?php foreach($users as $user): ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="receiver_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" class="user-item btn btn-link w-100 text-start <?php echo ($selectedUserId == $user['user_id']) ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($user['Username']); ?>
                                    <?php if ($user['unread_count'] > 0): ?>
                                        <span class="unread-badge"><?php echo $user['unread_count']; ?></span>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Messages</h5>
                    </div>
                    <div class="card-body">
                        <div class="message-container" id="messageContainer">
                            <?php if ($selectedUserId && count($messages) > 0): ?>
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
                            <?php elseif ($selectedUserId): ?>
                                <div>No messages yet.</div>
                            <?php else: ?>
                                <div>Select a user to view messages.</div>
                            <?php endif; ?>
                        </div>
                        <?php if ($selectedUserId): ?>
                        <form class="message-form" method="POST" id="messageForm">
                            <input type="hidden" name="receiver_id" value="<?php echo $selectedUserId; ?>">
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="3" placeholder="Type your message..." required></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const messageContainer = document.getElementById('messageContainer');
        if (messageContainer) messageContainer.scrollTop = messageContainer.scrollHeight;

        // Function to fetch and update messages
        function refreshMessages() {
            const selectedUserId = document.querySelector('input[name="receiver_id"]')?.value;
            if (!selectedUserId) return;

            fetch(`get_messages.php?receiver_id=${selectedUserId}`)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMessages = doc.getElementById('messageContainer').innerHTML;
                    const currentMessages = messageContainer.innerHTML;
                    
                    if (newMessages !== currentMessages) {
                        messageContainer.innerHTML = newMessages;
                        messageContainer.scrollTop = messageContainer.scrollHeight;
                    }
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        // Refresh messages every 5 seconds
        setInterval(refreshMessages, 5000);
    </script>
</body>
</html>
