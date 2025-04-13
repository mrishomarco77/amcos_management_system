<?php
// Ensure session and database connection are active
session_start();
include('includes/config.php');

// Validate Admin Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Handle Order Status Update (AJAX)
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $query = "UPDATE orders SET delivery_status = '$status' WHERE order_id = '$order_id'";
    if (mysqli_query($con, $query)) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status.";
    }
    exit();
}

// Fetch Orders based on filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search_query']) ? mysqli_real_escape_string($con, $_GET['search_query']) : '';

$query = "SELECT * FROM orders
          LEFT JOIN farmers ON orders.farmer_id = farmers.farmer_id
          LEFT JOIN inputs ON orders.input_id = inputs.input_id
          WHERE (orders.delivery_status LIKE '%$status%' OR '$status' = '')
          AND (farmers.first_name LIKE '%$search_query%' OR orders.order_id LIKE '%$search_query%')";
$result = mysqli_query($con, $query);

?>

<div class="container mt-4">
    <h2>Total Orders</h2>
    
    <!-- Filter and Search Form -->
    <form method="GET">
        <div class="form-group">
            <label>Filter by Status:</label>
            <select class="form-control" name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="delivered" <?php echo ($status == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                <option value="canceled" <?php echo ($status == 'canceled') ? 'selected' : ''; ?>>Canceled</option>
            </select>
        </div>

        <div class="form-group">
            <label>Search Orders:</label>
            <input type="text" class="form-control" name="search_query" placeholder="Search by Order ID or Farmer Name" value="<?php echo $search_query; ?>">
        </div>
    </form>

    <!-- Orders Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Farmer Name</th>
                <th>Input</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                <td><?php echo $row['input_name']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['order_date']; ?></td>
                <td>
                    <select class="form-control" onchange="updateOrderStatus(<?php echo $row['order_id']; ?>, this.value)">
                        <option value="pending" <?php echo ($row['delivery_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="delivered" <?php echo ($row['delivery_status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="canceled" <?php echo ($row['delivery_status'] == 'canceled') ? 'selected' : ''; ?>>Canceled</option>
                    </select>
                </td>
                <td>
                    <button class="btn btn-info" onclick="viewOrderDetails(<?php echo $row['order_id']; ?>)">View</button>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination (if needed) -->
    <nav>
        <ul class="pagination">
            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
    </nav>

    <!-- Bulk Actions -->
    <form id="bulkActionsForm">
        <div class="form-group">
            <button type="button" class="btn btn-success" onclick="bulkAction('delivered')">Mark as Delivered</button>
            <button type="button" class="btn btn-danger" onclick="bulkAction('canceled')">Cancel Orders</button>
        </div>
    </form>
</div>

<script>
// Function to update order status via AJAX
function updateOrderStatus(order_id, status) {
    $.ajax({
        url: '',
        type: 'POST',
        data: { order_id: order_id, status: status },
        success: function(response) {
            alert(response);
        }
    });
}

// Function to view detailed order info
function viewOrderDetails(order_id) {
    window.location.href = 'view_order_details.php?order_id=' + order_id;
}

// Bulk action for orders
function bulkAction(action) {
    const selectedOrders = [];
    $("input[name='order_checkbox']:checked").each(function() {
        selectedOrders.push($(this).val());
    });

    if (selectedOrders.length > 0) {
        if (confirm('Are you sure you want to mark these orders as ' + action + '?')) {
            $.ajax({
                url: 'bulk_update_orders.php',
                type: 'POST',
                data: { orders: selectedOrders, action: action },
                success: function(response) {
                    alert('Bulk action completed!');
                    location.reload();
                }
            });
        }
    } else {
        alert('No orders selected!');
    }
}
</script>
