<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['change_role']) && isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
    $new_role = $_GET['change_role'];

    if ($target_id === 1) {
        header("Location: users.php?error=SuperAdminProtection");
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $target_id);
        $stmt->execute();
        header("Location: users.php?msg=RoleUpdated");
    }
    exit();
}

if (isset($_GET['delete_id'])) {
    $u_id = (int)$_GET['delete_id'];
    if ($u_id !== 1) { 
        $conn->query("DELETE FROM bookings WHERE user_id = $u_id");
        $conn->query("DELETE FROM users WHERE id = $u_id");
        header("Location: users.php?msg=Deleted");
    }
    exit();
}

$res = $conn->query("SELECT id, fullname, email, role, created_at FROM users ORDER BY id ASC");

include 'admin_header.php';
?>

<style>
    .user-table { width: 100%; border-collapse: collapse; background: var(--secondary-bg); border-radius: 15px; overflow: hidden; }
    .user-table th, .user-table td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
    .user-table th { background: #334155; color: var(--accent-color); font-size: 13px; }
    .badge { padding: 4px 10px; border-radius: 5px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .badge-admin { background: rgba(56, 189, 248, 0.2); color: #38bdf8; border: 1px solid #38bdf8; }
    .badge-customer { background: rgba(148, 163, 184, 0.2); color: #94a3b8; border: 1px solid #94a3b8; }
    .role-btn { text-decoration: none; font-size: 12px; padding: 5px 10px; border-radius: 5px; font-weight: bold; border: 1px solid; transition: 0.3s; }
    .btn-make-admin { color: #4ade80; border-color: #4ade80; }
    .btn-make-admin:hover { background: #4ade80; color: #0f172a; }
    .btn-make-customer { color: #fbbf24; border-color: #fbbf24; }
    .btn-make-customer:hover { background: #fbbf24; color: #0f172a; }
</style>

<div style="padding: 20px;">
    <h1>User Management</h1>
    <p style="color: var(--text-gray);">Manage accounts and permissions.</p>

    <?php if(isset($_GET['error'])): ?>
        <div style="background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            The Super Admin account cannot be modified!
        </div>
    <?php endif; ?>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Permissions</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $res->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $user['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($user['fullname']); ?></strong></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <span class="badge <?php echo ($user['role'] == 'admin') ? 'badge-admin' : 'badge-customer'; ?>">
                        <?php echo $user['role']; ?>
                    </span>
                </td>
                <td>
                    <?php if($user['id'] != 1):  ?>
                        <?php if($user['role'] == 'customer'): ?>
                            <a href="users.php?id=<?php echo $user['id']; ?>&change_role=admin" class="role-btn btn-make-admin">Make Admin</a>
                        <?php else: ?>
                            <a href="users.php?id=<?php echo $user['id']; ?>&change_role=customer" class="role-btn btn-make-customer">Demote to Customer</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="font-size: 11px; color: #4ade80;">Super User (Fixed)</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($user['id'] != 1): ?>
                        <a href="users.php?delete_id=<?php echo $user['id']; ?>" style="color: #f87171; text-decoration: none; font-size: 13px;" onclick="return confirm('Delete this account permanently?')">Remove</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</div> </body> </html>