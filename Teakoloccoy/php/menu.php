<?php
include_once '../connection/Sessionstart.php';

if (!isset($_SESSION['username'])) {
    echo "<script>alert('You need to Log In First to View the Menu'); window.location.href = '../php/login.php';</script>";
    exit();
}

if ($_SESSION['isAdmin'] == 1) {
    header("Location: ../admin/admin.php");
    exit();
}
include_once '../connection/dbConnection.php';

// Function to get all products from database
function getProducts($conn) {
    $result = $conn->query("SELECT * FROM product ORDER BY category, product_name");
    if (!$result) {
        error_log("Error fetching products: " . $conn->error);
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all products
$products = getProducts($connect);

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['username'], $data['items'], $data['total'], $data['timestamp'], $data['deliveryAddress'], $data['deliveryName'], $data['deliveryPhone'])) {
        try {
            $connect->begin_transaction();

            // Get user_id from username
            $userStmt = $connect->prepare("SELECT user_id FROM users WHERE Username = ?");
            $userStmt->bind_param("s", $data['username']);
            $userStmt->execute();
            $userResult = $userStmt->get_result();
            $user = $userResult->fetch_assoc();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            $userId = $user['user_id'];

            // Debug log
            error_log("Creating order for user_id: " . $userId);
            error_log("Order data: " . print_r($data, true));

            // Insert into ORDER TABLE
            $orderStmt = $connect->prepare("INSERT INTO `order` (user_id, order_date, total_amount, order_status, delivery_address) VALUES (?, ?, ?, 'pending', ?)");
            $orderDate = date('Y-m-d H:i:s'); // Ensure proper timestamp format
            $totalAmount = floatval($data['total']); // Ensure proper number format
            $orderStmt->bind_param("isds", $userId, $orderDate, $totalAmount, $data['deliveryAddress']);
            
            if (!$orderStmt->execute()) {
                throw new Exception("Error creating order: " . $orderStmt->error);
            }
            
            $orderId = $connect->insert_id;
            error_log("Created order with ID: " . $orderId);

            // Insert into ORDER ADDRESS
            $addressStmt = $connect->prepare("INSERT INTO `order_address` (order_name, address, phone_number, order_id) VALUES (?, ?, ?, ?)");
            $addressStmt->bind_param("sssi", $data['deliveryName'], $data['deliveryAddress'], $data['deliveryPhone'], $orderId);
            
            if (!$addressStmt->execute()) {
                throw new Exception("Error creating order address: " . $addressStmt->error);
            }
            error_log("Created order address for order ID: " . $orderId);

            // Insert order items
            foreach ($data['items'] as $item) {
                // Get product_id from product name
                $productStmt = $connect->prepare("SELECT product_id FROM product WHERE product_name = ?");
                $productStmt->bind_param("s", $item['name']);
                $productStmt->execute();
                $productResult = $productStmt->get_result();
                $product = $productResult->fetch_assoc();
                
                if (!$product) {
                    throw new Exception("Product not found: " . $item['name']);
                }
                
                $productId = $product['product_id'];
                $itemPrice = floatval($item['price']); // Ensure proper number format

                // Insert order item
                $itemStmt = $connect->prepare("INSERT INTO `order_item` (quantity, item_price, product_id, order_id) VALUES (?, ?, ?, ?)");
                $quantity = 1; // Using integer for quantity
                $itemStmt->bind_param("idii", $quantity, $itemPrice, $productId, $orderId);
                
                if (!$itemStmt->execute()) {
                    throw new Exception("Error creating order item: " . $itemStmt->error);
                }
                error_log("Created order item for product ID: " . $productId);
            }

            $connect->commit();
            error_log("Order successfully created and committed");
            echo json_encode(['success' => true, 'message' => 'Order placed successfully']);
            
        } catch (Exception $e) {
            $connect->rollback();
            error_log("Error creating order: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        error_log("Missing required data in order request");
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
    }
    exit;
}
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Menu - Teakoloccoy </title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #checkoutModal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-button:hover, .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #checkoutForm label {
            display: block;
            margin-bottom: 5px;
        }

        #checkoutForm input[type=text] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        #checkoutForm button[type=submit] {
            background-color: #cd853f;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }

        #checkoutForm button[type=submit]:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php"> HOME </a></li>
                <li><a href="menu.php"> MENU </a></li>
                <li><a href="location.php"> LOCATION </a></li>
                <li><a href="about.php"> ABOUT US </a></li>
                <li>
<?php if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1): ?>
    <a href="../admin/message.php" class="messages-link"> MESSAGE</a>
<?php else: ?>
    <a href="message.php" class="messages-link"> MESSAGE</a>
