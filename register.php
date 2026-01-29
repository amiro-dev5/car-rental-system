<?php
require_once 'includes/db_connect.php';
$message = "";
$messageType = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname =htmlspecialchars($_POST['fullname']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $fullname, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "Account created successfully! <a href='login.php'>Login here</a>";
            $messageType = "success";
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        
        if ($conn->errno == 1062) {
            $message = "This email is already registered. Please try another.";
            $messageType = "error";
        } else {
            $message = "Something went wrong. Please try again later.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | GP3 Car Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { 
            background: #0f172a; 
            display: flex; justify-content: center; align-items: center; 
            height: 100vh; margin: 0; 
        }
        .register-container {
            background: #1e293b; color: white; padding: 40px;
            border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            width: 100%; max-width: 400px; text-align: center;
        }
        h2 { margin-bottom: 20px; font-weight: 600; color: #38bdf8; }
        input {
            width: 100%; padding: 12px; margin: 10px 0;
            background: #334155; border: 1px solid #475569;
            border-radius: 8px; color: white; outline: none;
        }
        input:focus { border-color: #38bdf8; }
        button {
            width: 100%; padding: 12px; margin-top: 20px;
            background: #38bdf8; border: none; border-radius: 8px;
            color: #0f172a; font-weight: 600; cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background: #0ea5e9; transform: translateY(-2px); }
        .alert {
            padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px;
        }
        .success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
        .error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
        a { color: #38bdf8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Join GP3 Rental</h2>
        <?php if($message): ?>
            <div class="alert <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
        </form>
        <p style="font-size: 14px; margin-top: 20px; color: #94a3b8;">
            Already have an account? <a href="login.php">Sign In</a>
        </p>
    </div>
</body>
</html>