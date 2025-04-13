<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once '../vendor/autoload.php'; // For TCPDF

// Validate Session
if (strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Get parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('AMCOS Management System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle(ucfirst($type) . ' Report');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, strtoupper($type) . ' REPORT', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Period: ' . date('F d, Y', strtotime($start_date)) . ' - ' . date('F d, Y', strtotime($end_date)), 0, 1, 'C');
$pdf->Ln(10);

try {
    switch ($type) {
        case 'purchases':
            // Fetch purchase data
            $query = "SELECT 
                        p.id,
                        p.created_at,
                        CONCAT(f.first_name, ' ', f.last_name) as farmer_name,
                        p.weight_kg,
                        p.price_per_kg,
                        p.total_amount,
                        p.status
                    FROM purchases p
                    JOIN farmers f ON p.farmer_id = f.id
                    WHERE p.created_at BETWEEN ? AND ?
                    ORDER BY p.created_at DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Create table header
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(40, 10, 'Date', 1, 0, 'C');
            $pdf->Cell(50, 10, 'Farmer', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Weight (kg)', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Price/kg', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Total (TZS)', 1, 1, 'C');
            
            // Add data rows
            $pdf->SetFont('helvetica', '', 12);
            $total_weight = 0;
            $total_amount = 0;
            
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(40, 10, date('Y-m-d', strtotime($row['created_at'])), 1, 0, 'C');
                $pdf->Cell(50, 10, $row['farmer_name'], 1, 0, 'L');
                $pdf->Cell(30, 10, number_format($row['weight_kg'], 2), 1, 0, 'R');
                $pdf->Cell(30, 10, number_format($row['price_per_kg'], 2), 1, 0, 'R');
                $pdf->Cell(40, 10, number_format($row['total_amount'], 2), 1, 1, 'R');
                
                $total_weight += $row['weight_kg'];
                $total_amount += $row['total_amount'];
            }
            
            // Add totals
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(90, 10, 'TOTAL', 1, 0, 'R');
            $pdf->Cell(30, 10, number_format($total_weight, 2), 1, 0, 'R');
            $pdf->Cell(30, 10, '', 1, 0, 'R');
            $pdf->Cell(40, 10, number_format($total_amount, 2), 1, 1, 'R');
            break;
            
        case 'sales':
            // Fetch sales data
            $query = "SELECT 
                        s.id,
                        s.created_at,
                        b.company_name,
                        s.weight_kg,
                        s.price_per_kg,
                        s.total_amount,
                        s.status
                    FROM sales s
                    JOIN buyers b ON s.buyer_id = b.id
                    WHERE s.created_at BETWEEN ? AND ?
                    ORDER BY s.created_at DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Create table header
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(40, 10, 'Date', 1, 0, 'C');
            $pdf->Cell(50, 10, 'Buyer', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Weight (kg)', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Price/kg', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Total (TZS)', 1, 1, 'C');
            
            // Add data rows
            $pdf->SetFont('helvetica', '', 12);
            $total_weight = 0;
            $total_amount = 0;
            
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(40, 10, date('Y-m-d', strtotime($row['created_at'])), 1, 0, 'C');
                $pdf->Cell(50, 10, $row['company_name'], 1, 0, 'L');
                $pdf->Cell(30, 10, number_format($row['weight_kg'], 2), 1, 0, 'R');
                $pdf->Cell(30, 10, number_format($row['price_per_kg'], 2), 1, 0, 'R');
                $pdf->Cell(40, 10, number_format($row['total_amount'], 2), 1, 1, 'R');
                
                $total_weight += $row['weight_kg'];
                $total_amount += $row['total_amount'];
            }
            
            // Add totals
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(90, 10, 'TOTAL', 1, 0, 'R');
            $pdf->Cell(30, 10, number_format($total_weight, 2), 1, 0, 'R');
            $pdf->Cell(30, 10, '', 1, 0, 'R');
            $pdf->Cell(40, 10, number_format($total_amount, 2), 1, 1, 'R');
            break;
            
        case 'payments':
            // Fetch payment data
            $query = "SELECT 
                        p.id,
                        p.created_at,
                        p.payment_type,
                        CASE 
                            WHEN p.payment_type = 'farmer' THEN CONCAT(f.first_name, ' ', f.last_name)
                            ELSE b.company_name
                        END as recipient,
                        p.amount,
                        p.payment_method,
                        p.reference_number
                    FROM payments p
                    LEFT JOIN farmers f ON p.farmer_id = f.id
                    LEFT JOIN buyers b ON p.buyer_id = b.id
                    WHERE p.created_at BETWEEN ? AND ?
                    ORDER BY p.created_at DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Create table header
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(40, 10, 'Date', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Type', 1, 0, 'C');
            $pdf->Cell(50, 10, 'Recipient', 1, 0, 'C');
            $pdf->Cell(40, 10, 'Amount (TZS)', 1, 0, 'C');
            $pdf->Cell(30, 10, 'Method', 1, 1, 'C');
            
            // Add data rows
            $pdf->SetFont('helvetica', '', 12);
            $total_farmer_payments = 0;
            $total_buyer_payments = 0;
            
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(40, 10, date('Y-m-d', strtotime($row['created_at'])), 1, 0, 'C');
                $pdf->Cell(30, 10, ucfirst($row['payment_type']), 1, 0, 'C');
                $pdf->Cell(50, 10, $row['recipient'], 1, 0, 'L');
                $pdf->Cell(40, 10, number_format($row['amount'], 2), 1, 0, 'R');
                $pdf->Cell(30, 10, ucfirst($row['payment_method']), 1, 1, 'C');
                
                if ($row['payment_type'] == 'farmer') {
                    $total_farmer_payments += $row['amount'];
                } else {
                    $total_buyer_payments += $row['amount'];
                }
            }
            
            // Add summary
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Payment Summary:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Total Payments to Farmers: TZS ' . number_format($total_farmer_payments, 2), 0, 1, 'L');
            $pdf->Cell(0, 10, 'Total Payments from Buyers: TZS ' . number_format($total_buyer_payments, 2), 0, 1, 'L');
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Net Balance: TZS ' . number_format($total_buyer_payments - $total_farmer_payments, 2), 0, 1, 'L');
            break;
            
        default:
            throw new Exception('Invalid report type');
    }
    
    // Output the PDF
    $pdf->Output(ucfirst($type) . '_Report_' . date('Y-m-d') . '.pdf', 'D');
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error generating report: " . $e->getMessage();
    header('location:reports.php');
    exit();
}
?> 