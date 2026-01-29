<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $get_img = $conn->prepare("SELECT image_url FROM cars WHERE id = ?");
    $get_img->bind_param("i", $id);
    $get_img->execute();
    $res = $get_img->get_result();
    $car_data = $res->fetch_assoc();
    if($car_data && !empty($car_data['image_url'])) {
        @unlink("../assets/img/" . $car_data['image_url']);
    }

    $sql = "DELETE FROM cars WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: manage_cars.php?msg=deleted");
        exit();
    }
}

$result = $conn->query("SELECT * FROM cars ORDER BY id DESC");
include 'admin_header.php'; 
?>

<style>
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .btn-add { background: var(--accent-color); color: #0f172a; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600; transition: 0.3s; }
    table { width: 100%; border-collapse: collapse; background: var(--secondary-bg); border-radius: 12px; overflow: hidden; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
    th { background: #334155; color: var(--accent-color); }
    .car-img { width: 70px; height: 45px; border-radius: 6px; object-fit: cover; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
    .available { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; }
    .booked { background: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid #fbbf24; }
    .action-links a { text-decoration: none; margin-right: 15px; font-size: 13px; font-weight: bold; }
</style>

<div class="header-actions">
    <h2 style="color: var(--accent-color); margin: 0;">Fleet Management</h2>
    <a href="add_car.php" class="btn-add"><i class="fas fa-plus"></i> Add New Car</a>
</div>

<table>
    <thead>
        <tr>
            <th>Preview</th>
            <th>Car Details</th>
            <th>Daily Rate</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><img src="../assets/img/<?php echo $row['image_url']; ?>" class="car-img"></td>
            <td>
                <strong><?php echo htmlspecialchars($row['brand']); ?></strong><br>
                <small style="color: var(--text-gray);"><?php echo htmlspecialchars($row['model_name']); ?></small>
            </td>
            <td><?php echo number_format($row['price_per_day'], 2); ?> ETB</td>
            <td>
                <span class="status-badge <?php echo ($row['status'] == 'available') ? 'available' : 'booked'; ?>">
                    <?php echo strtoupper($row['status']); ?>
                </span>
            </td>
            <td class="action-links">
                <a href="edit_car.php?id=<?php echo $row['id']; ?>" style="color: var(--accent-color);">Edit</a>
                <a href="manage_cars.php?delete_id=<?php echo $row['id']; ?>" style="color: #f87171;" onclick="return confirm('Delete this car?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div> </body>
</html>