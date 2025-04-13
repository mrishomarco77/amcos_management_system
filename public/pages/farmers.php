<?php
$pageTitle = 'Farmers';
require_once '../layouts/main.php';

// Database connection
require_once '../../includes/config.php';

// Fetch farmers from database
$query = "SELECT f.*, 
          (SELECT COUNT(*) FROM crops WHERE farmer_id = f.id) as total_crops,
          (SELECT SUM(quantity) FROM harvests WHERE farmer_id = f.id) as total_harvest
          FROM farmers f 
          ORDER BY f.created_at DESC";
$result = $connection->query($query);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">Farmers Management</h2>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="add_farmer.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Farmer
        </a>
    </div>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search farmers...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="cropFilter">
                    <option value="">All Crops</option>
                    <option value="maize">Maize</option>
                    <option value="coffee">Coffee</option>
                    <option value="rice">Rice</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" id="resetFilters">
                    <i class="fas fa-sync-alt me-2"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Farmers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="farmersTable">
                <thead>
                    <tr>
                        <th>Farmer</th>
                        <th>Contact</th>
                        <th>Farm Size</th>
                        <th>Crops</th>
                        <th>Total Harvest</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($farmer = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo !empty($farmer['profile_picture']) ? 'uploads/' . $farmer['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($farmer['first_name'] . '+' . $farmer['last_name']); ?>" 
                                         class="rounded-circle me-2" width="40" height="40">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($farmer['first_name'] . ' ' . $farmer['last_name']); ?></h6>
                                        <small class="text-muted">ID: <?php echo $farmer['national_id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($farmer['phone_number']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($farmer['address']); ?></small>
                            </td>
                            <td><?php echo number_format($farmer['farm_size'], 2); ?> acres</td>
                            <td>
                                <span class="badge bg-info"><?php echo $farmer['total_crops']; ?> crops</span>
                            </td>
                            <td><?php echo number_format($farmer['total_harvest'] ?? 0, 2); ?> kg</td>
                            <td>
                                <span class="badge bg-<?php echo $farmer['status'] == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($farmer['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="view_farmer.php?id=<?php echo $farmer['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_farmer.php?id=<?php echo $farmer['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFarmer(<?php echo $farmer['id']; ?>)">
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

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Showing <span id="showingCount">1</span> to <span id="totalCount">10</span> of <span id="totalRecords"><?php echo $result->num_rows; ?></span> entries
    </div>
    <nav>
        <ul class="pagination mb-0">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('#farmersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});

// Filter functionality
document.getElementById('cropFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

function applyFilters() {
    const cropFilter = document.getElementById('cropFilter').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#farmersTable tbody tr');
    
    rows.forEach(row => {
        const cropText = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
        const statusText = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
        
        const cropMatch = !cropFilter || cropText.includes(cropFilter);
        const statusMatch = !statusFilter || statusText.includes(statusFilter);
        
        row.style.display = cropMatch && statusMatch ? '' : 'none';
    });
}

// Reset filters
document.getElementById('resetFilters').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('cropFilter').value = '';
    document.getElementById('statusFilter').value = '';
    const rows = document.querySelectorAll('#farmersTable tbody tr');
    rows.forEach(row => row.style.display = '');
});

// Delete farmer confirmation
function deleteFarmer(id) {
    if (confirm('Are you sure you want to delete this farmer? This action cannot be undone.')) {
        // Add AJAX call to delete farmer
        fetch(`delete_farmer.php?id=${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting farmer: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the farmer.');
        });
    }
}
</script> 