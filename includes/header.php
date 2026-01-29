<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } 

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 60)) {
        session_unset();     
        session_destroy();   
        header("Location: login.php?message=expired");
        exit();
    }
    $_SESSION['last_activity'] = time(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRP3 CAR RENTAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        
        .user-top-bar {
            background: #020617; 
            padding: 12px 5%; 
            border-bottom: 1px solid #1e293b;
            display: flex;
            justify-content: center; 
            align-items: center;
        }
        
        .user-profile-link {
            display: flex;
            align-items: center;
            gap: 15px; 
            text-decoration: none;
            color: #38bdf8; 
            font-weight: 500;
            font-size: 15px; 
            transition: 0.3s;
        }

        .user-profile-link:hover {
            opacity: 0.8;
        }

        .user-avatar {
            width: 45px; 
            height: 45px; 
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            background: #0f172a;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: 0.3s;
        }

        .menu-icon {
            display: none;
            font-size: 24px;
            color: #38bdf8;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .menu-icon { display: block; }
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                background: #1e293b;
                padding: 20px;
                gap: 15px;
                border-bottom: 2px solid #38bdf8;
            }
            .nav-links.active { display: flex; }
        }
    </style>
</head>
<body>

<?php if(isset($_SESSION['user_id'])): ?>
    <div class="user-top-bar">
        <a href="my_profile.php" class="user-profile-link">
            <?php 
                $p_img = (isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])) ? $_SESSION['profile_pic'] : 'default_avatar.png';
            ?>
            <img src="assets/img/profiles/<?php echo $p_img; ?>" alt="Profile" class="user-avatar" onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png'">
            <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong></span>
            <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
        </a>
    </div>
<?php endif; ?>

<nav>
    <div class="logo" style="font-weight:800; color:#38bdf8; font-size:24px;">GRP3 CAR RENTAL</div>
    
    <div class="menu-icon" onclick="toggleMenu()">☰</div>

    <div class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="cars.php">Rent a Car</a>

        
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="my_profile.php">My Profile</a> 
            <a href="my_messages.php">Messages</a>
             <a href="wallet.php">Wallet</a>
            
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <a href="admin/dashboard.php" style="color: #fbbf24; font-weight:600;"><i class="fas fa-user-shield"></i> Admin Panel</a>
            <?php endif; ?>
            
            <a href="logout.php" style="color: #f87171;">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
             <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<script>
    function toggleMenu() {
        document.getElementById('navLinks').classList.toggle('active');
    }
</script>