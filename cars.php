<?php 
require_once 'includes/db_connect.php';
include 'includes/header.php'; 

$result = $conn->query("SELECT * FROM cars WHERE status = 'available' ORDER BY id DESC");
?>
<div style="padding: 50px 8%;">
    <div style="margin-bottom: 40px;">
        <h1 style="border-left: 5px solid #38bdf8; padding-left: 15px; margin: 0;">Our Rental Fleet</h1>
        <p style="color: #94a3b8; margin-top: 10px;">Choose the car that fits your style and budget. Premium quality guaranteed.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        <?php while($car = $result->fetch_assoc()): ?>
            <div class="car-card">
                <img src="assets/img/<?php echo $car['image_url']; ?>" style="width:100%; height:220px; object-fit:cover; border-bottom: 1px solid #334155;">
                
                <div style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin:0; font-size: 20px;"><?php echo htmlspecialchars($car['brand'] . " " . $car['model_name']); ?></h3>
                        <span style="background: rgba(56, 189, 248, 0.1); color: #38bdf8; padding: 4px 10px; border-radius: 5px; font-size: 12px; font-weight: 600;">Available</span>
                    </div>
                    
                    <p style="color: #38bdf8; font-size: 22px; font-weight: 600; margin: 15px 0;">
                        <?php echo number_format($car['price_per_day'], 2); ?> <span style="font-size: 14px; color: #94a3b8;">ETB / day</span>
                    </p>
                    
                    <a href="rent_confirm.php?id=<?php echo $car['id']; ?>" style="text-decoration: none;">
                        <button class="btn-rent" style="width:100%; padding: 14px; background:#38bdf8; border:none; border-radius:10px; font-weight:600; cursor:pointer; font-size: 16px; color: #0f172a;">Rent This Car</button>
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>