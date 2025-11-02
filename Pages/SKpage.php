<?php include 'dashboard.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SK Scholarship Management - Barangay Sampaguita</title>
  <link rel="stylesheet" href="../Styles/admin.css">
  <link rel="stylesheet" href="../Styles/Alerts.css">
  <style>
    /* SKpage-specific override: force all alerts to green */
    #global-alerts .global-alert,
    #global-alerts .global-alert.success,
    #global-alerts .global-alert.error,
    #global-alerts .global-alert.warning,
    #global-alerts .global-alert.info {
      background: linear-gradient(90deg,#4CAF50,#2E7D32) !important;
      color: #fff !important;
      box-shadow: 0 6px 18px rgba(0,0,0,0.12) !important;
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
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
    }
    .status-pending { background-color: #A5D6A7; color: #000; }
    .status-approved { background-color: #FFC107; color: #000; }
    .status-final-approved { background-color: #4CAF50; color: #fff; }
    .status-rejected { background-color: #2E7D32; color: #fff; }
    
    .action-btn-2 {
      padding: 6px 10px;
      margin: 2px;
      border-radius: 4px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      min-width: 30px;
      height: 30px;
    }
    .action-btn-2.view { background-color: #17a2b8; color: white; }
    .action-btn-2.approve { background-color: #28a745; color: white; }
    .action-btn-2.decline { background-color: #dc3545; color: white; }
    .action-btn-2.print { background-color: #6c757d; color: white; }
    .action-btn-2:hover { opacity: 0.8; transform: scale(1.05); }
    
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
    .govdoc-search-button, .add-user {
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
    .govdoc-search-button { background-color: #6c757d; color: white; }
    .add-user { background-color: #17a2b8; color: white; }
    
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
      background: rgba(0,0,0,0.5);
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
    .custom-confirm-btn.yes { background: #28a745; color: white; }
    .custom-confirm-btn.no { background: #dc3545; color: white; }

    /* View Modal Styles */
    .view-modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
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
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
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
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-12 col-md-2 sidebar">
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
          style="width: 100%; max-width: 160px; border-radius: 50%;" />
        <button class="sidebar-btn" onclick="showPanel('dashboardPanel')">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </button>
        
        <button class="sidebar-btn" onclick="showPanel('scholarshipPanel')">
          <i class="fas fa-graduation-cap"></i> Scholarship Applications
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

            <div class="chart-container">
              <div class="boxes">
                <h4>Scholarship Status Distribution</h4>
                <canvas id="scholarshipChart"></canvas>
              </div>
              <div class="genderbox">
                <h4>Applications by Month (<?php echo date('Y'); ?>)</h4>
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
              $scholarshipSql = "SELECT ApplicationID, UserID, Firstname, Lastname, Email, ContactNo, Address, Reason, RequestStatus, DateApplied, ScholarshipGrant 
                   FROM scholarship 
                   WHERE 1=1 $searchQuery
                   ORDER BY DateApplied DESC";
            } else {
              $scholarshipSql = "SELECT ApplicationID, UserID, Firstname, Lastname, Email, ContactNo, Address, Reason, RequestStatus, DateApplied 
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
                        $statusClass = "status-approved";
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
                          "Reason" => $row['Reason']
                        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                        
                        echo "<button type='button' class='action-btn-2 print' onclick='openScholarshipPrintModal(JSON.parse(`$scholarData`))'>
                                <i class='fas fa-print'></i>
                              </button>";
                      } elseif ($row["RequestStatus"] === "For Examination") {
                        // Show APPROVE button for For Examination status to move to final Approved
                        echo "<a href='finalapprovescholarship.php?id={$row["ApplicationID"]}' 
                               class='action-btn-2 approve'
                               onclick=\"return showCustomConfirm(event, this.href);\"
                               title='Final Approve Application'>
                               <i class='fas fa-check'></i>
                            </a>";
                      } else {
                        // Show APPROVE button for Pending status to move to For Examination
                        echo "<a href='approvescholarship.php?id={$row["ApplicationID"]}' 
                               class='action-btn-2 approve'
                               onclick=\"return showCustomConfirm(event, this.href);\"
                               title='Approve Application'>
                               <i class='fas fa-check'></i>
                            </a>";
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
                              data-status='{$row["RequestStatus"]}'
                              data-dateapplied='{$dateApplied}'
                              title='View Application'>
                              <i class='fas fa-eye'></i>
                            </button>
                            <button type='button' 
                               class='action-btn-2 decline'
                               data-status='{$row["RequestStatus"]}'
                               onclick=\"showRejectReasonModal({$row["ApplicationID"]}, '{$row["RequestStatus"]}');\"
                               title='Reject Application'>
                               <i class='fas fa-xmark'></i>
                            </button>
                          </div>
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
            </div>
          </div>
        </form>
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
            <div class="form-group">
              <label>Reason for Applying *</label>
              <textarea name="reason" id="addReason" rows="4" 
                placeholder="Explain why you're applying for this scholarship (minimum 50 words)" required></textarea>
            </div>
          </div>
          
          <div class="form-section">
            <h4 style="margin-bottom: 15px; color: #333;">Required Documents (JPEG/PNG only)</h4>
            <div class="form-grid">
              <div class="form-group">
                <label>School ID *</label>
                <input type="file" name="school_id" id="addSchoolId" accept="image/jpeg,image/png" required>
                <small style="color: #666;">Upload your school ID (JPEG/PNG)</small>
              </div>
              <div class="form-group">
                <label>Barangay ID *</label>
                <input type="file" name="barangay_id" id="addBarangayId" accept="image/jpeg,image/png" required>
                <small style="color: #666;">Upload your barangay ID (JPEG/PNG)</small>
              </div>
              <div class="form-group">
                <label>Certificate of Registration *</label>
                <input type="file" name="cor" id="addCor" accept="image/jpeg,image/png" required>
                <small style="color: #666;">Upload COR from school (JPEG/PNG)</small>
              </div>
              <div class="form-group">
                <label>Parents ID *</label>
                <input type="file" name="parents_id" id="addParentsId" accept="image/jpeg,image/png" required>
                <small style="color: #666;">Upload parent's ID (JPEG/PNG)</small>
              </div>
              <div class="form-group">
                <label>Birth Certificate *</label>
                <input type="file" name="birth_certificate" id="addBirthCert" accept="image/jpeg,image/png" required>
                <small style="color: #666;">Upload birth certificate (JPEG/PNG)</small>
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
      // Scholarship Status Chart - Different colors for each status
      const statusData = {
        labels: ['Pending', 'For Examination', 'Approved'],
        datasets: [{
          data: [
            <?php echo $pendingScholarshipCount; ?>,
            <?php echo $forExaminationCount; ?>,
            <?php echo $approvedScholarshipCount; ?>
          ],
          backgroundColor: [
            '#A5D6A7', // Light green for Pending
            '#FFC107', // Yellow for For Examination
            '#4CAF50'  // Green for Approved
          ],
          borderColor: [
            '#81C784',
            '#FF8F00',
            '#388E3C'
          ],
          borderWidth: 2
        }]
      };

      new Chart(document.getElementById('scholarshipChart'), {
        type: 'doughnut',
        data: statusData,
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });

      // Monthly Applications Chart
      const monthlyData = {
        labels: <?php echo json_encode($monthlyLabels); ?>,
        datasets: [{
          label: 'Applications',
          data: <?php echo json_encode($monthlyCounts); ?>,
          backgroundColor: '#4CAF50',
          borderColor: '#2E7D32',
          borderWidth: 2
        }]
      };

      new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: monthlyData,
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });

      function showPanel(panelId) {
        const panels = document.querySelectorAll('.panel-content');
        panels.forEach(panel => panel.classList.remove('active'));
        const target = document.getElementById(panelId);
        if (target) target.classList.add('active');
      }

      function confirmLogout(event) {
        event.preventDefault();
        const confirmed = confirm("Are you sure you want to logout?");
        if (confirmed) {
          window.location.href = "../Login/logout.php";
        }
      }

      // Set active panel based on URL parameters
      window.addEventListener("DOMContentLoaded", function () {
        const urlParams = new URLSearchParams(window.location.search);
        const panels = document.querySelectorAll(".panel-content");
        panels.forEach(p => p.classList.remove("active"));

        if (urlParams.has("search_name") || urlParams.has("status_filter")) {
          const scholarshipPanel = document.getElementById("scholarshipPanel");
          if (scholarshipPanel) {
            scholarshipPanel.classList.add("active");
            return;
          }
        } else {
          const dashboardPanel = document.getElementById("dashboardPanel");
          if (dashboardPanel) {
            dashboardPanel.classList.add("active");
            return;
          }
        }
      });

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
      document.querySelectorAll('.action-btn-2.view').forEach(button => {
        button.addEventListener('click', function() {
          document.getElementById('scholarApplicationID').value = this.dataset.applicationid;
          document.getElementById('scholarFirstname').value = this.dataset.firstname;
          document.getElementById('scholarLastname').value = this.dataset.lastname;
          document.getElementById('scholarEmail').value = this.dataset.email;
          document.getElementById('scholarContact').value = this.dataset.contact;
          document.getElementById('scholarAddress').value = this.dataset.address;
          document.getElementById('scholarReason').value = this.dataset.reason;
          document.getElementById('scholarStatus').value = this.dataset.status;
          document.getElementById('scholarDateApplied').value = this.dataset.dateapplied;

          document.getElementById('scholarshipViewModal').style.display = 'flex';
        });
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
          if (window.showAlert) showAlert('warning', 'Please select an amount.'); else alert('Please select an amount.');
          return;
        }

        fetch('update_scholarship_grant.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `application_id=${encodeURIComponent(appId)}&amount=${encodeURIComponent(amount)}`
        }).then(r => r.json()).then(res => {
          if (res.status === 'success') {
            if (window.showAlert) showAlert('success', 'Scholarship grant saved.'); else alert('Saved');
            closeGrantModal();
            // reload page to reflect updated grant
            setTimeout(() => window.location.reload(), 700);
          } else {
            if (window.showAlert) showAlert('error', res.message || 'Failed to save.'); else alert(res.message || 'Failed to save.');
          }
        }).catch(err => {
          console.error(err);
          if (window.showAlert) showAlert('error', 'Failed to save grant.'); else alert('Failed to save grant.');
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
          year: 'numeric', month: 'long', day: 'numeric'
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
      }

      // Scholarship Application Form Handler
      document.getElementById('scholarshipApplicationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        const reason = formData.get('reason');
        
        // Validate reason word count (minimum 50 words)
        if (reason.trim().split(/\s+/).length < 50) {
          if (window.showAlert) {
            showAlert('warning', 'Reason must be at least 50 words.');
          } else {
            alert('Reason must be at least 50 words.');
          }
          return;
        }
        
        // Validate contact number format
        const contactNo = formData.get('contact_no');
        if (!/^09[0-9]{9}$/.test(contactNo)) {
          if (window.showAlert) {
            showAlert('warning', 'Contact number must be 11 digits starting with 09.');
          } else {
            alert('Contact number must be 11 digits starting with 09.');
          }
          return;
        }
        
        // Validate file uploads
        const requiredFiles = ['school_id', 'barangay_id', 'cor', 'parents_id', 'birth_certificate'];
        const fileErrors = [];
        
        requiredFiles.forEach(fileName => {
          const file = formData.get(fileName);
          if (!file || file.size === 0) {
            fileErrors.push(`${fileName.replace('_', ' ')} is required`);
          } else {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
              fileErrors.push(`${fileName.replace('_', ' ')} must be JPEG or PNG format`);
            }
            
            // Validate file size (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
              fileErrors.push(`${fileName.replace('_', ' ')} must be less than 5MB`);
            }
          }
        });
        
        if (fileErrors.length > 0) {
          const msg = 'File validation errors:\n' + fileErrors.join('\n');
          if (window.showAlert) {
            showAlert('error', msg.replace(/\n/g, '<br>'), 8000);
          } else {
            alert('File validation errors:\n' + fileErrors.join('\n'));
          }
          return;
        }
        
        // Submit the form
        fetch('../Process/process.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
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
          console.error('Error:', error);
          if (window.showAlert) showAlert('error', 'An error occurred while submitting the application.');
          else alert('An error occurred while submitting the application.');
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
    </script>
  </div>
</body>
</html>