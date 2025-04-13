<?php
session_start();

// Turn on error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
include('../includes/config.php');

// Session check
if (strlen($_SESSION['aid']) == 0) {
    header('location:../index.php');
    exit();
}

// Delete user (teacher) logic
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $tid = intval($_GET['tid']);
    $profilepic = $_GET['profilepic'];
    $ppicpath = "../teacherspic/" . $profilepic;

    $stmt = $con->prepare("DELETE FROM tblteachers WHERE id = ?");
    $stmt->bind_param("i", $tid);

    if ($stmt->execute()) {
        if (file_exists($ppicpath)) {
            unlink($ppicpath);
        }
        echo "<script>alert('Teacher details deleted successfully.');</script>";
        echo "<script>document.location = 'manage-users.php';</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AMCOS Management System | Manage Users</title>

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
    <!-- jsPDF and html2canvas for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <?php 
    $navbar_path = '../includes/navbar.php';
    if (file_exists($navbar_path)) {
        include_once($navbar_path);
    } else {
        echo "<!-- Navbar file not found at $navbar_path -->";
    }
    ?>

    <!-- Sidebar -->
    <?php 
    $sidebar_path = '../includes/sidebar.php';
    if (file_exists($sidebar_path)) {
        include_once($sidebar_path);
    } else {
        echo "<!-- Sidebar file not found at $sidebar_path -->";
    }
    ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1>Manage Users</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Manage Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Export Buttons -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button class="btn btn-success" onclick="exportTableToExcel('example1', 'users-data')">Export to Excel</button>
                        <button class="btn btn-danger" onclick="exportTableToPDF('example1', 'users-data')">Export to PDF</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Users Details</h3></div>
                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Profile Pic</th>
                                            <th>Full Name</th>
                                            <th>Email ID</th>
                                            <th>Mobile Number</th>
                                            <th>Subject</th>
                                            <th>Reg. Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($con, "SELECT * FROM tblteachers");
                                        $cnt = 1;
                                        while ($result = mysqli_fetch_array($query)) {
                                        ?>
                                            <tr>
                                                <td><?php echo $cnt; ?></td>
                                                <td>
                                                    <?php if (!empty($result['teacherPic']) && file_exists("../teacherspic/" . $result['teacherPic'])) { ?>
                                                        <img src="../teacherspic/<?php echo htmlspecialchars($result['teacherPic']); ?>" width="80" alt="User Pic">
                                                    <?php } else { ?>
                                                        <span>No Image</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($result['fullName']); ?></td>
                                                <td><?php echo htmlspecialchars($result['teacherEmail']); ?></td>
                                                <td><?php echo htmlspecialchars($result['teacherMobileNo']); ?></td>
                                                <td><?php echo htmlspecialchars($result['teacherSubject']); ?></td>
                                                <td><?php echo htmlspecialchars($result['regDate']); ?></td>
                                                <td>
                                                    <a href="edit-teacher.php?tid=<?php echo $result['id']; ?>" title="Edit User" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                                                    <a href="manage-users.php?action=delete&tid=<?php echo $result['id']; ?>&profilepic=<?php echo htmlspecialchars($result['teacherPic'] ?? ''); ?>" title="Delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php $cnt++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <?php 
    $footer_path = '../includes/footer.php';
    if (file_exists($footer_path)) {
        include_once($footer_path);
    } else {
        echo "<!-- Footer file not found at $footer_path -->";
    }
    ?>
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