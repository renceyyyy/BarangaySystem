<?php
// Set resident session name BEFORE starting session
session_name('BarangayResidentSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['user_id'])) {
  header("Location: ../Login/login.php");
  exit();
}

// Prevent admin/staff from accessing resident pages
$userRole = $_SESSION['role'] ?? 'resident';
if (in_array($userRole, ['admin', 'finance', 'sk', 'SuperAdmin'])) {
  // Redirect admin back to their dashboard
  if ($userRole === 'admin') {
    header("Location: Adminpage.php");
  } elseif ($userRole === 'finance') {
    header("Location: FinancePage.php");
  } elseif ($userRole === 'sk') {
    header("Location: SKpage.php");
  } elseif ($userRole === 'SuperAdmin') {
    header("Location: SuperAdmin.php");
  }
  exit();
}

// Include database connection module
require_once '../Process/db_connection.php';

// Get database connection
$conn = getDBConnection();

// NEW FEATURE: Check for pending requests by type
function getPendingRequestTypes($conn, $userId)
{
  $pendingTypes = [];
  
  // Document requests
  $sql = "SELECT COUNT(*) as count FROM docsreqtbl WHERE UserId = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['document'] = true;
    }
    $stmt->close();
  }

  // Business requests
  $sql = "SELECT COUNT(*) as count FROM businesstbl WHERE UserId = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['business'] = true;
    }
    $stmt->close();
  }

  // Scholarship
  $sql = "SELECT COUNT(*) as count FROM scholarship WHERE UserID = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['scholarship'] = true;
    }
    $stmt->close();
  }

  // Unemployment
  $sql = "SELECT COUNT(*) as count FROM unemploymenttbl WHERE user_id = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['unemployment'] = true;
    }
    $stmt->close();
  }

  // Guardianship
  $sql = "SELECT COUNT(*) as count FROM guardianshiptbl WHERE user_id = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['guardianship'] = true;
    }
    $stmt->close();
  }

  // No Birth Certificate
  $sql = "SELECT COUNT(*) as count FROM no_birthcert_tbl WHERE user_id = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['no_birth'] = true;
    }
    $stmt->close();
  }

  // Cohabitation Form
  $sql = "SELECT COUNT(*) as count FROM cohabitationtbl WHERE UserId = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['cohabitation'] = true;
    }
    $stmt->close();
  }

  // Complaints
  $sql = "SELECT COUNT(*) as count FROM complaintbl WHERE Userid = ? AND RequestStatus = 'Pending'";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
      $pendingTypes['complaint'] = true;
    }
    $stmt->close();
  }

  return $pendingTypes;
}

$pendingByType = getPendingRequestTypes($conn, $_SESSION['user_id']);
// Store in session so navbar can access it
// Clear the session array first to prevent stale data
if (empty($pendingByType)) {
  // No pending requests, ensure session is empty
  $_SESSION['pending_by_type'] = [];
} else {
  $_SESSION['pending_by_type'] = $pendingByType;
}

function getUserRequests($conn, $userId)
{
  $requests = [];

  // Document requests
  $sql = "SELECT 'Document Request' as type, DocuType as description, refno, DateRequested as date_requested, RequestStatus as status FROM docsreqtbl WHERE UserId = ? ORDER BY DateRequested DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Business requests 
  $sql = "SELECT 'Business Request' as type, CONCAT(BusinessName, ' - ', RequestType) as description, refno, RequestedDate as date_requested, RequestStatus as status FROM businesstbl WHERE UserId = ? ORDER BY RequestedDate DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Scholarship applications
  $sql = "SELECT 'Scholarship Application' as type, 'Scholarship Application' as description, ApplicationID as refno, DateApplied as date_requested, RequestStatus as status FROM scholarship WHERE UserID = ? ORDER BY DateApplied DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Unemployment certificates
  $sql = "SELECT 'Unemployment Certificate' as type, CONCAT(certificate_type, ' Certificate') as description, refno, request_date as date_requested, RequestStatus as status FROM unemploymenttbl WHERE user_id = ? ORDER BY request_date DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Guardianship/Solo Parent requests
  $sql = "SELECT 'Guardianship/Solo Parent' as type, CONCAT(request_type, ' for ', child_name) as description, refno, request_date as date_requested, RequestStatus as status FROM guardianshiptbl WHERE user_id = ? ORDER BY request_date DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // No Birth Certificate requests
  $sql = "SELECT 'No Birth Certificate' as type, 'No Birth Certificate Request' as description, refno, request_date as date_requested, RequestStatus as status FROM no_birthcert_tbl WHERE user_id = ? ORDER BY request_date DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Complain requests 
  $sql = "SELECT 'Complaint' as type, Complain as description, refno, DateComplained as date_requested, RequestStatus as status FROM complaintbl WHERE Userid = ? ORDER BY DateComplained DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Cohabitation Form requests
  $sql = "SELECT 'Cohabitation Form' as type, CONCAT(Name, ' - ', Purpose) as description, refno, DateRequested as date_requested, RequestStatus as status FROM cohabitationtbl WHERE UserId = ? ORDER BY DateRequested DESC";
  $stmt = db_prepare($sql);
  if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
      $requests[] = $row;
    }
    $stmt->close();
  }

  // Sort all requests by date
  usort($requests, function ($a, $b) {
    return strtotime($b['date_requested']) - strtotime($a['date_requested']);
  });

  return $requests;
}

