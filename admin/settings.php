<?php
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Fetch current settings
try {
    $stmt = $connection->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching settings: " . $e->getMessage());
    $settings = [];
}
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="page-title">System Settings</h4>
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
                        <form action="process_settings.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <!-- General Settings -->
                                <div class="col-md-6">
                                    <h5 class="mb-4">General Settings</h5>
                                    
                                    <div class="mb-3">
                                        <label for="system_name" class="form-label">System Name</label>
                                        <input type="text" class="form-control" id="system_name" name="system_name" 
                                               value="<?php echo htmlspecialchars($settings['system_name'] ?? 'AMCOS Management System'); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="organization_name" class="form-label">Organization Name</label>
                                        <input type="text" class="form-control" id="organization_name" name="organization_name" 
                                               value="<?php echo htmlspecialchars($settings['organization_name'] ?? ''); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                                    </div>
                                </div>

                                <!-- System Settings -->
                                <div class="col-md-6">
                                    <h5 class="mb-4">System Configuration</h5>

                                    <div class="mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency" name="currency">
                                            <option value="TZS" <?php echo (($settings['currency'] ?? 'TZS') == 'TZS') ? 'selected' : ''; ?>>TZS (Tanzanian Shilling)</option>
                                            <option value="USD" <?php echo (($settings['currency'] ?? '') == 'USD') ? 'selected' : ''; ?>>USD (US Dollar)</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select" id="timezone" name="timezone">
                                            <option value="Africa/Dar_es_Salaam" <?php echo (($settings['timezone'] ?? 'Africa/Dar_es_Salaam') == 'Africa/Dar_es_Salaam') ? 'selected' : ''; ?>>East Africa Time (EAT)</option>
                                            <!-- Add more timezone options if needed -->
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="logo" class="form-label">System Logo</label>
                                        <?php if (!empty($settings['logo'])): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo htmlspecialchars($settings['logo']); ?>" alt="Current Logo" class="current-logo">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                                        <small class="text-muted">Recommended size: 200x200px</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="favicon" class="form-label">Favicon</label>
                                        <?php if (!empty($settings['favicon'])): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo htmlspecialchars($settings['favicon']); ?>" alt="Current Favicon" class="current-favicon">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                                        <small class="text-muted">Recommended size: 32x32px</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" name="update_settings" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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

.current-logo {
    max-width: 200px;
    height: auto;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
}

.current-favicon {
    max-width: 32px;
    height: auto;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 2px;
}

.form-label {
    font-weight: 500;
}

.text-muted {
    font-size: 0.875rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview image before upload for logo
    document.getElementById('logo').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.current-logo') || document.createElement('img');
                preview.src = e.target.result;
                preview.classList.add('current-logo');
                if (!document.querySelector('.current-logo')) {
                    this.parentElement.insertBefore(preview, this);
                }
            }.bind(this);
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Preview image before upload for favicon
    document.getElementById('favicon').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.current-favicon') || document.createElement('img');
                preview.src = e.target.result;
                preview.classList.add('current-favicon');
                if (!document.querySelector('.current-favicon')) {
                    this.parentElement.insertBefore(preview, this);
                }
            }.bind(this);
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
</body>
</html> 