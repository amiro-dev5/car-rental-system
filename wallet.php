<?php
require_once 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_POST['deposit_request'])) {
    $amount = $_POST['amount'];
    
    if (!empty($_FILES['receipt']['name'])) {
        $target_dir = "assets/img/deposits/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_name = time() . "_" . basename($_FILES['receipt']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO wallet_deposits (user_id, amount, receipt_image) VALUES (?, ?, ?)");
            $stmt->bind_param("ids", $user_id, $amount, $file_name);
            if ($stmt->execute()) {
                $message = "<div style='color: #4ade80; background: rgba(74, 222, 128, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;'>Deposit request sent! Wait for admin approval.</div>";
            }
        }
    }
}

$user_data = $conn->query("SELECT wallet_balance FROM users WHERE id = $user_id")->fetch_assoc();

$history = $conn->query("SELECT * FROM wallet_deposits WHERE user_id = $user_id ORDER BY id DESC");
?>

<div style="padding: 40px 5%; max-width: 1000px; margin: auto; font-family: 'Poppins', sans-serif; color: white;">
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #38bdf8, #1d4ed8); padding: 40px; border-radius: 20px; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
            <i class="fas fa-wallet fa-3x" style="margin-bottom: 15px; opacity: 0.8;"></i>
            <h3 style="margin: 0; font-weight: 300;">Current Balance</h3>
            <h1 style="margin: 10px 0; font-size: 48px;"><?php echo number_format($user_data['wallet_balance'], 2); ?> <small style="font-size: 18px;">ETB</small></h1>
        </div>

        <div style="background: #1e293b; padding: 30px; border-radius: 20px; border: 1px solid #334155;">
            <h3 style="color: #38bdf8; margin-top: 0;"><i class="fas fa-plus-circle"></i> Add Money</h3>
            <?php echo $message; ?>
            <p style="font-size: 13px; color: #94a3b8; margin-bottom: 15px;">Transfer to CBE: <strong>100012345678</strong> (GRP3 RENTAL) then upload screenshot.</p>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="number" name="amount" placeholder="Amount (ETB)" required style="width: 100%; padding: 12px; margin-bottom: 15px; background: #0f172a; border: 1px solid #334155; color: white; border-radius: 8px;">
                <label style="display: block; font-size: 12px; color: #94a3b8; margin-bottom: 5px;">Upload Receipt/Screenshot</label>
                <input type="file" name="receipt" required style="margin-bottom: 15px; font-size: 12px;">
                <button type="submit" name="deposit_request" style="width: 100%; padding: 12px; background: #38bdf8; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Send Deposit Request</button>
            </form>
        </div>
    </div>

    <h3 style="border-left: 4px solid #38bdf8; padding-left: 15px; margin-bottom: 20px;">Deposit History</h3>
    <div style="background: #1e293b; border-radius: 15px; overflow: hidden; border: 1px solid #334155;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: #0f172a;">
                <tr>
                    <th style="padding: 15px;">Date</th>
                    <th style="padding: 15px;">Amount</th>
                    <th style="padding: 15px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $history->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #334155;">
                    <td style="padding: 15px; font-size: 14px;"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td style="padding: 15px; font-weight: bold;"><?php echo number_format($row['amount'], 2); ?> ETB</td>
                    <td style="padding: 15px;">
                        <span style="padding: 5px 12px; border-radius: 20px; font-size: 11px; text-transform: uppercase; font-weight: bold; 
                            background: <?php echo ($row['status']=='approved' ? 'rgba(74,222,128,0.1)' : 'rgba(248,113,113,0.1)'); ?>;
                            color: <?php echo ($row['status']=='approved' ? '#4ade80' : ($row['status']=='pending' ? '#fbbf24' : '#f87171')); ?>;">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>