$userRequests = getUserRequests($conn, $_SESSION['user_id']);

function getStatusBadgeClass($status)
{
  switch (strtolower($status)) {
    case 'pending':
      return 'status-pending';
    case 'approved':
      return 'status-approved';
    case 'declined':
      return 'status-declined';
    case 'completed':
      return 'status-completed';
    default:
      return 'status-pending';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Request Status | Barangay Sampaguita</title>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Styles/StylesProfile.css">
  <link rel="stylesheet" href="../Styles/ReqStatus.css">
  <style>
    /* Pulse animation for real-time indicator */
    @keyframes pulse {
      0%, 100% {
        opacity: 1;
      }
      50% {
        opacity: 0.3;
      }
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
    }

    .status-approved {
      background-color: #d1edff;
      color: #0b5ed7;
      border: 1px solid #b3d7ff;
    }

    .status-declined {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .status-completed {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }

    .status-tabs {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 6px;
      scrollbar-width: thin;
      /* Firefox */
    }

    .status-tab {
      flex: 0 0 auto;
      /* prevent shrinking */
    }

    /* Ensure tables can be scrolled on narrow viewports */
    .requests-table-container {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    /* Provide a reasonable min-width so columns don’t crush */
    .requests-table {
      min-width: 720px;
    }

    /* Responsive layout tweaks */
    @media (max-width: 1024px) {

      /* Let stats auto-wrap into two columns if parent allows grid or flex */
      .stats-container {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
      }
    }

    @media (max-width: 768px) {
      .page-header h1 {
        font-size: 1.35rem;
        line-height: 1.2;
      }

      .page-header p {
        font-size: 0.95rem;
      }

      .request-container {
        padding-left: 14px;
        padding-right: 14px;
      }

      .requests-table {
        min-width: 640px;
      }

      .requests-table th,
      .requests-table td {
        padding: 8px 10px;
        font-size: 0.95rem;
      }

      .tab-badge {
        font-size: 0.75rem;
      }

      .stat-card h3 {
        font-size: 1.3rem;
      }
    }

    @media (max-width: 480px) {

      /* Stack stats into a single column for very small screens */
      .stats-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .requests-table {
        min-width: 560px;
      }

      .requests-table th,
      .requests-table td {
        padding: 6px 8px;
        font-size: 0.9rem;
      }

      .status-tab {
        padding: 8px 10px;
        font-size: 0.9rem;
      }

      /* Make action buttons full-width for better tap targets */
      .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }

      .action-buttons .btn,
      .action-buttons a.btn,
      .action-buttons button.btn {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <?php include './Navbar/navbar.php'; ?>

  <div class="request-container">
    <div class="page-header">
      <h1><i class="fas fa-file-alt"></i> My Request Status</h1>
      <p>Track all your submitted requests and their current status</p>
      <!-- Real-time monitoring indicator -->
      <div id="realtimeIndicator" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #28a745; margin-top: 8px;">
        <i class="fas fa-circle" style="font-size: 0.5rem; animation: pulse 2s infinite;"></i>
        <span>Real-time monitoring active</span>
      </div>
    </div>

    <!-- Document Collection Information -->
    <div style="background-color: #d1edff; border: 1px solid #b3d7ff; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 15px;">
      <i class="fas fa-info-circle" style="color: #0b5ed7; font-size: 1.3rem; flex-shrink: 0; margin-top: 2px;"></i>
      <div>
        <strong style="color: #0b5ed7;">Document Collection Information</strong>
        <p style="margin: 5px 0 0 0; color: #0b5ed7; font-size: 0.95rem; line-height: 1.4;">
          To claim your approved requested documents, please proceed to the barangay hall, pay the required payment, and collect your documents. For any questions or clarifications, please contact us at <strong>86380301</strong> during office hours.
        </p>
      </div>
    </div>

    <?php 
    // Check if there are any pending requests
    $hasPendingRequests = !empty($pendingByType);
    if ($hasPendingRequests): 
    ?>
      <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
        <i class="fas fa-info-circle" style="color: #856404; font-size: 1.3rem; flex-shrink: 0;"></i>
        <div>
          <strong style="color: #856404;">Pending Request(s) Found</strong>
          <p style="margin: 5px 0 0 0; color: #856404; font-size: 0.95rem;">
            You have pending request(s). You can access the service forms to update or modify your pending requests. 
            Click on the "Pending" tab below to view and manage your pending requests.
          </p>
        </div>
      </div>
    <?php endif; ?>

    <?php if (empty($userRequests)): ?>
      <div class="requests-section">
        <div class="no-requests">
          <i class="fas fa-inbox"></i>
          <h3>No Requests Found</h3>
          <p>You haven't made any requests yet. Visit our services page to submit your first request.</p>
        </div>
      </div>
    <?php else: ?>

      <?php
      // Segregate requests by status and count them - sort approved and Declined by date (latest first)
      $pendingRequests = [];
      $approvedRequests = [];
      $DeclinedRequests = [];
      $releasedRequests = [];

      foreach ($userRequests as $request) {
        switch (strtolower($request['status'])) {
          case 'pending':
            $pendingRequests[] = $request;
            break;
          case 'released':
            $releasedRequests[] = $request;
            break;
          case 'approved':
          case 'completed':
            $approvedRequests[] = $request;
            break;
          case 'declined':
            $DeclinedRequests[] = $request;
            break;
          default:
            $pendingRequests[] = $request;
        }
      }

      // Sort approved, released and Declined requests by date (latest first)
      usort($approvedRequests, function ($a, $b) {
        return strtotime($b['date_requested']) - strtotime($a['date_requested']);
      });

      usort($releasedRequests, function ($a, $b) {
        return strtotime($b['date_requested']) - strtotime($a['date_requested']);
      });

      usort($DeclinedRequests, function ($a, $b) {
        return strtotime($b['date_requested']) - strtotime($a['date_requested']);
      });
      ?>

      <!-- Statistics Cards -->
      <div class="stats-container">
        <div class="stat-card">
          <h3><?php echo count($userRequests); ?></h3>
          <p>Total Requests</p>
        </div>
        <div class="stat-card">
          <h3><?php echo count($pendingRequests); ?></h3>
          <p>Pending</p>
        </div>
        <div class="stat-card">
          <h3><?php echo count($approvedRequests); ?></h3>
          <p>Approved</p>
        </div>
        <div class="stat-card">
          <h3><?php echo count($releasedRequests); ?></h3>
          <p>Released</p>
        </div>
        <div class="stat-card">
          <h3><?php echo count($DeclinedRequests); ?></h3>
          <p>Declined</p>
        </div>
      </div>

      <div class="requests-section">
        <h2 class="section-title">
          <i class="fas fa-list"></i>
          Request History
        </h2>

        <!-- Status Tabs -->
        <div class="status-tabs">
          <button class="status-tab active" onclick="showTab('all')">
            All Requests <span class="tab-badge"><?php echo count($userRequests); ?></span>
          </button>
          <button class="status-tab" onclick="showTab('pending')">
            Pending <span class="tab-badge"><?php echo count($pendingRequests); ?></span>
          </button>
          <button class="status-tab" onclick="showTab('approved')">
            Approved <span class="tab-badge"><?php echo count($approvedRequests); ?></span>
          </button>
          <button class="status-tab" onclick="showTab('released')">
            Released <span class="tab-badge"><?php echo count($releasedRequests); ?></span>
          </button>
          <button class="status-tab" onclick="showTab('Declined')">
            Declined <span class="tab-badge"><?php echo count($DeclinedRequests); ?></span>
          </button>
        </div>

        <!-- All Requests Tab -->
        <div id="all-tab" class="tab-content active">
          <div class="requests-table-container">
            <table class="requests-table">
              <thead>
                <tr>
                  <th class="col-type">Request Type</th>
                  <th class="col-description">Description</th>
                  <th class="col-reference">Reference No.</th>
                  <th class="col-date">Date Requested</th>
                  <th class="col-status">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($userRequests as $request): ?>
                  <tr>
                    <td class="col-type"><?php echo htmlspecialchars($request['type']); ?></td>
                    <td class="col-description"><?php echo htmlspecialchars($request['description']); ?></td>
                    <td class="col-reference">
                      <span class="request-ref"><?php echo htmlspecialchars($request['refno']); ?></span>
                    </td>
                    <td class="col-date"><?php echo date('M d, Y', strtotime($request['date_requested'])); ?></td>
                    <td class="col-status">
                      <span class="status-badge <?php echo getStatusBadgeClass($request['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Requests Tab -->
        <div id="pending-tab" class="tab-content">
          <div class="requests-table-container">
            <table class="requests-table">
              <thead>
                <tr>
                  <th class="col-type">Request Type</th>
                  <th class="col-description">Description</th>
                  <th class="col-reference">Reference No.</th>
                  <th class="col-date">Date Requested</th>
                  <th class="col-status">Status</th>
                  <th class="col-action" style="text-align: center;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingRequests as $request): ?>
                  <tr>
                    <td class="col-type"><?php echo htmlspecialchars($request['type']); ?></td>
                    <td class="col-description"><?php echo htmlspecialchars($request['description']); ?></td>
                    <td class="col-reference">
                      <span class="request-ref"><?php echo htmlspecialchars($request['refno']); ?></span>
                    </td>
                    <td class="col-date"><?php echo date('M d, Y', strtotime($request['date_requested'])); ?></td>
                    <td class="col-status">
                      <span class="status-badge <?php echo getStatusBadgeClass($request['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                      </span>
                    </td>
                    <td class="col-action" style="text-align: center;">
                      <a href="#" onclick="updateRequest('<?php echo htmlspecialchars($request['refno']); ?>', '<?php echo htmlspecialchars($request['type']); ?>'); return false;" 
                         style="color: #0b5ed7; text-decoration: none; font-weight: 500;">
                        <i class="fas fa-edit"></i> Update
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($pendingRequests)): ?>
                  <tr>
                    <td colspan="6" class="no-requests-row">
                      <div class="no-requests-message">
                        <i class="fas fa-check-circle"></i>
                        <p>No pending requests found.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Approved Requests Tab -->
        <div id="approved-tab" class="tab-content">
          <div class="requests-table-container">
            <table class="requests-table">
              <thead>
                <tr>
                  <th class="col-type">Request Type</th>
                  <th class="col-description">Description</th>
                  <th class="col-reference">Reference No.</th>
                  <th class="col-date">Date Requested</th>
                  <th class="col-status">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($approvedRequests as $request): ?>
                  <tr>
                    <td class="col-type"><?php echo htmlspecialchars($request['type']); ?></td>
                    <td class="col-description"><?php echo htmlspecialchars($request['description']); ?></td>
                    <td class="col-reference">
                      <span class="request-ref"><?php echo htmlspecialchars($request['refno']); ?></span>
                    </td>
                    <td class="col-date"><?php echo date('M d, Y', strtotime($request['date_requested'])); ?></td>
                    <td class="col-status">
                      <span class="status-badge <?php echo getStatusBadgeClass($request['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($approvedRequests)): ?>
                  <tr>
                    <td colspan="5" class="no-requests-row">
                      <div class="no-requests-message">
                        <i class="fas fa-info-circle"></i>
                        <p>No approved requests found.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Released Requests Tab -->
        <div id="released-tab" class="tab-content">
          <div class="requests-table-container">
            <table class="requests-table">
              <thead>
                <tr>
                  <th class="col-type">Request Type</th>
                  <th class="col-description">Description</th>
                  <th class="col-reference">Reference No.</th>
                  <th class="col-date">Date Requested</th>
                  <th class="col-status">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($releasedRequests as $request): ?>
                  <tr>
                    <td class="col-type"><?php echo htmlspecialchars($request['type']); ?></td>
                    <td class="col-description"><?php echo htmlspecialchars($request['description']); ?></td>
                    <td class="col-reference">
                      <span class="request-ref"><?php echo htmlspecialchars($request['refno']); ?></span>
                    </td>
                    <td class="col-date"><?php echo date('M d, Y', strtotime($request['date_requested'])); ?></td>
                    <td class="col-status">
                      <span class="status-badge <?php echo getStatusBadgeClass($request['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($releasedRequests)): ?>
                  <tr>
                    <td colspan="5" class="no-requests-row">
                      <div class="no-requests-message">
                        <i class="fas fa-check-circle"></i>
                        <p>No released requests found.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Declined Requests Tab -->
        <div id="Declined-tab" class="tab-content">
          <div class="requests-table-container">
            <table class="requests-table">
              <thead>
                <tr>
                  <th class="col-type">Request Type</th>
                  <th class="col-description">Description</th>
                  <th class="col-reference">Reference No.</th>
                  <th class="col-date">Date Requested</th>
                  <th class="col-status">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($DeclinedRequests as $request): ?>
                  <tr>
                    <td class="col-type"><?php echo htmlspecialchars($request['type']); ?></td>
                    <td class="col-description"><?php echo htmlspecialchars($request['description']); ?></td>
                    <td class="col-reference">
                      <span class="request-ref"><?php echo htmlspecialchars($request['refno']); ?></span>
                    </td>
                    <td class="col-date"><?php echo date('M d, Y', strtotime($request['date_requested'])); ?></td>
                    <td class="col-status">
                      <span class="status-badge <?php echo getStatusBadgeClass($request['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($DeclinedRequests)): ?>
                  <tr>
                    <td colspan="5" class="no-requests-row">
                      <div class="no-requests-message">
                        <i class="fas fa-times-circle"></i>
                        <p>No Declined requests found.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
          <a href="../Pages/landingpage.php" class="btn btn-secondary">
            <i class="fas fa-user"></i> Back to Home
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <script>
    function showTab(tabName) {
      // Remove active class from all tabs and tab contents
      const tabs = document.querySelectorAll('.status-tab');
      const tabContents = document.querySelectorAll('.tab-content');

      tabs.forEach(tab => tab.classList.remove('active'));
      tabContents.forEach(content => content.classList.remove('active'));

      // Add active class to clicked tab
      event.target.classList.add('active');

      // Show corresponding tab content
      const targetTab = document.getElementById(tabName + '-tab');
      if (targetTab) {
        targetTab.classList.add('active');
      }
    }

    // NEW FEATURE: Update pending request
    function updateRequest(refNo, requestType) {
      // Map request types to their forms
      const updateMap = {
        'Document Request': '../NewRequests/NewGovernmentDocs.php?update=' + refNo,
        'Business Request': '../NewRequests/NewBusinessRequest.php?update=' + refNo,
        'Scholarship Application': '../NewRequests/NewScholar.php?update=' + refNo,
        'Unemployment Certificate': '../NewRequests/NewNoFixIncome.php?update=' + refNo,
        'Guardianship/Solo Parent': '../NewRequests/NewGuardianshipForm.php?update=' + refNo,
        'No Birth Certificate': '../NewRequests/NewNoBirthCertificate.php?update=' + refNo,
        'Complaint': '../NewRequests/NewComplain.php?update=' + refNo,
        'Cohabitation Form': '../NewRequests/CohabilitationForm.php?update=' + refNo
      };

      if (updateMap[requestType]) {
        window.location.href = updateMap[requestType];
      } else {
        alert('Update feature not available for this request type.');
      }
    }
  </script>
  
  <!-- Real-time Notification System -->
  <script>
    // Real-time notification checking
    let notificationCheckInterval;
    let isPageVisible = true;
    const STORAGE_KEY = 'barangay_resident_<?php echo $_SESSION['user_id']; ?>_status';
    const USER_ID = '<?php echo $_SESSION['user_id']; ?>';
    const USER_ROLE = '<?php echo $userRole; ?>';

    // Function to get stored request snapshot
    function getStoredSnapshot() {
      try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (!stored) return null;
        
        const data = JSON.parse(stored);
        
        // Validate that stored data belongs to current user
        if (data.userId && data.userId !== USER_ID) {
          console.log('⚠️ Stored data belongs to different user, clearing...');
          localStorage.removeItem(STORAGE_KEY);
          return null;
        }
        
        return data.requests || data;
      } catch (e) {
        console.log('Error reading stored snapshot:', e);
        return null;
      }
    }

    // Function to save request snapshot
    function saveSnapshot(snapshot) {
      try {
        const data = {
          userId: USER_ID,
          role: USER_ROLE,
          timestamp: Date.now(),
          requests: snapshot
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
      } catch (e) {
        console.log('Error saving snapshot:', e);
      }
    }

    // Function to check for status updates
    function checkForStatusUpdates() {
      // Only check if page is visible and user is active
      if (!isPageVisible) return;
      
      // Add timestamp to prevent caching
      const timestamp = Date.now();
      
      fetch(`../Process/check_status_updates.php?t=${timestamp}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache'
        }
      })
      .then(response => response.json())
      .then(data => {
        // Check if user role changed (admin logged in)
        if (data.error && data.role) {
          console.log('⚠️ Session changed - redirecting...');
          // Admin logged in, stop monitoring and redirect
          clearInterval(notificationCheckInterval);
          window.location.href = '../Login/login.php';
          return;
        }
        
        if (data.success && data.requests) {
          const currentRequests = data.requests;
          const previousRequests = getStoredSnapshot();
          
          console.log('📊 Current requests:', Object.keys(currentRequests).length);
          console.log('📊 Previous requests:', previousRequests ? Object.keys(previousRequests).length : 0);
          
          // If this is the first check, just store the current state
          if (!previousRequests) {
            saveSnapshot(currentRequests);
            console.log('✅ Real-time monitoring initialized - snapshot saved');
            return;
          }
          
          // Compare current with previous to detect changes
          const notifications = [];
          
          // Check each current request against previous state
          for (const refno in currentRequests) {
            const currentReq = currentRequests[refno];
            const previousReq = previousRequests[refno];
            
            // Normalize status to lowercase for comparison
            const currentStatus = currentReq.status.toLowerCase();
            const previousStatus = previousReq ? previousReq.status.toLowerCase() : null;
            
            console.log(`🔍 Checking ${refno}: ${previousStatus} -> ${currentStatus}`);
            
            // If request exists in both and status changed
            if (previousReq && currentStatus !== previousStatus) {
              console.log(`🔔 STATUS CHANGE DETECTED! ${refno}: ${previousStatus} -> ${currentStatus}`);
              const statusChangeNotif = detectStatusChange(currentReq, previousReq);
              if (statusChangeNotif) {
                notifications.push(statusChangeNotif);
                console.log('✅ Notification created:', statusChangeNotif);
              }
            }
          }
          
          // Show notifications if any status changes detected
          if (notifications.length > 0) {
            console.log('🎉 Showing notifications:', notifications.length);
            
            notifications.forEach(notification => {
              showStatusNotification(notification.message, notification.type);
            });
            
            // Update stored snapshot AFTER showing notification
            saveSnapshot(currentRequests);
            
            // Refresh the page after showing notifications
            setTimeout(() => {
              console.log('🔄 Reloading page to show updated status...');
              location.reload();
            }, 4000);
          } else {
            // No changes, just update the snapshot
            console.log('✅ No status changes detected');
            saveSnapshot(currentRequests);
          }
        }
      })
      .catch(error => {
        console.error('❌ Error checking updates:', error);
      });
    }

    // Function to detect and create notification for status change
    function detectStatusChange(currentReq, previousReq) {
      const oldStatus = previousReq.status;
      const newStatus = currentReq.status;
      const requestType = currentReq.type || 'Request';
      const refNo = currentReq.refno || 'N/A';
      
      // Pending -> Approved
      if (oldStatus === 'pending' && (newStatus === 'approved' || newStatus === 'completed')) {
        return {
          type: 'approved',
          message: `Your ${requestType} (Ref No: ${refNo}) is approved. Please proceed to the barangay office and pay the needed fee to get your request.`
        };
      }
      
      // Pending -> Declined
      if (oldStatus === 'pending' && newStatus === 'declined') {
        const reason = currentReq.decline_reason || 'incomplete requirements';
        return {
          type: 'declined',
          message: `Unfortunately, your ${requestType} (Ref No: ${refNo}) is declined due to ${reason}. For inquiries, go to the barangay or contact us at: 86380301.`
        };
      }
      
      // Approved -> Released
      if ((oldStatus === 'approved' || oldStatus === 'completed') && newStatus === 'released') {
        return {
          type: 'released',
          message: `Your ${requestType} (Ref No: ${refNo}) is now ready for pickup. Please proceed to the barangay office.`
        };
      }
      
      return null;
    }

    // Function to show status update notifications - Simple and clean design
    function showStatusNotification(message, type) {
      // Create notification element
      const notification = document.createElement('div');
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 450px;
        padding: 16px 20px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        animation: slideInFromRight 0.4s ease-out;
        cursor: pointer;
        border-left: 4px solid;
      `;
      
      // Set simple single-tone color based on type
      if (type === 'approved') {
        notification.style.backgroundColor = '#e8f5e9';
        notification.style.color = '#2e7d32';
        notification.style.borderLeftColor = '#2e7d32';
      } else if (type === 'declined') {
        notification.style.backgroundColor = '#ffebee';
        notification.style.color = '#c62828';
        notification.style.borderLeftColor = '#c62828';
      } else if (type === 'released') {
        notification.style.backgroundColor = '#e3f2fd';
        notification.style.color = '#1565c0';
        notification.style.borderLeftColor = '#1565c0';
      } else {
        notification.style.backgroundColor = '#f5f5f5';
        notification.style.color = '#424242';
        notification.style.borderLeftColor = '#757575';
      }
      
      // Simple text, no icons
      notification.textContent = message;
      
      // Add click to dismiss
      notification.addEventListener('click', function() {
        notification.style.animation = 'slideOutToRight 0.3s ease-out';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      });
      
      // Add to page
      document.body.appendChild(notification);
      
      // Auto remove after 10 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.style.animation = 'slideOutToRight 0.3s ease-out';
          setTimeout(() => {
            if (notification.parentNode) {
              notification.parentNode.removeChild(notification);
            }
          }, 300);
        }
      }, 10000);
    }

    // Add CSS animations for notifications
    if (!document.getElementById('notification-animations')) {
      const style = document.createElement('style');
      style.id = 'notification-animations';
      style.textContent = `
        @keyframes slideInFromRight {
          from {
            transform: translateX(100%);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
        
        @keyframes slideOutToRight {
          from {
            transform: translateX(0);
            opacity: 1;
          }
          to {
            transform: translateX(100%);
            opacity: 0;
          }
        }
      `;
      document.head.appendChild(style);
    }

    // Page visibility detection
    document.addEventListener('visibilitychange', function() {
      isPageVisible = !document.hidden;
      
      if (isPageVisible) {
        // Page became visible, do immediate check
        checkForStatusUpdates();
      }
    });

    // Start checking for updates when page loads
    document.addEventListener('DOMContentLoaded', function() {
      // Clean up any admin localStorage keys
      const allKeys = Object.keys(localStorage);
      allKeys.forEach(key => {
        if (key.includes('barangay') && !key.includes('resident') && !key.includes(USER_ID)) {
          localStorage.removeItem(key);
        }
      });
      
      console.log('✅ Real-time notification system active (User ID: ' + USER_ID + ')');
      
      // Initial check after a short delay
      setTimeout(checkForStatusUpdates, 2000);
      
      // Set up regular interval checking (every 5 seconds)
      notificationCheckInterval = setInterval(checkForStatusUpdates, 5000);
    });

    // Clean up interval when page unloads
    window.addEventListener('beforeunload', function() {
      if (notificationCheckInterval) {
        clearInterval(notificationCheckInterval);
      }
    });

    // Also check when window gains focus
    window.addEventListener('focus', function() {
      setTimeout(checkForStatusUpdates, 500);
    });
  </script>
</body>

</html>
