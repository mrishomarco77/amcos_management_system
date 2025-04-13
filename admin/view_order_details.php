<?php
// Ensure session and database connection are active
session_start();
include('includes/config.php');

// Validate Admin Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Check if order_id is passed in the URL
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch the order details along with related farmer and input details
    $query = "SELECT orders.*, farmers.*, inputs.*, orders.delivery_status AS order_status 
              FROM orders 
              LEFT JOIN farmers ON orders.farmer_id = farmers.farmer_id 
              LEFT JOIN inputs ON orders.input_id = inputs.input_id
              WHERE orders.order_id = '$order_id'";

    $result = mysqli_query($con, $query);
    $order_details = mysqli_fetch_assoc($result);

    if (!$order_details) {
        echo "<script>alert('Order not found!'); window.location.href = 'total_orders.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('No order ID provided!'); window.location.href = 'total_orders.php';</script>";
    exit();
}

?>

<div class="container mt-4">
    <h2>Order Details</h2>

    <table class="table table-bordered">
        <tr>
            <th>Order ID</th>
            <td><?php echo $order_details['order_id']; ?></td>
        </tr>
        <tr>
            <th>Farmer Name</th>
            <td><?php echo $order_details['first_name'] . ' ' . $order_details['last_name']; ?></td>
        </tr>
        <tr>
            <th>Farmer Address</th>
            <td><?php echo $order_details['address']; ?></td>
        </tr>
        <tr>
            <th>Farmer Phone</th>
            <td><?php echo $order_details['phone_number']; ?></td>
        </tr>
        <tr>
            <th>Input Name</th>
            <td><?php echo $order_details['input_name']; ?></td>
        </tr>
        <tr>
            <th>Input Description</th>
            <td><?php echo $order_details['input_description']; ?></td>
        </tr>
        <tr>
            <th>Quantity</th>
            <td><?php echo $order_details['quantity']; ?></td>
        </tr>
        <tr>
            <th>Order Date</th>
            <td><?php echo $order_details['order_date']; ?></td>
        </tr>
        <tr>
            <th>Delivery Status</th>
            <td><?php echo $order_details['order_status']; ?></td>
        </tr>
    </table>

    <!-- Delivery Status History (if applicable) -->
    <h4>Delivery Status History</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Old Status</th>
                <th>New Status</th>
                <th>Change Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch status history if you track status changes
            $status_history_query = "SELECT * FROM order_status_history WHERE order_id = '$order_id' ORDER BY change_date DESC";
            $status_history_result = mysqli_query($con, $status_history_query);
            while ($status_history = mysqli_fetch_assoc($status_history_result)) {
            ?>
            <tr>
                <td><?php echo $status_history['old_status']; ?></td>
                <td><?php echo $status_history['new_status']; ?></td>
                <td><?php echo $status_history['change_date']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Update Status Form -->
    <h4>Update Order Status</h4>
    <form method="POST" action="update_order_status.php">
        <div class="form-group">
            <label for="delivery_status">Delivery Status:</label>
            <select class="form-control" name="delivery_status" id="delivery_status">
                <option value="pending" <?php echo ($order_details['order_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="delivered" <?php echo ($order_details['order_status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                <option value="canceled" <?php echo ($order_details['order_status'] == 'canceled') ? 'selected' : ''; ?>>Canceled</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary" name="update_status">Update Status</button>
        </div>
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
    </form>
</div>

<?php
// If status update form is submitted
if (isset($_POST['update_status'])) {
    $new_status = $_POST['delivery_status'];
    $order_id = $_POST['order_id'];

    // Update order status
    $update_query = "UPDATE orders SET delivery_status = '$new_status' WHERE order_id = '$order_id'";
    if (mysqli_query($con, $update_query)) {
        // Optionally record the status change history
        $old_status = $order_details['order_status'];
        $status_history_query = "INSERT INTO order_status_history (order_id, old_status, new_status, change_date) 
                                 VALUES ('$order_id', '$old_status', '$new_status', NOW())";
        mysqli_query($con, $status_history_query);

        echo "<script>alert('Order status updated successfully!'); window.location.href = 'view_order_details.php?order_id=$order_id';</script>";
    } else {
        echo "<script>alert('Failed to update order status.');</script>";
    }
}
?>
