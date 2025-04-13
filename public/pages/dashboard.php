<?php
$pageTitle = 'Dashboard';
require_once '../layouts/main.php';
?>

<div class="row g-4">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>245</h3>
                    <p class="mb-0">Total Farmers</p>
                </div>
                <i class="fas fa-users fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, var(--success-color), #16a34a);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>1,234</h3>
                    <p class="mb-0">Total Crops</p>
                </div>
                <i class="fas fa-seedling fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, var(--warning-color), #d97706);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>$52K</h3>
                    <p class="mb-0">Total Revenue</p>
                </div>
                <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stats-card" style="background: linear-gradient(135deg, var(--info-color), #2563eb);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>15</h3>
                    <p class="mb-0">Active Markets</p>
                </div>
                <i class="fas fa-store fa-2x opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Recent Farmers -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Farmers</h5>
                    <a href="/public/farmers.php" class="btn btn-primary btn-sm">View All</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Crop Type</th>
                                <th>Farm Size</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=John+Doe" class="rounded-circle me-2" width="32" height="32">
                                        John Doe
                                    </div>
                                </td>
                                <td>Maize</td>
                                <td>2.5 acres</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=Jane+Smith" class="rounded-circle me-2" width="32" height="32">
                                        Jane Smith
                                    </div>
                                </td>
                                <td>Coffee</td>
                                <td>1.8 acres</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Market Prices -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Market Prices</h5>
                    <a href="/public/market_prices.php" class="btn btn-primary btn-sm">View All</a>
                </div>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Maize</h6>
                                <small class="text-muted">per kg</small>
                            </div>
                            <span class="text-success fw-bold">$0.45</span>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Coffee</h6>
                                <small class="text-muted">per kg</small>
                            </div>
                            <span class="text-success fw-bold">$2.80</span>
                        </div>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Rice</h6>
                                <small class="text-muted">per kg</small>
                            </div>
                            <span class="text-success fw-bold">$1.20</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item pb-4">
                        <div class="d-flex">
                            <div class="timeline-icon bg-primary text-white rounded-circle p-2">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1">New Farmer Registration</h6>
                                <p class="text-muted mb-0">John Doe registered as a new farmer</p>
                                <small class="text-muted">2 hours ago</small>
                            </div>
                        </div>
                    </div>
                    <div class="timeline-item pb-4">
                        <div class="d-flex">
                            <div class="timeline-icon bg-success text-white rounded-circle p-2">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1">Market Price Update</h6>
                                <p class="text-muted mb-0">Coffee prices updated to $2.80 per kg</p>
                                <small class="text-muted">5 hours ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 