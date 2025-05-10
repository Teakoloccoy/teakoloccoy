<?php
include_once '../connection/Sessionstart.php';
include_once '../connection/dbConnection.php';

if (!isset($_SESSION['username']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../php/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (isset($_POST['action'])) {
            $connect->begin_transaction();

            switch ($_POST['action']) {
                case 'add':
                    if (empty($_POST['name']) || !is_numeric($_POST['price']) || empty($_POST['category']) || empty($_POST['image'])) {
                        throw new Exception("All fields are required and price must be a number");
                    }
                    
                    $stmt = $connect->prepare("INSERT INTO product (product_name, base_price, category, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sdss", $_POST['name'], $_POST['price'], $_POST['category'], $_POST['image']);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to add item: " . $stmt->error);
                    }
                    break;

                case 'edit':
                    if (empty($_POST['name']) || !is_numeric($_POST['price']) || !is_numeric($_POST['id']) || empty($_POST['category']) || empty($_POST['image'])) {
                        throw new Exception("All fields are required and price must be a number");
                    }

                    $stmt = $connect->prepare("UPDATE product SET product_name = ?, base_price = ?, category = ?, image_path = ? WHERE product_id = ?");
                    $stmt->bind_param("sdssi", $_POST['name'], $_POST['price'], $_POST['category'], $_POST['image'], $_POST['id']);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update item: " . $stmt->error);
                    }
                    break;

                case 'delete':
                    if (!is_numeric($_POST['id'])) {
                        throw new Exception("Invalid ID");
                    }

                    $stmt = $connect->prepare("DELETE FROM product WHERE product_id = ?");
                    $stmt->bind_param("i", $_POST['id']);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to delete item: " . $stmt->error);
                    }
                    break;

                default:
                    throw new Exception("Invalid action");
            }

            $connect->commit();
            $response['success'] = true;
            $response['message'] = "Operation completed successfully";
            $response['products'] = getProducts($connect);
        }
    } catch (Exception $e) {
        $connect->rollback();
        $response['error'] = $e->getMessage();
        $response['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

function getProducts($conn) {
    $result = $conn->query("SELECT * FROM product ORDER BY category, product_name");
    if (!$result) {
        throw new Exception("Failed to fetch products: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu Editor</title>
    <link rel="stylesheet" href="../admin/menu-admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
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

    <main>
        <div class="editor-panel">
            <h2>Add New Item</h2>
            <form class="editor-form" id="addForm">
                <input type="text" name="name" placeholder="Item Name" required>
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <select name="category" required>
                    <option value="foods">Food</option>
                    <option value="drinks">Drink</option>
                    <option value="promo">Promo</option>
                </select>
                <input type="text" name="image" placeholder="Image Path" required>
                <button type="submit">Add Item</button>
            </form>
        </div>

        <div class="editor-panel">
            <h2>Current Menu Items</h2>
            <div id="productList">
                <?php foreach (getProducts($connect) as $product): ?>
                <div class="product-item" data-id="<?= $product['product_id'] ?>">
                    <img src="<?= $product['image_path'] ?>" style="width: 80px; margin-right: 15px;">
                    <div style="flex-grow: 1;">
                        <h3><?= $product['product_name'] ?></h3>
                        <p>₱<?= number_format($product['base_price'], 2) ?></p>
                        <small>Category: <?= $product['category'] ?></small>
                    </div>
                    <button class="editBtn">Edit</button>
                    <button class="deleteBtn">Delete</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
    function showMessage(message, isError = false) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${isError ? 'alert-danger' : 'alert-success'} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.querySelector('main').insertBefore(alertDiv, document.querySelector('.editor-panel'));
        setTimeout(() => alertDiv.remove(), 5000);
    }

    async function sendRequest(formData) {
        try {
            const response = await fetch('menu-admin.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Operation failed');
            }
            
            return data;
        } catch (error) {
            showMessage(error.message, true);
            throw error;
        }
    }

    document.getElementById('addForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'add');
        
        try {
            const response = await sendRequest(formData);
            updateProductList(response.products);
            e.target.reset();
            showMessage('Item added successfully!');
        } catch (error) {
            // Error already shown by sendRequest
        }
    });

    function attachEditDeleteHandlers() {
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const productItem = btn.closest('.product-item');
                const id = productItem.dataset.id;
                
                const newName = prompt('Enter new name:', productItem.querySelector('h3').textContent);
                if (!newName) return;

                const priceText = productItem.querySelector('p').textContent.replace('₱', '');
                const newPrice = parseFloat(prompt('Enter new price:', priceText));
                if (isNaN(newPrice)) {
                    showMessage('Invalid price entered', true);
                    return;
                }

                const currentCategory = productItem.querySelector('small').textContent.split(': ')[1];
                const newCategory = prompt('Enter category (foods/drinks/promo):', currentCategory);
                if (!newCategory || !['foods', 'drinks', 'promo'].includes(newCategory)) {
                    showMessage('Invalid category entered', true);
                    return;
                }

                const newImage = prompt('Enter new image path:', productItem.querySelector('img').src.split('?')[0]);
                if (!newImage) return;

                const formData = new FormData();
                formData.append('action', 'edit');
                formData.append('id', id);
                formData.append('name', newName);
                formData.append('price', newPrice);
                formData.append('category', newCategory);
                formData.append('image', newImage);

                try {
                    const response = await sendRequest(formData);
                    updateProductList(response.products);
                    showMessage('Item updated successfully!');
                } catch (error) {
                    
                }
            });
        });

        document.querySelectorAll('.deleteBtn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Are you sure you want to delete this item?')) return;
                const id = btn.closest('.product-item').dataset.id;
                
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                try {
                    const response = await sendRequest(formData);
                    updateProductList(response.products);
                    showMessage('Item deleted successfully!');
                } catch (error) {

                }
            });
        });
    }

    function updateProductList(products) {
        const productList = document.getElementById('productList');
        productList.innerHTML = products.map(product => `
            <div class="product-item" data-id="${product.product_id}">
                <img src="${product.image_path}?${Date.now()}" style="width: 80px; margin-right: 15px;" onerror="this.src='../images/placeholder.png'">
                <div style="flex-grow: 1;">
                    <h3>${product.product_name}</h3>
                    <p>₱${parseFloat(product.base_price).toFixed(2)}</p>
                    <small>Category: ${product.category}</small>
                </div>
                <button class="editBtn">Edit</button>
                <button class="deleteBtn">Delete</button>
            </div>
        `).join('');

        attachEditDeleteHandlers();
    }

    attachEditDeleteHandlers();
    </script>
</body>
</html>