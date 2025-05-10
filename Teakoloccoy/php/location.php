<?php
        include_once '../connection/Sessionstart.php';
        include_once '../connection/dbConnection.php';
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Location - Teakoloccoy </title>
    <link rel="stylesheet" href="../css/location.css">
    <script>
        function Map() {
            var contentCard = document.getElementById('content-card');
            contentCard.innerHTML = `
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3845.9555389628417!2d119.90623937388237!3d15.432950856925162!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33943d8b108abb23%3A0xb7a213071f465bca!2sAmungan%20-%20Palauig-Banlog%20Rd%2C%20Palauig%2C%20Zambales%2C%20Philippines!5e0!3m2!1sen!2sus!4v1740222959413!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                <button class="button" onclick="Content()">Back</button>
            `;
        }

        function Content() {
            var contentCard = document.getElementById('content-card');
            contentCard.innerHTML = `
                <img src = "../images/try.jpg" alt = "Teakoloccoy Cafe" style = "width: 300px; height: 300px;">
                <div class="container">
                    <h2> Teakoloccoy Cafe Map </h2>
                    <p class="title"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path fill="white" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z"/>
                    </svg> West Poblacion, Palauig, Zambales </p>
                    <p><strong><button class="button" onclick="Map()">View Location</button></strong></p>
                </div>
            `;
        }
    </script>
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
                        include_once '../connection/Session.php';
                    ?>
                </div>
            </ul>
        </nav>
    </header>
    <div class="content-container">
        <img src = "../images/teakonobg.png" style = "width: 12%; height: 12% " alt="TeakoloccoyLogo">
    </div>
    <div class = "card" id = "content-card">
        <img src = "../images/try.jpg" alt = "Teakoloccoy Cafe" style = "width: 300px; height: 300px;">
        <div class="container">
            <h2> Teakoloccoy Cafe Map </h2>
            <p class = "title"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path fill="white" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z"/>
                    </svg> West Poblacion, Palauig, Zambales </p>
            <p><strong><button class = "button" onclick = "Map()"> View Location </button></strong></p>
        </div>
    </div>
</body>
</html>