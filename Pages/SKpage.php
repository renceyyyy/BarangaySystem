<?php 
// Initialize role-based session for SK
require_once __DIR__ . '/../config/session_config.php';
initRoleBasedSession('sk');

// Security check — only sk users allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'sk') {
    header("Location: ../Login/login.php");
    exit();
}

include 'dashboard.php'; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SK Scholarship Management - Barangay Sampaguita</title>
  <link rel="stylesheet" href="../Styles/admin.css">
  <style>
    /* SKpage-specific override: force all alerts to green */
    #global-alerts .global-alert,
    #global-alerts .global-alert.success,
    #global-alerts .global-alert.error,
    #global-alerts .global-alert.warning,
    #global-alerts .global-alert.info {
      background: linear-gradient(90deg, #4CAF50, #2E7D32) !important;
      color: #fff !important;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12) !important;
    }

    #global-alerts .alert-icon,
    #global-alerts .alert-close {
      color: #fff !important;
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="../Styles/admin.css">
  <style>
    .status-badge {
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-pending {
      background-color: #E3F2FD;
      color: #1976D2;
      border: 1px solid #90CAF9;
    }

    .status-approved {
      background-color: #FFF3E0;
      color: #F57C00;
      border: 1px solid #FFB74D;
    }

    .status-for-examination {
      background-color: #E8F5E9;
      color: #388E3C;
      border: 1px solid #81C784;
    }

    .status-final-approved {
      background-color: #4CAF50;
      color: #fff;
      border: 1px solid #388E3C;
    }

    .status-rejected {
      background-color: #FFEBEE;
      color: #C62828;
      border: 1px solid #E57373;
    }

    .action-btn-2 {
      padding: 8px 14px;
      margin: 2px;
      border-radius: 6px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      justify-content: center;
      font-size: 13px;
      font-weight: 500;
      min-width: 30px;
      transition: all 0.2s ease;
      border: none;
      cursor: pointer;
      height: 30px;
    }

    .action-btn-2.view {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
    }

    .action-btn-2.approve {
      background: linear-gradient(135deg, #28a745 0%, #218838 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }

    .action-btn-2.decline {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }

    .action-btn-2.print {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
    }

    .action-btn-2:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .action-btn-2:active {
      transform: translateY(0);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .govdoc-search-group {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
      margin-bottom: 20px;
    }

    .govdoc-search-input {
      min-width: 200px;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 8px 12px;
    }

    .govdoc-search-button,
    .add-user {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
    }

    .govdoc-search-button {
      background-color: #6c757d;
      color: white;
    }

    .add-user {
      background-color: #17a2b8;
      color: white;
    }

    .scrollable-table-container {
      max-height: 600px;
      overflow-y: auto;
      border: 1px solid #ddd;
      border-radius: 5px;
      margin-top: 10px;
    }

    .styled-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    .styled-table th {
      background-color: #4CAF50;
      color: white;
      position: sticky;
      top: 0;
      z-index: 10;
      padding: 12px;
      text-align: left;
      border-bottom: 2px solid #ddd;
    }

    .styled-table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .styled-table tr:hover {
      background-color: #f5f5f5;
    }

    .styled-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .action-buttons {
      display: flex;
      gap: 5px;
      justify-content: center;
    }

    /* Panel styling */
    .panel-content {
      display: none;
    }

    .panel-content.active {
      display: block;
    }

    /* Custom confirm overlay styles */
    .custom-confirm-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .custom-confirm-box {
      background: white;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
      min-width: 300px;
    }

    .custom-confirm-actions {
      margin-top: 15px;
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .custom-confirm-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .custom-confirm-btn.yes {
      background: #28a745;
      color: white;
    }

    .custom-confirm-btn.no {
      background: #dc3545;
      color: white;
    }

    /* View Modal Styles */
    .view-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
    }

    .view-modal-content {
      background-color: #fff;
      padding: 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 700px;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
    }

    .close-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 24px;
      cursor: pointer;
      color: #666;
    }

    .close-btn:hover {
      color: #000;
    }

    .modal-form {
      margin-top: 20px;
    }

    .form-section {
      margin-bottom: 25px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    .form-group label {
      font-weight: bold;
      margin-bottom: 5px;
      color: #333;
    }

    .form-group input {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      background-color: #f9f9f9;
    }

    /* Print Modal Styles */
    .modal-content {
      border-radius: 10px;
      border: none;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
      background-color: #4CAF50;
      color: white;
      border-bottom: none;
      border-radius: 10px 10px 0 0;
    }

    .modal-title {
      font-weight: bold;
    }

    .btn-primary {
      background-color: #4CAF50;
      border-color: #4CAF50;
    }

    .btn-primary:hover {
      background-color: #45a049;
      border-color: #45a049;
    }

    /* ===== RESPONSIVE DESIGN ===== */
    @media (max-width: 768px) {

      /* Sidebar becomes top navbar on mobile */
      .sidebar {
        position: relative !important;
        width: 100% !important;
        height: auto !important;
        padding: 15px !important;
        display: flex !important;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
      }

      .sidebar img {
        display: none;
        /* Hide logo on mobile */
      }

      .sidebar-btn {
        display: inline-block;
        margin: 5px;
        padding: 10px 15px;
        font-size: 14px;
        flex: 0 0 auto;
      }

      .logout-link {
        display: inline-block;
        margin: 5px;
        flex: 0 0 auto;
      }

      /* Main content full width on mobile */
      .col-md-10 {
        flex: 0 0 100%;
        max-width: 100%;
      }

      .main-content-scroll {
        padding: 10px !important;
      }

      /* Stack stat cards vertically */
      .stat-card-container {
        flex-direction: column !important;
        gap: 10px;
      }

      .stat-card {
        width: 100% !important;
        margin-bottom: 10px;
        min-width: auto !important;
      }

      /* Stack charts vertically */
      .chart-container {
        flex-direction: column !important;
        gap: 15px !important;
      }

      .boxes,
      .genderbox {
        width: 100% !important;
        min-width: auto !important;
        flex: none !important;
        margin-bottom: 20px;
      }

      /* Make tables horizontally scrollable */
      .scrollable-table-container {
        overflow-x: auto;
      }

      .styled-table {
        min-width: 600px;
      }

      /* Search form responsiveness */
      .govdoc-search-group {
        flex-direction: column;
        align-items: stretch;
      }

      .govdoc-search-input,
      .govdoc-search-button,
      .add-user {
        width: 100%;
      }

      /* Modal adjustments */
      .modal-content {
        margin: 10px;
        width: calc(100% - 20px);
      }

      /* Reports dashboard - stack tables */
      .row .col-md-4,
      .row .col-md-6 {
        width: 100% !important;
        margin-bottom: 15px;
        flex: 0 0 100%;
        max-width: 100%;
      }

      /* Admin header */
      .admin-header h1 {
        font-size: 18px !important;
      }
    }

    @media (min-width: 769px) and (max-width: 1024px) {

      /* Tablet adjustments */
      .sidebar {
        width: 180px !important;
      }

      .sidebar-btn {
        font-size: 12px;
        padding: 8px;
      }

      .stat-card {
        min-width: 180px;
      }

      .chart-container {
        gap: 15px;
      }

      .boxes,
      .genderbox {
        min-width: 280px;
      }
    }

    /* ===== PRINT STYLES ===== */
    @media print {

      /* Hide URL and page info */
      @page {
        margin: 0.5in;
      }

      /* Hide everything first */
      body * {
        visibility: hidden;
      }

      /* Show only report container */
      #reportContainer,
      #reportContainer * {
        visibility: visible;
      }

      #reportContainer {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
        padding: 20px;
        margin: 0;
      }

      /* Hide UI elements */
      .sidebar,
      .admin-header,
      button,
      .no-print,
      input[type="date"] {
        display: none !important;
      }

      /* Add print header */
      #reportContainer::before {
        content: "SK Scholarship Management - Barangay Sampaguita";
        display: block;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
        color: #000;
      }

      #reportContainer::after {
        content: "Generated on: " attr(data-print-date);
        display: block;
        text-align: right;
        font-size: 10px;
        margin-top: 10px;
        color: #666;
      }

      /* Table styling for print */
      table {
        page-break-inside: avoid;
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
      }

      table thead {
        background-color: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      table th {
        border: 1px solid #000 !important;
        padding: 6px !important;
        font-weight: bold;
      }

      table td {
        border: 1px solid #ccc !important;
        padding: 4px !important;
      }

      /* Badge styling */
      .badge {
        border: 1px solid #000 !important;
        padding: 2px 6px !important;
        border-radius: 3px !important;
        font-size: 10px !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .badge.bg-success {
        background-color: #4CAF50 !important;
        color: white !important;
      }

      .badge.bg-warning {
        background-color: #FFC107 !important;
        color: black !important;
      }

      .badge.bg-info {
        background-color: #17a2b8 !important;
        color: white !important;
      }

      /* Chart sizing */
      canvas {
        max-height: 250px !important;
        max-width: 400px !important;
      }

      /* Page breaks */
      h5 {
        page-break-after: avoid;
      }

      .row {
        page-break-inside: avoid;
      }

      /* Hide form inputs in print */
      input {
        border: none !important;
        background: transparent !important;
      }
    }

    /* ===== IMPROVED ALERT STYLES ===== */
    .custom-alert {
      position: fixed;
      top: 20px;
      right: 20px;
      min-width: 300px;
      max-width: 400px;
      padding: 16px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: slideInRight 0.3s ease-out;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    @keyframes slideInRight {
      from {
        transform: translateX(400px);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes slideOutRight {
      from {
        transform: translateX(0);
        opacity: 1;
      }

      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }

    .custom-alert.hiding {
      animation: slideOutRight 0.3s ease-out;
    }

    .custom-alert.success {
      background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
      color: white;
    }

    .custom-alert.error {
      background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
      color: white;
    }

    .custom-alert.warning {
      background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
      color: white;
    }

    .custom-alert.info {
      background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
      color: white;
    }

    .custom-alert-icon {
      font-size: 24px;
      flex-shrink: 0;
    }

    .custom-alert-content {
      flex: 1;
    }

    .custom-alert-title {
      font-weight: 600;
      font-size: 15px;
      margin-bottom: 4px;
    }

    .custom-alert-message {
      font-size: 14px;
      opacity: 0.95;
    }

    .custom-alert-close {
      background: none;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      padding: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background 0.2s;
      flex-shrink: 0;
    }

    .custom-alert-close:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    /* Mobile alert adjustments */
    @media (max-width: 768px) {
      .custom-alert {
        top: 10px;
        right: 10px;
        left: 10px;
        min-width: auto;
        max-width: none;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-12 col-md-2 sidebar">
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
          style="width: 100%; max-width: 160px; border-radius: 50%;" />
        <button class="sidebar-btn" type="button" onclick="showPanel('dashboardPanel')">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </button>

        <button class="sidebar-btn" onclick="showPanel('scholarshipPanel')">
          <i class="fas fa-graduation-cap"></i> Scholarship Applications
        </button>

        <button class="sidebar-btn" onclick="showPanel('reportsPanel')">
          <i class="fas fa-chart-pie"></i> Reports Dashboard
        </button>

        <a href="#" class="logout-link mt-auto" onclick="confirmLogout(event)">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>

      <!-- Main Content -->
      <div class="col-12 col-md-10 p-0">
        <div class="main-content-scroll p-3">
          <div class="admin-header">
            <h1>SANGGUNIANG KABATAAN SCHOLARSHIP MANAGEMENT</h1>
          </div>

          <?php
          // Display session success/error messages
          if (isset($_SESSION['success_message'])) {
            echo "<script>
                  document.addEventListener('DOMContentLoaded', function() {
                      if (window.showAlert) {
                          showAlert('success', '" . addslashes($_SESSION['success_message']) . "', 5000);
                      }
                  });
              </script>";
            unset($_SESSION['success_message']);
          }

          if (isset($_SESSION['error_message'])) {
            echo "<script>
                  document.addEventListener('DOMContentLoaded', function() {
                      if (window.showAlert) {
                          showAlert('error', '" . addslashes($_SESSION['error_message']) . "', 5000);
                      }
                  });
              </script>";
            unset($_SESSION['error_message']);
          }
          ?>

          <!-- Panels -->
          <div id="dashboardPanel" class="panel-content active">
            <h3>SK Scholarship Dashboard</h3>
            <div class="stat-card-container mb-3">
              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                  <p>TOTAL APPLICATIONS</p>
                </div>
                <h4><?php echo $scholarshipCount; ?></h4>
              </div>

              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fas fa-clock"></i></div>
                  <p>PENDING</p>
                </div>
                <h4><?php echo $pendingScholarshipCount; ?></h4>
              </div>

              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fas fa-eye"></i></div>
                  <p>FOR EXAMINATION</p>
                </div>
                <h4><?php echo $forExaminationCount; ?></h4>
              </div>

              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                  <p>APPROVED</p>
                </div>
                <h4><?php echo $approvedScholarshipCount; ?></h4>
              </div>

              <!-- REJECTED stat card removed per request -->
            </div>

            <div class="chart-container" style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 30px;">
              <div class="boxes" style="flex: 1 !important; min-width: 300px !important; background: white !important; border: none !important; border-radius: 8px !important; padding: 20px !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; width: auto !important;">
                <h4 style="color: #333; margin-bottom: 20px; font-size: 18px; font-weight: 600; text-align: center; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Scholarship Status Distribution</h4>
                <canvas id="scholarshipChart"></canvas>
              </div>
              <div class="genderbox" style="flex: 1 !important; min-width: 300px !important; background: white !important; border: none !important; border-radius: 8px !important; padding: 20px !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; width: auto !important;">
                <h4 style="color: #333; margin-bottom: 20px; font-size: 18px; font-weight: 600; text-align: center; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Applications by Month (<?php echo date('Y'); ?>)</h4>
                <canvas id="monthlyChart"></canvas>
              </div>
            </div>
          </div>

          <div id="scholarshipPanel" class="panel-content">
            <h3>Scholarship Application Management</h3>

            <!-- Search Form similar to government documents -->
            <form method="GET" action="" class="govdoc-search-form">
              <div class="govdoc-search-group">
                <input type="text" name="search_name" class="govdoc-search-input" placeholder="Search by Name"
                  value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>">
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>
                <select name="status_filter" class="govdoc-search-input">
                  <option value="">All Status</option>
                  <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="For Examination" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'For Examination') ? 'selected' : ''; ?>>For Examination</option>
                  <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                </select>
                <button type="submit" name="filter" value="1" class="govdoc-search-button">
                  <i class="fas fa-filter"></i> Filter
                </button>
                <button class="add-user" type="button" onclick="openScholarshipModal()">
                  <i class="fa-regular fa-plus"></i> Add Application
                </button>
              </div>
            </form>

            <?php
            // Database connection
            $connection = new mysqli("localhost", "root", "", "barangaydb");
            if ($connection->connect_error) {
              die("Connection failed: " . $connection->connect_error);
            }

            // Build search query
            $searchQuery = "";
            if (isset($_GET['search_name']) && !empty(trim($_GET['search_name']))) {
              $search = $connection->real_escape_string($_GET['search_name']);
              $searchQuery = " AND (Firstname LIKE '%$search%' OR Lastname LIKE '%$search%')";
            }

            if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
              $status = $connection->real_escape_string($_GET['status_filter']);
              // The UI no longer exposes a 'Rejected' filter; ignore any explicit 'Rejected' filter.
              if ($status !== 'Rejected') {
                $searchQuery .= " AND RequestStatus = '$status'";
              }
            }

            // Exclude rejected applications from the listing by default
            $searchQuery .= " AND RequestStatus != 'Rejected'";

            // Check if ScholarshipGrant column exists so we can display it
            $grantColumnCheck = $connection->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'scholarship' AND COLUMN_NAME = 'ScholarshipGrant'");
            $includeGrant = false;
            if ($grantColumnCheck && $rowc = $grantColumnCheck->fetch_assoc()) {
              $includeGrant = ($rowc['cnt'] > 0);
            }

            // Query to get scholarship applications (include ScholarshipGrant if available)
            if ($includeGrant) {
              $scholarshipSql = "SELECT ApplicationID, UserID, Firstname, Lastname, Email, ContactNo, Address, Reason, EducationLevel, RequestStatus, DateApplied, ScholarshipGrant
                   FROM scholarship
                   WHERE 1=1 $searchQuery
                   ORDER BY DateApplied DESC";
            } else {
              $scholarshipSql = "SELECT ApplicationID, UserID, Firstname, Lastname, Email, ContactNo, Address, Reason, EducationLevel, RequestStatus, DateApplied
                   FROM scholarship
                   WHERE 1=1 $searchQuery
                   ORDER BY DateApplied DESC";
            }

            $scholarshipResult = $connection->query($scholarshipSql);
            ?>

            <div class="scrollable-table-container">
              <table class="styled-table" id="scholarshipTable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th>CONTACT</th>
                    <th>ADDRESS</th>
                    <th>REASON</th>
                    <th>EDUCATION LEVEL</th>
                    <th>DATE APPLIED</th>
                    <th>STATUS</th>
                    <th>SCHOLARSHIP GRANT</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($scholarshipResult && $scholarshipResult->num_rows > 0) {
                    while ($row = $scholarshipResult->fetch_assoc()) {
                      $statusClass = "";
                      if ($row["RequestStatus"] == "For Examination") {
                        $statusClass = "status-for-examination";
                        $displayStatus = "For Examination";
                      } elseif ($row["RequestStatus"] == "Approved") {
                        $statusClass = "status-final-approved";
                        $displayStatus = "Approved";
                      } elseif ($row["RequestStatus"] == "Rejected") {
                        $statusClass = "status-rejected";
                        $displayStatus = $row["RequestStatus"];
                      } else {
                        $statusClass = "status-pending";
                        $displayStatus = $row["RequestStatus"];
                      }

                      // Format date
                      $dateApplied = date('M d, Y h:i A', strtotime($row["DateApplied"]));

                      // Get education level with fallback
                      $educationLevelRaw = isset($row["EducationLevel"]) && !empty($row["EducationLevel"]) ? $row["EducationLevel"] : 'Not Specified';
                      $educationLevel = htmlspecialchars($educationLevelRaw);
                      $educationLevelJS = addslashes($educationLevelRaw); // For JavaScript onclick

                      echo "<tr>
                        <td>{$row["ApplicationID"]}</td>
                        <td>" . htmlspecialchars(strtoupper($row["Firstname"] . " " . $row["Lastname"])) . "</td>
                        <td>
                          <strong>Phone:</strong> " . htmlspecialchars($row["ContactNo"]) . "<br>
                          <strong>Email:</strong> " . htmlspecialchars($row["Email"]) . "
                        </td>
                        <td>" . htmlspecialchars($row["Address"]) . "</td>
                        <td title='" . htmlspecialchars($row["Reason"]) . "'>" .
                        (strlen($row["Reason"]) > 50 ? substr($row["Reason"], 0, 50) . "..." : $row["Reason"]) .
                        "</td>
                        <td>{$educationLevel}</td>
                        <td>{$dateApplied}</td>
                        <td><span class='status-badge {$statusClass}'>{$displayStatus}</span></td>";

                      // Scholarship Grant cell (visible / functional only when Approved)
                      if ($row["RequestStatus"] === 'Approved') {
                        if ($includeGrant) {
                          $grantVal = isset($row['ScholarshipGrant']) && $row['ScholarshipGrant'] !== null && $row['ScholarshipGrant'] !== '' ? '₱' . number_format((float)$row['ScholarshipGrant'], 2) : '';
                          if ($grantVal !== '') {
                            echo "<td id='grant-cell-{$row["ApplicationID"]}'>{$grantVal}</td>";
                          } else {
                            echo "<td id='grant-cell-{$row["ApplicationID"]}'><button class='action-btn-2 approve' onclick=\"openGrantModal({$row['ApplicationID']}, '')\">Set Grant</button></td>";
                          }
                        } else {
                          echo "<td id='grant-cell-{$row["ApplicationID"]}'><button class='action-btn-2 approve' onclick=\"openGrantModal({$row['ApplicationID']}, '')\">Set Grant</button></td>";
                        }
                      } else {
                        echo "<td></td>";
                      }

                      echo "<td>
                          <div class='action-buttons'>";

                      // Show PRINT button only if approved (final status)
                      if ($row["RequestStatus"] === "Approved") {
                        $scholarData = json_encode([
                          "ApplicationID" => $row['ApplicationID'],
                          "Firstname" => $row['Firstname'],
                          "Lastname" => $row['Lastname'],
                          "Address" => $row['Address'],
                          "DateApplied" => $row['DateApplied'],
                          "Reason" => $row['Reason'],
                          "EducationLevel" => $row['EducationLevel']
                        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                        echo "<button type='button' class='action-btn-2 print' onclick='openScholarshipPrintModal(JSON.parse(`$scholarData`))'>
                                <i class='fas fa-print'></i>
                              </button>";
                      } elseif ($row["RequestStatus"] === "For Examination") {
                        // Show PASSED and FAILED buttons for For Examination status
                        echo "<button type='button'
                               class='action-btn-2 approve'
                               onclick=\"showPassedModal({$row['ApplicationID']}, '{$educationLevelJS}');\"
                               title='Mark as Passed'>
                               <i class='fas fa-check-circle'></i> Passed
                            </button>";
                        echo "<button type='button'
                               class='action-btn-2 decline'
                               onclick=\"showFailedModal({$row['ApplicationID']});\"
                               title='Mark as Failed'
                               style='margin-left: 5px;'>
                               <i class='fas fa-times-circle'></i> Failed
                            </button>";
                      } else {
                        // Show APPROVE button for Pending status to move to For Examination
                        echo "<button type='button'
                               class='action-btn-2 approve'
                               onclick=\"showApproveToExaminationModal({$row['ApplicationID']});\"
                               title='Approve to Examination'>
                               <i class='fas fa-check'></i>
                            </button>";
                      }

                      echo "<button
                              type='button'
                              class='action-btn-2 view'
                              data-applicationid='{$row["ApplicationID"]}'
                              data-firstname='" . htmlspecialchars($row["Firstname"]) . "'
                              data-lastname='" . htmlspecialchars($row["Lastname"]) . "'
                              data-email='" . htmlspecialchars($row["Email"]) . "'
                              data-contact='" . htmlspecialchars($row["ContactNo"]) . "'
                              data-address='" . htmlspecialchars($row["Address"]) . "'
                              data-reason='" . htmlspecialchars($row["Reason"]) . "'
                              data-educationlevel='{$educationLevel}'
                              data-status='{$row["RequestStatus"]}'
                              data-dateapplied='{$dateApplied}'
                              title='View Application'>
                              <i class='fas fa-eye'></i>
                            </button>";

                      // Only show reject button if status is NOT "Approved"
                      if ($row["RequestStatus"] !== "Approved") {
                        echo "<button type='button'
                               class='action-btn-2 decline'
                               data-status='{$row["RequestStatus"]}'
                               onclick=\"showRejectReasonModal({$row["ApplicationID"]}, '{$row["RequestStatus"]}');\"
                               title='Reject Application'>
                               <i class='fas fa-xmark'></i>
                            </button>";
                      }

                      echo "</div>
                        </td>
                      </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No scholarship applications found.</td></tr>";
                  }
                  $connection->close();
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Reports Dashboard Panel -->
          <div id="reportsPanel" class="panel-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
              <div>
                <h3 style="margin: 0;">SANGGUNIANG KABATAAN SCHOLARSHIP MANAGEMENT</h3>
                <p style="font-size: 14px; color: #666; margin: 5px 0 0 0;">Reports Dashboard</p>
              </div>
              <button onclick="printReport()" class="btn btn-success no-print" style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-print"></i> Print Report
              </button>
            </div>

            <!-- Report Container -->
            <div id="reportContainer" style="background: #f8f9fa; padding: 30px; border-radius: 8px;">
              <!-- Budget Allocation Section -->
              <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                  <h5 style="margin: 0; color: #333;">Scholarship Budget Allocation - ₱<span id="totalBudget">0</span></h5>
                  <div style="text-align: right;">
                    <p style="margin: 0; font-size: 14px; color: #666;">Overall Budget</p>
                    <p style="margin: 0; font-size: 24px; font-weight: bold; color: #4CAF50;">₱76,200</p>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <canvas id="budgetPieChart" style="max-height: 400px;"></canvas>
                  </div>
                  <div class="col-md-6" style="display: flex; align-items: center; justify-content: center;">
                    <div id="budgetLegend" style="font-size: 16px;">
                      <!-- Legend will be populated by JavaScript -->
                    </div>
                  </div>
                </div>
              </div>

              <!-- Three Tables by Education Level -->
              <div class="row" style="margin-bottom: 30px;">
                <!-- Junior High School Table -->
                <div class="col-md-4">
                  <div style="background: white; padding: 15px; border-radius: 8px; height: 100%;">
                    <h5 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Junior High School</h5>
                    <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                      <table class="table table-sm table-striped">
                        <thead style="position: sticky; top: 0; background: white;">
                          <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Education Level</th>
                            <th>Date Applied</th>
                            <th>Date of Resubmitting</th>
                            <th style="text-align: center;">Action</th>
                          </tr>
                        </thead>
                        <tbody id="jhsTableBody">
                          <tr>
                            <td colspan="6" class="text-center">Loading...</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Senior High School Table -->
                <div class="col-md-4">
                  <div style="background: white; padding: 15px; border-radius: 8px; height: 100%;">
                    <h5 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Senior High School</h5>
                    <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                      <table class="table table-sm table-striped">
                        <thead style="position: sticky; top: 0; background: white;">
                          <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Education Level</th>
                            <th>Date Applied</th>
                            <th>Date of Resubmitting</th>
                            <th style="text-align: center;">Action</th>
                          </tr>
                        </thead>
                        <tbody id="shsTableBody">
                          <tr>
                            <td colspan="6" class="text-center">Loading...</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- College Table -->
                <div class="col-md-4">
                  <div style="background: white; padding: 15px; border-radius: 8px; height: 100%;">
                    <h5 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">College</h5>
                    <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                      <table class="table table-sm table-striped">
                        <thead style="position: sticky; top: 0; background: white;">
                          <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Education Level</th>
                            <th>Date Applied</th>
                            <th>Date of Resubmitting</th>
                            <th style="text-align: center;">Action</th>
                          </tr>
                        </thead>
                        <tbody id="collegeTableBody">
                          <tr>
                            <td colspan="6" class="text-center">Loading...</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Scholars from September to February -->
              <div style="background: white; padding: 20px; border-radius: 8px;">
                <h5 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Scholars from September to February</h5>
                <div style="overflow-x: auto;">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Education Level</th>
                      </tr>
                    </thead>
                    <tbody id="scholarsTableBody">
                      <tr>
                        <td colspan="3" class="text-center">Loading...</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Scholarship View Modal -->
    <div id="scholarshipViewModal" class="view-modal" style="display:none;">
      <div class="view-modal-content">
        <span class="close-btn">&times;</span>
        <div style="text-align: center;">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
            style="width: 70%; max-width: 120px; border-radius: 50%;" />
        </div>
        <h2>Scholarship Application Information</h2>
        <form class="modal-form">
          <div class="form-section">
            <div class="form-grid">
              <div class="form-group">
                <label>Application ID</label>
                <input type="text" id="scholarApplicationID" readonly>
              </div>
              <div class="form-group">
                <label>First Name</label>
                <input type="text" id="scholarFirstname" readonly>
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="scholarLastname" readonly>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="text" id="scholarEmail" readonly>
              </div>
              <div class="form-group">
                <label>Contact No</label>
                <input type="text" id="scholarContact" readonly>
              </div>
              <div class="form-group">
                <label>Address</label>
                <input type="text" id="scholarAddress" readonly>
              </div>
              <div class="form-group">
                <label>Education Level</label>
                <input type="text" id="scholarEducationLevel" readonly>
              </div>
              <div class="form-group">
                <label>Status</label>
                <input type="text" id="scholarStatus" readonly>
              </div>
              <div class="form-group">
                <label>Date Applied</label>
                <input type="text" id="scholarDateApplied" readonly>
              </div>
            </div>
          </div>
          <div class="form-section">
            <div class="form-group">
              <label>Reason for Application</label>
              <textarea id="scholarReason" rows="4" readonly style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; background-color:#f9f9f9;"></textarea>
              <!-- Button to view handwritten document if it exists -->
              <div id="reasonDocumentButton" style="margin-top: 10px; display: none;">
                <button type="button" class="btn btn-sm btn-warning" onclick="viewDocument('reason_file')" style="width: 100%; padding: 8px; background-color: #ffc107; color: #000; border: none; border-radius: 4px; cursor: pointer;">
                  <i class="fas fa-file-alt"></i> View Handwritten Reason Document
                </button>
              </div>
            </div>
          </div>

          <!-- Required Documents Section -->
          <div class="form-section">
            <h4 style="margin-bottom: 15px; color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Required Documents</h4>
            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
              <div class="form-group">
                <label>School ID</label>
                <button type="button" class="btn btn-sm btn-info" onclick="viewDocument('school_id')" style="width: 100%; padding: 8px; background-color: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
                  <i class="fas fa-eye"></i> View Document
                </button>
              </div>
              <div class="form-group">
                <label>Valid ID</label>
                <button type="button" class="btn btn-sm btn-info" onclick="viewDocument('barangay_id')" style="width: 100%; padding: 8px; background-color: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
                  <i class="fas fa-eye"></i> View Document
                </button>
              </div>
              <div class="form-group">
                <label>Certificate of Registration</label>
                <button type="button" class="btn btn-sm btn-info" onclick="viewDocument('cor')" style="width: 100%; padding: 8px; background-color: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
                  <i class="fas fa-eye"></i> View Document
                </button>
              </div>
              <div class="form-group">
                <label>Parents ID</label>
                <button type="button" class="btn btn-sm btn-info" onclick="viewDocument('parents_id')" style="width: 100%; padding: 8px; background-color: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
                  <i class="fas fa-eye"></i> View Document
                </button>
              </div>
              <div class="form-group">
                <label>Birth Certificate</label>
                <button type="button" class="btn btn-sm btn-info" onclick="viewDocument('birth_certificate')" style="width: 100%; padding: 8px; background-color: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer;">
                  <i class="fas fa-eye"></i> View Document
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Document Viewer Modal -->
    <div id="documentViewerModal" class="view-modal" style="display:none;">
      <div class="view-modal-content" style="max-width: 90%; max-height: 90vh; overflow: auto;">
        <span class="close-btn" onclick="closeDocumentViewer()">&times;</span>
        <h3 id="documentTitle" style="margin-bottom: 20px;">Document Viewer</h3>
        <div id="documentContainer" style="text-align: center; min-height: 400px;">
          <p>Loading document...</p>
        </div>
      </div>
    </div>

    <!-- Scholarship Print Modal -->
    <div class="modal fade" id="scholarshipPrintFormModal" tabindex="-1" aria-labelledby="scholarshipPrintFormModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content p-3">
          <form id="scholarshipPrintForm">
            <div class="mb-3">
              <div style="text-align: center;">
                <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h2>Scholarship Approval Form</h2>
              <label for="scholar_modal_id" class="form-label">Application ID</label>
              <input type="text" name="application_id" id="scholar_modal_id" readonly required class="form-control" />
            </div>

            <div class="mb-3">
              <label for="scholar_modal_name" class="form-label">Applicant Name</label>
              <input type="text" name="name" id="scholar_modal_name" readonly required class="form-control" />
            </div>

            <div class="mb-3">
              <label for="scholar_modal_address" class="form-label">Address</label>
              <input type="text" name="address" id="scholar_modal_address" readonly required class="form-control" />
            </div>

            <div class="mb-3">
              <label for="scholar_modal_date" class="form-label">Date Applied</label>
              <input type="text" name="date" id="scholar_modal_date" readonly required class="form-control" />
            </div>

            <div class="mb-3">
              <label for="scholar_modal_reason" class="form-label">Reason</label>
              <textarea name="reason" id="scholar_modal_reason" readonly required class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
              <label for="scholar_modal_amount" class="form-label">Scholarship Amount</label>
              <input type="number" step="1" class="form-control" id="scholar_modal_amount" name="amount"
                placeholder="Enter scholarship amount" required />
            </div>

            <div class="text-end">
              <button type="submit" class="btn btn-primary">Generate Scholarship Certificate</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Custom Confirm Modal for Approve -->
    <div id="customConfirm" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box">
        <p>Are you sure you want to approve this scholarship application?</p>
        <div class="custom-confirm-actions">
          <button id="customConfirmYes" class="custom-confirm-btn yes">Yes</button>
          <button id="customConfirmNo" class="custom-confirm-btn no">No</button>
        </div>
      </div>
    </div>

    <!-- Custom Confirm Modal for Decline -->
    <div id="customDeclineConfirm" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box">
        <p>Are you sure you want to reject this scholarship application?</p>
        <div class="custom-confirm-actions">
          <button id="customDeclineConfirmYes" class="custom-confirm-btn yes">Yes</button>
          <button id="customDeclineConfirmNo" class="custom-confirm-btn no">No</button>
        </div>
      </div>
    </div>

    <!-- Reason for Decline Modal -->
    <div id="declineReasonModal" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box">
        <p>Please enter the reason for rejecting:</p>
        <textarea id="declineReasonText" rows="3" style="width:100%; margin: 10px 0;"></textarea>
        <div class="custom-confirm-actions">
          <button id="declineReasonSubmit" class="custom-confirm-btn yes">Submit</button>
          <button id="declineReasonCancel" class="custom-confirm-btn no">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Approve to Examination Modal -->
    <div id="approveToExaminationModal" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box">
        <h3 style="margin-bottom: 15px; color: #2c5f2d;">Approve to Examination</h3>
        <p>Are you sure you want to move this scholarship application to <strong>"For Examination"</strong> status?</p>
        <p style="font-size: 0.9em; color: #666; margin-top: 10px;">The applicant will be notified and their application will proceed to the examination phase.</p>
        <div class="custom-confirm-actions">
          <button id="approveToExaminationYes" class="custom-confirm-btn yes">Yes, Proceed</button>
          <button id="approveToExaminationNo" class="custom-confirm-btn no">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Passed Examination - JHS/SHS Modal -->
    <div id="passedJHSSHSModal" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box" style="max-width: 450px;">
        <div style="text-align: center; margin-bottom: 15px;">
          <i class="fas fa-graduation-cap" style="font-size: 50px; color: #2c5f2d;"></i>
        </div>
        <h3 style="margin-bottom: 15px; color: #2c5f2d; text-align: center;">Examination Passed!</h3>
        <p style="text-align: center;">The applicant has <strong>PASSED</strong> the scholarship examination.</p>
        <div style="background: #f0f8f0; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #2c5f2d;">
          <p style="margin: 5px 0;"><strong>Education Level:</strong> <span id="passedEducationLevelJHSSHS"></span></p>
          <p style="margin: 5px 0;"><strong>Scholarship Grant:</strong> <span id="passedJHSSHSGrantAmount">₱1,000.00</span></p>
        </div>
        <p style="font-size: 0.9em; color: #666; text-align: center;">Confirm to approve this scholarship and set the grant amount.</p>
        <div class="custom-confirm-actions">
          <button id="passedJHSSHSYes" class="custom-confirm-btn yes">Confirm & Approve</button>
          <button id="passedJHSSHSNo" class="custom-confirm-btn no">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Passed Examination - College Modal -->
    <div id="passedCollegeModal" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box" style="max-width: 500px;">
        <div style="text-align: center; margin-bottom: 15px;">
          <i class="fas fa-graduation-cap" style="font-size: 50px; color: #2c5f2d;"></i>
        </div>
        <h3 style="margin-bottom: 15px; color: #2c5f2d; text-align: center;">Examination Passed!</h3>
        <p style="text-align: center;">The applicant has <strong>PASSED</strong> the scholarship examination.</p>
        <div style="background: #f0f8f0; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #2c5f2d;">
          <p style="margin: 5px 0 10px 0;"><strong>Education Level:</strong> College</p>
          <p style="margin: 10px 0 5px 0;"><strong>Select Scholarship Level:</strong></p>
          <div style="margin: 10px 0;">
            <label style="display: block; margin: 8px 0; padding: 10px; background: white; border: 2px solid #ddd; border-radius: 5px; cursor: pointer;">
              <input type="radio" name="collegeLevel" value="College A" id="collegeA" style="margin-right: 8px;">
              <strong>College A</strong> - ₱3,000.00
            </label>
            <label style="display: block; margin: 8px 0; padding: 10px; background: white; border: 2px solid #ddd; border-radius: 5px; cursor: pointer;">
              <input type="radio" name="collegeLevel" value="College B" id="collegeB" style="margin-right: 8px;">
              <strong>College B</strong> - ₱1,500.00
            </label>
          </div>

          <!-- Verification Document Upload -->
          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #d0e8d0;">
            <p style="margin: 5px 0 10px 0;"><strong>Upload Verification Document:</strong></p>
            <p style="font-size: 0.85em; color: #666; margin-bottom: 8px;">Upload a document to verify College A or B status (The result of the examination)</p>
            <input type="file" id="collegeVerificationDoc" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
              style="display: block; width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9em;">
            <p id="collegeDocFileName" style="font-size: 0.85em; color: #28a745; margin-top: 5px; display: none;">
              <i class="fas fa-file-check"></i> <span></span>
            </p>
            <p style="font-size: 0.8em; color: #999; margin-top: 5px;">Accepted formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</p>
          </div>
        </div>
        <p style="font-size: 0.9em; color: #666; text-align: center;">Select the scholarship level, upload verification document, and confirm to approve.</p>
        <div class="custom-confirm-actions">
          <button id="passedCollegeYes" class="custom-confirm-btn yes">Confirm & Approve</button>
          <button id="passedCollegeNo" class="custom-confirm-btn no">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Failed Examination Modal -->
    <div id="failedExaminationModal" class="custom-confirm-overlay" style="display:none;">
      <div class="custom-confirm-box" style="max-width: 450px;">
        <div style="text-align: center; margin-bottom: 15px;">
          <i class="fas fa-times-circle" style="font-size: 50px; color: #dc3545;"></i>
        </div>
        <h3 style="margin-bottom: 15px; color: #dc3545; text-align: center;">Examination Failed</h3>
        <p style="text-align: center;">Are you sure the applicant <strong>FAILED</strong> the scholarship examination?</p>
        <div style="background: #fff5f5; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #dc3545;">
          <p style="margin: 5px 0; color: #666;">This action will:</p>
          <ul style="margin: 10px 0; padding-left: 20px; color: #666;">
            <li>Update the application status to "Failed"</li>
            <li>The applicant will be notified of the result</li>
            <li>This action cannot be undone</li>
          </ul>
        </div>
        <div class="custom-confirm-actions">
          <button id="failedExaminationYes" class="custom-confirm-btn yes" style="background: #dc3545;">Confirm Failed</button>
          <button id="failedExaminationNo" class="custom-confirm-btn no">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Scholarship Application Modal -->
    <div id="scholarshipApplicationModal" class="view-modal" style="display:none;">
      <div class="view-modal-content" style="max-width: 600px;">
        <span class="close-btn" onclick="closeScholarshipModal()">&times;</span>
        <div style="text-align: center;">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
            style="width: 70%; max-width: 120px; border-radius: 50%;" />
        </div>
        <h2>Add New Scholarship Application</h2>

        <form id="scholarshipApplicationForm" class="modal-form" enctype="multipart/form-data">
          <input type="hidden" name="scholar_request" value="1">
          <div class="form-section">
            <div class="form-grid">
              <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="firstname" id="addFirstname" required>
              </div>
              <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="lastname" id="addLastname" required>
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" id="addEmail" required>
              </div>
              <div class="form-group">
                <label>Contact Number *</label>
                <input type="tel" name="contact_no" id="addContact" placeholder="09XX-XXX-XXXX" required>
              </div>
            </div>
          </div>

          <div class="form-section">
            <div class="form-group">
              <label>Complete Address *</label>
              <input type="text" name="address" id="addAddress" placeholder="House No., Street, Barangay, City" required>
            </div>
          </div>

          <div class="form-section">
            <h4 style="margin-bottom: 10px; color: #333;">Scholarship Details</h4>
            <div class="form-group">
              <label>Reason for Applying *</label>
              <div class="reason-options" style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div class="reason-option" style="flex: 1; text-align: center; padding: 10px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; background: white;" id="addTextOption">
                  <input type="radio" id="add_reason_type_text" name="reason_type" value="text" checked style="margin-right: 5px;">
                  <label for="add_reason_type_text" style="cursor: pointer; margin: 0;">Type Reason</label>
                </div>
                <div class="reason-option" style="flex: 1; text-align: center; padding: 10px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; background: white;" id="addFileOption">
                  <input type="radio" id="add_reason_type_file" name="reason_type" value="file" style="margin-right: 5px;">
                  <label for="add_reason_type_file" style="cursor: pointer; margin: 0;">Upload Handwritten Document</label>
                </div>
              </div>

              <div id="add_reason_text_area" class="reason-textarea" style="display: block;">
                <textarea name="reason" id="addReason" rows="4"
                  placeholder="Explain why you are applying for this scholarship..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                <small style="color: #666;">Type your reason for applying</small>
              </div>

              <div id="add_reason_file_area" class="reason-file" style="display: none;">
                <input type="file" name="reason_file" id="addReasonFile" accept=".jpg,.jpeg,.png,.gif,.pdf" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <small style="color: #666;">Upload a scanned copy or photo of your handwritten reason (JPG, PNG, GIF, PDF - Max: 5MB)</small>
              </div>
            </div>

            <div class="form-group">
              <label>Education Level *</label>
              <select name="education_level" id="addEducationLevel" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select Education Level</option>
                <option value="Junior High School">Junior High School</option>
                <option value="Senior High School">Senior High School</option>
                <option value="College">College</option>
              </select>
            </div>
          </div>

          <div class="form-section">
            <h4 style="margin-bottom: 15px; color: #333;">Required Documents</h4>
            <p style="font-size: 14px; color: #666; margin-bottom: 15px;">Please upload the following documents (Max file size: 5MB each):</p>
            <div class="form-grid">
              <div class="form-group">
                <label>School ID *</label>
                <input type="file" name="school_id" id="addSchoolId" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                <small style="color: #666;">JPG, PNG, GIF, PDF</small>
              </div>
              <div class="form-group">
                <label>Valid ID *</label>
                <input type="file" name="barangay_id" id="addBarangayId" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                <small style="color: #666;">JPG, PNG, GIF, PDF</small>
              </div>
              <div class="form-group">
                <label>Certificate of Registration *</label>
                <input type="file" name="cor" id="addCor" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                <small style="color: #666;">JPG, PNG, GIF, PDF</small>
              </div>
              <div class="form-group">
                <label>Parents ID *</label>
                <input type="file" name="parents_id" id="addParentsId" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                <small style="color: #666;">JPG, PNG, GIF, PDF</small>
              </div>
              <div class="form-group">
                <label>Birth Certificate *</label>
                <input type="file" name="birth_certificate" id="addBirthCert" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                <small style="color: #666;">JPG, PNG, GIF, PDF</small>
              </div>
            </div>
          </div>

          <div class="custom-confirm-actions">
            <button type="button" id="cancelApplication" class="custom-confirm-btn no">Cancel</button>
            <button type="submit" class="custom-confirm-btn yes">Submit Application</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Scholarship Grant Modal -->
    <div id="grantModal" class="view-modal" style="display:none;">
      <div class="view-modal-content" style="max-width:420px;">
        <span class="close-btn" onclick="closeGrantModal()">&times;</span>
        <h3>Set Scholarship Grant</h3>
        <form id="grantForm" class="modal-form">
          <input type="hidden" id="grant_application_id" />
          <div class="form-group">
            <label>Amount (PHP)</label>
            <select id="grant_amount" class="form-control" required>
              <option value="">-- Select amount --</option>
              <option value="3000">3,000</option>
              <option value="5000">5,000</option>
              <option value="10000">10,000</option>
            </select>
          </div>
          <div style="margin-top:12px; text-align:right;">
            <button type="button" class="custom-confirm-btn no" onclick="closeGrantModal()">Cancel</button>
            <button type="button" class="custom-confirm-btn yes" onclick="saveGrantAmount()">Save</button>
          </div>
        </form>
      </div>
    </div>

    <?php include 'alerts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      // Define functions globally so they're accessible to onclick handlers
      function showPanel(panelId) {
        const panels = document.querySelectorAll('.panel-content');
        panels.forEach(panel => panel.classList.remove('active'));
        const target = document.getElementById(panelId);
        if (target) target.classList.add('active');

        // Load reports data if switching to reports panel
        if (panelId === 'reportsPanel' && typeof loadReportsData === 'function') {
          loadReportsData();
        }
      }

      function confirmLogout(event) {
        event.preventDefault();
        const confirmed = confirm("Are you sure you want to logout?");
        if (confirmed) {
          window.location.href = "../Login/logout.php";
        }
      }

      // Initialize charts when DOM is ready
      document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing charts...');

        // Debug: Log the data
        console.log('Pending Count:', <?php echo isset($pendingScholarshipCount) ? $pendingScholarshipCount : 0; ?>);
        console.log('For Examination Count:', <?php echo isset($forExaminationCount) ? $forExaminationCount : 0; ?>);
        console.log('Approved Count:', <?php echo isset($approvedScholarshipCount) ? $approvedScholarshipCount : 0; ?>);

        // ===== Scholarship Status Distribution Pie Chart =====
        const statusData = {
          labels: ['Pending', 'For Examination', 'Approved'],
          datasets: [{
            data: [
              <?php echo isset($pendingScholarshipCount) ? $pendingScholarshipCount : 0; ?>,
              <?php echo isset($forExaminationCount) ? $forExaminationCount : 0; ?>,
              <?php echo isset($approvedScholarshipCount) ? $approvedScholarshipCount : 0; ?>
            ],
            backgroundColor: [
              '#FFC107', // Yellow for Pending
              '#388E3C', // Green for For Examination
              '#4CAF50' // Light green for Approved
            ],
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 8
          }]
        };

        const totalBudget = <?php echo isset($totalBudget) ? $totalBudget : 0; ?>;
        const totalStudents = <?php echo isset($totalApprovedStudents) ? $totalApprovedStudents : 0; ?>;

        // Plugin to display text in center of doughnut
        const centerTextPlugin = {
          id: 'centerText',
          afterDraw: function(chart) {
            if (chart.config.type === 'doughnut') {
              const ctx = chart.ctx;
              const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
              const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;

              ctx.save();
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';

              // Draw total number
              ctx.font = 'bold 28px Arial';
              ctx.fillStyle = '#2E7D32';
              ctx.fillText(totalStudents, centerX, centerY - 12);

              // Draw label
              ctx.font = '14px Arial';
              ctx.fillStyle = '#666';
              ctx.fillText('Total Applications', centerX, centerY + 15);

              ctx.restore();
            }
          }
        };

        Chart.register(centerTextPlugin);

        try {
          const scholarshipChartElement = document.getElementById('scholarshipChart');
          if (scholarshipChartElement) {
            new Chart(scholarshipChartElement, {
              type: 'pie',
              data: statusData,
              options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                  legend: {
                    position: 'right',
                    labels: {
                      padding: 15,
                      font: {
                        size: 14
                      },
                      generateLabels: function(chart) {
                        const data = chart.data;
                        if (data.labels.length && data.datasets.length) {
                          const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                          return data.labels.map((label, i) => {
                            const value = data.datasets[0].data[i];
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return {
                              text: label + ': ' + value + ' (' + percentage + '%)',
                              fillStyle: data.datasets[0].backgroundColor[i],
                              hidden: false,
                              index: i
                            };
                          });
                        }
                        return [];
                      }
                    }
                  },
                  tooltip: {
                    callbacks: {
                      label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return label + ': ' + value + ' applications (' + percentage + '%)';
                      }
                    }
                  }
                }
              }
            });
          } else {
            console.error('scholarshipChart element not found');
          }
        } catch (error) {
          console.error('Error creating pie chart:', error);
        }

        // ===== NEW: Monthly Applications Bar Chart with Budget Data =====
        const budgetMonthlyData = {
          labels: <?php echo !empty($budgetMonthlyLabels) ? json_encode($budgetMonthlyLabels) : '["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]'; ?>,
          datasets: [{
            label: 'Applications',
            data: <?php echo !empty($budgetMonthlyCounts) ? json_encode($budgetMonthlyCounts) : '[0,0,0,0,0,0,0,0,0,0,0,0]'; ?>,
            backgroundColor: [
              '#81C784', '#81C784', '#81C784', // Jan-Mar: Light green
              '#4CAF50', '#4CAF50', '#4CAF50', // Apr-Jun: Medium green
              '#2E7D32', '#2E7D32', '#2E7D32', // Jul-Sep: Dark green
              '#66BB6A', '#66BB6A', '#66BB6A' // Oct-Dec: Another shade
            ],
            borderColor: '#fff',
            borderWidth: 1,
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(46, 125, 50, 0.9)'
          }]
        };

        try {
          const monthlyChartElement = document.getElementById('monthlyChart');
          if (monthlyChartElement) {
            new Chart(monthlyChartElement, {
              type: 'bar',
              data: budgetMonthlyData,
              options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.8,
                scales: {
                  y: {
                    beginAtZero: true,
                    max: 20,
                    ticks: {
                      stepSize: 2,
                      font: {
                        size: 12
                      },
                      callback: function(value) {
                        return value;
                      }
                    },
                    grid: {
                      color: 'rgba(0, 0, 0, 0.08)',
                      drawBorder: false
                    },
                    border: {
                      display: false
                    }
                  },
                  x: {
                    ticks: {
                      font: {
                        size: 11,
                        weight: '500'
                      }
                    },
                    grid: {
                      display: false
                    },
                    border: {
                      display: false
                    }
                  }
                },
                plugins: {
                  legend: {
                    display: false
                  },
                  tooltip: {
                    backgroundColor: 'rgba(46, 125, 50, 0.9)',
                    padding: 12,
                    titleFont: {
                      size: 14,
                      weight: 'bold'
                    },
                    bodyFont: {
                      size: 13
                    },
                    callbacks: {
                      label: function(context) {
                        return 'Applications: ' + context.parsed.y;
                      }
                    }
                  }
                }
              },
              plugins: [{
                afterDatasetsDraw: function(chart) {
                  const ctx = chart.ctx;
                  chart.data.datasets.forEach(function(dataset, i) {
                    const meta = chart.getDatasetMeta(i);
                    if (!meta.hidden) {
                      meta.data.forEach(function(element, index) {
                        const data = dataset.data[index];
                        if (data > 0) {
                          ctx.fillStyle = '#2E7D32';
                          ctx.font = 'bold 12px Arial';
                          ctx.textAlign = 'center';
                          ctx.textBaseline = 'bottom';
                          ctx.fillText(data, element.x, element.y - 5);
                        }
                      });
                    }
                  });
                }
              }]
            });
          } else {
            console.error('monthlyChart element not found');
          }
        } catch (error) {
          console.error('Error creating bar chart:', error);
        }

        // Set active panel based on URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const panels = document.querySelectorAll(".panel-content");
        panels.forEach(p => p.classList.remove("active"));

        // Check if there are search parameters
        const hasSearchParams = urlParams.has("search_name") || urlParams.has("status_filter");

        if (hasSearchParams) {
          // Show scholarship panel if search parameters exist
          const scholarshipPanel = document.getElementById("scholarshipPanel");
          if (scholarshipPanel) {
            scholarshipPanel.classList.add("active");
          }
        } else {
          // Always show dashboard by default
          const dashboardPanel = document.getElementById("dashboardPanel");
          if (dashboardPanel) {
            dashboardPanel.classList.add("active");
          }

          // Clean URL - remove all query parameters on refresh
          if (window.location.search) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
          }
        }

        console.log('Charts and page initialization complete');
      }); // End DOMContentLoaded

      // Custom confirmation modals
      let approveUrl = null;
      let rejectApplicationId = null;

      function showCustomConfirm(event, url) {
        event.preventDefault();
        approveUrl = url;
        document.getElementById("customConfirm").style.display = "flex";
      }

      function showRejectReasonModal(applicationId, status) {
        // Check if the application is already approved
        if (status === 'Approved') {
          if (window.showAlert) {
            showAlert('warning', 'Cannot reject this application because it is already approved.');
          } else {
            alert('Cannot reject this application because it is already approved.');
          }
          return;
        }

        rejectApplicationId = applicationId;
        document.getElementById("declineReasonModal").style.display = "flex";
        document.getElementById("declineReasonText").value = "";
      }

      document.getElementById("customConfirmYes").addEventListener("click", function() {
        if (approveUrl) {
          window.location.href = approveUrl;
        }
      });

      document.getElementById("customConfirmNo").addEventListener("click", function() {
        document.getElementById("customConfirm").style.display = "none";
        approveUrl = null;
      });

      document.getElementById("customDeclineConfirmYes").addEventListener("click", function() {
        document.getElementById("customDeclineConfirm").style.display = "none";
        document.getElementById("declineReasonModal").style.display = "flex";
      });

      document.getElementById("customDeclineConfirmNo").addEventListener("click", function() {
        document.getElementById("customDeclineConfirm").style.display = "none";
        declineUrl = null;
      });

      document.getElementById("declineReasonCancel").addEventListener("click", function() {
        document.getElementById("declineReasonModal").style.display = "none";
        rejectApplicationId = null;
        document.getElementById("declineReasonText").value = "";
      });

      document.getElementById("declineReasonSubmit").addEventListener("click", function() {
        const reason = document.getElementById("declineReasonText").value.trim();
        if (!reason) {
          if (window.showAlert) {
            showAlert('warning', 'Please enter a reason for rejecting.');
          } else {
            alert("Please enter a reason for rejecting.");
          }
          return;
        }
        if (rejectApplicationId) {
          const url = `rejectscholarship.php?id=${rejectApplicationId}&reason=${encodeURIComponent(reason)}`;
          window.location.href = url;
        }
      });

      // Scholarship View Modal functionality
      let currentViewApplicationId = null;

      document.querySelectorAll('.action-btn-2.view').forEach(button => {
        button.addEventListener('click', function() {
          currentViewApplicationId = this.dataset.applicationid;

          document.getElementById('scholarApplicationID').value = this.dataset.applicationid;
          document.getElementById('scholarFirstname').value = this.dataset.firstname;
          document.getElementById('scholarLastname').value = this.dataset.lastname;
          document.getElementById('scholarEmail').value = this.dataset.email;
          document.getElementById('scholarContact').value = this.dataset.contact;
          document.getElementById('scholarAddress').value = this.dataset.address;

          const reason = this.dataset.reason;
          document.getElementById('scholarReason').value = reason;

          // Show/hide the handwritten document button based on whether it's a file upload
          const reasonDocButton = document.getElementById('reasonDocumentButton');
          if (reason === '[Handwritten document uploaded]') {
            reasonDocButton.style.display = 'block';
          } else {
            reasonDocButton.style.display = 'none';
          }

          document.getElementById('scholarEducationLevel').value = this.dataset.educationlevel || 'Not Specified';
          document.getElementById('scholarStatus').value = this.dataset.status;
          document.getElementById('scholarDateApplied').value = this.dataset.dateapplied;

          document.getElementById('scholarshipViewModal').style.display = 'flex';
        });
      });

      // Document viewing functions
      function viewDocument(documentType) {
        if (!currentViewApplicationId) {
          alert('Application ID not found');
          return;
        }

        const documentNames = {
          'school_id': 'School ID',
          'barangay_id': 'Valid ID',
          'cor': 'Certificate of Registration',
          'parents_id': 'Parents ID',
          'birth_certificate': 'Birth Certificate',
          'reason_file': 'Handwritten Reason Document'
        };

        document.getElementById('documentTitle').textContent = documentNames[documentType] || 'Document';
        document.getElementById('documentContainer').innerHTML = '<p style="padding: 20px;">Loading document...</p>';
        document.getElementById('documentViewerModal').style.display = 'flex';

        // Fetch and display the document
        fetch(`view_scholarship_document.php?id=${currentViewApplicationId}&type=${documentType}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              if (data.isPdf) {
                // Display PDF using iframe or embed
                document.getElementById('documentContainer').innerHTML =
                  `<embed src="${data.dataUrl}" type="application/pdf" width="100%" height="600px" style="border: 1px solid #ddd;" />`;
              } else {
                // Display image
                document.getElementById('documentContainer').innerHTML =
                  `<img src="${data.dataUrl}" alt="${documentNames[documentType]}" style="max-width: 100%; height: auto; border: 1px solid #ddd;" />`;
              }
            } else {
              document.getElementById('documentContainer').innerHTML =
                `<p style="color: red; padding: 20px;">${data.message || 'Failed to load document'}</p>`;
            }
          })
          .catch(error => {
            console.error('Error loading document:', error);
            document.getElementById('documentContainer').innerHTML =
              '<p style="color: red; padding: 20px;">Error loading document. Please try again.</p>';
          });
      }

      function closeDocumentViewer() {
        document.getElementById('documentViewerModal').style.display = 'none';
      }

      // Close document viewer when clicking outside
      window.addEventListener('click', function(e) {
        if (e.target == document.getElementById('documentViewerModal')) {
          closeDocumentViewer();
        }
      });

      // Close scholarship view modal
      document.querySelector('#scholarshipViewModal .close-btn').addEventListener('click', function() {
        document.getElementById('scholarshipViewModal').style.display = 'none';
      });

      window.addEventListener('click', function(e) {
        if (e.target == document.getElementById('scholarshipViewModal')) {
          document.getElementById('scholarshipViewModal').style.display = 'none';
        }
      });

      // Scholarship Print Modal functionality
      let selectedScholarData = null;
      let currentApproveToExaminationId = null;

      // Approve to Examination Modal functionality
      function showApproveToExaminationModal(applicationId) {
        currentApproveToExaminationId = applicationId;
        document.getElementById('approveToExaminationModal').style.display = 'flex';
      }

      document.getElementById('approveToExaminationYes').addEventListener('click', function() {
        if (currentApproveToExaminationId) {
          window.location.href = `approve_to_examination.php?id=${currentApproveToExaminationId}`;
        }
      });

      document.getElementById('approveToExaminationNo').addEventListener('click', function() {
        document.getElementById('approveToExaminationModal').style.display = 'none';
        currentApproveToExaminationId = null;
      });

      // Passed and Failed Examination Modal functionality
      let currentPassedApplicationId = null;
      let currentPassedEducationLevel = null;

      function showPassedModal(applicationId, educationLevel) {
        console.log('📋 showPassedModal called');
        console.log('Application ID:', applicationId);
        console.log('Education Level:', educationLevel);

        currentPassedApplicationId = applicationId;
        currentPassedEducationLevel = educationLevel;

        // Determine which modal to show based on education level
        if (educationLevel === 'Junior High School' || educationLevel === 'Senior High School') {
          console.log('✅ Showing JHS/SHS modal');
          // Show JHS/SHS modal with appropriate grant amount
          document.getElementById('passedEducationLevelJHSSHS').textContent = educationLevel;

          // Set grant amount based on level: JHS = 1000, SHS = 1200
          const grantAmount = educationLevel === 'Junior High School' ? 1000 : 1200;
          document.getElementById('passedJHSSHSGrantAmount').textContent = '₱' + grantAmount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });

          document.getElementById('passedJHSSHSModal').style.display = 'flex';
        } else if (educationLevel === 'College') {
          console.log('✅ Showing College modal');
          // Show College modal with A/B options
          document.getElementById('passedCollegeModal').style.display = 'flex';
        } else {
          console.error('❌ Unknown education level:', educationLevel);
          alert('Unknown education level. Please verify the application data.');
        }
      }

      // JHS/SHS Passed Modal handlers
      document.getElementById('passedJHSSHSYes').addEventListener('click', function() {
        if (currentPassedApplicationId && currentPassedEducationLevel) {
          // Set grant amount: JHS = 1000, SHS = 1200
          const grantAmount = currentPassedEducationLevel === 'Junior High School' ? 1000 : 1200;

          console.log('🚀 Submitting to process_passed_examination.php');
          console.log('Application ID:', currentPassedApplicationId);
          console.log('Education Level:', currentPassedEducationLevel);
          console.log('Grant Amount:', grantAmount);

          // Create form with hidden inputs
          const form = document.createElement('form');
          form.method = 'GET';
          form.action = 'process_passed_examination.php';

          // Add hidden inputs
          const idInput = document.createElement('input');
          idInput.type = 'hidden';
          idInput.name = 'id';
          idInput.value = currentPassedApplicationId;
          form.appendChild(idInput);

          const grantInput = document.createElement('input');
          grantInput.type = 'hidden';
          grantInput.name = 'grant';
          grantInput.value = grantAmount;
          form.appendChild(grantInput);

          const levelInput = document.createElement('input');
          levelInput.type = 'hidden';
          levelInput.name = 'level';
          levelInput.value = currentPassedEducationLevel;
          form.appendChild(levelInput);

          document.body.appendChild(form);
          form.submit();
        } else {
          console.error('❌ Missing data:', {
            currentPassedApplicationId,
            currentPassedEducationLevel
          });
        }
      });

      document.getElementById('passedJHSSHSNo').addEventListener('click', function() {
        document.getElementById('passedJHSSHSModal').style.display = 'none';
        currentPassedApplicationId = null;
        currentPassedEducationLevel = null;
      });

      // File upload preview for college verification
      document.getElementById('collegeVerificationDoc').addEventListener('change', function(e) {
        const fileNameDisplay = document.getElementById('collegeDocFileName');
        const fileNameSpan = fileNameDisplay.querySelector('span');

        if (this.files.length > 0) {
          const file = this.files[0];
          const fileSize = (file.size / (1024 * 1024)).toFixed(2); // Convert to MB

          // Validate file size (5MB max)
          if (file.size > 5 * 1024 * 1024) {
            if (window.showAlert) {
              showAlert('error', 'File size exceeds 5MB limit. Please choose a smaller file.');
            } else {
              alert('File size exceeds 5MB limit. Please choose a smaller file.');
            }
            this.value = ''; // Clear the file input
            fileNameDisplay.style.display = 'none';
            return;
          }

          fileNameSpan.textContent = `${file.name} (${fileSize} MB)`;
          fileNameDisplay.style.display = 'block';
        } else {
          fileNameDisplay.style.display = 'none';
        }
      });

      // College Passed Modal handlers
      document.getElementById('passedCollegeYes').addEventListener('click', function() {
        const selectedLevel = document.querySelector('input[name="collegeLevel"]:checked');
        const fileInput = document.getElementById('collegeVerificationDoc');

        if (!selectedLevel) {
          if (window.showAlert) {
            showAlert('warning', 'Please select College A or College B');
          } else {
            alert('Please select College A or College B');
          }
          return;
        }

        if (!fileInput.files.length) {
          if (window.showAlert) {
            showAlert('warning', 'Please upload a verification document');
          } else {
            alert('Please upload a verification document');
          }
          return;
        }

        if (currentPassedApplicationId) {
          const levelValue = selectedLevel.value; // "College A" or "College B"
          const grantAmount = levelValue === 'College A' ? 3000 : 1500;
          const file = fileInput.files[0];

          // Create FormData for file upload
          const formData = new FormData();
          formData.append('id', currentPassedApplicationId);
          formData.append('grant', grantAmount);
          formData.append('level', levelValue);
          formData.append('verification_doc', file);

          console.log('� Submitting College Passed with file upload:');
          console.log('Application ID:', currentPassedApplicationId);
          console.log('Education Level:', levelValue);
          console.log('Grant Amount:', grantAmount);
          console.log('File:', file.name);

          // Show loading state
          const confirmBtn = document.getElementById('passedCollegeYes');
          const originalText = confirmBtn.textContent;
          confirmBtn.disabled = true;
          confirmBtn.textContent = 'Uploading...';

          // Submit via AJAX
          fetch('process_passed_examination.php', {
              method: 'POST',
              body: formData
            })
            .then(response => response.json())
            .then(data => {
              confirmBtn.disabled = false;
              confirmBtn.textContent = originalText;

              if (data.success) {
                if (window.showAlert) {
                  showAlert('success', data.message || 'Application approved successfully!');
                } else {
                  alert(data.message || 'Application approved successfully!');
                }

                // Close modal and reload
                document.getElementById('passedCollegeModal').style.display = 'none';
                document.querySelectorAll('input[name="collegeLevel"]').forEach(radio => radio.checked = false);
                fileInput.value = '';
                document.getElementById('collegeDocFileName').style.display = 'none';
                currentPassedApplicationId = null;
                currentPassedEducationLevel = null;

                setTimeout(() => window.location.reload(), 1000);
              } else {
                if (window.showAlert) {
                  showAlert('error', data.message || 'Failed to process application');
                } else {
                  alert(data.message || 'Failed to process application');
                }
              }
            })
            .catch(error => {
              console.error('Error:', error);
              confirmBtn.disabled = false;
              confirmBtn.textContent = originalText;
              if (window.showAlert) {
                showAlert('error', 'An error occurred while processing the request');
              } else {
                alert('An error occurred while processing the request');
              }
            });
        } else {
          console.error('❌ Missing application ID');
        }
      });

      document.getElementById('passedCollegeNo').addEventListener('click', function() {
        document.getElementById('passedCollegeModal').style.display = 'none';
        // Clear radio selection and file input
        document.querySelectorAll('input[name="collegeLevel"]').forEach(radio => radio.checked = false);
        document.getElementById('collegeVerificationDoc').value = '';
        document.getElementById('collegeDocFileName').style.display = 'none';
        currentPassedApplicationId = null;
        currentPassedEducationLevel = null;
      });

      // Failed Examination Modal functionality
      let currentFailedApplicationId = null;

      function showFailedModal(applicationId) {
        currentFailedApplicationId = applicationId;
        document.getElementById('failedExaminationModal').style.display = 'flex';
      }

      document.getElementById('failedExaminationYes').addEventListener('click', function() {
        if (currentFailedApplicationId) {
          window.location.href = `process_failed_examination.php?id=${currentFailedApplicationId}`;
        }
      });

      document.getElementById('failedExaminationNo').addEventListener('click', function() {
        document.getElementById('failedExaminationModal').style.display = 'none';
        currentFailedApplicationId = null;
      });

      function openScholarshipPrintModal(data) {
        if (typeof data === "string") {
          data = JSON.parse(data);
        }
        selectedScholarData = data;

        document.getElementById("scholar_modal_id").value = data.ApplicationID;
        document.getElementById("scholar_modal_name").value = data.Firstname + " " + data.Lastname;
        document.getElementById("scholar_modal_address").value = data.Address;
        document.getElementById("scholar_modal_date").value = data.DateApplied;
        document.getElementById("scholar_modal_reason").value = data.Reason;

        const modal = new bootstrap.Modal(document.getElementById('scholarshipPrintFormModal'));
        modal.show();
      }

      // Grant modal handlers
      function openGrantModal(applicationId, currentAmount) {
        document.getElementById('grant_application_id').value = applicationId;
        // currentAmount might come empty or formatted (e.g. '₱3,000.00')
        const select = document.getElementById('grant_amount');
        const numeric = String(currentAmount || '').replace(/[^0-9]/g, '');
        if (numeric && (numeric === '3000' || numeric === '5000' || numeric === '10000')) {
          select.value = numeric;
        } else {
          select.value = '';
        }
        document.getElementById('grantModal').style.display = 'flex';
      }

      function closeGrantModal() {
        document.getElementById('grantModal').style.display = 'none';
        document.getElementById('grantForm').reset();
      }

      function saveGrantAmount() {
        const appId = document.getElementById('grant_application_id').value;
        const amount = document.getElementById('grant_amount').value;
        if (!appId || amount === '') {
          if (window.showAlert) showAlert('warning', 'Please select an amount.');
          else alert('Please select an amount.');
          return;
        }

        fetch('update_scholarship_grant.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `application_id=${encodeURIComponent(appId)}&amount=${encodeURIComponent(amount)}`
        }).then(r => r.json()).then(res => {
          if (res.status === 'success') {
            if (window.showAlert) showAlert('success', 'Scholarship grant saved.');
            else alert('Saved');
            closeGrantModal();
            // reload page to reflect updated grant
            setTimeout(() => window.location.reload(), 700);
          } else {
            if (window.showAlert) showAlert('error', res.message || 'Failed to save.');
            else alert(res.message || 'Failed to save.');
          }
        }).catch(err => {
          console.error(err);
          if (window.showAlert) showAlert('error', 'Failed to save grant.');
          else alert('Failed to save grant.');
        });
      }

      document.getElementById("scholarshipPrintForm").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const amount = document.getElementById("scholar_modal_amount").value;

        // Here you would typically send the data to a server script
        // For now, we'll just generate the certificate
        generateScholarshipCertificate(selectedScholarData, amount);

        // Close the modal
        bootstrap.Modal.getInstance(document.getElementById('scholarshipPrintFormModal')).hide();
      });

      function generateScholarshipCertificate(data, amount) {
        const today = new Date();
        const formattedDate = today.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });

        const printWindow = window.open('', '_blank', 'width=900,height=700');

        printWindow.document.write(`
          <html>
            <head>
              <title>Scholarship Certificate - ${data.Firstname} ${data.Lastname}</title>
              <style>
                body {
                  font-family: 'Times New Roman', serif;
                  margin: 40px;
                  line-height: 1.6;
                }
                .certificate-container {
                  border: 3px solid #4CAF50;
                  padding: 40px;
                  position: relative;
                  min-height: 600px;
                }
                .watermark {
                  position: absolute;
                  top: 50%;
                  left: 50%;
                  transform: translate(-50%, -50%);
                  opacity: 0.1;
                  z-index: 0;
                  width: 400px;
                }
                .header {
                  text-align: center;
                  margin-bottom: 30px;
                }
                .logos {
                  display: flex;
                  justify-content: center;
                  align-items: center;
                  gap: 40px;
                  margin-bottom: 20px;
                }
                .logos img {
                  width: 100px;
                  height: 100px;
                  object-fit: contain;
                }
                .content {
                  text-align: justify;
                  margin: 30px 0;
                  position: relative;
                  z-index: 1;
                }
                .footer {
                  margin-top: 60px;
                  text-align: right;
                }
                .signature-line {
                  border-top: 1px solid #000;
                  width: 200px;
                  margin-top: 60px;
                  display: inline-block;
                }
              </style>
            </head>
            <body>
              <div class="certificate-container">
                <img src="/Capstone/Assets/sampaguitalogo.png" class="watermark" alt="Watermark">

                <div class="header">
                  <div class="logos">
                    <img src="/Capstone/Assets/sampaguitalogo.png" alt="Sampaguita Logo">
                    <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo">
                  </div>
                  <h1>SANGGUNIANG KABATAAN SCHOLARSHIP CERTIFICATE</h1>
                  <h2>Barangay Sampaguita, San Pedro, Laguna</h2>
                  <p>Date Issued: ${formattedDate}</p>
                </div>

                <div class="content">
                  <p>This is to certify that <strong>${data.Firstname} ${data.Lastname}</strong>,
                  resident of ${data.Address}, has been approved for the Sangguniang Kabataan Scholarship Program.</p>

                  <p>The scholarship grant in the amount of <strong>₱${parseInt(amount).toLocaleString()}.00</strong>
                  has been awarded based on the applicant's qualifications and the evaluation committee's recommendation.</p>

                  <p>The purpose of this scholarship is to support the educational endeavors of deserving youth
                  in our community as stated in the application: "${data.Reason}"</p>

                  <p>This certificate serves as official documentation of the scholarship approval and
                  may be presented to educational institutions for verification purposes.</p>
                </div>

                <div class="footer">
                  <div class="signature-line"></div>
                  <p>HON. [SK CHAIRMAN NAME]</p>
                  <p>SK Chairman</p>
                  <p>Barangay Sampaguita</p>
                </div>
              </div>
            </body>
          </html>
        `);

        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        setTimeout(() => {
          printWindow.close();
        }, 1000);
      }

      function openScholarshipModal() {
        document.getElementById('scholarshipApplicationModal').style.display = 'flex';
        // Clear form
        document.getElementById('scholarshipApplicationForm').reset();
      }

      function closeScholarshipModal() {
        document.getElementById('scholarshipApplicationModal').style.display = 'none';
        document.getElementById('scholarshipApplicationForm').reset();
        // Reset reason type to text
        document.getElementById('add_reason_type_text').checked = true;
        document.getElementById('add_reason_text_area').style.display = 'block';
        document.getElementById('add_reason_file_area').style.display = 'none';
        document.getElementById('addTextOption').style.border = '2px solid #2c5f2d';
        document.getElementById('addTextOption').style.background = '#f0f8f0';
        document.getElementById('addFileOption').style.border = '2px solid #ddd';
        document.getElementById('addFileOption').style.background = 'white';
      }

      // Handle reason type toggle in Add modal
      document.getElementById('add_reason_type_text').addEventListener('change', function() {
        if (this.checked) {
          document.getElementById('add_reason_text_area').style.display = 'block';
          document.getElementById('add_reason_file_area').style.display = 'none';
          document.getElementById('addTextOption').style.border = '2px solid #2c5f2d';
          document.getElementById('addTextOption').style.background = '#f0f8f0';
          document.getElementById('addFileOption').style.border = '2px solid #ddd';
          document.getElementById('addFileOption').style.background = 'white';
        }
      });

      document.getElementById('add_reason_type_file').addEventListener('change', function() {
        if (this.checked) {
          document.getElementById('add_reason_text_area').style.display = 'none';
          document.getElementById('add_reason_file_area').style.display = 'block';
          document.getElementById('addFileOption').style.border = '2px solid #2c5f2d';
          document.getElementById('addFileOption').style.background = '#f0f8f0';
          document.getElementById('addTextOption').style.border = '2px solid #ddd';
          document.getElementById('addTextOption').style.background = 'white';
        }
      });

      // Make reason option divs clickable
      document.getElementById('addTextOption').addEventListener('click', function() {
        document.getElementById('add_reason_type_text').click();
      });

      document.getElementById('addFileOption').addEventListener('click', function() {
        document.getElementById('add_reason_type_file').click();
      });

      // Trigger initial state
      document.getElementById('addTextOption').style.border = '2px solid #2c5f2d';
      document.getElementById('addTextOption').style.background = '#f0f8f0';

      // Scholarship Application Form Handler
      document.getElementById('scholarshipApplicationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('=== Form submission triggered ===');

        // Get form data
        const formData = new FormData(this);
        const reasonType = formData.get('reason_type');
        const reason = formData.get('reason');
        const educationLevel = formData.get('education_level');

        console.log('Step 1: Got form data');
        console.log('Reason Type:', reasonType);
        console.log('Education Level:', educationLevel);

        console.log('Reason Type:', reasonType);
        console.log('Education Level:', educationLevel);

        // Validate education level
        if (!educationLevel || educationLevel === '') {
          console.log('❌ VALIDATION FAILED: Education level is empty');
          if (window.showAlert) {
            showAlert('warning', 'Please select an education level.');
          } else {
            alert('Please select an education level.');
          }
          return;
        }
        console.log('✅ Education level OK:', educationLevel);

        // Validate reason based on type
        if (reasonType === 'text') {
          // Validate that reason is not empty
          const trimmedReason = reason ? reason.trim() : '';

          console.log('📝 Text reason - Length:', trimmedReason.length);
          if (!trimmedReason || trimmedReason.length === 0) {
            console.log('❌ VALIDATION FAILED: Reason is empty');
            if (window.showAlert) {
              showAlert('warning', 'Please provide a reason for applying.');
            } else {
              alert('Please provide a reason for applying.');
            }
            return;
          }
          console.log('✅ Text reason validation PASSED');
        } else if (reasonType === 'file') {
          const reasonFile = formData.get('reason_file');
          console.log('📄 File reason - Size:', reasonFile ? reasonFile.size : 'NO FILE');
          if (!reasonFile || reasonFile.size === 0) {
            console.log('❌ VALIDATION FAILED: No file uploaded');
            if (window.showAlert) {
              showAlert('warning', 'Please upload a handwritten reason document.');
            } else {
              alert('Please upload a handwritten reason document.');
            }
            return;
          }
          // Validate file size (5MB max)
          if (reasonFile.size > 5242880) {
            console.log('❌ VALIDATION FAILED: File too large');
            if (window.showAlert) {
              showAlert('warning', 'Reason document must be smaller than 5MB.');
            } else {
              alert('Reason document must be smaller than 5MB.');
            }
            return;
          }
          console.log('✅ File reason validation PASSED');
        }

        // Validate contact number format
        const contactNo = formData.get('contact_no');
        console.log('📞 Contact number:', contactNo);
        if (!/^09[0-9]{9}$/.test(contactNo)) {
          console.log('❌ VALIDATION FAILED: Invalid format (must be 09XXXXXXXXX)');
          if (window.showAlert) {
            showAlert('warning', 'Contact number must be 11 digits starting with 09.');
          } else {
            alert('Contact number must be 11 digits starting with 09.');
          }
          return;
        }
        console.log('✅ Contact number validation PASSED');

        // Validate file uploads
        const requiredFiles = ['school_id', 'barangay_id', 'cor', 'parents_id', 'birth_certificate'];
        const fileErrors = [];
        console.log('📎 Validating required file uploads...');

        requiredFiles.forEach(fileName => {
          const file = formData.get(fileName);
          console.log(`  - ${fileName}:`, file ? `${file.size} bytes` : 'NOT PROVIDED');
          if (!file || file.size === 0) {
            fileErrors.push(`${fileName.replace(/_/g, ' ')} is required`);
          } else {
            // Validate file type (JPG, PNG, GIF, PDF)
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
              fileErrors.push(`${fileName.replace(/_/g, ' ')} must be JPG, PNG, GIF, or PDF format`);
            }

            // Validate file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
              fileErrors.push(`${fileName.replace(/_/g, ' ')} must be less than 5MB`);
            }
          }
        });

        if (fileErrors.length > 0) {
          console.log('❌ FILE VALIDATION FAILED:', fileErrors.length, 'errors');
          console.log(fileErrors);
          const msg = 'File validation errors:\n' + fileErrors.join('\n');
          if (window.showAlert) {
            showAlert('error', msg.replace(/\n/g, '<br>'), 8000);
          } else {
            alert('File validation errors:\n' + fileErrors.join('\n'));
          }
          return;
        }
        console.log('✅ All file uploads validated successfully');

        // Submit the form
        console.log('🚀 Submitting form to ../Process/process.php');
        fetch('../Process/process.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            console.log('📥 Response received:', response.status, response.statusText);
            console.log('Response headers:', response.headers.get('content-type'));
            return response.text().then(text => {
              console.log('📄 Raw response:', text);
              try {
                return JSON.parse(text);
              } catch (e) {
                console.error('❌ JSON parse error:', e);
                console.error('Response was not valid JSON:', text);
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
              }
            });
          })
          .then(data => {
            console.log('✅ Parsed data:', data);
            if (data.status === 'success') {
              if (window.showAlert) showAlert('success', 'Scholarship application submitted successfully!');
              else alert('Scholarship application submitted successfully!');
              closeScholarshipModal();
              // Refresh the page to show the new application
              window.location.reload();
            } else {
              if (window.showAlert) showAlert('error', 'Error: ' + (data.message || 'Unknown error'));
              else alert('Error: ' + data.message);
            }
          })
          .catch(error => {
            console.error('❌ Fetch Error:', error);
            if (window.showAlert) showAlert('error', 'An error occurred while submitting the application: ' + error.message);
            else alert('An error occurred while submitting the application: ' + error.message);
          });
      });

      // Cancel button handler
      document.getElementById('cancelApplication').addEventListener('click', function() {
        closeScholarshipModal();
      });

      // Close modal when clicking outside
      window.addEventListener('click', function(e) {
        if (e.target == document.getElementById('scholarshipApplicationModal')) {
          closeScholarshipModal();
        }
      });

      // ===== REPORTS DASHBOARD FUNCTIONS =====
      let budgetChart = null;

      // Load reports data when Reports tab is clicked
      function loadReportsData() {
        fetch('get_scholarship_reports.php')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update total budget
              document.getElementById('totalBudget').textContent = formatCurrency(data.totalBudget);

              // Render pie chart
              renderBudgetChart(data.budgetAllocation);

              // Populate tables
              populateTable('jhsTableBody', data.juniorHighSchool);
              populateTable('shsTableBody', data.seniorHighSchool);
              populateTable('collegeTableBody', data.college);
              populateScholarsTable('scholarsTableBody', data.septemberToFebruary);
            } else {
              console.error('Failed to load reports:', data.message);
            }
          })
          .catch(error => {
            console.error('Error fetching reports:', error);
          });
      }

      // Format currency
      function formatCurrency(amount) {
        return new Intl.NumberFormat('en-PH', {
          minimumFractionDigits: 0,
          maximumFractionDigits: 0
        }).format(amount);
      }

      // Render budget pie chart
      function renderBudgetChart(budgetData) {
        const ctx = document.getElementById('budgetPieChart');
        if (!ctx) return;

        // Destroy existing chart
        if (budgetChart) {
          budgetChart.destroy();
        }

        // Prepare data
        const labels = [];
        const amounts = [];
        const colors = {
          'Junior High School': '#81C784',
          'Senior High School': '#4CAF50',
          'College': '#2E7D32',
          'Administrative': '#C8E6C9'
        };
        const backgroundColors = [];

        budgetData.forEach(item => {
          labels.push(item.category + ': ₱' + formatCurrency(item.amount));
          amounts.push(item.amount);
          backgroundColors.push(colors[item.category] || '#999');
        });

        // Create chart
        budgetChart = new Chart(ctx, {
          type: 'pie',
          data: {
            labels: labels,
            datasets: [{
              data: amounts,
              backgroundColor: backgroundColors,
              borderColor: '#fff',
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
              legend: {
                position: 'right',
                labels: {
                  padding: 15,
                  font: {
                    size: 14
                  },
                  generateLabels: function(chart) {
                    const data = chart.data;
                    if (data.labels.length && data.datasets.length) {
                      return data.labels.map((label, i) => {
                        const value = data.datasets[0].data[i];
                        return {
                          text: label,
                          fillStyle: data.datasets[0].backgroundColor[i],
                          hidden: false,
                          index: i
                        };
                      });
                    }
                    return [];
                  }
                }
              },
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return context.label;
                  }
                }
              }
            }
          }
        });
      }

      // Populate education level tables
      function populateTable(tableId, data) {
        const tbody = document.getElementById(tableId);
        if (!tbody) return;

        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6" class="text-center">No records found</td></tr>';
          return;
        }

        let html = '';
        data.forEach(row => {
          const statusClass = getStatusClass(row.Status);
          const resubmitDate = row.DateOfResubmitting || '';
          html += `
            <tr>
              <td style="font-size: 12px;">${escapeHtml(row.Name)}</td>
              <td><span class="badge ${statusClass}" style="font-size: 10px;">${escapeHtml(row.Status)}</span></td>
              <td style="font-size: 11px;">${escapeHtml(row.EducationLevel)}</td>
              <td style="font-size: 11px;">${row.DateApplied}</td>
              <td style="font-size: 11px;">
                <input type="date"
                  class="form-control form-control-sm"
                  value="${resubmitDate}"
                  min="2025-09-01"
                  max="2026-02-28"
                  onchange="updateResubmitDate(${row.ApplicationID}, this.value)"
                  style="font-size: 10px; padding: 2px 4px;">
              </td>
              <td style="text-align: center;">
                ${resubmitDate ? `<button onclick="printKatunayanForm(${row.ApplicationID}, '${escapeHtml(row.Name)}', '${resubmitDate}')"
                  class="btn btn-sm btn-success no-print"
                  style="padding: 4px 8px; font-size: 11px;"
                  title="Print Katunayan Form">
                  <i class="fas fa-print"></i>
                </button>` : '<span style="color: #999; font-size: 10px;">No date set</span>'}
              </td>
            </tr>
          `;
        });
        tbody.innerHTML = html;
      }

      // Update resubmit date
      function updateResubmitDate(applicationId, newDate) {
        if (!newDate) return;

        fetch('update_resubmit_date.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              applicationId: applicationId,
              resubmitDate: newDate
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              if (window.showAlert) {
                showAlert('success', 'Resubmit date updated successfully');
              }
            } else {
              if (window.showAlert) {
                showAlert('error', data.message || 'Failed to update date');
              }
              // Reload data to revert the change
              loadReportsData();
            }
          })
          .catch(error => {
            console.error('Error updating date:', error);
            if (window.showAlert) {
              showAlert('error', 'Error updating resubmit date');
            }
          });
      }

      // Populate scholars table (September to February)
      function populateScholarsTable(tableId, data) {
        const tbody = document.getElementById(tableId);
        if (!tbody) return;

        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="3" class="text-center">No scholars found for September to February</td></tr>';
          return;
        }

        let html = '';
        data.forEach(row => {
          const statusClass = getStatusClass(row.Status);
          html += `
            <tr>
              <td>${escapeHtml(row.Name)}</td>
              <td><span class="badge ${statusClass}">${escapeHtml(row.Status)}</span></td>
              <td>${escapeHtml(row.EducationLevel)}</td>
            </tr>
          `;
        });
        tbody.innerHTML = html;
      }

      // Get status badge class
      function getStatusClass(status) {
        switch (status) {
          case 'Pending':
            return 'status-pending';
          case 'For Examination':
            return 'status-for-examination';
          case 'Approved':
            return 'status-final-approved';
          case 'Active':
            return 'status-final-approved';
          case 'Rejected':
            return 'status-rejected';
          case 'Inactive':
            return 'bg-secondary';
          default:
            return 'bg-secondary';
        }
      }

      // Escape HTML to prevent XSS
      function escapeHtml(text) {
        const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
      }

      // Override showPanel to load reports when Reports tab is shown
      const originalShowPanel = window.showPanel;
      window.showPanel = function(panelId) {
        originalShowPanel(panelId);

        if (panelId === 'reportsPanel') {
          loadReportsData();
        }
      };

      // ===== PRINT REPORT FUNCTION =====
      function printReport() {
        // Add print date to report container
        const reportContainer = document.getElementById('reportContainer');
        const currentDate = new Date().toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
        reportContainer.setAttribute('data-print-date', currentDate);

        // Trigger print
        window.print();
      }

      // ===== PRINT KATUNAYAN FORM =====
      function printKatunayanForm(applicationId, applicantName, resubmitDate) {
        // Open new window for printing
        const printWindow = window.open('', '_blank', 'width=800,height=600');

        // Parse the resubmit date (format: YYYY-MM-DD)
        const [year, month, day] = resubmitDate.split('-').map(num => parseInt(num));
        const resubmitDateObj = new Date(year, month - 1, day);
        const formattedDate = resubmitDateObj.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });

        // Get current date
        const today = new Date();
        const currentDate = today.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });

        // Create the Katunayan form HTML
        const htmlContent = `
          <!DOCTYPE html>
          <html>
          <head>
            <title>Katunayan - ${applicantName}</title>
            <style>
              body {
                font-family: Arial, sans-serif;
                padding: 40px;
                line-height: 1.8;
              }
              .header {
                text-align: center;
                margin-bottom: 30px;
              }
              .header h1 {
                margin: 10px 0;
                font-size: 24px;
              }
              .header h2 {
                margin: 5px 0;
                font-size: 18px;
                font-weight: normal;
              }
              .content {
                margin: 40px 0;
                text-align: justify;
              }
              .signature {
                margin-top: 60px;
                text-align: right;
              }
              .signature-line {
                display: inline-block;
                width: 250px;
                border-top: 1px solid #000;
                margin-top: 50px;
              }
              @media print {
                body { padding: 20px; }
                button { display: none; }
              }
            </style>
          </head>
          <body>
            <div class="header">
              <h2>Republic of the Philippines</h2>
              <h2>Province of Laguna</h2>
              <h2>Barangay Sampaguita</h2>
              <h1>BARANGAY SANGGUNIANG KABATAAN</h1>
              <h2>Office of the SK Chairman</h2>
            </div>

            <div class="content">
              <h2 style="text-align: center; margin: 30px 0;">KATUNAYAN</h2>

              <p style="text-indent: 50px;">
                Ito ay nagpapatunay na si <strong>${applicantName}</strong> ay nag-apply para sa
                Sangguniang Kabataan Scholarship Program at nakatanggap ng pahintulot na
                muling magsumite ng kumpletong requirements.
              </p>

              <p style="text-indent: 50px;">
                Ang aplikante ay inaasahang magsusumite ng lahat ng kinakailangang dokumento
                sa o bago ang <strong>${formattedDate}</strong>.
              </p>

              <p style="text-indent: 50px;">
                Ang katunayang ito ay inisyu ngayong <strong>${currentDate}</strong> para sa
                anumang legal na layunin na maaaring kailanganin.
              </p>
            </div>

            <div class="signature">
              <div>
                <div class="signature-line"></div>
                <p style="margin: 5px 0;"><strong>SK Chairman</strong></p>
                <p style="margin: 0; font-size: 14px;">Barangay Sangguniang Kabataan</p>
              </div>
            </div>

            <button onclick="window.print()" style="margin-top: 30px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
              Print This Form
            </button>
          </body>
          </html>
        `;

        printWindow.document.write(htmlContent);
        printWindow.document.close();
      }

      // ===== IMPROVED ALERT SYSTEM =====
      let alertShown = {}; // Track shown alerts to prevent duplicates

      window.showCustomAlert = function(type, title, message, duration = 5000) {
        // Create unique key for this alert
        const alertKey = `${type}-${message}`;

        // Prevent duplicate alerts
        if (alertShown[alertKey]) {
          return;
        }
        alertShown[alertKey] = true;

        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert ${type}`;

        // Icon based on type
        let icon = '';
        switch (type) {
          case 'success':
            icon = '<i class="fas fa-check-circle custom-alert-icon"></i>';
            break;
          case 'error':
            icon = '<i class="fas fa-times-circle custom-alert-icon"></i>';
            break;
          case 'warning':
            icon = '<i class="fas fa-exclamation-triangle custom-alert-icon"></i>';
            break;
          case 'info':
            icon = '<i class="fas fa-info-circle custom-alert-icon"></i>';
            break;
        }

        alertDiv.innerHTML = `
          ${icon}
          <div class="custom-alert-content">
            <div class="custom-alert-title">${title}</div>
            <div class="custom-alert-message">${message}</div>
          </div>
          <button class="custom-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
          </button>
        `;

        document.body.appendChild(alertDiv);

        // Auto-dismiss after duration
        setTimeout(() => {
          if (alertDiv.parentElement) {
            alertDiv.classList.add('hiding');
            setTimeout(() => {
              if (alertDiv.parentElement) {
                alertDiv.remove();
              }
              // Clear the alert key after it's dismissed
              delete alertShown[alertKey];
            }, 300);
          }
        }, duration);
      };

      // Override existing showAlert for compatibility - always use custom alerts
      window.showAlert = function(type, message, duration = 5000) {
        const titles = {
          'success': 'Success!',
          'error': 'Error!',
          'warning': 'Warning!',
          'info': 'Information'
        };
        showCustomAlert(type, titles[type] || 'Notice', message, duration);
      };

      // Wrap form submissions to show one-time alerts
      document.addEventListener('DOMContentLoaded', function() {
        // Intercept all form submissions
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
          form.addEventListener('submit', function(e) {
            // Clear alert tracking for new submission
            alertShown = {};
          });
        });
      });
    </script>
  </div>
</body>

</html>
