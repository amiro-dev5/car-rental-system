<?php
require_once 'includes/db_connect.php';
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT * FROM messages WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<div style="padding: 40px 5%; max-width: 800px; margin: auto; min-height: 80vh;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="color: #38bdf8;">My Support Tickets</h2>
        <p style="color: #94a3b8;">Track your inquiries and admin responses here.</p>
    </div>

    <?php if($res->num_rows > 0): ?>
        <?php while($msg = $res->fetch_assoc()): ?>
            <div style="background: #1e293b; padding: 25px; border-radius: 15px; margin-bottom: 25px; border: 1px solid #334155; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <h3 style="color: #38bdf8; margin: 0; font-size: 18px;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
                    <small style="color: #64748b; font-size: 11px;"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></small>
                </div>
                
                <p style="color: #cbd5e1; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>

                <?php if($msg['reply']): ?>
                    <div style="background: rgba(74, 222, 128, 0.05); border-left: 4px solid #4ade80; padding: 15px; margin-top: 20px; border-radius: 0 10px 10px 0;">
                        <strong style="color: #4ade80; font-size: 13px;"><i class="fas fa-reply"></i> Admin Response:</strong>
                        <p style="color: #f8fafc; margin-top: 8px; font-size: 14px;"><?php echo nl2br(htmlspecialchars($msg['reply'])); ?></p>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 15px; display: flex; align-items: center; gap: 8px;">
                        <div style="width: 8px; height: 8px; background: #fbbf24; border-radius: 50%;"></div>
                        <span style="color: #fbbf24; font-size: 12px; font-weight: 500;">Awaiting response from admin...</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 60px; background: #1e293b; border-radius: 20px;">
            <i class="fas fa-envelope-open-text fa-3x" style="color: #334155; margin-bottom: 20px;"></i>
            <p style="color: #94a3b8;">You haven't sent any messages yet.</p>
            <a href="contact_support.php" style="color: #38bdf8; text-decoration: none; font-weight: bold;">Contact Support Now</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>