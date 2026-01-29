<?php
session_start(); 
require_once 'includes/db_connect.php';

$message = "";
    
// --- bezu dekika pageun sayneka koyeto kawetaw le useru meknyatun endinagraw madregya nw
if (isset($_GET['message']) && $_GET['message'] == 'expired') {
    $message = "Your session has expired for your security. Please login again.";
}
// sewyew yasgebawen meraja mekabaya

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, fullname, password, role, profile_pic FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
       
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname']; 
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['profile_pic'] = $user['profile_pic']; 

            header("Location: index.php");
            exit();
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "No account found with that email!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | GP3 Car Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
       
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #0f172a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #1e293b; color: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); width: 100%; max-width: 400px; text-align: center; }
        h2 { margin-bottom: 20px; color: #38bdf8; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #334155; border: 1px solid #475569; border-radius: 8px; color: white; outline: none; }
        button { width: 100%; padding: 12px; margin-top: 20px; background: #38bdf8; border: none; border-radius: 8px; color: #0f172a; font-weight: 600; cursor: pointer; }
        .error { color: #f87171; margin-bottom: 15px; font-size: 14px; }
        a { color: #38bdf8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome Back</h2>
        <?php if($message) echo "<p class='error'>$message</p>"; ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        <p style="font-size: 14px; margin-top: 20px; color: #94a3b8;">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </div>
</body>
</html>