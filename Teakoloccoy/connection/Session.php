<?php
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    
    if ($hashedPassword && password_verify($password, $hashedPassword)) {
        $_SESSION['username'] = $username;
    } else {
        echo "Invalid username or password. Please try again.";
    }
}


    if (isset($_POST['logout']) && isset($_SESSION['username'])) {
        session_unset();
        session_destroy();
        echo "You have successfully logged out.";
}

    if (isset($_SESSION['username'])) {
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 100 100">
                <circle cx="50" cy="30" r="20" fill="white"/>
                    <rect x="30" y="55" width="40" height="35" rx="10" fill="white"/>
                        </svg>';
        echo '<span class="username">' . $_SESSION['username'] . '</span>';
        echo '<div class="logout-link">';
        echo '<a href="../connection/logoutPHP.php">Logout</a>';
        echo '</div>';
        $isLoggedIn = true;
} else {
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 100 100">
                <circle cx="50" cy="30" r="20" fill="white"/>
                    <rect x="30" y="55" width="40" height="35" rx="10" fill="white"/>
                        </svg>';
        echo "<p> Logged Out </p>";
        $isLoggedIn = false;
    }
?>