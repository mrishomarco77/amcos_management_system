<?php
include('includes/config.php');

if (isset($_POST['orders']) && isset($_POST['action'])) {
    $orders = $_POST['orders'];
    $action = $_POST['action'];

    foreach ($orders as $order_id) {
        $query = "UPDATE orders SET delivery_status = '$action' WHERE order_id = '$order_id'";
        mysqli_query($con, $query);
    }

    echo "Bulk update successful!";
} else {
    echo "No orders or action specified!";
}
?>
