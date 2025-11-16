<?php
session_start();
require_once '../Process/db_connection.php';

// Only allow admin users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $connection = getDBConnection();
    $id = $connection->real_escape_string($_GET['id']);
    
    $sql = "SELECT * FROM no_birthcert_tbl WHERE id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Data found, display it
    } else {
        header("Location: Adminpage.php?panel=nobirthCertPanel&error=not_found");
        exit;
    }
    
    $stmt->close();
} else {
    header("Location: Adminpage.php?panel=nobirthCertPanel&error=no_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View No Birth Certificate Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .details {
            margin-top: 20px;
        }
        .detail-item {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-pending { background: #ffd700; color: #000; }
        .status-approved { background: #90EE90; color: #000; }
        .status-printed { background: #87CEEB; color: #000; }
        .status-released { background: #98FB98; color: #000; }
        .status-declined { background: #FFB6C1; color: #000; }
        .back-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="logo">
            <h2>No Birth Certificate Request Details</h2>
        </div>
        
        <div class="details">
            <div class="detail-item row">
                <div class="col-md-4 label">Reference Number:</div>
                <div class="col-md-8 value"><?php echo htmlspecialchars($row['refno']); ?></div>
            </div>
            
            <div class="detail-item row">
                <div class="col-md-4 label">Requestor Name:</div>
                <div class="col-md-8 value"><?php echo htmlspecialchars($row['requestor_name']); ?></div>
            </div>
            
            <div class="detail-item row">
                <div class="col-md-4 label">Document Type:</div>
                <div class="col-md-8 value"><?php echo htmlspecialchars($row['DocuType']); ?></div>
            </div>
            
            <div class="detail-item row">
                <div class="col-md-4 label">Request Date:</div>
                <div class="col-md-8 value"><?php echo date('F d, Y', strtotime($row['request_date'])); ?></div>
            </div>
            
            <div class="detail-item row">
                <div class="col-md-4 label">Status:</div>
                <div class="col-md-8 value">
                    <span class="status-badge status-<?php echo strtolower($row['RequestStatus']); ?>">
                        <?php echo htmlspecialchars($row['RequestStatus']); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($row['RequestStatus'] === 'Released'): ?>
            <div class="detail-item row">
                <div class="col-md-4 label">Released By:</div>
                <div class="col-md-8 value"><?php echo htmlspecialchars($row['ReleasedBy']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ($row['RequestStatus'] === 'Declined' && !empty($row['DeclineReason'])): ?>
            <div class="detail-item row">
                <div class="col-md-4 label">Decline Reason:</div>
                <div class="col-md-8 value"><?php echo htmlspecialchars($row['DeclineReason']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center back-button">
            <a href="Adminpage.php?panel=nobirthCertPanel" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php $connection->close(); ?>