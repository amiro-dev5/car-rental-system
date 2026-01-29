<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $car = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['car_id'];
    $brand = htmlspecialchars($_POST['brand']);
    $model = htmlspecialchars($_POST['model_name']);
    $price = $_POST['price_per_day'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE cars SET brand=?, model_name=?, price_per_day=?, status=? WHERE id=?");
    $stmt->bind_param("ssdsi", $brand, $model, $price, $status, $id);
    if ($stmt->execute()) { header("Location: manage_cars.php?msg=updated"); exit(); }
}

include 'admin_header.php';
?>

<style>
    .form-box { background: var(--secondary-bg); padding: 30px; border-radius: 15px; max-width: 600px; }
    input, select { width: 100%; padding: 12px; margin: 10px 0 20px 0; background: #334155; border: 1px solid #475569; border-radius: 8px; color: white; }
    .btn-update { background: var(--accent-color); color: #0f172a; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
</style>

<h2 style="margin-bottom: 25px;">Edit Vehicle Details</h2>

<div class="form-box">
    <form action="" method="POST">
        <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
        
        <label>Brand</label>
        <input type="text" name="brand" value="<?php echo $car['brand']; ?>" required>
        
        <label>Model</label>
        <input type="text" name="model_name" value="<?php echo $car['model_name']; ?>" required>
        
        <label>Price Per Day</label>
        <input type="number" name="price_per_day" value="<?php echo $car['price_per_day']; ?>" required>
        
        <label>Availability Status</label>
        <select name="status">
            <option value="available" <?php if($car['status'] == 'available') echo 'selected'; ?>>Available</option>
            <option value="booked" <?php if($car['status'] == 'booked') echo 'selected'; ?>>Booked</option>
        </select>
        
        <button type="submit" class="btn-update">Update Car</button>
        <a href="manage_cars.php" style="color: var(--text-gray); margin-left: 15px; text-decoration: none;">Cancel</a>
    </form>
</div>

</div>
</body>
</html>