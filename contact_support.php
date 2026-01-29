<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message_sent = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $subject = htmlspecialchars($_POST['subject']);
    $msg_text = htmlspecialchars($_POST['message']);

    if (!empty($subject) && !empty($msg_text)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $subject, $msg_text);
        
        if ($stmt->execute()) {
            $message_sent = true;
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Support | Car Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .support-container { background: #1e293b; padding: 40px; border-radius: 20px; width: 100%; max-width: 500px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); border: 1px solid #334155; }
        h2 { color: #38bdf8; text-align: center; margin-bottom: 10px; }
        p.subtitle { text-align: center; color: #94a3b8; font-size: 14px; margin-bottom: 30px; }
        .input-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-size: 14px; color: #cbd5e1; }
        input, textarea { width: 100%; padding: 12px; background: #0f172a; border: 1px solid #334155; border-radius: 10px; color: white; font-size: 15px; box-sizing: border-box; }
        textarea { height: 120px; resize: none; }
        input:focus, textarea:focus { border-color: #38bdf8; outline: none; }
        .btn-send { width: 100%; padding: 14px; background: #38bdf8; border: none; border-radius: 10px; color: #0f172a; font-weight: bold; font-size: 16px; cursor: pointer; transition: 0.3s; }
        .btn-send:hover { background: #0ea5e9; transform: translateY(-2px); }
        /* ከታች ያለው የSuccess Msg ዲዛይን ተሻሽሏል */
        .success-msg { background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 15px; border-radius: 10px; text-align: center; margin-bottom: 20px; border: 1px solid rgba(74, 222, 128, 0.3); }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="support-container">
    <h2><i class="fas fa-headset"></i> Support Center</h2>
    <p class="subtitle">Have a question or complaint? We're here to help.</p>

    <?php if($message_sent): ?>
        <div class="success-msg">
            <i class="fas fa-check-circle"></i> 
            <strong>Message Sent Successfully!</strong><br>
            <span style="font-size: 13px; opacity: 0.9;">We have received your inquiry. Please expect a response within 24 hours.</span>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div style="background: rgba(248, 113, 113, 0.1); color: #f87171; padding: 10px; border-radius: 10px; text-align: center; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="input-group">
            <label>Subject</label>
            <input type="text" name="subject" placeholder="What is this about?" required>
        </div>
        <div class="input-group">
            <label>Your Message</label>
            <textarea name="message" placeholder="Describe your issue here..." required></textarea>
        </div>
        <button type="submit" class="btn-send">
            <i class="fas fa-paper-plane"></i> Send Message
        </button>
    </form>
    
    <a href="index.php" class="back-link">← Back to Home</a>
</div>

</body>
</html>