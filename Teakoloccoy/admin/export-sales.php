<?php
include_once '../connection/SessionStart.php';
include_once '../connection/dbConnection.php';
date_default_timezone_set('Asia/Manila');
$connect->query("SET time_zone = '+08:00'");

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../php/login.php");
    exit();
}

$query = "SELECT s.sales_id, o.user_id, o.total_amount, s.status, 
          s.delivered_date, s.cancelled_date, o.order_date, u.Username, oa.phone_number
          FROM `sales` s
          JOIN `order` o ON s.order_id = o.order_id
          JOIN `users` u ON o.user_id = u.user_id
          JOIN `order_address` oa ON o.order_id = oa.order_id";
$result = $connect->query($query);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report.csv"');

$output = fopen('php://output', 'w');

fputcsv($output, ['Sales ID', 'User ID', 'Customer', 'Product Name', 'Total Amount', 'Status', 'Order Date', 'Delivered Date', 'Cancelled Date', 'Phone']);

function toManila($dateStr) {
    if (!$dateStr) return '';
    $dt = new DateTime($dateStr, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Asia/Manila'));
    return $dt->format('m/d/Y H:i');
}

while ($row = $result->fetch_assoc()) {
    // Fetch product names for this order
    $orderId = $row['sales_id'];
    $productNames = [];
    $productQuery = $connect->prepare("SELECT p.product_name FROM order_item oi JOIN product p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
    $productQuery->bind_param("i", $row['sales_id']);
    $productQuery->execute();
    $productResult = $productQuery->get_result();
    while ($prod = $productResult->fetch_assoc()) {
        $productNames[] = $prod['product_name'];
    }
    $productList = implode(', ', $productNames);
    $productQuery->close();

    $orderDate = toManila($row['order_date']);
    $deliveredDate = toManila($row['delivered_date']);
    $cancelledDate = toManila($row['cancelled_date']);

    fputcsv($output, [
        $row['sales_id'],
        $row['user_id'],
        $row['Username'],
        $productList,
        $row['total_amount'],
        $row['status'],
        $orderDate,
        $deliveredDate,
        $cancelledDate,
        $row['phone_number']
    ]);
}

fclose($output);
exit();
?>