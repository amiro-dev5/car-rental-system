<?php
require_once '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

if (isset($_GET['delete_id'])) {
    $d_id = $_GET['delete_id'];
    $conn->query("DELETE FROM wallet_deposits WHERE id = $d_id");
    header("Location: manage_deposits.php?msg=Deleted");
    exit();
}

if (isset($_POST['approve_id'])) {
    $deposit_id = $_POST['approve_id'];
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE wallet_deposits SET status = 'approved' WHERE id = $deposit_id");
        $conn->query("UPDATE users SET wallet_balance = wallet_balance + $amount WHERE id = $user_id");
        $conn->commit();
        $message = "<div class='alert success'>Deposit Approved & Wallet Updated!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='alert danger'>Error: " . $e->getMessage() . "</div>";
    }
}

if (isset($_POST['reject_id'])) {
    $deposit_id = $_POST['reject_id'];
    $conn->query("UPDATE wallet_deposits SET status = 'rejected' WHERE id = $deposit_id");
    $message = "<div class='alert danger'>Deposit Rejected!</div>";
}

$pending_sql = "SELECT d.*, u.fullname FROM wallet_deposits d JOIN users u ON d.user_id = u.id WHERE d.status = 'pending' ORDER BY d.id DESC";
$pending_res = $conn->query($pending_sql);

$history_sql = "SELECT d.*, u.fullname FROM wallet_deposits d JOIN users u ON d.user_id = u.id WHERE d.status != 'pending' ORDER BY d.id DESC";
$history_res = $conn->query($history_sql);

include 'admin_header.php'; 
?>

<style>
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
    .success { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; }
    .danger { background: rgba(248, 113, 113, 0.1); color: #f87171; border: 1px solid #f87171; }
    
    .request-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .pending-card { background: #1e293b; padding: 20px; border-radius: 15px; border: 1px solid #38bdf8; position: relative; }
    
    table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 12px; overflow: hidden; margin-top: 20px; }
    th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
    th { background: #334155; color: #38bdf8; font-size: 13px; }
    
    .receipt-img { width: 50px; height: 50px; border-radius: 5px; cursor: pointer; object-fit: cover; }
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
    .btn-sm { padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; border: none; cursor: pointer; }
</style>

<h2><i class="fas fa-wallet"></i> Manage Wallet Deposits</h2>
<p style="color: #94a3b8;">Review and approve customer deposit receipts.</p>

<?php echo $message; ?>

<h3 style="color: #fbbf24; margin-top: 30px;"><i class="fas fa-clock"></i> Pending Approvals</h3>
<?php if ($pending_res->num_rows > 0): ?>
    <div class="request-grid">
        <?php while($row = $pending_res->fetch_assoc()): ?>
            <div class="pending-card">
                <div style="display: flex; gap: 15px;">
                    <img src="../assets/img/deposits/<?php echo $row['receipt_image']; ?>" class="receipt-img" style="width: 80px; height: 80px;" onclick="viewReceipt(this.src)">
                    <div>
                        <h4 style="margin: 0;"><?php echo $row['fullname']; ?></h4>
                        <p style="color: #4ade80; font-weight: bold; margin: 5px 0;"><?php echo number_format($row['amount'], 2); ?> ETB</p>
                        <small style="color: #94a3b8;"><?php echo $row['created_at']; ?></small>
                    </div>
                </div>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                        <button type="submit" name="approve_id" value="<?php echo $row['id']; ?>" class="btn-sm" style="background: #4ade80; color: #0f172a; width: 100%;">Approve</button>
                    </form>
                    <form method="POST" style="flex: 1;">
                        <button type="submit" name="reject_id" value="<?php echo $row['id']; ?>" class="btn-sm" style="background: #f87171; color: white; width: 100%;">Reject</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p style="color: #64748b; font-style: italic;">No pending requests found.</p>
<?php endif; ?>

<h3 style="color: #38bdf8; margin-top: 50px;"><i class="fas fa-history"></i> Deposit History</h3>
<table>
    <thead>
        <tr>
            <th>Receipt</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $history_res->fetch_assoc()): ?>
            <tr>
                <td><img src="../assets/img/deposits/<?php echo $row['receipt_image']; ?>" class="receipt-img" onclick="viewReceipt(this.src)"></td>
                <td><strong><?php echo $row['fullname']; ?></strong></td>
                <td style="color: #4ade80;"><?php echo number_format($row['amount']); ?> ETB</td>
                <td><small><?php echo $row['created_at']; ?></small></td>
                <td>
                    <?php if($row['status'] == 'approved'): ?>
                        <span class="status-badge" style="border: 1px solid #4ade80; color: #4ade80;">ACCEPTED</span>
                    <?php else: ?>
                        <span class="status-badge" style="border: 1px solid #f87171; color: #f87171;">REJECTED</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="manage_deposits.php?delete_id=<?php echo $row['id']; ?>" 
                       style="color: #f87171;" 
                       onclick="return confirm('ይህ ታሪክ እስከመጨረሻው ይጥፋ?')">
                       <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div id="imgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; justify-content:center; align-items:center;" onclick="this.style.display='none'">
    <img id="fullImg" style="max-width: 90%; max-height: 90%; border: 3px solid white; border-radius: 10px;">
</div>

<script>
    function viewReceipt(src) {
        document.getElementById('imgModal').style.display = 'flex';
        document.getElementById('fullImg').src = src;
    }
</script>

