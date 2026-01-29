<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. መረጃ የማዘመን ስራ (Profile Update Logic)
if (isset($_POST['update_profile'])) {
    $fullname = htmlspecialchars($_POST['fullname']);
    $phone = htmlspecialchars($_POST['phone']);
    $bio = isset($_POST['bio']) ? htmlspecialchars($_POST['bio']) : "";

    $user_check = $conn->query("SELECT profile_pic FROM users WHERE id = $user_id")->fetch_assoc();
    $profile_pic = $user_check['profile_pic'];

    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "assets/img/profiles/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_pic = $file_name;
            $_SESSION['profile_pic'] = $profile_pic;
        }
    }

    $stmt = $conn->prepare("UPDATE users SET fullname=?, phone=?, bio=?, profile_pic=? WHERE id=?");
    $stmt->bind_param("ssssi", $fullname, $phone, $bio, $profile_pic, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['fullname'] = $fullname;
        $message = "<div style='color: #4ade80; background: rgba(74, 222, 128, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Profile updated successfully!</div>";
    }
}

// 2. ፓስወርድ መቀየር (Password Change Logic)
if (isset($_POST['change_password'])) {
    $current_pw = $_POST['current_password'];
    $new_pw = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $pw_query = $conn->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc();
    
    if (password_verify($current_pw, $pw_query['password'])) {
        $conn->query("UPDATE users SET password = '$new_pw' WHERE id = $user_id");
        $message = "<div style='color: #4ade80; background: rgba(74, 222, 128, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Password changed successfully!</div>";
    } else {
        $message = "<div style='color: #f87171; background: rgba(248, 113, 113, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Current password is incorrect!</div>";
    }
}

// 3. የኪራይ ታሪክ መረጃ
$sql = "SELECT bookings.*, cars.brand, cars.model_name, cars.price_per_day, cars.image_url 
        FROM bookings 
        JOIN cars ON bookings.car_id = cars.id 
        WHERE bookings.user_id = $user_id 
        ORDER BY bookings.id DESC";
$booking_result = $conn->query($sql);

$user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
?>

