<?php
include('includes/config.php');

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? '';

if ($type === 'farmers') {
    $query = "SELECT first_name, last_name, phone_number, crop_type, farm_size FROM farmers";
    $filename = "registered_farmers";
} elseif ($type === 'sub_admins') {
    $query = "SELECT AdminName, AdminUserName, Email, MobileNumber FROM tbladmin";
    $filename = "registered_sub_admins";
} else {
    die("Invalid type specified.");
}

$result = mysqli_query($connection, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($connection));
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

if ($format === 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename={$filename}.xls");
    echo implode("\t", array_keys($data[0])) . "\n";
    foreach ($data as $row) {
        echo implode("\t", array_values($row)) . "\n";
    }
} elseif ($format === 'pdf') {
    require_once('tcpdf/tcpdf.php');
    $pdf = new TCPDF();
    $pdf->AddPage();
    $html = '<table border="1"><thead><tr>';
    foreach (array_keys($data[0]) as $header) {
        $html .= "<th>{$header}</th>";
    }
    $html .= '</tr></thead><tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= "<td>{$cell}</td>";
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html);
    $pdf->Output("{$filename}.pdf", 'D');
} else {
    die("Invalid format specified.");
}
?>
