<?php
        include_once '../connection/Sessionstart.php';
        include_once '../connection/dbConnection.php';
    ?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> Login - Teakoloccoy </title>
    <link rel="stylesheet" type="text/css" href="../css/login.css">
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="../php/index.php">HOME</a></li>
                <li><a href="../php/menu.php">MENU</a></li>
                <li><a href="../php/location.php">LOCATION</a></li>
                <li><a href="../php/about.php">ABOUT US</a></li>
                <div class = "logged-account">
            <?php
                include_once '../connection/loginHandler.php';
            ?>

                </div>
            </ul>
        </nav>
    </header>

    <div class="content-container">
        <img src="../images/teakonobg.png" style="width: 12%; height: 12%" alt="TeakoloccoyLogo">
        <div class="form-container">
            <div class="login-signup">
                <div id="login-form">
        <?php
        include_once '../connection/loginPHP.php';
        ?>
             </div>
                    <h2> Login </h2>
                    <form action="../php/login.php" method="post"autocomplete="off" >
        <input type="text" name="username" placeholder="Username" autocomplete="off" required>
        <input type="password" name="password" placeholder="Password" autocomplete="off" required>
        <button type="submit" name="login"> Log In </button>
</form>

                        <div class="form-change">
                            <p> Don't have an account? <a href="../php/registration.php"> Register </a></p>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</body>
</html>