<?php endif; ?>
                </li>
            </ul>
            <button id="cartBtn" onclick="toggleCart()">
                <img src="../images/cart.png" alt="Cart" style="width: 30px; height: 30px;">
                <span id="cartCount"> 0 </span>
            </button>
        </nav>
    </header>

    <div id="cart" style="display:none; position: fixed; top: 0; right: 0; background-color: white; width: 300px; height: 100%; padding: 20px; border-left: 2px solid #cd853f;">
        <button onclick="closeCart()" style="position: absolute; top: 10px; right: 10px;">Close</button>
        <h3> Cart </h3>
        <div id="cartItems">
        </div>
        <p><strong>Total: </strong> ₱ <span id="cartTotal"> 0 </span> </p>
        <button id="clearCartBtn" onclick="clearCart()">Clear Cart</button>
        <button id="checkoutBtn" onclick="checkout()">Checkout</button>
    </div>

    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeCheckoutModal()">&times;</span>
            <h3>Enter Delivery Details</h3>
            <p style="color: #b35c00; font-size: 15px; margin-bottom: 18px;"><strong>Note:</strong> This cafe only delivers within the municipality of Palauig, Zambales.</p>
            <form id="checkoutForm">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="address">Delivery Address:</label>
                <input type="text" id="address" name="address" required>

                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" required>
                <button type="submit">Place Order</button>
            </form>
        </div>
    </div>

    <script>
        // Pass PHP session data to JavaScript
        const sessionData = {
            username: "<?php echo htmlspecialchars($_SESSION['username']); ?>",
            isAdmin: <?php echo isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0; ?>
        };

        function openCheckoutModal() {
            const checkoutModal = document.getElementById('checkoutModal');
            checkoutModal.style.display = 'block';
        }

        // Function to close the checkout modal
        function closeCheckoutModal() {
            const checkoutModal = document.getElementById('checkoutModal');
            checkoutModal.style.display = 'none';
        }

        // Function to close the cart modal
        function closeCart() {
            const cart = document.getElementById('cart');
            cart.style.display = 'none'; // Hide the cart
        }
    </script>

    <div class="main">
        <h1>MENU</h1>
        <hr>
        <h2>TEAKOLOCCOY BEST SELLERS</h2>

        <div id="myBtnContainer">
            <button class="btn active" onclick="filterSelection('all')"> Show all</button>
            <button class="btn" onclick="filterSelection('foods')"> Foods</button>
            <button class="btn" onclick="filterSelection('drinks')"> Drinks</button>
            <button class="btn" onclick="filterSelection('promo')"> Promos</button>
        </div>

        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="column <?php echo htmlspecialchars($product['category']); ?>">
                <div class="content">
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                         style="width:100%"
                         onerror="this.src='../images/placeholder.png'">
                    <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                    <h4><?php echo number_format($product['base_price'], 2); ?> pesos</h4>
                    <button type="button" 
                            class="addToCartBtn" 
                            data-name="<?php echo htmlspecialchars($product['product_name']); ?>" 
                            data-price="<?php echo htmlspecialchars($product['base_price']); ?>">
                        ADD TO ORDER
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Pass PHP session data to JavaScript
        const sessionData = {
            username: "<?php echo htmlspecialchars($_SESSION['username']); ?>",
            isAdmin: <?php echo isset($_SESSION['isAdmin']) ? $_SESSION['isAdmin'] : 0; ?>
        };

        // Filter function
        function filterSelection(c) {
            var x, i;
            x = document.getElementsByClassName("column");
            if (c == "all") c = "";
            for (i = 0; i < x.length; i++) {
                w3RemoveClass(x[i], "show");
                if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], "show");
            }
        }

        function w3AddClass(element, name) {
            var i, arr1, arr2;
            arr1 = element.className.split(" ");
            arr2 = name.split(" ");
            for (i = 0; i < arr2.length; i++) {
                if (arr1.indexOf(arr2[i]) == -1) {element.className += " " + arr2[i];}
            }
        }

        function w3RemoveClass(element, name) {
            var i, arr1, arr2;
            arr1 = element.className.split(" ");
            arr2 = name.split(" ");
            for (i = 0; i < arr2.length; i++) {
                while (arr1.indexOf(arr2[i]) > -1) {
                    arr1.splice(arr1.indexOf(arr2[i]), 1);     
                }
            }
            element.className = arr1.join(" ");
        }

        // Add click event to filter buttons
        var btnContainer = document.getElementById("myBtnContainer");
        var btns = btnContainer.getElementsByClassName("btn");
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener("click", function(){
                var current = document.getElementsByClassName("active");
                current[0].className = current[0].className.replace(" active", "");
                this.className += " active";
            });
        }
    </script>
    <script src="../js/menu.js"></script>   
</body>
</html>