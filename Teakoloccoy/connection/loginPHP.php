<?php
if (isset($_POST["login"])) {
    $userName = $_POST['username'];
    $passWord = $_POST['password'];

    require_once '../connection/dbConnection.php';

    $sqli = "SELECT * FROM users WHERE Username = ?";
    $stmt = $connect->prepare($sqli);
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($passWord, $user["Password"])) {
            include_once '../connection/Sessionstart.php';
            $_SESSION['username'] = $userName;
            $_SESSION['isAdmin'] = $user["isAdmin"];
            $_SESSION['user_id'] = $user["user_id"];

            if ($user["isAdmin"] == 1) { 
                header("Location: ../admin/admin.php");
            } elseif ($user["isAdmin"] == 0) {
                header("Location: ../php/menu.php");
            } else {
                echo "<div class='alert alert-danger'> Role not recognized. </div>";
            }
            die();
        } else {
            echo "<div class='alert alert-danger'> Incorrect Username or Password </div>";
        }
    } else {
        echo "<div class='alert alert-danger'> Incorrect Username or Password </div>";
    }
}
?>