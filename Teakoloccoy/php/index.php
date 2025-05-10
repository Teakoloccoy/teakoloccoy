<?php
        include_once '../connection/Sessionstart.php';
        include_once '../connection/dbConnection.php';
        include_once '../config/maintenance.php';
        check_maintenance();
    ?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Home - Teakoloccoy </title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href = "../php/index.php"> HOME </a></li>
                <li><a href = "../php/menu.php"> MENU </a></li>
                <li><a href="../php/location.php">LOCATION</a></li>
                <li><a href = "../php/about.php"> ABOUT US </a></li>
                <div class = "logged-account">
                    <?php
                        include_once '../connection/Session.php';
                    ?>
                </div>
            </ul>
        </nav>
    </header>
    

    <div class = "content-container">
        <img src = "../images/teakonobg.png" style = "width: 14%; height: 14% " alt="TeakoloccoyLogo">
        <h1> Start Your Day With A Fresh Brewed Coffee and Tea </h1>
        <div class = "cafe-welcome"> </div>
        <p style = "font-size: 20px; margin-top: -2%;"> Welcome to Teakoloccoy Official Website, where we believe that every great day <br> &nbsp starts with a perfect cup of coffee or tea. Our freshly brewed beverages are crafted <br> &nbsp &nbsp with the finest ingredients to ensure a delightful experience with every sip. </p>
    </div>

    <div class = "content-buttons">
        <div class = "content-button">
            <a href = "../php/login.php" class = "buttonLink">
                <button class = "nav-button"> Log In </button>
            </a>
        </div>
        <div class = "content-button">
            <a href = "../php/registration.php" class = "buttonLink">
                <button class = "nav-button"> Register </button>
            </a>
        </div>
    </div>  
           

    <footer>
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">  
                    <circle cx="12" cy="12" r="10" fill="white" />  
                    <line x1="12" y1="6" x2="12" y2="12" stroke="black" stroke-width="2" />  
                    <line x1="12" y1="12" x2="16" y2="14" stroke="black" stroke-width="2" />  
                    </svg> Everyday: 8:00am to 10:00pm &nbsp &nbsp &nbsp

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path fill="white" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5 14.5 7.62 14.5 9 13.38 11.5 12 11.5z"/>
                    </svg> West Poblacion, Palauig, Zambales &nbsp &nbsp &nbsp

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20px" height="20px">
                    <path fill="white" d="M6.62 10.79a15.91 15.91 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.27c1.21.48 2.53.75 3.89.75a1 1 0 011 1v3.55a1 1 0 01-1 1A19 19 0 013 5a1 1 0 011-1h3.55a1 1 0 011 1c0 1.36.27 2.68.75 3.89a1 1 0 01-.26 1.11l-2.2 2.2z"/>
                    </svg> 0946 523 8947 &nbsp &nbsp &nbsp

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="20px" height="20px">
                    <path d="M22 12C22 6.477 17.523 2 12 2C6.477 2 2 6.477 2 12C2 17.523 6.477 22 12 22C12.69 22 13.37 21.94 14.04 21.82V14.31H11.29V12H14.04V10C14.04 8.38 15.07 7.29 16.44 7.29C16.99 7.29 17.55 7.36 18.11 7.46V9.69H17.14C16.48 9.69 16.04 10.14 16.04 10.72V12H18.04L17.64 14.31H16.04V21.82C18.649 21.15 20.65 19.384 21.521 17H22C22 16.014 22 14.906 22 14.31V12Z"/>
                    </svg> Teakoloccoy Café </p>
        </footer>
    </body>
</html>
