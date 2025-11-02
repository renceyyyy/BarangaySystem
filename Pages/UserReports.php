<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) {
  header("Location: ../Login/login.php");
  exit();
}

// Include database connection module
require_once '../Process/db_connection.php';

// Get database connection
$conn = getDBConnection();

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
    </div>

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

      foreach ($userRequests as $request) {
        switch (strtolower($request['status'])) {
          case 'pending':
            $pendingRequests[] = $request;
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

      // Sort approved and Declined requests by date (latest first)
      usort($approvedRequests, function ($a, $b) {
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
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($pendingRequests)): ?>
                  <tr>
                    <td colspan="5" class="no-requests-row">
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
  </script>
</body>

</html>