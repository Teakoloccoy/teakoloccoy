<?php
// Maintenance mode configuration
$maintenance_mode = false;
$allowed_ips = array(
    '127.0.0.1', // localhost
    // Add your IP address here to access the site during maintenance
);

// Function to check if the current IP is allowed
function is_allowed_ip() {
    global $allowed_ips;
    return in_array($_SERVER['REMOTE_ADDR'], $allowed_ips);
}

// Function to check maintenance mode
function check_maintenance() {
    global $maintenance_mode;
    if ($maintenance_mode && !is_allowed_ip()) {
        header('Location: /maintenance.php');
        exit();
    }
}
?>