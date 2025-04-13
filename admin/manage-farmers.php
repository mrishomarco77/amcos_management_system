<?php 
session_start();
error_reporting(0);
// Database Connection
include('includes/config.php');

// Validating Session
if (strlen($_SESSION['aid']) == 0) { 
    header('location:index.php');
} else {

// Code for deleting a farmer
if ($_GET['action'] == 'delete') {
    $farmer_id = intval($_GET['fid']);
    $profilepic = $_GET['profilepic'];
    $ppicpath = "../farmerspic/" . $profilepic;

    $query = mysqli_query($con, "DELETE FROM farmers WHERE farmer_id='$farmer_id'");
    
    if ($query) {
        if (!empty($profilepic) && file_exists($ppicpath)) {
            unlink($ppicpath);
        }
        echo "<script>alert('Farmer details deleted successfully.');</script>";
        echo "<script type='text/javascript'> document.location = 'manage-farmers.php'; </script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AMCOS Management System | Manage Farmers</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include_once("includes/navbar.php"); ?>
    <?php include_once("includes/sidebar.php"); ?>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Farmers</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Manage Farmers</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Export Buttons -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button class="btn btn-success" onclick="exportTableToExcel('example1', 'farmers-data')">Export to Excel</button>
                        <button class="btn btn-danger" onclick="exportTableToPDF('example1', 'farmers-data')">Export to PDF</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Farmers Details</h3>
                            </div>
                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Profile Pic</th>
                                            <th>Full Name</th>
                                            <th>NIDA</th>
                                            <th>Phone Number</th>
                                            <th>Farm Size</th>
                                            <th>Crop Type</th>
                                            <th>Reg. Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php 
$query = mysqli_query($con, "SELECT * FROM farmers");
$cnt = 1;
while ($result = mysqli_fetch_array($query)) {
?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td>
                                                <?php if (!empty($result['profile_picture']) && file_exists("farmerspic/" . $result['profile_picture'])) { ?>
                                                    <img src="../farmerspic/<?php echo htmlspecialchars($result['profile_picture']); ?>" width="80" alt="Farmer Pic">
                                                <?php } else { ?>
                                                    <span>No Image</span>
                                                <?php } ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($result['first_name'] . " " . $result['middle_name'] . " " . $result['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars(trim($result['nida'], '-')); ?></td>
                                            <td><?php echo htmlspecialchars($result['phone_number']); ?></td>
                                            <td><?php echo !empty($result['farm_size']) ? $result['farm_size'] . " acres" : "N/A"; ?></td>
                                            <td><?php echo !empty($result['crop_type']) ? htmlspecialchars($result['crop_type']) : "N/A"; ?></td>
                                            <td><?php echo htmlspecialchars($result['registration_date']); ?></td>
                                            <td>
                                                <?php if (!empty($result['farmer_id'])) { ?>
                                                    <a href="edit-farmer.php?fid=<?php echo $result['farmer_id']; ?>" title="Edit Farmer Details" class="btn btn-sm btn-primary"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                                    <a href="manage-farmers.php?action=delete&fid=<?php echo $result['farmer_id']; ?>&profilepic=<?php echo htmlspecialchars($result['profile_picture'] ?? ''); ?>" title="Delete this record" class="btn btn-sm btn-danger" onclick="return confirm('Do you really want to delete this record?');"><i class="fa fa-trash" aria-hidden="true"></i></a>
                                                <?php } else { ?>
                                                    <span>No Actions</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
<?php 
$cnt++; 
} 
?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php include_once('includes/footer.php'); ?>
</div>

<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- OverlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- DataTables & Plugins -->
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<!-- JSZip for Excel export -->
<script src="../plugins/jszip/jszip.min.js"></script>
<!-- PDFMake for PDF export -->
<script src="../plugins/pdfmake/pdfmake.min.js"></script>
<script src="../plugins/pdfmake/vfs_fonts.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>

<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });

    // Custom export functions (if DataTables export isn't working as expected)
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        filename = filename ? filename + '.xls' : 'excel_data.xls';
        
        downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        
        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], { type: dataType });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
            downloadLink.download = filename;
            downloadLink.click();
        }
    }

    function exportTableToPDF(tableID, filename = '') {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.autoTable({ html: '#' + tableID });
        doc.save(filename ? filename + '.pdf' : 'table_data.pdf');
    }
</script>
</body>
</html>
<?php } ?>