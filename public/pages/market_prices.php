<?php
$pageTitle = 'Market Prices';
require_once '../layouts/main.php';

// Database connection
require_once '../../includes/config.php';

// Fetch market prices
$query = "SELECT mp.*, c.name as crop_name 
          FROM market_prices mp 
          JOIN crops c ON mp.crop_id = c.id 
          ORDER BY mp.date DESC";
$result = $connection->query($query);

// Get price history for charts
$priceHistoryQuery = "SELECT c.name as crop_name, 
                     DATE_FORMAT(mp.date, '%Y-%m-%d') as date,
                     AVG(mp.price) as avg_price
                     FROM market_prices mp
                     JOIN crops c ON mp.crop_id = c.id
                     WHERE mp.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     GROUP BY c.name, DATE_FORMAT(mp.date, '%Y-%m-%d')
                     ORDER BY mp.date ASC";
$priceHistory = $connection->query($priceHistoryQuery);

// Prepare data for charts
$chartData = [];
while ($row = $priceHistory->fetch_assoc()) {
    if (!isset($chartData[$row['crop_name']])) {
        $chartData[$row['crop_name']] = [
            'dates' => [],
            'prices' => []
        ];
    }
    $chartData[$row['crop_name']]['dates'][] = $row['date'];
    $chartData[$row['crop_name']]['prices'][] = $row['avg_price'];
}
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Market Prices</h2>
    </div>
    <div class="col-md-6 text-md-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPriceModal">
            <i class="fas fa-plus me-2"></i>Add New Price
        </button>
    </div>
</div>

<!-- Price Trends Chart -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Price Trends (Last 30 Days)</h5>
    </div>
    <div class="card-body">
        <canvas id="priceChart" height="300"></canvas>
    </div>
</div>

<!-- Current Prices -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Current Market Prices</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Crop</th>
                        <th>Current Price</th>
                        <th>Change (24h)</th>
                        <th>Last Updated</th>
                        <th>Market</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($price = $result->fetch_assoc()): 
                        // Calculate price change
                        $changeQuery = "SELECT price FROM market_prices 
                                      WHERE crop_id = ? AND date < ? 
                                      ORDER BY date DESC LIMIT 1";
                        $stmt = $connection->prepare($changeQuery);
                        $stmt->bind_param("is", $price['crop_id'], $price['date']);
                        $stmt->execute();
                        $prevPrice = $stmt->get_result()->fetch_assoc();
                        $change = $prevPrice ? (($price['price'] - $prevPrice['price']) / $prevPrice['price']) * 100 : 0;
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/crops/<?php echo strtolower($price['crop_name']); ?>.png" 
                                         class="rounded-circle me-2" width="32" height="32">
                                    <?php echo htmlspecialchars($price['crop_name']); ?>
                                </div>
                            </td>
                            <td>$<?php echo number_format($price['price'], 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $change >= 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $change >= 0 ? '+' : ''; ?><?php echo number_format($change, 2); ?>%
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($price['date'])); ?></td>
                            <td><?php echo htmlspecialchars($price['market_name']); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editPrice(<?php echo $price['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deletePrice(<?php echo $price['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Price Modal -->
<div class="modal fade" id="addPriceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Market Price</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="priceForm">
                    <div class="mb-3">
                        <label class="form-label">Crop</label>
                        <select class="form-select" name="crop_id" required>
                            <option value="">Select Crop</option>
                            <?php
                            $cropsQuery = "SELECT id, name FROM crops ORDER BY name";
                            $crops = $connection->query($cropsQuery);
                            while ($crop = $crops->fetch_assoc()):
                            ?>
                                <option value="<?php echo $crop['id']; ?>">
                                    <?php echo htmlspecialchars($crop['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" class="form-control" name="price" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Market</label>
                        <input type="text" class="form-control" name="market_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePrice()">Save Price</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize price chart
const ctx = document.getElementById('priceChart').getContext('2d');
const datasets = [];

Object.entries(<?php echo json_encode($chartData); ?>).forEach(([crop, data]) => {
    datasets.push({
        label: crop,
        data: data.prices,
        borderColor: getRandomColor(),
        tension: 0.1
    });
});

new Chart(ctx, {
    type: 'line',
    data: {
        labels: Object.values(<?php echo json_encode($chartData); ?>)[0]?.dates || [],
        datasets: datasets
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Market Price Trends'
            }
        },
        scales: {
            y: {
                beginAtZero: false
            }
        }
    }
});

function getRandomColor() {
    const colors = [
        '#2563eb', '#16a34a', '#d97706', '#dc2626',
        '#7c3aed', '#0891b2', '#65a30d', '#9f1239'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

// Save new price
function savePrice() {
    const form = document.getElementById('priceForm');
    const formData = new FormData(form);
    
    fetch('save_price.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error saving price: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the price.');
    });
}

// Delete price
function deletePrice(id) {
    if (confirm('Are you sure you want to delete this price record?')) {
        fetch(`delete_price.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting price: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the price.');
        });
    }
}
</script> 