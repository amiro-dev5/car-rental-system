<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Car Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-bg: #0f172a;
            --secondary-bg: #1e293b;
            --accent-color: #38bdf8;
            --text-white: #f8fafc;
            --text-gray: #94a3b8;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--primary-bg); color: var(--text-white); overflow-x: hidden; }

        .menu-toggle { display: none; position: fixed; top: 20px; right: 20px; z-index: 1000; background: var(--accent-color); color: var(--primary-bg); padding: 10px; border-radius: 5px; cursor: pointer; }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--secondary-bg);
            position: fixed;
            left: 0;
            top: 0;
            padding: 30px 20px;
            transition: 0.3s;
            z-index: 999;
        }

        .logo { font-size: 22px; font-weight: 600; color: var(--accent-color); margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }

        .nav-links { list-style: none; }
        .nav-links li { margin-bottom: 8px; }
        .nav-links a {
            text-decoration: none;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 10px;
            transition: 0.3s;
            font-size: 14px;
        }

        .nav-links a i { margin-right: 12px; width: 20px; }
        .nav-links a:hover, .nav-links a.active { background: rgba(56, 189, 248, 0.1); color: var(--accent-color); }

        .divider { height: 1px; background: #334155; margin: 20px 0; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
            transition: 0.3s;
        }

        @media (max-width: 768px) {
            .menu-toggle { display: block; }
            .sidebar { left: -100%; width: 240px; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; padding: 20px; padding-top: 80px; }
        }
    </style>
</head>
<body>

    <div class="menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>

    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class="fas fa-car-side"></i> Rental Admin
        </div>
        
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="manage_cars.php"><i class="fas fa-car"></i> Add car</a></li>
            <li><a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
             <li><a href="manage_deposits.php"><i class="fas fa-wallet"></i> Manage Wallet</a></li>
            
            <div class="divider"></div>
            
            <li><a href="../index.php"><i class="fas fa-external-link-alt"></i> Go to Home Page</a></li>
            <li><a href="../logout.php" style="color: #f87171;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

    <script>
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>