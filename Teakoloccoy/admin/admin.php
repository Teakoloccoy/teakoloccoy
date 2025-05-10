<?php
include_once '../connection/SessionStart.php';
date_default_timezone_set('Asia/Manila');
$connect->query("SET time_zone = '+08:00'");

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../php/login.php");
    exit();
}

include_once '../connection/dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $connect->begin_transaction();
        $orderId = $_POST['order_id'];
        $newStatus = $_POST['update_status'];
        $checkStmt = $connect->prepare("SELECT order_id FROM `order` WHERE order_id = ?");
        $checkStmt->bind_param("i", $orderId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("Order not found");
        }
        $updateStmt = $connect->prepare("UPDATE `order` SET order_status = ? WHERE order_id = ?");
        $updateStmt->bind_param("si", $newStatus, $orderId);
        if (!$updateStmt->execute()) {
            throw new Exception("Error updating order status: " . $updateStmt->error);
        }
        if ($newStatus === 'completed' || $newStatus === 'cancelled') {
            $checkSales = $connect->prepare("SELECT sales_id FROM sales WHERE order_id = ? AND status = ?");
            $checkSales->bind_param("is", $orderId, $newStatus);
            $checkSales->execute();
            $checkSales->store_result();
            if ($checkSales->num_rows == 0) {
                $salesStmt = $connect->prepare("INSERT INTO `sales` (order_id, status, delivered_date, cancelled_date) VALUES (?, ?, ?, ?)");
                $deliveredDate = ($newStatus === 'completed') ? date('Y-m-d H:i:s') : null;
                $cancelledDate = ($newStatus === 'cancelled') ? date('Y-m-d H:i:s') : null;
                $salesStmt->bind_param("isss", $orderId, $newStatus, $deliveredDate, $cancelledDate);
                if (!$salesStmt->execute()) {
                    throw new Exception("Error updating sales record: " . $salesStmt->error);
                }
                $salesStmt->close();
            }
            $checkSales->close();
        }
        $connect->commit();
        header("Location: admin.php");
        exit();
    } catch (Exception $e) {
        $connect->rollback();
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../admin/admin.css">
</head>
<body style="background: url('../admin/images/cafebg.jpg') no-repeat center center fixed; background-size: cover;">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #c88339 !important;">
        <div class="container">
            <a class="navbar-brand" href="#">Teakoloccoy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../php/index.php">Homepage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/menu-admin.php">Edit Menu</a>
                    </li>
                    <?php if($_SESSION['isAdmin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="../admin/admin.php">Admin Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/message.php"> Messages </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/maintenance-admin.php"> Maintenance</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Admin Dashboard</h2>

        <!-- Pending Orders Table -->
        <div class="card order-table">
            <div class="card-header table-header">
                <h4>Pending Orders</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Address</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        function executeQuery($connect, $query, $errorMessage) {
                            $result = $connect->query($query);
                            if (!$result) {
                                echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($errorMessage) . " - " . $connect->error . "</div>";
                                return false;
                            }
                            return $result;
                        }

                        $pendingQuery = "SELECT o.*, u.Username, oa.order_name, oa.address, oa.phone_number 
                                        FROM `order` o
                                        JOIN `order_address` oa ON o.order_id = oa.order_id
                                        JOIN `users` u ON o.user_id = u.user_id
                                        WHERE o.order_status = 'pending'
                                        ORDER BY o.order_date DESC";
                        $pendingResult = executeQuery($connect, $pendingQuery, "Failed to fetch pending orders");
                        
                        if ($pendingResult->num_rows > 0) {
                            while($order = $pendingResult->fetch_assoc()) {
                                $orderId = $order['order_id'];
                                $productNames = [];
                                $productQuery = $connect->prepare("SELECT p.product_name FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                                $productQuery->bind_param("i", $orderId);
                                $productQuery->execute();
                                $productResult = $productQuery->get_result();
                                while ($prod = $productResult->fetch_assoc()) {
                                    $productNames[] = $prod['product_name'];
                                }
                                $productList = implode(', ', $productNames);
                                $productQuery->close();

                                echo '<tr>
                                        <td>#'.$order['user_id'].'</td>
                                        <td>'.$order['order_name'].'<br><small>'.$order['Username'].'</small></td>
                                        <td><strong>'.htmlspecialchars($productList).'</strong><br>₱'.number_format($order['total_amount'], 2).'</td>
                                        <td>'.$order['address'].'</td>
                                        <td>'.$order['phone_number'].'</td>
                                        <td>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="order_id" value="'.$order['order_id'].'">
                                                <button type="submit" name="update_status" value="cancelled" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Cancel Order
                                                </button>
                                                <button type="submit" name="update_status" value="completed" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Complete
                                                </button>
                                            </form>
                                        </td>
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No pending orders</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Completed Orders Table -->
        <div class="card order-table">
            <div class="card-header table-header">
                <h4>Completed Orders</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $completedQuery = "SELECT o.*, u.Username, oa.order_name, oa.address 
                                         FROM `order` o
                                         JOIN `order_address` oa ON o.order_id = oa.order_id
                                         JOIN `users` u ON o.user_id = u.user_id
                                         WHERE o.order_status = 'completed'
                                         ORDER BY o.order_date DESC";
                        $completedResult = executeQuery($connect, $completedQuery, "Failed to fetch completed orders");
                        
                        if ($completedResult->num_rows > 0) {
                            while($order = $completedResult->fetch_assoc()) {
                                $orderId = $order['order_id'];
                                $productNames = [];
                                $productQuery = $connect->prepare("SELECT p.product_name FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                                $productQuery->bind_param("i", $orderId);
                                $productQuery->execute();
                                $productResult = $productQuery->get_result();
                                while ($prod = $productResult->fetch_assoc()) {
                                    $productNames[] = $prod['product_name'];
                                }
                                $productList = implode(', ', $productNames);
                                $productQuery->close();

                                echo '<tr>
                                        <td>#'.$order['user_id'].'</td>
                                        <td>'.$order['order_name'].'<br><small>'.$order['Username'].'</small></td>
                                        <td><strong>'.htmlspecialchars($productList).'</strong><br>₱'.number_format($order['total_amount'], 2).'</td>
                                        <td>'.$order['address'].'</td>
                                        <td><span class="status-badge completed">COMPLETED</span></td>
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">No completed orders</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cancelled Orders Table -->
        <div class="card order-table">
            <div class="card-header table-header">
                <h4>Cancelled Orders</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        $cancelledQuery = "SELECT o.*, u.Username, oa.order_name, oa.address 
                                          FROM `order` o
                                          JOIN `order_address` oa ON o.order_id = oa.order_id
                                          JOIN `users` u ON o.user_id = u.user_id
                                          WHERE o.order_status = 'cancelled'
                                          ORDER BY o.order_date DESC";
                        $cancelledResult = executeQuery($connect, $cancelledQuery, "Failed to fetch cancelled orders");
                        
                        if ($cancelledResult->num_rows > 0) {
                            while($order = $cancelledResult->fetch_assoc()) {
                                $orderId = $order['order_id'];
                                $productNames = [];
                                $productQuery = $connect->prepare("SELECT p.product_name FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
                                $productQuery->bind_param("i", $orderId);
                                $productQuery->execute();
                                $productResult = $productQuery->get_result();
                                while ($prod = $productResult->fetch_assoc()) {
                                    $productNames[] = $prod['product_name'];
                                }
                                $productList = implode(', ', $productNames);
                                $productQuery->close();

                                echo '<tr>
                                        <td>#'.$order['user_id'].'</td>
                                        <td>'.$order['order_name'].'<br><small>'.$order['Username'].'</small></td>
                                        <td><strong>'.htmlspecialchars($productList).'</strong><br>₱'.number_format($order['total_amount'], 2).'</td>
                                        <td>'.$order['address'].'</td>
                                        <td><span class="status-badge cancelled">CANCELLED</span></td>
                                      </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">No cancelled orders</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <center>
    <div class="mb-4">
    <button style="border: none;"> <a href="export-sales.php" class="btn btn-primary">
        <i class="fas fa-download"> </i> Export Sales Report</a> </button> </center>
    </div>
</body>
</html>