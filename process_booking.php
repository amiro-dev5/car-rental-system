<?php
session_start();
require_once 'includes/db_connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #0f172a; font-family: 'Segoe UI', sans-serif; }
    </style>
</head>
<body>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Access Denied',
                text: 'Please login first!',
                confirmButtonColor: '#38bdf8'
            }).then(() => { window.location.href = 'login.php'; });
        </script>";
        exit();
    }

    $user_id = (int)$_SESSION['user_id'];
    $car_id = (int)$_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $car_query = $conn->query("SELECT price_per_day FROM cars WHERE id = $car_id");
    if ($car_query->num_rows == 0) { die("Error: Car not found."); }
    $car = $car_query->fetch_assoc();

    $user_query = $conn->query("SELECT wallet_balance FROM users WHERE id = $user_id");
    $user = $user_query->fetch_assoc();

    $date1 = new DateTime($start_date);
    $date2 = new DateTime($end_date);
    $interval = $date1->diff($date2);
    $days = $interval->days ?: 1; 
    $total_price = $days * $car['price_per_day'];

    if ($user['wallet_balance'] < $total_price) {
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Low Balance',
                text: 'Your wallet balance is insufficient.',
                confirmButtonColor: '#38bdf8'
            }).then(() => { window.location.href = 'wallet.php'; });
        </script>";
        exit();
    }

    $conn->begin_transaction();

    try {
        $update_wallet = "UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?";
        $stmt1 = $conn->prepare($update_wallet);
        $stmt1->bind_param("di", $total_price, $user_id);
        $stmt1->execute();

        $insert_booking = "INSERT INTO bookings (user_id, car_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt2 = $conn->prepare($insert_booking);
        $stmt2->bind_param("iiss", $user_id, $car_id, $start_date, $end_date);
        $stmt2->execute();

        $conn->commit();

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Request Sent Successfully!',
                text: 'Your booking is pending. Please wait for Admin approval.',
                background: '#1e293b',
                color: '#fff',
                confirmButtonColor: '#38bdf8',
                confirmButtonText: 'View My Profile'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'my_profile.php';
                }
            });
        </script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>
</body>
</html>