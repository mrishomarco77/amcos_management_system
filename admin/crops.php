<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle Delete Operation
if (isset($_POST['delete_crop']) && isset($_POST['crop_id'])) {
    try {
        $stmt = $connection->prepare("DELETE FROM crops WHERE id = ?");
        $stmt->execute([$_POST['crop_id']]);
        $_SESSION['success'] = "Crop deleted successfully";
        header("Location: crops.php");
        exit();
    } catch (PDOException $e) {
        error_log("Error deleting crop: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting crop. Please try again.";
    }
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="page-title">Manage Crops</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCropModal">
                        <i class="fas fa-plus"></i> Add New Crop
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="cropsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Price per KG</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $stmt = $connection->query("SELECT * FROM crops ORDER BY name ASC");
                                        while ($crop = $stmt->fetch()) {
                                            $image_path = !empty($crop['image']) ? "images/" . $crop['image'] : "images/cotton1.jpg";
                                            $price = isset($crop['price_per_kg']) ? $crop['price_per_kg'] : 0;
                                            $status = isset($crop['status']) ? $crop['status'] : 'inactive';
                                            
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($crop['id']) . "</td>";
                                            echo "<td><img src='" . htmlspecialchars($image_path) . "' alt='" . htmlspecialchars($crop['name']) . "' class='crop-thumbnail'></td>";
                                            echo "<td>" . htmlspecialchars($crop['name']) . "</td>";
                                            echo "<td>" . htmlspecialchars($crop['description']) . "</td>";
                                            echo "<td>TZS " . number_format($price, 2) . "</td>";
                                            echo "<td><span class='badge bg-" . ($status == 'active' ? 'success' : 'danger') . "'>" 
                                                . ucfirst(htmlspecialchars($status)) . "</span></td>";
                                            echo "<td>
                                                    <button class='btn btn-sm btn-primary edit-crop' data-bs-toggle='modal' 
                                                            data-bs-target='#editCropModal' data-crop='" . htmlspecialchars(json_encode($crop)) . "'>
                                                        <i class='fas fa-edit'></i>
                                                    </button>
                                                    <button class='btn btn-sm btn-danger delete-crop' data-bs-toggle='modal' 
                                                            data-bs-target='#deleteCropModal' data-crop-id='" . $crop['id'] . "'>
                                                        <i class='fas fa-trash'></i>
                                                    </button>
                                                </td>";
                                            echo "</tr>";
                                        }
                                    } catch (PDOException $e) {
                                        error_log("Error fetching crops: " . $e->getMessage());
                                        echo "<tr><td colspan='7' class='text-center'>Error loading crops</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Crop Modal -->
<div class="modal fade" id="addCropModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Crop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_crop.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Crop Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Crop Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label for="price_per_kg" class="form-label">Price per KG (TZS)</label>
                        <input type="number" class="form-control" id="price_per_kg" name="price_per_kg" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_crop" class="btn btn-primary">Add Crop</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Crop Modal -->
<div class="modal fade" id="editCropModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Crop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_crop.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="crop_id" id="edit_crop_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Crop Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Crop Image</label>
                        <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                        <div id="current_image" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_price_per_kg" class="form-label">Price per KG (TZS)</label>
                        <input type="number" class="form-control" id="edit_price_per_kg" name="price_per_kg" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_crop" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Crop Modal -->
<div class="modal fade" id="deleteCropModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Crop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this crop?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <form action="" method="POST">
                    <input type="hidden" name="crop_id" id="delete_crop_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_crop" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.page-title {
    color: #2c3e50;
    font-weight: 600;
}

.card {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    margin: 0 0.1rem;
}

.crop-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}

#current_image img {
    max-width: 100px;
    height: auto;
    border-radius: 5px;
    margin-top: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#cropsTable').DataTable({
        "order": [[2, "asc"]], // Sort by name (column index 2)
        "pageLength": 10,
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Search crops:"
        }
    });

    // Handle Edit Crop
    document.querySelectorAll('.edit-crop').forEach(button => {
        button.addEventListener('click', function() {
            const crop = JSON.parse(this.dataset.crop);
            document.getElementById('edit_crop_id').value = crop.id;
            document.getElementById('edit_name').value = crop.name;
            document.getElementById('edit_description').value = crop.description;
            document.getElementById('edit_price_per_kg').value = crop.price_per_kg || '';
            document.getElementById('edit_status').value = crop.status || 'inactive';
            
            // Show current image if exists
            const currentImageDiv = document.getElementById('current_image');
            if (crop.image) {
                currentImageDiv.innerHTML = `<img src="images/${crop.image}" alt="${crop.name}">`;
            } else {
                currentImageDiv.innerHTML = '';
            }
        });
    });

    // Handle Delete Crop
    document.querySelectorAll('.delete-crop').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('delete_crop_id').value = this.dataset.cropId;
        });
    });
});
</script>
</body>
</html> 