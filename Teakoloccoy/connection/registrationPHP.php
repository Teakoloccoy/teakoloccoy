<?php
if (isset($_POST["submit"])) {
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $userName = htmlspecialchars($_POST['username']);
    $passWord = htmlspecialchars($_POST['password']);
    $addRess = htmlspecialchars($_POST['address']);
    $phoneNumber = htmlspecialchars($_POST['phone']);
    $errors = array();

    if (strlen($passWord) < 8) {
    array_push($errors, "<span style='color: red; font-size: 15px;'>Password must be at least 8 characters</span>");
    }
    if (!preg_match('/[A-Z]/', $passWord)) {
    array_push($errors, "<span style='color: red; font-size: 15px;'>Password must include at least one uppercase letter</span>");
    }
    if (!preg_match('/[a-z]/', $passWord)) {
    array_push($errors, "<span style='color: red; font-size: 15px;'>Password must include at least one lowercase letter</span>");
    }
    if (!preg_match('/[0-9]/', $passWord)) {
    array_push($errors, "<span style='color: red; font-size: 15px;'>Password must include at least one number</span>");
    }
    if (strlen($phoneNumber) != 11) {
        array_push($errors, "<span style='color: red; font-size: 15px;'>Phone Number should be 11 numbers</span>");
    }
    if ($addRess == "default") {
        array_push($errors, "<span style='color: red; font-size: 15px;'>Please Select a Valid Address</span>");
    }

    if (!isset($_POST['terms'])) {
        array_push($errors, "<span style='color: red; font-size: 15px;'>You must agree to the Terms & Conditions</span>");
    }

    require_once '../connection/dbConnection.php';

    $sqli = "SELECT * FROM users WHERE Username = ?";
    $statement = mysqli_stmt_init($connect);

    if (mysqli_stmt_prepare($statement, $sqli)) {
        mysqli_stmt_bind_param($statement, "s", $userName);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        $numRows = mysqli_num_rows($result);

        while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='user-data'>" . htmlspecialchars($row['First_Name']) . " " . htmlspecialchars($row['Last_Name']) . "</div>";
    }


        if ($numRows > 0) {
            array_push($errors, "<span style='color: red; font-size: 15px;'>Username Already Exists!</span>");
        }

        mysqli_stmt_close($statement);
    } else {
        die("Failed to prepare the statement!");
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    } else {
        $hashedPassword = password_hash($passWord, PASSWORD_DEFAULT);
        $dateCreated = date("Y-m-d H:i:s");

        $sqli = "INSERT INTO users (First_Name, Last_Name, Username, Password, Address, Phone_Num, date_created) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $statement = mysqli_stmt_init($connect);

        if (mysqli_stmt_prepare($statement, $sqli)) {
            mysqli_stmt_bind_param($statement, "sssssss", $firstName, $lastName, $userName, $hashedPassword, $addRess, $phoneNumber, $dateCreated);
            mysqli_stmt_execute($statement);
            echo "<div class='alert alert-success'> <span style='color: green; font-size: 15px;'>You are now registered! Please Click Login below </span> </div>";
            mysqli_stmt_close($statement);
        } else {
            die("Something went wrong!");
        }
    }

    mysqli_close($connect);
}
?>