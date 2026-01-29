<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['read_id'])) {
    $m_id = (int)$_GET['read_id'];
    $conn->query("UPDATE messages SET status = 'read' WHERE id = $m_id AND status = 'unread'");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_reply'])) {
    $m_id = (int)$_POST['msg_id'];
    $reply_text = htmlspecialchars($_POST['reply_content']);
    $stmt = $conn->prepare("UPDATE messages SET reply = ?, status = 'replied' WHERE id = ?");
    $stmt->bind_param("si", $reply_text, $m_id);
    $stmt->execute();
    header("Location: messages.php?msg=replied");
    exit();
}

if (isset($_GET['delete_id'])) {
    $m_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM messages WHERE id = $m_id");
    header("Location: messages.php?msg=deleted");
    exit();
}

$sql = "SELECT m.*, u.fullname, u.email 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        ORDER BY m.status ASC, m.created_at DESC";
$res = $conn->query($sql);

include 'admin_header.php';
?>

<style>
    .msg-card {
        background: var(--secondary-bg);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 5px solid #334155;
        transition: 0.3s;
    }
    .unread { border-left-color: var(--accent-color); background: rgba(56, 189, 248, 0.05); }
    .replied { border-left-color: #4ade80; }
    .msg-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .sender-info { font-size: 14px; color: var(--text-gray); }
    .msg-subject { font-weight: 600; color: var(--accent-color); font-size: 16px; margin: 5px 0; }
    .msg-body { color: #cbd5e1; line-height: 1.6; font-size: 14px; background: #0f172a; padding: 15px; border-radius: 8px; margin: 10px 0; }
    .msg-footer { font-size: 12px; color: #64748b; display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
    .status-badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
    .badge-unread { background: var(--accent-color); color: #0f172a; }
    .badge-read { background: #334155; color: #94a3b8; }
    .badge-replied { background: #4ade80; color: #0f172a; }
    .reply-form { margin-top: 15px; background: #1e293b; padding: 15px; border-radius: 10px; display: none; }
    .action-btn { text-decoration: none; font-size: 12px; font-weight: bold; margin-left: 15px; cursor: pointer; }
</style>

<div style="padding: 10px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Customer Support Inbox</h1>
        <div style="font-size: 14px; color: var(--text-gray);">
            Total: <strong><?php echo $res->num_rows; ?> Messages</strong>
        </div>
    </div>

    <?php if($res->num_rows > 0): ?>
        <?php while($msg = $res->fetch_assoc()): ?>
            <div class="msg-card <?php echo $msg['status']; ?>">
                <div class="msg-header">
                    <div class="sender-info">
                        <i class="fas fa-user-circle"></i> <strong><?php echo htmlspecialchars($msg['fullname']); ?></strong> 
                        <span style="margin: 0 10px;">|</span> 
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($msg['email']); ?>
                    </div>
                    <span class="status-badge badge-<?php echo $msg['status']; ?>">
                        <?php echo $msg['status']; ?>
                    </span>
                </div>

                <div class="msg-subject">Subject: <?php echo htmlspecialchars($msg['subject']); ?></div>
                <div class="msg-body"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>

                <?php if($msg['reply']): ?>
                    <div style="margin-top: 15px; padding: 15px; background: rgba(74, 222, 128, 0.05); border-radius: 8px; border-left: 3px solid #4ade80;">
                        <span style="color: #4ade80; font-size: 13px; font-weight: bold;"><i class="fas fa-reply"></i> Your Response:</span>
                        <p style="color: #cbd5e1; font-size: 14px; margin-top: 5px;"><?php echo nl2br(htmlspecialchars($msg['reply'])); ?></p>
                    </div>
                <?php endif; ?>

                <div id="reply_box_<?php echo $msg['id']; ?>" class="reply-form">
                    <form action="" method="POST">
                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                        <textarea name="reply_content" style="width:100%; height:80px; background:#0f172a; color:white; border:1px solid #334155; padding:10px; border-radius:8px; resize:none;" placeholder="Type your response here..." required></textarea>
                        <div style="margin-top:10px;">
                            <button type="submit" name="send_reply" style="background:#4ade80; color:#0f172a; border:none; padding:8px 20px; border-radius:5px; font-weight:bold; cursor:pointer;">Send Reply</button>
                            <button type="button" onclick="document.getElementById('reply_box_<?php echo $msg['id']; ?>').style.display='none'" style="background:transparent; color:white; border:none; margin-left:10px; cursor:pointer;">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="msg-footer">
                    <span><i class="fas fa-clock"></i> Sent on: <?php echo date('M d, Y - h:i A', strtotime($msg['created_at'])); ?></span>
                    <div>
                        <?php if(!$msg['reply']): ?>
                            <a onclick="document.getElementById('reply_box_<?php echo $msg['id']; ?>').style.display='block'" class="action-btn" style="color: #4ade80;">
                                <i class="fas fa-comment-dots"></i> Reply
                            </a>
                        <?php endif; ?>

                        <?php if($msg['status'] == 'unread'): ?>
                            <a href="messages.php?read_id=<?php echo $msg['id']; ?>" class="action-btn" style="color: var(--accent-color);">
                                <i class="fas fa-check-double"></i> Mark as Read
                            </a>
                        <?php endif; ?>

                        <a href="messages.php?delete_id=<?php echo $msg['id']; ?>" class="action-btn" style="color: #f87171;" onclick="return confirm('Delete this message?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 100px; color: var(--text-gray); background: var(--secondary-bg); border-radius: 15px;">
            <i class="fas fa-inbox fa-3x" style="margin-bottom: 20px; opacity: 0.3;"></i>
            <p>Your inbox is empty. No messages from customers yet.</p>
        </div>
    <?php endif; ?>
</div>

</div> </body>
</html>