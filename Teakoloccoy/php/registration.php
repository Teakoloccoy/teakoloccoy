<?php
        include_once '../connection/Sessionstart.php';
        include_once '../connection/dbConnection.php';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Teakoloccoy</title>
    <link rel="stylesheet" href="../css/registration.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="../php/index.php">HOME</a></li>
                <li><a href="../php/menu.php">MENU</a></li>
                <li><a href="../php/location.php">LOCATION</a></li>
                <li><a href="../php/about.php">ABOUT US</a></li>
            </ul>
        </nav>
    </header>
    <div class="content-container">
        <img src="../images/teakonobg.png" style="width: 12%; height: 12%;" alt="TeakoloccoyLogo">
        <div class="form-container">
            <div id="alert-container">
                <?php
                    include_once '../connection/registrationPHP.php';
                ?>
                </div>
            <div class="registration">
                <div id="register-form">
                    <h2>Register Form</h2>
                    <form action="../php/registration.php" method="post" autocomplete="off" >
                        <input type="text" name="first_name" placeholder="First Name" autocomplete="off" required >
                        <input type="text" name="last_name" placeholder="Last Name" autocomplete="off" required >
                        <input type="text" name="username" placeholder="Username" autocomplete="off" required >
                        <input type="password" name="password" placeholder="Password" autocomplete="off" required >
                        <select name = "address">
                            <option value = "default"> Select an Address </option>
                            <option value = "Brgy. Santo Tomas, Palauig, Zambales"> Brgy. Santo Tomas, Palauig, Zambales </option>
                            <option value = "Brgy. Santo Niño, Palauig, Zambales"> Brgy. Santo Niño, Palauig, Zambales </option>
                            <option value = "Brgy. Macarang, Palauig, Zambales"> Brgy. Macarang, Palauig, Zambales </option>
                            <option value = "Brgy. Macarang, Palauig, Zambales"> Brgy. Alwa, Palauig, Zambales </option>
                            <option value = "Brgy. Bato, Palauig, Zambales"> Brgy. Bato, Palauig, Zambales </option>
                            <option value = "Brgy. Bulawen, Palauig, Zambales"> Brgy. Bulawen, Palauig, Zambales </option>
                            <option value = "Brgy. Liozon, Palauig, Zambales"> Brgy. Liozon, Palauig, Zambales </option>
                            <option value = "Brgy. Cauyan, Palauig, Zambales"> Brgy. Cauyan, Palauig, Zambales </option>
                            <option value = "Brgy. East Poblacion, Palauig, Zambales"> Brgy. East Poblacion, Palauig, Zambales </option>
                            <option value = "Brgy. West Poblacion, Palauig, Zambales"> Brgy. West Poblacion, Palauig, Zambales </option>
                            <option value = "Brgy. Garetta, Palauig, Zambales"> Brgy. Garetta, Palauig, Zambales </option>
                            <option value = "Brgy. Libaba, Palauig, Zambales"> Brgy. Libaba, Palauig, Zambales </option>
                            <option value = "Brgy. Lipay, Palauig, Zambales"> Brgy. Lipay, Palauig, Zambales </option>
                            <option value = "Brgy. Locloc, Palauig, Zambales"> Brgy. Locloc, Palauig, Zambales </option>
                            <option value = "Brgy. Magalawa, Palauig, Zambales"> Brgy. Magalawa, Palauig, Zambales </option>
                            <option value = "Brgy. Pangolingan, Palauig, Zambales"> Brgy. Pangolingan, Palauig, Zambales </option>
                            <option value = "Brgy. Salaza, Palauig, Zambales"> Brgy. Salaza, Palauig, Zambales </option>
                            <option value = "Brgy. San Juan, Palauig, Zambales"> Brgy. San Juan, Palauig, Zambales </option>
                            <option value = "Brgy. San Vicente, Palauig, Zambales"> Brgy. San Vicente, Palauig, Zambales </option>
                        </select>
                        <input type="text" name="phone" placeholder="Phone Number" required>
                        <div class="terms-container">
                            <input type="checkbox" name="terms" id="terms" required>
                            <label for="terms">I agree to the <a href="#" onclick="showTerms()">Terms & Conditions</a></label>
                        </div>
                        <button type="submit" name="submit"> Register </button>
                        <div class="form-change">
                            <p> Already have an account? <a href="../php/login.php">Log In </a> </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div id="termsModal" class="modal" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 600px; border-radius: 10px;">
            <span class="close" onclick="closeTerms()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h2>Terms & Conditions</h2>
            <div style="max-height: 400px; overflow-y: auto;">
                <p>By registering with Teakoloccoy, you agree to the following terms and conditions:</p>
                <ol>
                    <li>You must provide accurate and complete information during registration.</li>
                    <li>You are responsible for maintaining the confidentiality of your account.</li>
                    <li>You agree to use the service in accordance with all applicable laws and regulations.</li>
                    <li>We reserve the right to modify these terms at any time.</li>
                    <li>Your personal information will be handled in accordance with our privacy policy.</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
    function showTerms() {
        document.getElementById('termsModal').style.display = 'block';
    }

    function closeTerms() {
        document.getElementById('termsModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('termsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    </script>
</body>
</html>
