<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = htmlspecialchars($_POST['brand']);
    $model = htmlspecialchars($_POST['model_name']);
    $price = $_POST['price_per_day'];
    
    $target_dir = "../assets/img/";
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_name = time() . "_" . basename($_FILES["car_image"]["name"]);
    $target_file = $target_dir . $image_name;

    if (move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO cars (brand, model_name, price_per_day, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssds", $brand, $model, $price, $image_name);
        
        if ($stmt->execute()) {
            header("Location: manage_cars.php?msg=added");
            exit();
        } else {
            $message = "Database error occured.";
        }
    } else {
        $message = "Failed to upload image.";
    }
}

include 'admin_header.php'; 
?>

<style>
    .form-container-box {
        background: var(--secondary-bg);
        padding: 40px;
        border-radius: 20px;
        max-width: 650px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        border: 1px solid #334155;
    }
    .input-group { margin-bottom: 20px; }
    .input-group label { display: block; margin-bottom: 8px; color: var(--text-gray); font-size: 14px; }
    .input-group input {
        width: 100%;
        padding: 14px;
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 10px;
        color: white;
        transition: 0.3s;
    }
    .input-group input:focus { border-color: var(--accent-color); outline: none; }
    .btn-save {
        background: var(--accent-color);
        color: #0f172a;
        padding: 15px 30px;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }
    .btn-save:hover { opacity: 0.9; transform: translateY(-2px); }
</style>

<div style="max-width: 900px;">
    <h1 style="margin-bottom: 10px;">Register Vehicle</h1>
    <p style="color: var(--text-gray); margin-bottom: 30px;">Fill in the details to add a new car to your rental fleet.</p>

    <?php if($message): ?>
        <div style="background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f87171;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="form-container-box">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label><i class="fas fa-tag"></i> Brand Name</label>
                <input type="text" name="brand" placeholder="e.g. Toyota" required>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-car"></i> Model Name</label>
                <input type="text" name="model_name" placeholder="e.g. Corolla / Vitz" required>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-money-bill-wave"></i> Price Per Day (ETB)</label>
                <input type="number" name="price_per_day" placeholder="Amount in ETB" required>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-image"></i> Car Exterior Image</label>
                <input type="file" name="car_image" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn-save">
                <i class="fas fa-cloud-upload-alt"></i> Confirm & Save Vehicle
            </button>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="manage_cars.php" style="color: var(--text-gray); text-decoration: none; font-size: 14px;">
                    <i class="fas fa-arrow-left"></i> Discard and Go Back
                </a>
            </div>
        </form>
    </div>
</div>

</div> </body>
</html>