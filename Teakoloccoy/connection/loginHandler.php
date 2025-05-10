<?php
if (isset($_POST['login']) && !isset($_SESSION['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    require_once '../connection/dbConnection.php';

    $sqli = "SELECT * FROM users WHERE Username = ?";
    $stmt = $connect->prepare($sqli);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["Password"])) {
        $_SESSION['username'] = $username;
    } else {
        
    }
}

?>