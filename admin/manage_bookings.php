<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['delete_id'])) {
    $d_id = $_GET['delete_id'];
    $conn->query("DELETE FROM bookings WHERE id = $d_id");
    header("Location: manage_bookings.php?msg=Deleted");
    exit();
}

if (isset($_GET['approve_id'])) {
    $b_id = $_GET['approve_id'];
    $c_id = $_GET['car_id'];
    $conn->query("UPDATE bookings SET status = 'approved' WHERE id = $b_id");
    $conn->query("UPDATE cars SET status = 'booked' WHERE id = $c_id");
    header("Location: manage_bookings.php?msg=Approved");
    exit();
}

if (isset($_GET['reject_id'])) {
    $b_id = $_GET['reject_id'];
    $info_query = "SELECT b.user_id, b.start_date, b.end_date, c.price_per_day 
                   FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.id = $b_id";
    $info = $conn->query($info_query)->fetch_assoc();
    
    if($info) {
        $start = new DateTime($info['start_date']);
        $end = new DateTime($info['end_date']);
        $days = $start->diff($end)->days ?: 1;
        $refund_amount = $days * $info['price_per_day'];
        
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE users SET wallet_balance = wallet_balance + $refund_amount WHERE id = " . $info['user_id']);
            $conn->query("UPDATE bookings SET status = 'rejected' WHERE id = $b_id");
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            die("Refund Failed: " . $e->getMessage());
        }
    }
    header("Location: manage_bookings.php?msg=Rejected");
    exit();
}

if (isset($_GET['return_car_id']) && isset($_GET['booking_id'])) {
    $c_id = $_GET['return_car_id'];
    $b_id = $_GET['booking_id'];
    $conn->query("UPDATE cars SET status = 'available' WHERE id = $c_id");
    $conn->query("UPDATE bookings SET status = 'completed' WHERE id = $b_id");
    header("Location: manage_bookings.php?msg=Returned");
    exit();
}

$res = $conn->query("SELECT bookings.*, users.fullname, cars.brand, cars.model_name, cars.price_per_day, cars.status AS car_current_status
                    FROM bookings 
                    JOIN users ON bookings.user_id = users.id 
                    JOIN cars ON bookings.car_id = cars.id 
                    ORDER BY bookings.id DESC");

include 'admin_header.php'; 
?>

<style>
    table { width: 100%; border-collapse: collapse; background: var(--secondary-bg); border-radius: 12px; overflow: hidden; }
    th, td { padding: 18px; border-bottom: 1px solid #334155; text-align: left; }
    th { background: #334155; color: var(--accent-color); font-size: 13px; }
    .btn { padding: 8px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 12px; display: inline-block; margin-right: 5px; }
    .approve { background: #4ade80; color: #0f172a; }
    .reject { background: #fbbf24; color: #0f172a; } /* ቀለም ቀይሬዋለሁ ከዲሊት ጋር እንዳይመሳሰል */
    .return { background: var(--accent-color); color: #0f172a; }
    .delete { background: #f87171; color: white; } /* የመሰረዣ ቀለም */
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; border: 1px solid; }
</style>

<h2 style="margin-bottom: 30px;">Rental Requests Management</h2>

<table>
    <thead>
        <tr>
            <th>Customer</th>
            <th>Car</th>
            <th>Duration</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
            <td><?php echo htmlspecialchars($row['brand'] . " " . $row['model_name']); ?></td>
            <td><small><?php echo $row['start_date']; ?> to <?php echo $row['end_date']; ?></small></td>
            <td>
                <?php 
                    $s = $row['status'];
                    $cl = ($s == 'approved') ? '#4ade80' : (($s == 'rejected') ? '#f87171' : (($s == 'completed') ? '#38bdf8' : '#94a3b8'));
                ?>
                <span class="status-badge" style="color:<?php echo $cl; ?>;">
                    <?php echo $s; ?>
                </span>
            </td>
            <td>
                <div style="display: flex; align-items: center;">
                    <?php if($row['status'] == 'pending'): ?>
                        <?php if($row['car_current_status'] == 'available'): ?>
                            <a href="manage_bookings.php?approve_id=<?php echo $row['id']; ?>&car_id=<?php echo $row['car_id']; ?>" class="btn approve">Approve</a>
                        <?php else: ?>
                            <span style="color: #f87171; font-size: 11px; margin-right: 10px;">Car Taken</span>
                        <?php endif; ?>
                        <a href="manage_bookings.php?reject_id=<?php echo $row['id']; ?>" class="btn reject" onclick="return confirm('ውድቅ ይደረግ? ገንዘቡ ይመለሳል።')">Reject</a>
                    <?php elseif($row['status'] == 'approved'): ?>
                        <a href="manage_bookings.php?return_car_id=<?php echo $row['car_id']; ?>&booking_id=<?php echo $row['id']; ?>" class="btn return" onclick="return confirm('መኪናው ተመልሷል?')">Return</a>
                    <?php endif; ?>

                    <a href="manage_bookings.php?delete_id=<?php echo $row['id']; ?>" class="btn delete" onclick="return confirm('ይህ የቡኪንግ መረጃ እስከመጨረሻው ይጥፋ?')">Delete</a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>