<div style="padding: 40px 5%; max-width: 1200px; margin: auto; font-family: 'Poppins', sans-serif;">
    
    <?php echo $message; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-bottom: 50px;">
        
        <div style="background: #1e293b; padding: 30px; border-radius: 20px; border: 1px solid #334155; text-align: center;">
            <img src="assets/img/profiles/<?php echo $user_data['profile_pic'] ?: 'default_avatar.png'; ?>" 
                 style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid #38bdf8;"
                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'">
            <h2 style="margin: 15px 0 5px 0; color: #38bdf8;"><?php echo htmlspecialchars($user_data['fullname']); ?></h2>
            <p style="color: #94a3b8; font-size: 13px;">User Dashboard</p>
            <div style="margin-top: 15px; padding: 10px; background: rgba(56, 189, 248, 0.1); border-radius: 10px;">
                <small style="color: #94a3b8;">Wallet Balance</small>
                <div style="color: #4ade80; font-weight: bold;"><?php echo number_format($user_data['wallet_balance'], 2); ?> ETB</div>
            </div>
        </div>

        <div style="background: #1e293b; padding: 25px; border-radius: 20px; border: 1px solid #334155;">
            <h4 style="color: #38bdf8; margin-top: 0;">Update Profile</h4>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="text" name="fullname" value="<?php echo $user_data['fullname']; ?>" style="width: 100%; padding: 10px; margin-bottom: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 5px;">
                <input type="text" name="phone" value="<?php echo $user_data['phone']; ?>" style="width: 100%; padding: 10px; margin-bottom: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 5px;">
                <input type="file" name="profile_image" style="color: #94a3b8; font-size: 12px; margin-bottom: 10px;">
                <button type="submit" name="update_profile" style="width: 100%; padding: 10px; background: #38bdf8; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Save Profile</button>
            </form>
        </div>

        <div style="background: #1e293b; padding: 25px; border-radius: 20px; border: 1px solid #334155;">
            <h4 style="color: #f87171; margin-top: 0;">Change Password</h4>
            <form action="" method="POST">
                <input type="password" name="current_password" placeholder="Current Password" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 5px;">
                <input type="password" name="new_password" placeholder="New Password" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 5px;">
                <button type="submit" name="change_password" style="width: 100%; padding: 10px; background: #f87171; border: none; color: white; border-radius: 5px; cursor: pointer; font-weight: bold;">Update Password</button>
            </form>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2 style="color: #38bdf8; border-left: 5px solid #38bdf8; padding-left: 15px; margin: 0;">My Rental Requests</h2>
        <a href="contact_support.php" style="background: #38bdf8; color: #0f172a; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px;">
            <i class="fas fa-headset"></i> Need Help?
        </a>
    </div>

    <?php if ($booking_result->num_rows > 0): ?>
        <div id="booking-list" style="display: grid; gap: 20px;">
            <?php 
            $count = 0;
            while($row = $booking_result->fetch_assoc()): 
                $start = new DateTime($row['start_date']);
                $end = new DateTime($row['end_date']);
                $days = $start->diff($end)->days ?: 1;
                $total_price = $days * $row['price_per_day'];
                
                $status_color = '#94a3b8';
                if($row['status'] == 'approved') $status_color = '#4ade80';
                if($row['status'] == 'rejected') $status_color = '#f87171';
                if($row['status'] == 'completed') $status_color = '#38bdf8';
                
                $count++;
                $is_hidden = ($count > 5) ? 'style="display:none;" class="booking-item extra-item"' : 'class="booking-item"';
            ?>
                <div <?php echo $is_hidden; ?> style="background: #1e293b; border-radius: 15px; padding: 20px; display: flex; align-items: center; flex-wrap: wrap; border: 1px solid #334155; gap: 20px;">
                    <img src="assets/img/<?php echo $row['image_url']; ?>" style="width: 120px; height: 80px; object-fit: cover; border-radius: 10px; background: #0f172a;">
                    <div style="flex: 2; min-width: 200px;">
                        <h3 style="margin: 0; color: white;"><?php echo $row['brand'] . " " . $row['model_name']; ?></h3>
                        <p style="color: #94a3b8; font-size: 14px; margin: 5px 0;"><i class="far fa-calendar-alt"></i> <?php echo $row['start_date']; ?> to <?php echo $row['end_date']; ?></p>
                        <small style="color: #38bdf8;"><?php echo $days; ?> Days Rental</small>
                    </div>
                    <div style="flex: 1; text-align: center; min-width: 120px;">
                        <p style="color: #4ade80; font-weight: bold; margin: 0; font-size: 18px;"><?php echo number_format($total_price); ?> ETB</p>
                        <small style="color: #94a3b8;">Total Amount</small>
                    </div>
                    <div style="flex: 1; text-align: right; min-width: 120px;">
                        <span style="display: inline-block; padding: 6px 15px; border-radius: 20px; background: rgba(0,0,0,0.2); color: <?php echo $status_color; ?>; font-weight: bold; text-transform: uppercase; font-size: 11px; border: 1px solid <?php echo $status_color; ?>;">
                            <?php echo $row['status']; ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($count > 5): ?>
        <div style="text-align: center; margin-top: 25px;">
            <button id="seeMoreBtn" onclick="showMore()" style="background: transparent; border: 1px solid #38bdf8; color: #38bdf8; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                See More Requests <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: #1e293b; border-radius: 20px; border: 1px dashed #334155;">
            <i class="fas fa-car-side fa-3x" style="color: #334155; margin-bottom: 20px;"></i>
            <p style="color: #94a3b8; font-size: 18px;">You haven't made any rental requests yet.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function showMore() {
    const extraItems = document.querySelectorAll('.extra-item');
    extraItems.forEach(item => {
        item.style.display = 'flex';
    });
    document.getElementById('seeMoreBtn').style.display = 'none';
}
</script>

<?php include 'includes/footer.php'; ?>