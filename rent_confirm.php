<?php 
require_once 'includes/db_connect.php';
include 'includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$car_id = $_GET['id'];

$car = $conn->query("SELECT * FROM cars WHERE id = $car_id")->fetch_assoc();
$user = $conn->query("SELECT wallet_balance FROM users WHERE id = $user_id")->fetch_assoc();
?>

<div style="padding: 50px 8%; max-width: 900px; margin: auto;">
    <div style="background: #1e293b; border-radius: 20px; overflow: hidden; display: flex; flex-wrap: wrap; border: 1px solid #334155;">
        
        <div style="flex: 1; min-width: 300px;">
            <img src="assets/img/<?php echo $car['image_url']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
        </div>

        <div style="flex: 1; padding: 40px; min-width: 300px; color: white;">
            <h2 style="color: #38bdf8; margin-top: 0;"><?php echo $car['brand'] . " " . $car['model_name']; ?></h2>
            
            <div style="background: rgba(56, 189, 248, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px dashed #38bdf8;">
                <p style="margin: 0; font-size: 14px; color: #94a3b8;">Your Wallet Balance:</p>
                <h3 style="margin: 5px 0; color: #4ade80;"><?php echo number_format($user['wallet_balance'], 2); ?> ETB</h3>
            </div>

            <p style="color: #94a3b8;">Daily Rate: <strong><?php echo number_format($car['price_per_day'], 2); ?> ETB</strong></p>
            
            <form action="process_booking.php" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                <input type="hidden" name="price_per_day" id="price_per_day" value="<?php echo $car['price_per_day']; ?>">
                <input type="hidden" name="wallet_balance" value="<?php echo $user['wallet_balance']; ?>">
                
                <label style="display: block; margin-bottom: 8px;">Pick-up Date:</label>
                <input type="date" name="start_date" id="start_date" required 
                       min="<?php echo date('Y-m-d'); ?>" 
                       style="width: 100%; padding: 12px; background: #334155; border: 1px solid #475569; border-radius: 8px; color: white; margin-bottom: 20px;">

                <label style="display: block; margin-bottom: 8px;">Return Date:</label>
                <input type="date" name="end_date" id="end_date" required 
                       style="width: 100%; padding: 12px; background: #334155; border: 1px solid #475569; border-radius: 8px; color: white; margin-bottom: 20px;">

                <div id="total_cost_box" style="margin-bottom: 20px; display: none;">
                    <p style="color: #94a3b8; margin-bottom: 5px;">Total Rental Cost:</p>
                    <h2 id="total_display" style="color: #f59e0b; margin: 0;">0.00 ETB</h2>
                </div>

                <button type="submit" id="submit_btn" style="width: 100%; padding: 15px; background: #38bdf8; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; font-size: 16px; color: #0f172a;">Confirm & Pay from Wallet</button>
            </form>
        </div>
    </div>
</div>

<script>
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const totalBox = document.getElementById('total_cost_box');
    const totalDisplay = document.getElementById('total_display');
    const pricePerDay = <?php echo $car['price_per_day']; ?>;
    const walletBalance = <?php echo $user['wallet_balance']; ?>;
    const submitBtn = document.getElementById('submit_btn');

    function calculateTotal() {
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            const end = new Date(endDateInput.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1; 
            const total = diffDays * pricePerDay;

            totalBox.style.display = 'block';
            totalDisplay.innerText = total.toLocaleString() + " ETB";

            if (total > walletBalance) {
                submitBtn.style.background = '#475569';
                submitBtn.innerText = 'Insufficient Wallet Balance';
                submitBtn.disabled = true;
            } else {
                submitBtn.style.background = '#38bdf8';
                submitBtn.innerText = 'Confirm & Pay from Wallet';
                submitBtn.disabled = false;
            }
        }
    }

    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        calculateTotal();
    });
    endDateInput.addEventListener('change', calculateTotal);
</script>

<?php include 'includes/footer.php'; ?>