<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$revenue_query = $conn->query("SELECT SUM(DATEDIFF(end_date, start_date) * cars.price_per_day) AS total 
                               FROM bookings 
                               JOIN cars ON bookings.car_id = cars.id 
                               WHERE bookings.status = 'completed'");
$total_revenue = $revenue_query->fetch_assoc()['total'] ?? 0;

$active_query = $conn->query("SELECT COUNT(*) AS active FROM bookings WHERE status = 'approved'");
$active_rentals = $active_query->fetch_assoc()['active'];

$cars_query = $conn->query("SELECT COUNT(*) AS total_cars FROM cars");
$total_cars = $cars_query->fetch_assoc()['total_cars'];


$pending_query = $conn->query("SELECT COUNT(*) AS pending FROM bookings WHERE status = 'pending'");
$pending_requests = $pending_query->fetch_assoc()['pending'];


$chart_data = $conn->query("SELECT DATE(created_at) as date, SUM(total_price) as daily_total 
                            FROM bookings WHERE status = 'completed' 
                            GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
$dates = [];
$amounts = [];
while($row = $chart_data->fetch_assoc()){
    $dates[] = date('M d', strtotime($row['date']));
    $amounts[] = $row['daily_total'];
}

$recent_bookings = $conn->query("SELECT bookings.*, users.fullname, cars.brand 
                                 FROM bookings 
                                 JOIN users ON bookings.user_id = users.id 
                                 JOIN cars ON bookings.car_id = cars.id 
                                 ORDER BY bookings.id DESC LIMIT 5");

include 'admin_header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --accent-color: #38bdf8;
        --secondary-bg: #1e293b;
    }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { 
        background: var(--secondary-bg); padding: 25px; border-radius: 15px; 
        border-bottom: 4px solid var(--accent-color);
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-card h3 { margin: 0; font-size: 14px; color: #94a3b8; text-transform: uppercase; }
    .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: bold; color: #f8fafc; }
    
    .dashboard-flex { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 30px; }
    .chart-container { background: var(--secondary-bg); padding: 20px; border-radius: 15px; flex: 2; min-width: 300px; }
    .recent-section { background: var(--secondary-bg); padding: 20px; border-radius: 15px; flex: 1.5; min-width: 300px; }
    
    .recent-table { width: 100%; border-collapse: collapse; }
    .recent-table th, .recent-table td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; font-size: 14px; }
    .recent-table th { color: var(--accent-color); }
    .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: bold; }
</style>

<h1 style="margin-bottom: 30px; font-weight: 800;">Admin Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card" style="border-color: #38bdf8;">
        <h3>💰 Total Revenue</h3>
        <p><?php echo number_format($total_revenue, 0); ?> <span style="font-size: 14px;">ETB</span></p>
    </div>
    <div class="stat-card" style="border-color: #4ade80;">
        <h3>🚗 Active Rentals</h3>
        <p><?php echo $active_rentals; ?></p>
    </div>
    <div class="stat-card" style="border-color: #fbbf24;">
        <h3>🚘 Total Cars</h3>
        <p><?php echo $total_cars; ?></p>
    </div>
    <div class="stat-card" style="border-color: #f87171;">
        <h3>📩 New Requests</h3>
        <p><?php echo $pending_requests; ?></p>
    </div>
</div>

<div class="dashboard-flex">
    <div class="chart-container">
        <h3 style="margin-bottom: 20px;">Revenue Overview (Last 7 Days)</h3>
        <canvas id="revenueChart"></canvas>
    </div>

    <div class="recent-section">
        <h3 style="margin-bottom: 20px;">Recent Activity</h3>
        <table class="recent-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $recent_bookings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['brand']); ?></td>
                    <td>
                        <?php 
                        $status = $row['status'];
                        $color = ($status == 'approved') ? '#4ade80' : (($status == 'completed') ? '#38bdf8' : '#94a3b8');
                        ?>
                        <span class="status-badge" style="background: <?php echo $color; ?>22; color: <?php echo $color; ?>;">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_reverse($dates)); ?>,
        datasets: [{
            label: 'Daily Revenue (ETB)',
            data: <?php echo json_encode(array_reverse($amounts)); ?>,
            borderColor: '#38bdf8',
            backgroundColor: 'rgba(56, 189, 248, 0.1)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#334155' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
