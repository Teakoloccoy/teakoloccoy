<?php
include_once '../connection/Sessionstart.php';
include_once '../connection/dbConnection.php';

// Check if user is admin
if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 1) {
    header('Location: ../php/login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenance_mode = isset($_POST['maintenance_mode']) ? true : false;
    
    // Update the maintenance configuration
    $config_content = "<?php\n";
    $config_content .= "// Maintenance mode configuration\n";
    $config_content .= "\$maintenance_mode = " . ($maintenance_mode ? "true" : "false") . ";\n";
    $config_content .= "\$allowed_ips = array(\n";
    $config_content .= "    '127.0.0.1', // localhost\n";
    $config_content .= "    // Add your IP address here to access the site during maintenance\n";
    $config_content .= ");\n\n";
    $config_content .= "// Function to check if the current IP is allowed\n";
    $config_content .= "function is_allowed_ip() {\n";
    $config_content .= "    global \$allowed_ips;\n";
    $config_content .= "    return in_array(\$_SERVER['REMOTE_ADDR'], \$allowed_ips);\n";
    $config_content .= "}\n\n";
    $config_content .= "// Function to check maintenance mode\n";
    $config_content .= "function check_maintenance() {\n";
    $config_content .= "    global \$maintenance_mode;\n";
    $config_content .= "    if (\$maintenance_mode && !is_allowed_ip()) {\n";
    $config_content .= "        header('Location: /maintenance.php');\n";
    $config_content .= "        exit();\n";
    $config_content .= "    }\n";
    $config_content .= "}\n";
    $config_content .= "?>";
    
    file_put_contents('../config/maintenance.php', $config_content);
    $success_message = "Maintenance mode has been " . ($maintenance_mode ? "enabled" : "disabled");
}

// Read current maintenance status
include_once '../config/maintenance.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance Control - Admin Panel</title>
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
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Maintenance Mode Control</h2>
            </div>
            <div class="card-body">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group mb-4">
                        <label class="form-label">Maintenance Mode:</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceSwitch" <?php echo $maintenance_mode ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="maintenanceSwitch">
                                Current Status: <span class="badge <?php echo $maintenance_mode ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $maintenance_mode ? 'Enabled' : 'Disabled'; ?>
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 