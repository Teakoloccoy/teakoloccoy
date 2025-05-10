<?php
include_once '../connection/Sessionstart.php';

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 0) {
    header("Location: ../php/login.php");
    exit();
}

include_once '../connection/dbConnection.php';

$currentUserId = $_SESSION['user_id'];
$adminId = 7; // Set to the correct admin user_id
$messages = [];

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $connect->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at, notification) VALUES (?, ?, ?, NOW(), 1)");
        $stmt->bind_param("iis", $currentUserId, $adminId, $message);
        $stmt->execute();
        $stmt->close();
        // Redirect to prevent form resubmission
        header("Location: message.php");
        exit();
    }
}

// Mark messages as read when viewing them
$updateStmt = $connect->prepare("UPDATE messages SET notification = 0 WHERE sender_id = ? AND receiver_id = ?");
$updateStmt->bind_param("ii", $adminId, $currentUserId);
$updateStmt->execute();
$updateStmt->close();

// Get unread message count
$unreadQuery = "SELECT COUNT(*) as unread_count FROM messages WHERE sender_id = ? AND receiver_id = ? AND notification = 1";
$unreadStmt = $connect->prepare($unreadQuery);
$unreadStmt->bind_param("ii", $adminId, $currentUserId);
$unreadStmt->execute();
$unreadResult = $unreadStmt->get_result();
$unreadCount = $unreadResult->fetch_assoc()['unread_count'];
$unreadStmt->close();

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Messages - Teakoloccoy </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/menu.css">
    <style>
        .logged-account {
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            color: whitesmoke;
            font-weight: bold;
            gap: 10px;
            font-size: 20px;
        }

        .logged-account svg {
            margin-right: 5px;
        }

        .logged-account .username {
            color: whitesmoke;
            font-weight: bold;
            margin-right: 8px;
        }

        .logout-link {
            margin-left: 8px;
        }

        .logout-link a, .logged-account .logout-link a {
            color: red !important;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        .logout-link a:hover, .logged-account .logout-link a:hover {
            text-decoration: underline;
            color: #b30000 !important;
        }
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
            text-align: right;
        }
        .message.received {
            background-color: #e9ecef;
            color: #333;
            margin-right: auto;
            text-align: left;
        }
        .message-time {
            font-size: 0.8em;
            color: #6c757d;
            margin-top: 5px;
        }
        .message-status {
            font-size: 0.75em;
            color: #888;
            margin-top: 2px;
            display: block;
        }
        .message-form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
            display: flex;
            flex-direction: column;
        }
        .message-form button[type="submit"] {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #cd853f;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
            align-self: flex-end;
        }
        .message-form button[type="submit"]:hover {
            background-color: #b06d2b;
        }
        .unread-badge {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href = "../php/index.php"> HOME </a></li>
                <li><a href = "../php/menu.php"> MENU </a></li>
                <li><a href="../php/location.php">LOCATION</a></li>
                <li><a href = "../php/about.php"> ABOUT US </a></li>
                <li><a href="../php/message.php">MESSAGE</a></li>
            </ul>
            <div class = "logged-account">
                <?php include_once '../connection/Session.php'; ?>
            </div>
        </nav>
    </header>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            Chat with Seller
                            <?php if ($unreadCount > 0): ?>
                                <span class="unread-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
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
                        <form class="message-form" method="POST" id="messageForm">
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="3" placeholder="Type your message..." required></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const messageContainer = document.getElementById('messageContainer');
        messageContainer.scrollTop = messageContainer.scrollHeight;

        // Function to fetch and update messages
        function refreshMessages() {
            fetch('get_messages.php')
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