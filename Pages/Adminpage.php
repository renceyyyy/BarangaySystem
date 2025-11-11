<?php
session_start(); // ✅ Always first — before any HTML or includes

// Security check — only finance users allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Login/login.php");
    exit();
}

// Debug logs — helps you confirm session data
error_log("AdminPage.php — Username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("AdminPage.php — Fullname: " . ($_SESSION['fullname'] ?? 'NOT SET'));
error_log("AdminPage.php — Role: " . ($_SESSION['role'] ?? 'NOT SET'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Barangay Sampaguita</title>
  <link rel="stylesheet" href="../Styles/admin.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
  <link rel="stylesheet" href="../Styles/admin.css">
</head>

<body>
  <?php
include 'dashboard.php';
require_once '../Process/db_connection.php';
?>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-12 col-md-2 sidebar">
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
          style="width: 100%; max-width: 160px; border-radius: 50%;" />
        <button class="sidebar-btn" onclick="showPanel('dashboardPanel')">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </button>
        <button class="sidebar-btn" onclick="showPanel('residencePanel')">
          <i class="fas fa-users"></i> Residence
        </button>
        <div class="dropdown w-100">
          <button class="sidebar-btn w-100" onclick="toggleDropdown(event)">
            <i class="fas fa-book"></i> Document Request <i class="fas fa-caret-down ms-auto"></i>
          </button>

          <div id="dropdownMenu" class="dropdown-content-custom">
            <a href="#" onclick="showPanel('governmentDocumentPanel')">Government Document</a>
            <a href="#" onclick="showPanel('businessPermitPanel')">Business Permit</a>
            <a href="#" onclick="showPanel('businessUnemploymentCertificatePanel')">Unemployment Certificate Request</a>
            <a href="#" onclick="showPanel('guardianshipPanel')">Guardianship</a>
            <!-- <a href="#" onclick="showPanel('nobirthCertPanel')">No Birth Certificate</a> -->
      
          </div>
        </div>

        <button class="sidebar-btn" onclick="showPanel('itemrequestsPanel')">
          <i class="fas fa-box-open"></i> Item Request
        </button>

        <!-- <button class="sidebar-btn" onclick="showPanel('blotterComplaintPanel')">
          <i class="fas fa-exclamation-triangle"></i> Blotter/Complaint
        </button> -->

        <div class="dropdown w-100">
          <button class="sidebar-btn w-100" onclick="toggleDropdown(event, 'blotterDropdownMenu')">
            <i class="fas fa-exclamation-triangle"></i> Blotter <i class="fas fa-caret-down ms-auto"></i>
          </button>
          <div id="blotterDropdownMenu" class="dropdown-content-custom">
            <a href="#" onclick="showPanel('blotterComplaintPanel')">Blotter/Complaint</a>
            <a href="#" onclick="showPanel('blotteredIndividualsPanel')">Blottered Individuals</a>
          </div>
        </div>



        <button class="sidebar-btn" onclick="showPanel('reportsPanel')">
          <i class="fas fa-file-alt"></i> Reports
        </button>
        <button class="sidebar-btn" onclick="showPanel('auditTrailPanel')">
          <i class="fas fa-history"></i> Activity Logs
        </button>
        <button class="sidebar-btn" onclick="showPanel('announcementPanel')">
          <i class="fas fa-newspaper"></i> Announcement
        </button>
        <a href="#" class="logout-link mt-auto" onclick="openLogoutModal(event)">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>

        <!-- Modal -->
        <div id="logoutModal" class="modal-overlay">
          <div class="modal-box">
            <p>Are you sure you want to logout?</p>
            <div class="modal-actions">
              <button class="btn-yes" onclick="confirmLogout()">Yes</button>
              <button class="btn-no" onclick="closeLogoutModal()">No</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-12 col-md-10 p-0">
        <div class="main-content-scroll p-3">
          <div class="admin-header">

          </div>

          <!-- Panels -->
          <div id="dashboardPanel" class="panel-content ">

            <h1>Dashboard</h1>
            <div class="stat-card-container mb-3">

              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-regular fa-user"></i></div>
                  <p>REGISTER</p>
                </div>
                <h4><?php echo $RegisterCount; ?></h4>
              </div>

              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                  <p>SCHOLARSHIP</p>
                </div>
                <h4><?php echo $scholarshipCount; ?></h4>
              </div>
              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                  <p>BLOTTER</p>
                </div>
                <h4><?php echo $BlotterCount; ?></h4>
              </div>

              <?php include 'collectiontotal.php'; ?>
              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
                  <p>COLLECTION</p>
                </div>
                <h4>₱<?php echo number_format($totalApproved); ?></h4>
              </div>


            </div>

            <div class="chart-container">
              <div class="boxes">
                <h4>Age Distribution</h4>
                <canvas id="ageChart"></canvas>
              </div>
              <div class="genderbox">
                <h4>Gender Distribution</h4>
                <canvas id="genderChart"></canvas>
              </div>
              <div class="business-box">
                <h4>Business Permit Requests</h4>
                <canvas id="businessChart"></canvas>
              </div>
               <div class="document-box">
                <h4>Government Document Requests</h4>
                <canvas id="documentChart"></canvas>
              </div>
              
              <div class="unemployment-box">
                <h4>Unemployment Certificate Requests</h4>
                <canvas id="unemploymentChart"></canvas>
              </div>
            </div>
          </div>

         <div id="residencePanel" class="panel-content">
  <h1>Residence Information</h1>

  <!-- Tab Navigation -->
  <div class="tabs-container">
    <button class="tab-btn active" onclick="switchTab(event, 'unverified')">
      Unverified
    </button>
    <button class="tab-btn" onclick="switchTab(event, 'pending')">
      Pending
    </button>
    <button class="tab-btn" onclick="switchTab(event, 'verified')">
      Verified
    </button>
  </div>

  <?php
  require_once '../Process/db_connection.php';
  $connection = getDBConnection();

  if ($connection->connect_error) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
  }

  // Handle save user action
  if (isset($_POST['saveUser'])) {
    $UserID = $_POST['UserID'];
    $Firstname = $_POST['Firstname'];
    $Lastname = $_POST['Lastname'];
    $Middlename = $_POST['Middlename'];
    $Email = $_POST['Email'];
    $ContactNo = $_POST['ContactNo'];
    $Address = $_POST['Address'];
    $Birthdate = $_POST['Birthdate'];
    $Gender = $_POST['Gender'];
    $Birthplace = $_POST['Birthplace'];
    $CivilStatus = $_POST['CivilStatus'];
    $Nationality = $_POST['Nationality'];
    $AccountStatus = $_POST['AccountStatus'];

    $sql_insert = "INSERT INTO userloginfo
        (UserID, Firstname, Lastname, Middlename, Email, ContactNo, Address, Birthdate, Gender, Birthplace, CivilStatus, Nationality, AccountStatus)
        VALUES 
        ('$UserID','$Firstname','$Lastname','$Middlename','$Email','$ContactNo','$Address','$Birthdate','$Gender','$Birthplace','$CivilStatus','$Nationality', '$AccountStatus')";

    if ($connection->query($sql_insert) === TRUE) {
      echo "<script>alert('User added successfully'); window.location.href='';</script>";
    } else {
      echo "Error: " . $connection->error;
    }
  }

  // Function to render table for specific status
  function renderTableForStatus($connection, $status) {
    $search = isset($_GET['search_lastname']) ? $connection->real_escape_string($_GET['search_lastname']) : '';
    
    if (!empty(trim($search))) {
      $sql = "SELECT UserID, Firstname, Lastname, Middlename, Email, ContactNo, Address, Birthdate, Gender, Birthplace, CivilStatus, Nationality, AccountStatus, ValidID
              FROM userloginfo
              WHERE AccountStatus = '$status' AND Lastname LIKE '%$search%'";
    } else {
      $sql = "SELECT UserID, Firstname, Lastname, Middlename, Email, ContactNo, Address, Birthdate, Gender, Birthplace, CivilStatus, Nationality, AccountStatus, ValidID
              FROM userloginfo
              WHERE AccountStatus = '$status'";
    }

    $result = $connection->query($sql);

    if (!$result) {
      die("Invalid query: " . $connection->error);
    }

    $rows = '';
    $hasRows = false;

    while ($row = $result->fetch_assoc()) {
      $hasRows = true;
      $rows .= "<tr>
          <td>" . $row["UserID"] . "</td>
          <td>" . strtoupper($row["Firstname"]) . "</td>
          <td>" . strtoupper($row["Lastname"]) . "</td>
          <td>" . strtoupper($row["Middlename"]) . "</td>
          <td>" . $row["Email"] . "</td>
          <td>" . $row["AccountStatus"] . "</td>
          <td>";
      
      if ($row["AccountStatus"] == "pending") {
        $rows .= "<a href='approveaccount.php?id=" . $row["UserID"] . "' 
                    class='action-btn-2 approve' 
                    onclick=\"showCustomConfirm(event, this.href);\">
                    <i class='fas fa-check'></i>
                </a>";
      }
      
      $rows .= "<a href='viewusers.php?id=" . htmlspecialchars($row['UserID']) . "' 
                   class='action-btn-2 view'> 
                   <i class='fas fa-eye'></i>
                </a>";
      
      $rows .= "</td></tr>";
    }

    if (!$hasRows) {
      $rows = "<tr><td colspan='7' style='text-align: center;'>No records found</td></tr>";
    }

    return $rows;
  }
  ?>

  <!-- Unverified Tab -->
  <div id="unverified" class="tab-content active">
    <!-- Search Form -->
    <form method="GET" action="" class="mb-3 search-form">
      <div class="search-form-group">
        <input type="text" name="search_lastname" class="form-control search-input"
          placeholder="Search by Lastname"
          value="<?php echo isset($_GET['search_lastname']) ? htmlspecialchars($_GET['search_lastname']) : ''; ?>">
        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i> Search
        </button>
      </div>
    </form>

    <!-- Table -->
    <div class="scrollable-table-container">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>FIRSTNAME</th>
            <th>LASTNAME</th>
            <th>MIDDLENAME</th>
            <th>EMAIL</th>
            <th>ACCOUNT STATUS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php echo renderTableForStatus($connection, 'unverified'); ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pending Tab -->
  <div id="pending" class="tab-content">
    <!-- Search Form -->
    <form method="GET" action="" class="mb-3 search-form">
      <div class="search-form-group">
        <input type="text" name="search_lastname" class="form-control search-input"
          placeholder="Search by Lastname"
          value="<?php echo isset($_GET['search_lastname']) ? htmlspecialchars($_GET['search_lastname']) : ''; ?>">
        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i> Search
        </button>
      </div>
    </form>

    <!-- Table -->
    <div class="scrollable-table-container">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>FIRSTNAME</th>
            <th>LASTNAME</th>
            <th>MIDDLENAME</th>
            <th>EMAIL</th>
            <th>ACCOUNT STATUS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php echo renderTableForStatus($connection, 'pending'); ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Verified Tab -->
  <div id="verified" class="tab-content">
    <!-- Search Form -->
    <form method="GET" action="" class="mb-3 search-form">
      <div class="search-form-group">
        <input type="text" name="search_lastname" class="form-control search-input"
          placeholder="Search by Lastname"
          value="<?php echo isset($_GET['search_lastname']) ? htmlspecialchars($_GET['search_lastname']) : ''; ?>">
        <button type="submit" class="search-btn">
          <i class="fas fa-search"></i> Search
        </button>
      </div>
    </form>

    <!-- Table -->
    <div class="scrollable-table-container">
      <table class="styled-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>FIRSTNAME</th>
            <th>LASTNAME</th>
            <th>MIDDLENAME</th>
            <th>EMAIL</th>
            <th>ACCOUNT STATUS</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php echo renderTableForStatus($connection, 'verified'); ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.tabs-container {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 2px solid #e0e0e0;
}

.tab-btn {
  padding: 12px 24px;
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  color: #666;
  transition: all 0.3s ease;
}

.tab-btn:hover {
  color: #333;
  background: #f5f5f5;
}

.tab-btn.active {
  color: #007bff;
  border-bottom-color: #007bff;
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}
</style>

<script>
function switchTab(event, tabName) {
  // Hide all tab contents
  const tabContents = document.getElementsByClassName('tab-content');
  for (let i = 0; i < tabContents.length; i++) {
    tabContents[i].classList.remove('active');
  }

  // Remove active class from all tab buttons
  const tabBtns = document.getElementsByClassName('tab-btn');
  for (let i = 0; i < tabBtns.length; i++) {
    tabBtns[i].classList.remove('active');
  }

  // Show the selected tab content
  document.getElementById(tabName).classList.add('active');
  
  // Add active class to the clicked button
  event.currentTarget.classList.add('active');
}
</script>

        



          <div id="governmentDocumentPanel" class="panel-content">
            <h1>Government Documents Requests</h1>

            <!-- Search Form for Government Documents (Auto-submit on status change; No Declined option) -->
            <form method="GET" action="" class="govdoc-search-form">
              <div class="govdoc-search-group">

                <input type="text" name="search_lastname" class="govdoc-search-input" placeholder="Search by lastname"
                  value="<?php echo isset($_GET['search_lastname']) ? htmlspecialchars($_GET['search_lastname']) : ''; ?>" />

                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>
                <select name="status_filter" class="govdoc-status-filter" onchange="this.form.submit();">
                  <option value="all" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'all') ? 'selected' : ''; ?>>All Status</option>
                  <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                  <option value="Printed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Printed') ? 'selected' : ''; ?>>Printed</option>
                  <option value="Released" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Released') ? 'selected' : ''; ?>>Released</option>
                  <!-- Declined option removed as per request -->
                </select>
                <button class="add-user" type="button" onclick="openDocumentModal()">
                  <i class="fa-regular fa-plus"></i> Request
                </button>
              </div>
            </form>

            <!-- SCROLLABLE TABLE DESIGN -->
            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>FIRSTNAME</th>
                    <th>LASTNAME</th>
                    <th>REFERENCE</th>
                    <th>TYPE</th>
                    <th>DATE</th></th>
                    <th>STATUS</th> <!-- Added STATUS column -->
                    <th>RELEASED BY</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php


                  // Define redirect URL (adjust filename if needed, e.g., for panel activation)
                  $redirectUrl = $_SERVER['PHP_SELF'] . "?panel=governmentDocumentPanel";

                  // ---------- INSERT NEW DOCUMENT REQUEST ----------
                  if (isset($_POST['saveDocument'])) {
                    $ReqId = $_POST['UserId'];
                    $Firstname = $_POST['Firstname'];
                    $Lastname = $_POST['Lastname'];
                    $Gender = $_POST['Gender'];
                    $ContactNo = $_POST['ContactNo'];
                    $Address = $_POST['Address'];
                    $Docutype = $_POST['Docutype'];
                    $DateRequested = $_POST['DateRequested'];
                    $Reference = $_POST['Reference'];

                    $sql_insert = "INSERT INTO docsreqtbl
        (ReqId, Firstname, Lastname, Gender, ContactNo, Address, refno,Docutype, DateRequested, RequestStatus)
        VALUES
        ('$ReqId','$Firstname','$Lastname','$Gender','$ContactNo','$Address','$Reference','$Docutype','$DateRequested','Pending')";

                    if ($connection->query($sql_insert) === TRUE) {
                      echo "<script>alert('Document request added successfully'); window.location.href='';</script>";
                    } else {
                      echo "Error: " . $connection->error;
                    }
                  }
                  $result = $connection->query($sql);

                  if (!$result) {
                    die("Invalid query: " . $connection->error);
                  }


                  // Build SQL with filters (using prepared statement for safety) - Always exclude Declined
                 $sql = "SELECT ReqId, Firstname, Lastname, Gender, ReqPurpose, ContactNo, Address, refno, Docutype, DateRequested, RequestStatus,ReleasedBy, CertificateImage 
FROM docsreqtbl WHERE RequestStatus != 'Declined' AND 1=1";

                  
                  $params = [];
                  $types = "";

                  // Lastname search filter
                  if (isset($_GET['search_lastname']) && !empty(trim($_GET['search_lastname']))) {
                    $search = $connection->real_escape_string($_GET['search_lastname']);
                    $sql .= " AND Lastname LIKE ?";
                    $params[] = "%$search%";
                    $types .= "s";
                  }

                  // Status filter (only Pending/Approved; applied on top of Declined exclusion)
                  if (isset($_GET['status_filter']) && $_GET['status_filter'] !== 'all') {
                    $status = $connection->real_escape_string($_GET['status_filter']);
                    $sql .= " AND RequestStatus = ?";
                    $params[] = $status;
                    $types .= "s";
                  }

                  $sql .= " ORDER BY DateRequested DESC"; // Sort by date descending
                  
                  // Prepare and execute query
                  $stmt = $connection->prepare($sql);
                  if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                  }
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if (!$result) {
                    die("Invalid query: " . $connection->error);
                  }

                  $hasRows = false;
                  while ($row = $result->fetch_assoc()) {
                    $hasRows = true;
                    echo "<tr>
            <td>" . htmlspecialchars($row["ReqId"]) . "</td>
            <td>" . strtoupper(htmlspecialchars($row["Firstname"])) . "</td>
            <td>" . strtoupper(htmlspecialchars($row["Lastname"])) . "</td>
            <td>" . htmlspecialchars($row["refno"]) . "</td>
            <td>" . strtoupper(htmlspecialchars($row["Docutype"])) . "</td>
            <td>" . date("Y-m-d", strtotime($row["DateRequested"])) . "</td>
            <td>" . strtoupper(htmlspecialchars($row['RequestStatus'])) . "</td> <!-- Status Column -->
            <td>" . strtoupper(htmlspecialchars($row['ReleasedBy'])) . "</td>
            <td>";

                    $docData = json_encode([
  "ReqId" => $row['ReqId'],
  "refno" => $row['refno'],
  "Firstname" => $row['Firstname'],
  "Lastname" => $row['Lastname'],
  "Docutype" => $row['Docutype'],
  "Address" => $row['Address'],
  "DateRequested" => $row['DateRequested'],
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);



// ✅ If Released — show only the View button
if ($row["RequestStatus"] === "Released") {
  echo "<a href='viewdocument.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

// ✅ If Printed — show Release + View
elseif ($row["RequestStatus"] === "Printed") {
  echo "<button type='button' class='action-btn-2 release' 
          onclick='releaseDocument(" . htmlspecialchars($row["ReqId"]) . ")'>
          <i class='fas fa-share'></i> Release
        </button>";

  echo "<a href='viewdocument.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

// ✅ If Approved — show Print + View
elseif ($row["RequestStatus"] === "Approved") {
  echo "<button type='button' class='action-btn-2 print' 
          onclick='openPrintModal(JSON.parse(`$docData`), " . htmlspecialchars($row["ReqId"]) . ")'>
          <i class='fas fa-print'></i>
        </button>";

  echo "<a href='viewdocument.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

// ✅ If Pending — show Approve + View + Decline
elseif ($row["RequestStatus"] === "Pending") {
  echo "<a href='approve.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 approve' 
          onclick=\"showCustomConfirm(event, this.href);\">
          <i class='fas fa-check'></i>
        </a>";

  echo "<a href='viewdocument.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";

  echo "<a href='decline.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 decline' 
          onclick=\"showCustomDeclineConfirm(event, this.href);\">
          <i class='fas fa-xmark'></i>
        </a>";
}

// ✅ If Declined or unknown — show View only
else {
  echo "<a href='viewdocument.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

                    echo "</td></tr>";
                  }

                  // Show message if no rows (helps debug empty results)
                  if (!$hasRows) {
                    echo "<tr><td colspan='10' style='text-align: center; padding: 20px;'>No records found matching the filters.</td></tr>";
                  }

                  $stmt->close();
                  $connection->close();
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <!-- Print Form Modal -->
          <div class="modal fade" id="printFormModal" tabindex="-1" aria-labelledby="printFormModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="printForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
                      <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                        style="width: 70%; max-width: 120px; border-radius: 50%;" />
                    </div>
                    <h2>Payment Form</h2>
                    <label for="modal_refno" class="form-label">Reference No.</label>
                    <input type="text" name="refno" id="modal_refno" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="modal_name" class="form-label">Name</label>
                    <input type="text" name="name" id="modal_name" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="modal_type" class="form-label">Document Type</label>
                    <input type="text" name="docutype" id="modal_type" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="modal_address" class="form-label">Address</label>
                    <input type="text" name="address" id="modal_address" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="modal_date" class="form-label">Date</label>
                    <input type="text" name="date" id="modal_date" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="modal_amount" class="form-label">Amount</label>
                    <input type="number" step="1" class="form-control" id="modal_amount" name="amount"
                      placeholder="Enter amount" required />
                  </div>

                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">Submit Payment & Print</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Success Popup (Renamed to avoid class conflicts) -->
          <div id="successModal" class="success-popup" style="display: none;">
            <div class="success-popup-content">
              <div class="success-popup-header">
                <i class="fas fa-check-circle success-popup-icon"></i>
                <span class="success-popup-title">Success!</span>
              </div>
              <div class="success-popup-body">
                <p id="modalMessage">Document request approved successfully!</p>
              </div>
              <div class="success-popup-footer">
                <button id="closeModal" class="success-popup-close-btn">Close</button>
              </div>
            </div>
          </div>
<script>
function alertNotPaid() {
  alert("This request is not yet paid.");
}
</script>




          <div id="customConfirm" class="custom-confirm-overlay" style="display:none;">
            <div class="custom-confirm-box">
              <p>Are you sure you want to approve this request?</p>
              <div class="custom-confirm-actions">
                <button id="customConfirmYes" class="custom-confirm-btn yes">Yes</button>
                <button id="customConfirmNo" class="custom-confirm-btn no">No</button>
              </div>
            </div>
          </div>
          <!-- Decline Confirmation Modal -->
          <div id="customDeclineConfirm" class="custom-confirm-overlay" style="display:none;">
            <div class="custom-confirm-box">
              <p>Are you sure you want to decline this request?</p>
              <div class="custom-confirm-actions">
                <button id="customDeclineConfirmYes" class="custom-confirm-btn yes">Yes</button>
                <button id="customDeclineConfirmNo" class="custom-confirm-btn no">No</button>
              </div>
            </div>
          </div>

          <!-- Reason for Decline Modal -->
          <div id="declineReasonModal" class="custom-confirm-overlay" style="display:none;">
            <div class="custom-confirm-box">
              <p>Please enter the reason for declining:</p>
              <textarea id="declineReasonText" rows="3" style="width:100%;"></textarea>
              <div class="custom-confirm-actions" style="margin-top:10px;">
                <button id="declineReasonSubmit" class="custom-confirm-btn yes">Submit</button>
                <button id="declineReasonCancel" class="custom-confirm-btn no">Cancel</button>
              </div>
            </div>
          </div>

          <div id="addDocumentModal" class="document-popup" style="display:none;">
            <div class="document-modal-box">
              <span class="close-btn" onclick="closeDocumentModal()">&times;</span>
              <div style="text-align: center;">
                <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h2>Document Request Form</h2>

              <form id="addDocumentForm" method="POST" action="" class="modal-form">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Reference</label>
                    <input type="text" name="Reference">
                  </div>
                  <div class="form-group">
                    <label>Firstname</label>
                    <input type="text" name="Firstname" required>
                  </div>
                  <div class="form-group">
                    <label>Lastname</label>
                    <input type="text" name="Lastname" required>
                  </div>
                  <div class="form-group">
                    <label>Gender</label>
                    <select name="Gender" required>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" name="ContactNo">
                  </div>
                  <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="Address">
                  </div>
                  <div class="form-group">
                    <label>Document Type</label>
                    <select name="Docutype" required>
                      <option value="Cedula">Cedula</option>
                      <option value="Barangay Certificate">Barangay Certificate</option>
                      <option value="Employment Form">Employment Form</option>
                      <option value="First Time Job Seeker">First Time Job Seeker</option>
                      <option value="Indengency Form">Indigency Form</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Date Requested</label>
                    <input type="date" name="DateRequested" value="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div style="margin-top:20px; text-align:right;">
                    <button type="submit" name="saveDocument" class="btn-save">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>




          <div id="businessPermitPanel" class="panel-content">
            <h1>Business Permit Requests</h1>

            <!-- Search Form for Business Permits -->
            <form method="GET" action="" class="govdoc-search-form">
              <div class="govdoc-search-group">
                <!-- preserve active panel when submitting the filter -->
                <input type="hidden" name="panel" value="businessPermitPanel" />
                <input type="text" name="search_refno" class="govdoc-search-input"
                  placeholder="Search by reference number" autocomplete="off"
                  value="<?php echo isset($_GET['search_refno']) ? htmlspecialchars($_GET['search_refno']) : ''; ?>" />
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>
                <select name="status_filter" class="govdoc-status-filter" onchange="this.form.submit();">
                  <option value="all" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'all') ? 'selected' : ''; ?>>All Status</option>
                  <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                  <option value="Printed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Printed') ? 'selected' : ''; ?>>Printed</option>
                  <option value="Released" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Released') ? 'selected' : ''; ?>>Released</option>
                  <!-- Declined option removed as per request -->
                </select>
                <button class="add-user" type="button" onclick="openBusinessModal()">
                  <i class="fa-regular fa-plus"></i> Request
                </button>
              </div>
            </form>

            <!-- SCROLLABLE TABLE DESIGN -->
            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th>TYPE</th>
                    <th>REFERENCE</th>
                    <th>DATE</th>
                    <th>STATUS</th> <!-- Added STATUS column -->
                    <th>RELEASED BY</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php

                  $connection = new mysqli($servername, $username, $password, $database);

                  if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                  }

                  // Define redirect URL (adjust filename if needed, e.g., for panel activation)
                  $redirectUrl = $_SERVER['PHP_SELF'] . "?panel=businessPermitPanel";

                  // Handle INSERT for new business request (moved outside loop)
                  if (isset($_POST['saveBusiness'])) {
                    $BusinessName = $_POST['BusinessName'];
                    $BusinessLoc = $_POST['BusinessLoc'];
                    $OwnerName = $_POST['OwnerName'];
                    $RequestType = $_POST['RequestType'];
                    $refno = $_POST['refno'];
                    $RequestedDate = $_POST['RequestedDate'];

                    // Use prepared statement for INSERT
                    $insertStmt = $connection->prepare("
    INSERT INTO businesstbl 
    (BusinessName, BusinessLoc, OwnerName, RequestType, refno, RequestedDate, RequestStatus) 
    VALUES (?, ?, ?, ?, ?, ?, 'Pending')
  ");
                    $insertStmt->bind_param("ssssss", $BusinessName, $BusinessLoc, $OwnerName, $RequestType, $refno, $RequestedDate);

                    if ($insertStmt->execute()) {
                      // Redirect safely
                      if (!headers_sent()) {
                        header("Location: " . $redirectUrl . "&message=approved");
                        exit;
                      } else {
                        $target = $redirectUrl . "&message=approved";
                        echo "<script>
        alert('Business request added successfully');
        window.location.href = " . json_encode($target) . ";
      </script>";
                        exit;
                      }
                    } else {
                      echo "<script>
      alert(" . json_encode("Error adding business request: " . $insertStmt->error) . ");
    </script>";
                    }
                    $insertStmt->close();
                  }

                  // Build SQL with filters (using prepared statement for safety) - Always exclude Declined
                  $sql = "SELECT BsnssID, BusinessName, BusinessLoc, OwnerName, RequestType, refno, RequestedDate, RequestStatus, ReleasedBy 
                FROM businesstbl WHERE RequestStatus != 'Declined' AND 1=1"; // Base query: Exclude Declined always
                  
                  $params = [];
                  $types = "";

                  // Reference number search filter
                  if (isset($_GET['search_refno']) && !empty(trim($_GET['search_refno']))) {
                    $search = $connection->real_escape_string($_GET['search_refno']);
                    $sql .= " AND refno LIKE ?";  // Fixed: Use .= to append, not = (overwriting)
                    $params[] = "%$search%";
                    $types .= "s";
                  }

                  // Status filter (only Pending/Approved; applied on top of Declined exclusion)
                  if (isset($_GET['status_filter']) && $_GET['status_filter'] !== 'all') {
                    $status = $connection->real_escape_string($_GET['status_filter']);
                    $sql .= " AND RequestStatus = ?";  // Fixed: Append correctly
                    $params[] = $status;
                    $types .= "s";
                  }

                  $sql .= " ORDER BY RequestedDate DESC"; // Sort by date descending
                  
                  // Prepare and execute query
                  $stmt = $connection->prepare($sql);
                  if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                  }
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if (!$result) {
                    die("Invalid query: " . $connection->error);
                  }

                  $hasRows = false;
                  while ($row = $result->fetch_assoc()) {
                    $hasRows = true;
                    echo "<tr>
            <td>" . htmlspecialchars($row["BsnssID"]) . "</td>  <!-- Added htmlspecialchars for safety -->
            <td>" . strtoupper(htmlspecialchars($row["OwnerName"])) . "</td>  <!-- Added htmlspecialchars -->
            <td>" . strtoupper(htmlspecialchars($row["RequestType"])) . "</td>  <!-- Added htmlspecialchars -->
            <td>" . htmlspecialchars($row["refno"]) . "</td>  <!-- Added htmlspecialchars -->
            <td>" . date("Y-m-d", strtotime($row["RequestedDate"])) . "</td>
            <td><span class='status-badge status-" . strtolower(htmlspecialchars($row['RequestStatus'])) . "'>" . strtoupper(htmlspecialchars($row['RequestStatus'])) . "</span></td> <!-- Fixed: Removed invalid 'string:' syntax; Added badge styling -->
             <td>" . strtoupper(htmlspecialchars($row['ReleasedBy'])) . "</td> 

            <td>";

                    // Data for print modal (escaped properly) - include BsnssID so client can reference the record
                    $printData = json_encode([
                      "BsnssID" => $row['BsnssID'],
                      "refno" => $row['refno'],
                      "OwnerName" => $row['OwnerName'],
                      "RequestType" => $row['RequestType'],
                      "RequestedDate" => $row['RequestedDate'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                    if ($row["RequestStatus"] === "Released") {
  echo "<a href='viewbusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}
elseif ($row["RequestStatus"] === "Printed") {
  echo "<button type='button' class='action-btn-2 release' 
          onclick='releaseBusinessDocument(" . htmlspecialchars($row["BsnssID"]) . ")'>
          <i class='fas fa-share'></i> Release
        </button>";

  echo "<a href='viewbusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}


                    elseif ($row["RequestStatus"] === "Approved") {
                      echo "<button type='button' class='action-btn-2 print' 
                      onclick='openBusinessPrintModal(" . $printData . ")'>  
              <i class='fas fa-print'></i>
            </button>";
                    echo "<a href='viewbusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";

      
}


// ✅ If Pending — show Approve + View + Decline
elseif ($row["RequestStatus"] === "Pending") {
                      echo "<a href='approvebusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
              class='action-btn-2 approve' 
              onclick=\"showCustomConfirm(event, this.href);\">
              <i class='fas fa-check'></i>
            </a>";

                    echo "<a href='viewbusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
                    class='action-btn-2 view'>  
            <i class='fas fa-eye'></i>
          </a>";

                      echo "<a href='declinebusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
               class='action-btn-2 decline' 
               onclick=\"showCustomDeclineConfirm(event, this.href);\">
              <i class='fas fa-xmark'></i>
            </a>"; 

                    }
                    else {
                      echo "<a href='viewbusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
                    }

                    echo "</td></tr>";
                  }

                  // Show message if no rows (helps debug empty results)
                  if (!$hasRows) {
                    echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No records found matching the filters.</td></tr>";  // Fixed: colspan='7' (7 columns total)
                  }

                  $stmt->close();
                  $connection->close();
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal fade" id="businessPrintFormModal" tabindex="-1"
            aria-labelledby="businessPrintFormModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="businessPrintForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
                      <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                        style="width: 70%; max-width: 120px; border-radius: 50%;" />
                    </div>
                    <h2>Business Permit Payment Form</h2>
                    <label for="business_modal_refno" class="form-label">Reference No.</label>
                    <input type="text" name="refno" id="business_modal_refno" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="business_modal_owner" class="form-label">Owner Name</label>
                    <input type="text" name="owner" id="business_modal_owner" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="business_modal_type" class="form-label">Business Type</label>
                    <input type="text" name="type" id="business_modal_type" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="business_modal_date" class="form-label">Date</label>
                    <input type="text" name="date" id="business_modal_date" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="business_modal_amount" class="form-label">Amount</label>
                    <input type="number" step="1" class="form-control" id="business_modal_amount" name="amount"
                      placeholder="Enter amount" required />
                  </div>

                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">Submit Payment & Print</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div id="addBusinessModal" class="business-popup" style="display:none;">
            <div class="business-modal-box">
              <span class="close-btn" onclick="closeBusinessModal()">&times;</span>
              <div style="text-align: center;">
 <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h2>Business Request Form</h2>
              <form id="addBusinessForm" method="POST" action="" class="modal-form">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Business Name</label>
                    <input type="text" name="BusinessName" required>
                  </div>
                  <div class="form-group">
                    <label>Business Location</label>
                    <input type="text" name="BusinessLoc" required>
                  </div>
                  <div class="form-group">
                    <label>Owner Name</label>
                    <input type="text" name="OwnerName" required>
                  </div>
                  <div class="form-group">
                    <label>Request Type</label>
                    <select name="RequestType" required>
                      <option value="permit">permit</option>
                      <option value="closure">closure</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Reference</label>
                    <input type="text" name="refno" required>
                  </div>
                  <div class="form-group">
                    <label>Requested Date</label>
                    <input type="date" name="DateRequested" value="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div style="margin-top:20px; text-align:right;">
                    <button type="submit" name="saveBusiness" class="btn-save">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>



          <div id="businessUnemploymentCertificatePanel" class="panel-content">
            <h1>Unemployment Certificate Request</h1>

            <form method="GET" action="" class="govdoc-search-form">
              <!-- Hidden field to preserve the panel on form submission -->
              <input type="hidden" name="panel" value="businessUnemploymentCertificatePanel" />

              <div class="govdoc-search-group">
                <input type="text" name="search_lastname" class="govdoc-search-input" placeholder="Search by lastname"
                  value="<?php echo isset($_GET['search_lastname']) ? htmlspecialchars($_GET['search_lastname']) : ''; ?>" />
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>
                <select name="status_filter" class="govdoc-status-filter" onchange="this.form.submit();">
                  <option value="all" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'all') ? 'selected' : ''; ?>>All Status</option>
                  <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                  <option value="Printed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Printed') ? 'selected' : ''; ?>>Printed</option>
                  <option value="Released" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Released') ? 'selected' : ''; ?>>Released</option>
                  <!-- Declined option removed as per request -->
                </select>
                <button class="add-user" type="button" onclick="openUnemploymentModal()">
                  <i class="fa-regular fa-plus"></i> Request
                </button>
              </div>
            </form>

            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead> <!-- Added thead for better structure -->
                  <tr>
                    <th>ID</th>
                    <th>NAME</th>
                    <th>TYPE</th>
                    <th>REFERENCE</th>
                    <th>DATE</th>
                    <th>STATUS</th> <!-- Added STATUS column -->
                    <th>RELEASED BY</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php


                  $connection = new mysqli($servername, $username, $password, $database);

                  if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                  }

                  // Preserve panel in redirect URL (and handle message if present)
                  $panelParam = isset($_GET['panel']) ? '?panel=' . urlencode($_GET['panel']) : '?panel=businessUnemploymentCertificatePanel';
                  $redirectUrl = $_SERVER['PHP_SELF'] . $panelParam;

                  if (isset($_POST['saveUnemployment'])) {
                    $fullname = $_POST['fullname'];
                    $age = $_POST['age'];
                    $address = $_POST['address'];
                    $unemployed_since = $_POST['unemployed_since'];
                    $certificate_type = $_POST['certificate_type'];
                    $refno = $_POST['refno'];
                    $request_date = $_POST['request_date'];

                    $insertStmt = $connection->prepare("INSERT INTO unemploymenttbl (fullname, age, address, unemployed_since, certificate_type, refno, request_date, RequestStatus) VALUES (?, ?, ?, ?, ?, ?, ?,'Pending')");
                    $insertStmt->bind_param("sisssss", $fullname, $age, $address, $unemployed_since, $certificate_type, $refno, $request_date);
                    if ($insertStmt->execute()) {
                      // Preserve any existing message or add one
                      $message = isset($_GET['message']) ? '&message=' . urlencode($_GET['message']) : '&message=approved';
                      header("Location: " . $redirectUrl . $message);
                      exit;
                    } else {
                      echo "<script>alert('Error adding unemployment request: " . $connection->error . "');</script>";
                    }
                    $insertStmt->close();
                  }

                  $sql = "SELECT id, fullname, certificate_type, refno, request_date, RequestStatus, ReleasedBy 
                        FROM unemploymenttbl WHERE RequestStatus != 'Declined' AND 1=1"; // Base query: Exclude Declined always
                  
                  $params = [];
                  $types = "";

                  if (isset($_GET['search_lastname']) && !empty(trim($_GET['search_lastname']))) {
                    $search = $connection->real_escape_string($_GET['search_lastname']);
                    $sql .= " AND fullname LIKE ?";  // Fixed: Use .= to append, not = (overwriting)
                    $params[] = "%$search%";
                    $types .= "s";
                  }
                  if (isset($_GET['status_filter']) && $_GET['status_filter'] !== 'all') {
                    $status = $connection->real_escape_string($_GET['status_filter']);
                    $sql .= " AND RequestStatus = ?";  // Fixed: Append correctly
                    $params[] = $status;
                    $types .= "s";
                  }
                  $sql .= " ORDER BY request_date DESC"; // Sort by date descending
                  
                  $stmt = $connection->prepare($sql);
                  if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                  }
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if (!$result) {
                    die("Invalid query: " . $connection->error);
                  }
                  $hasRows = false;
                  while ($row = $result->fetch_assoc()) {
                    $hasRows = true;
                    echo "<tr>
                        <td>" . htmlspecialchars($row["id"]) . "</td>
                        <td>" . strtoupper(htmlspecialchars($row["fullname"])) . "</td>
                        <td>" . strtoupper(htmlspecialchars($row["certificate_type"])) . "</td>
                        <td>" . htmlspecialchars($row["refno"]) . "</td>
                        <td>" . date("Y-m-d", strtotime($row["request_date"])) . "</td>
                        <td>" . strtoupper(htmlspecialchars($row['RequestStatus'])) . "</td> <!-- Status Column -->
                        <td>" . strtoupper(htmlspecialchars($row['ReleasedBy'])) . "</td>
                        <td>";

                    $docData = json_encode([
                      "id" => $row['id'], // Add ID for status update
                      "refno" => $row['refno'],
                      "fullname" => $row['fullname'],
                      "certificate_type" => $row['certificate_type'],
                      "request_date" => $row['request_date'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                    // ✅ If Released — show only the View button
if ($row["RequestStatus"] === "Released") {
  echo "<a href='viewunemployment.php?id=" . htmlspecialchars($row["id"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

// ✅ If Printed — show Release + View
elseif ($row["RequestStatus"] === "Printed") {
  echo "<button type='button' class='action-btn-2 release'
          onclick='releaseUnemploymentDocument(" . htmlspecialchars($row["id"]) . ")'>
          <i class='fas fa-share'></i> Release
        </button>";

  echo "<a href='viewunemployment.php?id=" . htmlspecialchars($row["id"]) . "'
          class='action-btn-2 view'>  
      <i class='fas fa-eye'></i>
    </a>";
}

                      // ✅ If Approved — show Print + View
                    elseif ($row["RequestStatus"] === "Approved") {
                      echo "<button type='button' class='action-btn-2 print' 
                      onclick='openUnemploymentPrintModal(JSON.parse(`$docData`))'>    
                            <i class='fas fa-print'></i>
                        </button>";
                          echo "<a href='viewunemployment.php?id=" . htmlspecialchars($row["id"]) . "'
          class='action-btn-2 view'>  
      <i class='fas fa-eye'></i>
    </a>";

                    } 

                    // ✅ If Pending — show Approve + View + Decline
                    elseif ($row["RequestStatus"] === "Pending") {
                      echo "<a href='approveunemployement.php?id=" . htmlspecialchars($row["id"]) . "'
                              class='action-btn-2 approve'
                              onclick=\"showCustomConfirm(event, this.href);\">
                              <i class='fas fa-check'></i>
                          </a> ";

                      echo "<a href='viewunemployment.php?id=" . htmlspecialchars($row["id"]) . "'
                          class='action-btn-2 view'>  
                  <i class='fas fa-eye'></i>
                </a>";
                      echo "<a href='decline.php?id=" . htmlspecialchars($row["id"]) . "' 
                            class='action-btn-2 decline' 
                            onclick=\"showCustomDeclineConfirm(event, this.href);\">
                            <i class='fas fa-xmark'></i>
                        </a>";
                    }
                    // ✅ If Declined or unknown — show View only
                    else {
                      echo "<a href='viewunemployment.php?id=" . htmlspecialchars($row["id"]) . "'
          class='action-btn-2 view'>  
      <i class='fas fa-eye'></i>
    </a>";
                    }
                    echo "</td></tr>";
                  }
                  if (!$hasRows) {
                    echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No records found matching the filters.</td></tr>";
                  }
                  $stmt->close();
                  $connection->close();
                  ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="modal fade" id="unemploymentPrintFormModal" tabindex="-1"
            aria-labelledby="unemploymentPrintFormModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="unemploymentPrintForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
 <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"                        style="width: 70%; max-width: 120px; border-radius: 50%;" />
                    </div>
                    <h2>Unemployment Certificate Form</h2>
                    <label for="unemployment_modal_refno" class="form-label">Reference No.</label>
                    <input type="text" name="refno" id="unemployment_modal_refno" readonly required
                      class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_name" class="form-label">Name</label>
                    <input type="text" name="fullname" id="unemployment_modal_name" readonly required
                      class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_type" class="form-label">Request Type</label>
                    <input type="text" name="certificate_type" id="unemployment_modal_type" readonly required
                      class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_date" class="form-label">Date</label>
                    <input type="text" name="request_date" id="unemployment_modal_date" readonly required
                      class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_amount" class="form-label">Amount</label>
                    <input type="number" step="1" class="form-control" id="unemployment_modal_amount" name="amount"
                      placeholder="Enter amount" required />
                  </div>

                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">Submit Payment & Print</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div id="addUnemploymentModal" class="unemployment-popup" style="display:none;">
            <div class="unemployment-modal-box">
              <span class="close-btn" onclick="closeUnemployment()">&times;</span>
              <div style="text-align: center;">
 <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h2>Unemployment Request Form</h2>
              <form id="addUnemploymentForm" method="POST" action="" class="modal-form">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                  </div>
                  <div class="form-group">
                    <label>Age</label>
                    <input type="number" name="age" required>
                  </div>
                  <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" required>
                  </div>
                  <div class="form-group">
                    <label>Unemployed Since</label>
                    <input type="date" name="unemployed_since" required>
                  </div>
                  <div class="form-group">
                    <label>Certificate Type</label>
                    <select name="certificate_type" required>
                      <option value="No Income">No Income (Unemployed)</option>
                      <option value="No Fixed Income">No Fixed Income</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Reference</label>
                    <input type="text" name="refno" required>
                  </div>
                  <div class="form-group">
                    <label>Request Date</label>
                    <input type="date" name="DateRequested" value="<?php echo date('Y-m-d'); ?>">
                  </div>
                  <div style="margin-top:20px; text-align:right;">
                    <button type="submit" name="saveUnemployment" class="btn-save">Save</button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div id="guardianshipPanel" class="panel-content">
            <h1>Guardianship Documents</h1>

            <!-- Search Form for Government Documents -->
            <form method="GET" action="" class="govdoc-search-form">
              <input type="hidden" name="panel" value="guardianshipPanel" />

              <div class="govdoc-search-group">
                <input type="text" name="search_refno" class="govdoc-search-input" placeholder="Search by refno"
                  value="<?php echo isset($_GET['search_refno']) ? htmlspecialchars($_GET['search_refno']) : ''; ?>" />
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>
                <select name="status_filter" class="govdoc-status-filter" onchange="this.form.submit();">
                  <option value="all" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'all') ? 'selected' : ''; ?>>All Status</option>
                  <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                  <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                  <option value="Printed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Printed') ? 'selected' : ''; ?>>Printed</option>
                  <option value="Released" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Released') ? 'selected' : ''; ?>>Released</option>
                  <!-- Declined option removed as per request -->
                </select>

                <button class="add-user" type="button" onclick="openGuardianshipModal()">
                  <i class="fa-regular fa-plus"></i> Request
                </button>

              </div>
            </form>

            <!--SCROLLABLE TABLE DESIGN -->
            <div class="scrollable-table-container">
              <table class="styled-table">
                <tr>
                  <th>ID</th>
                  <th>NAME</th>
                  <th>TYPE</th>
                  <th>REFERENCE</th>
                  <th>DATE</th>
                  <th>STATUS</th> <!-- Added STATUS column -->
                  <th>RELEASED BY</th>
                  <th>ACTION</th>
                </tr>
                </thead>
                <tbody>
                  <?php


                  $connection = new mysqli($servername, $username, $password, $database);

                  if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                  }

                  $panelParam = isset($_GET['panel']) ? '?panel=' . urlencode($_GET['panel']) : '?panel=guardianshipPanel';
                  $redirectUrl = $_SERVER['PHP_SELF'] . $panelParam;

                  if (isset($_POST['saveGuardianship'])) {
                    // Get values safely
                    $refno = $_POST['refno'];
                    $applicant_name = $_POST['applicant_name'];
                    $request_type = $_POST['request_type'];
                    $child_name = $_POST['child_name'];
                    $child_age = (int) $_POST['child_age'];
                    $child_address = $_POST['child_address'];
                    $request_date = $_POST['request_date'];

                    // Prepare statement (prevents SQL injection)
                    $insertstmt = $connection->prepare("INSERT INTO guardianshiptbl 
        (refno, applicant_name, request_type, child_name, child_age, child_address, request_date, RequestStatus) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending)");
                    $insertstmt->bind_param(
                      "ssssiss",
                      $refno,
                      $applicant_name,
                      $request_type,
                      $child_name,
                      $child_age,
                      $child_address,
                      $request_date,
                      $request_status
                    );

                    if ($insertstmt->execute()) {
                      $message = isset($_GET['message']) ? '&message=' . urlencode($_GET['message']) : '&message=approved';
                      header("Location: " . $redirectUrl . $message);
                      exit;
                    } else {
                      echo "<script>alert('Error saving guardianship: " . addslashes($stmt->error) . "');</script>";
                    }

                    $
                      $insertstmt->close();
                  }
                  $sql = "SELECT id, applicant_name, request_type, refno, request_date, RequestStatus, ReleasedBy 
                          FROM guardianshiptbl WHERE RequestStatus != 'Declined' AND 1=1"; // Base query: Exclude Declined always
                  $params = [];
                  $types = "";
                  if (isset($_GET['search_refno']) && !empty(trim($_GET['search_refno']))) {
                    $search = $connection->real_escape_string($_GET['search_refno']);
                    $sql .= " AND refno LIKE ?";  // Fixed: Use .= to append, not = (overwriting)
                    $params[] = "%$search%";
                    $types .= "s";
                  }
                  if (isset($_GET['status_filter']) && $_GET['status_filter'] !== 'all') {
                    $status = $connection->real_escape_string($_GET['status_filter']);
                    $sql .= " AND RequestStatus = ?";  // Fixed: Append correctly
                    $params[] = $status;
                    $types .= "s";
                  }
                  $sql .= " ORDER BY request_date DESC"; // Sort by date descending
                  $stmt = $connection->prepare($sql);
                  if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                  }
                  $stmt->execute();
                  $result = $stmt->get_result();
                  if (!$result) {
                    die("Invalid query: " . $connection->error);
                  }
                  $hasRows = false;

                  while ($row = $result->fetch_assoc()) {
                    $hasRows = true;
                    echo "<tr>
            <td>" . $row["id"] . "</td>
            <td>" . strtoupper($row["applicant_name"]) . "</td>
            <td>" . strtoupper($row["request_type"]) . "</td>      
            <td>" . $row["refno"] . "</td>
            <td>" . date("Y-m-d", strtotime($row["request_date"])) . "</td>
            <td>" . strtoupper($row['RequestStatus']) . "</td> <!-- Status Column -->
            <td>" . strtoupper(htmlspecialchars($row['ReleasedBy'])) . "</td>
             <td>";

                    $docData = json_encode([
                      "id" => $row['id'], // Add ID for status update
                      "refno" => $row['refno'],
                      "applicant_name" => $row['applicant_name'],
                      "request_type" => $row['request_type'],
                      "request_date" => $row['request_date'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                    // ✅ If Released — show only the View button
if ($row["RequestStatus"] === "Released") {
  echo "<a href='viewguardianship.php?id=" . htmlspecialchars($row["id"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

// ✅ If Printed — show Release + View
elseif ($row["RequestStatus"] === "Printed") {
  echo "<button type='button' class='action-btn-2 release' 
          onclick='releaseGuardianshipDocument(" . htmlspecialchars($row["id"]) . ")'>
          <i class='fas fa-share'></i> Release
        </button>";

  echo "<a href='viewguardianship.php?id=" . htmlspecialchars($row["id"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
}

// ✅ If Approved — show Print + View
                    elseif ($row["RequestStatus"] === "Approved") {
                      // Use the pre-built $docData (includes id) so the client has the record id for status update
                      echo "<button type='button' class='action-btn-2 print' 
                      onclick='openGuardianshipPrintModal(" . $docData . ")'>
    <i class='fas fa-print'></i>
  </button>";

    echo "<a href='viewguardianship.php?id=" . htmlspecialchars($row["id"]) . "' 
          class='action-btn-2 view'>
          <i class='fas fa-eye'></i>
        </a>";
                    }
// ✅ If Pending — show Approve + View + Decline
        elseif ($row["RequestStatus"] === "Pending") {
                      // Show APPROVE button
                      echo "<a href='approveguardianship.php?id=" . $row["id"] . "'
    class='action-btn-2 approve'
    onclick=\"showCustomConfirm(event, this.href);\">
    <i class='fas fa-check'></i></a>
  ";
                    echo "<a href='viewguardianship.php?id=" . htmlspecialchars($row["id"]) . "' class='action-btn-2 view'>
                    <i class='fas fa-eye'></i></a>";


                      echo "<a href='declineguardianship.php?id=" . htmlspecialchars($row["id"]) . "'
    class='action-btn-2 decline'
    onclick=\"showCustomDeclineConfirm(event, this.href);\">
    <i class='fas fa-xmark'></i>
  </a>";
        
        }               


else {
                    echo "<a href='viewguardianship.php?id=" . htmlspecialchars($row["id"]) . "' class='action-btn-2 view'>
                    <i class='fas fa-eye'></i></a>";

                  
                    }

                    echo "</td></tr>";
                  }
                  if (!$hasRows) {
                    echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No records found matching the filters.</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal fade" id="guardianshipPrintFormModal" tabindex="-1"
            aria-labelledby="guardianshipPrintFormModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="guardianshipPrintForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
 <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"                        style="width: 70%; max-width: 120px; border-radius: 50%;" />
                    </div>
                    <h2>Guardianship Document Form</h2>
                    <label for="guardianship_modal_refno" class="form-label
">Reference No.</label>
                    <input type="text" name="refno" id="guardianship_modal_refno" readonly required
                      class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label for="guardianship_modal_name" class="form-label
">Applicant Name</label>
                    <input type="text" name="applicant_name" id="guardianship_modal_name" readonly required
                      class="form-control" />
                  </div>
                
                  <div class="mb-3">
                    <label for="guardianship_modal_type" class="form-label
">Request Type</label>
                    <input type="text" name="request_type" id="guardianship_modal_type" readonly required
                      class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label for="guardianship_modal_date" class="form-label
">Date</label>
                    <input type="text" name="request_date" id="guardianship_modal_date" readonly required
                      class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label for="guardianship_modal_amount" class="form-label
">Amount</label>
                    <input type="number" step="1" class="form-control" id="guardianship_modal_amount" name="amount"
                      placeholder="Enter amount" required />
                  </div>
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">Submit Payment & Print</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div id="addOpenGuardianship" class="guardianship-popup" style="display:none;">
            <div class="document-modal-box">
              <span class="close-btn" onclick="closebirthcertificate()">&times;</span>
              <div style="text-align: center;">
 <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h2>Guardianship Request Form</h2>
              <form id="addGuardianshipForm" method="POST" action="" class="modal-form">
                <div class="form-grid">
                  <div class="form-group">
                    <label>Reference</label>
                    <input type="text" name="refno" readonly required>
                  </div>
                  <div class="form-group">
                    <label>Applicant Name</label>
                    <input type="text" name="applicant_name" required>
                  </div>
                  <div class="form-group">
                    <label>Request Type</label>
                    <select name="request_type" required>
                      <option value="Guardianship">Guardianship</option>
                      <option value="Solo Parent">Solo Parent</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Child Name</label>
                    <input type="text" name="child_name" required>
                  </div>
                  <div class="form-group">
                    <label>Child Age</label>
                    <input type="number" name="child_age" required>
                  </div>
                  <div class="form-group">
                    <label>Child Address</label>
                    <input type="text" name="child_address" required>
                  </div>
                  <div class="form-group">
                    <label>Request Date</label>
                    <input type="date" name="DateRequested" value="<?php echo date('Y-m-d'); ?>">
                  </div>
                </div>
                <div style="margin-top:20px; text-align:right;">
                  <button type="submit" name="saveGuardianship" class="btn-save">Save</button>
                </div>
              </form>
            </div>
          </div>

         

          

          

          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div id="itemrequestsPanel" class="panel-content">
  <div class="item-requests-header">
    <h1>Item Requests</h1>
    <div class="header-buttons">
      <button id="openRequestModalBtn" class="item-requests-btn">
        <i class="fa fa-plus"></i> Add Request
      </button>
      <button id="openAddQuantityBtn" class="item-requests-btn-add-quantity">
        <i class="fas fa-plus-circle"></i> Add Item Quantity
      </button>
    </div>
  </div>

  <!-- Display Inventory Availability -->
  <div class="inventory-availability">
    <div class="inventory-header">
      <h2>Item Availability</h2>
    </div>

    <?php
    $conn = new mysqli("localhost", "root", "", "barangayDb");
    if ($conn->connect_error) die("DB error: " . $conn->connect_error);
    $invRes = $conn->query("SELECT item_name, total_stock, on_loan FROM inventory");
    
    if ($invRes && $invRes->num_rows > 0) {
      echo '<div class="inventory-grid">';
      while ($invRow = $invRes->fetch_assoc()) {
        $available = $invRow['total_stock'] - $invRow['on_loan'];
        $availablePercent = $invRow['total_stock'] > 0 ? ($available / $invRow['total_stock']) * 100 : 0;
        
        $statusClass = 'status-high';
        if ($availablePercent <= 20) {
          $statusClass = 'status-critical';
        } elseif ($availablePercent <= 50) {
          $statusClass = 'status-low';
        }
        
        echo '<div class="inventory-card ' . $statusClass . '">';
        echo '  <div class="card-header">';
        echo '    <h3 class="item-name">' . htmlspecialchars($invRow['item_name']) . '</h3>';
        echo '    <span class="availability-badge">' . $available . ' Available</span>';
        echo '  </div>';
        echo '  <div class="card-body">';
        echo '    <div class="stat-row">';
        echo '      <div class="stat-item">';
        echo '        <span class="stat-label">Total Stock</span>';
        echo '        <span class="stat-value">' . $invRow['total_stock'] . '</span>';
        echo '      </div>';
        echo '      <div class="stat-item">';
        echo '        <span class="stat-label">On Loan</span>';
        echo '        <span class="stat-value">' . $invRow['on_loan'] . '</span>';
        echo '      </div>';
        echo '    </div>';
        echo '    <div class="progress-bar">';
        echo '      <div class="progress-fill" style="width: ' . $availablePercent . '%"></div>';
        echo '    </div>';
        echo '    <div class="progress-label">' . round($availablePercent) . '% Available</div>';
        echo '  </div>';
        echo '</div>';
      }
      echo '</div>';
    } else {
      echo '<div class="empty-state">';
      echo '  <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
      echo '    <path d="M20 7h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM10 4h4v3h-4V4zm10 16H4V9h16v11z"/>';
      echo '  </svg>';
      echo '  <p class="empty-text">No inventory data found</p>';
      echo '  <p class="empty-subtext">Add items to get started</p>';
      echo '</div>';
    }
    ?>
  </div>

  <style>
  .header-buttons {
    display: flex;
    gap: 10px;
  }

  .item-requests-btn-add-quantity {
     padding: 10px 16px;
      background-color: #5CB25D;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
  }

  .item-requests-btn-add-quantity:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(56, 239, 125, 0.4);
  }

  .inventory-availability {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  }

  .inventory-header {
    margin-bottom: 2rem;
  }

  .inventory-header h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 0.5rem 0;
  }

  .inventory-grid {
    display: flex;
  flex-wrap: wrap;
  gap: .9rem;
  justify-content: flex-start;
  }

  .inventory-card {
     display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex: 0 0 372px;
  padding: 1rem;
  /* NEW look */
  background-color: #d9f2dc;       /* light green fill */
  border: 2px solid #2d7a3e;       /* darker green border line */
  border-radius: 6px;
  color: #2d7a3e;                  /* dark green text */
  box-shadow: none;                /* remove heavy shadow for clean look */
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .inventory-card:hover {
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
  }

  .status-high { border-left-color: #0b9920ff; }
  .status-low { border-left-color: #f59e0b; }
  .status-critical { border-left-color: #ef4444; }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: #d9f2dc;
    border-bottom: 1px solid #e2e8f0;
  }

  .item-name {
    font-size: 1.125rem;
    font-weight: 600;
    color: #2d7a3e;
    margin: 0;
  }

  .availability-badge {
    background: #d9f2dc;
    color: #2d7a3e;
    padding: 0.375rem 0.875rem;
    border-radius: 20px;
    font-size: 1.125rem;
    font-weight: 600;
  }

  .card-body { padding: .5rem; }

  .stat-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.25rem;
  }

  .stat-item {
    flex: 1;
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
  }

  .stat-label {
    display: block;
    font-size: 0.75rem;
    color: #2d7a3e;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.375rem;
    font-weight: 600;
  }

  .stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d7a3e;
  }

  .progress-bar {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 0.5rem;
  }

  .status-high .progress-fill { background: #4CAF50; }
  .status-low .progress-fill { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); }
  .status-critical .progress-fill { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }

  .progress-fill {
    height: 100%;
    transition: width 0.6s ease;
    border-radius: 10px;
  }

  .progress-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 500;
  }

  /* Details View Modal Styles */
  .details-view-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .details-view-modal-content {
    background: white;
    margin: 3% auto;
    padding: 0;
    border-radius: 24px;
    width: 50%;
    max-width: 700px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    animation: modalZoom 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    overflow: hidden;
  }

  @keyframes modalZoom {
    from {
      transform: scale(0.7) rotateX(20deg);
      opacity: 0;
    }
    to {
      transform: scale(1) rotateX(0);
      opacity: 1;
    }
  }

  .details-view-modal-header {
    padding: 2.5rem;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
  }

  .details-view-modal-header img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 5px solid white;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
    background: white;
    object-fit: cover;
  }

  .details-view-modal-header h4 {
    margin: 0;
    font-size: 1.85rem;
    font-weight: 700;
    position: relative;
    z-index: 1;
    color: black;
    text-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
    letter-spacing: 0.5px;
  }

  .details-view-modal-close {
    position: absolute;
    right: 1.5rem;
    top: 1.5rem;
    color: black;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    z-index: 2;
    backdrop-filter: blur(10px);
  }

  .details-view-modal-close:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: rotate(180deg) scale(1.1);
  }

  .details-view-modal-body {
    padding: 2.5rem;
    background: linear-gradient(to bottom, #ffffff 0%, #f9fafb 100%);
  }

  /* Return Modal Styles */
  .return-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
  }

  .return-modal-content {
    background: #059629ff;
    margin: 8% auto;
    padding: 0;
    border-radius: 20px;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.4s ease;
    overflow: hidden;
  }

  .return-modal-content h4 {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    color: black;
    padding: 25px 30px;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  }

  .return-modal-content form {
    padding: 30px;
    background: white;
  }

  .return-modal-content label {
    display: block;
    color: #333;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .return-modal-content select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    cursor: pointer;
    margin-bottom: 20px;
  }

  .return-modal-content select:focus {
    outline: none;
    border-color: #667eea;
    background-color: white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .return-modal-content button[type="submit"] {
    width: 100%;
    padding: 14px;
    background: #069e1dff;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 10px;
  }

  .return-modal-content button[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  }

  .return-modal-close {
    position: absolute;
    right: 20px;
    top: 20px;
    color: black;
    font-size: 32px;
    font-weight: 300;
    cursor: pointer;
    z-index: 1;
    transition: all 0.3s ease;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
  }

  .return-modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-50px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Add Quantity Modal Styles */
  .add-quantity-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
  }

  .add-quantity-modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.4s ease;
    overflow: hidden;
  }

  .add-quantity-modal .modal-header {
    background: white;
    padding: 25px 30px;
    position: relative;
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .add-quantity-modal .modal-header img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    padding: 5px;
  }

  .add-quantity-modal .modal-header h4 {
    color: black;
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    flex: 1;
  }

  .add-quantity-modal-close {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: black;
    font-size: 32px;
    font-weight: 300;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
  }

  .add-quantity-modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) rotate(90deg);
  }

  .add-quantity-modal .modal-body {
    padding: 30px;
  }

  .add-quantity-modal .form-group {
    margin-bottom: 20px;
  }

  .add-quantity-modal .form-group label {
    display: block;
    color: #333;
    font-weight: 600;
    margin-bottom: 10px;
    font-size: 14px;
  }

  .add-quantity-modal .form-group label i {
    margin-right: 8px;
    color: #11998e;
  }

  .add-quantity-modal .form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    box-sizing: border-box;
  }

  .add-quantity-modal .form-control:focus {
    outline: none;
    border-color: #11998e;
    background-color: white;
    box-shadow: 0 0 0 3px rgba(17, 153, 142, 0.1);
  }

  .add-quantity-modal .info-box {
    background: linear-gradient(135deg, #11998e15 0%, #38ef7d15 100%);
    border-left: 4px solid #11998e;
    padding: 15px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .add-quantity-modal .info-box i {
    color: #11998e;
    font-size: 20px;
  }

  .add-quantity-modal .info-box span {
    color: #333;
    font-size: 15px;
  }

  .add-quantity-modal .info-box strong {
    color: #11998e;
    font-size: 18px;
  }

  .add-quantity-modal .btn-submit {
    width: 100%;
    padding: 14px;
    background: #0aa242ff;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 10px;
  }

  .add-quantity-modal .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(56, 239, 125, 0.4);
  }

  @media (max-width: 768px) {
    .header-buttons {
      flex-direction: column;
    }

    .inventory-availability {
      padding: 1rem;
    }

    .inventory-grid {
      grid-template-columns: 1fr;
    }

    .details-view-modal-content {
      width: 95%;
      margin: 5% auto;
    }

    .return-modal-content,
    .add-quantity-modal-content {
      width: 95%;
      margin: 15% auto;
    }
  }

  .item-requests-table-container {
  max-height: 700px; /* Adjust height as needed */
  overflow-y: auto;  /* Enables vertical scrolling */
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
}

/* Keep the table header fixed during scroll */
.item-requests-table {
  width: 100%;
  border-collapse: collapse;
}

.item-requests-thead th {
  position: sticky;
  top: 0;
  background-color: #f8fafc; /* Header background */
  z-index: 2;
  padding: 10px;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #cbd5e1;
}

.item-requests-tbody td {
  padding: 8px 10px;
  border-bottom: 1px solid #e2e8f0;
  background: #ffffff;
}

/* Optional: subtle hover effect */
.item-requests-tbody tr:hover {
  background-color: #f1f5f9;
  transition: background-color 0.2s ease-in-out;
}

  </style>

  <!-- Modal: Add Request -->
  <div id="requestModal" class="request-modal">
    <div class="request-modal-content">
      <div class="modal-header">
        <span class="request-modal-close">&times;</span>
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" />
        <h4>Request Item</h4>
      </div>
      
      <div class="modal-body">
        <form method="POST" id="requestForm">
          <div class="form-group">
            <label><i class="fas fa-user"></i> Resident Name:</label>
            <div class="autocomplete-container">
              <input 
                type="text" 
                id="residentNameInput" 
                name="residentNameDisplay" 
                class="form-control autocomplete-input" 
                placeholder="Search resident name..."
                autocomplete="off"
                required
              />
              <input type="hidden" name="residentName" id="residentNameHidden" />
              <input type="hidden" name="residentUserId" id="residentUserId" />
              <div id="autocompleteDropdown" class="autocomplete-dropdown"></div>
            </div>
          </div>

          <div class="form-group">
            <label><i class="fas fa-box"></i> Select Item:</label>
            <select name="itemSelect" class="form-control" required>
              <option value="">--Choose an item--</option>
              <option value="Tent">Tent</option>
              <option value="Monoblock Chair">Monoblock Chair</option>
              <option value="Table">Table</option>
            </select>
          </div>

          <div class="form-group">
            <label><i class="fas fa-sort-numeric-up"></i> Quantity:</label>
            <input type="number" name="quantity" class="form-control" min="1" required />
          </div>

          <div class="form-group">
            <label><i class="fas fa-file-alt"></i> Purpose of Request:</label>
            <textarea name="purpose" class="form-control" rows="3" placeholder="Describe the purpose..." required></textarea>
          </div>

          <div class="form-group">
            <label><i class="fas fa-calendar-alt"></i> Date & Time Needed:</label>
            <input type="datetime-local" name="eventDatetime" class="form-control" required />
          </div>

          <button type="submit" name="saveItemRequest" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Submit Request
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Add Quantity -->
  <div id="addQuantityModal" class="add-quantity-modal" style="display:none;">
    <div class="add-quantity-modal-content">
      <div class="modal-header">
        <span class="add-quantity-modal-close">&times;</span>
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" />
        <h4>Add Item Quantity</h4>
      </div>
      
      <div class="modal-body">
        <form method="POST" id="addQuantityForm">
          <div class="form-group">
            <label><i class="fas fa-box"></i> Select Item:</label>
            <select name="itemName" id="itemNameSelect" class="form-control" required>
              <option value="">--Choose an item--</option>
              <?php
              $connQ = new mysqli("localhost", "root", "", "barangayDb");
              if (!$connQ->connect_error) {
                $result = $connQ->query("SELECT item_name, total_stock FROM inventory ORDER BY item_name");
                if ($result && $result->num_rows > 0) {
                  while ($item = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($item['item_name']) . "' data-current-stock='" . $item['total_stock'] . "'>";
                    echo htmlspecialchars($item['item_name']) . " (Current: " . $item['total_stock'] . ")";
                    echo "</option>";
                  }
                }
                $connQ->close();
              }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label><i class="fas fa-plus-circle"></i> Quantity to Add:</label>
            <input type="number" name="quantityToAdd" id="quantityToAdd" class="form-control" min="1" placeholder="Enter quantity to add" required />
          </div>

          <div class="form-group" id="newTotalDisplay" style="display:none;">
            <div class="info-box">
              <i class="fas fa-info-circle"></i>
              <span>New Total: <strong id="newTotalValue">0</strong></span>
            </div>
          </div>

          <button type="submit" name="addItemQuantity" class="btn-submit">
            <i class="fas fa-check-circle"></i> Add Quantity
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal: Return Item -->
  <div id="returnModal" class="return-modal" style="display:none;">
    <div class="return-modal-content">
      <span class="return-modal-close" onclick="document.getElementById('returnModal').style.display='none';">&times;</span>
      <h4>Return Item</h4>
      <form method="POST">
        <input type="hidden" name="returnId" id="returnId">
        <label>Damage Status:</label><br>
        <select name="damageStatus" required>
          <option value="Good">Good</option>
          <option value="Damaged">Damaged</option>
        </select><br><br>
        <button type="submit" name="processReturn">Submit Return</button>
      </form>
    </div>
  </div>

  <!-- Modal: View Request Details -->
  <div id="requestDetailsModal" class="details-view-modal">
    <div class="details-view-modal-content">
      <div class="details-view-modal-header">
        <span class="details-view-modal-close" onclick="document.getElementById('requestDetailsModal').style.display='none';">&times;</span>
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" />
        <h4>Request Details</h4>
      </div>
      
      <div class="details-view-modal-body" id="viewDetails">
        <!-- Details will be populated here via JS -->
      </div>
    </div>
  </div>

  <!-- Popup Modal -->
  <div id="popupModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5);">
    <div style="background-color:#fff; margin:15% auto; padding:20px; border-radius:5px; width:300px; text-align:center;">
      <p id="popupMessage"></p>
      <button onclick="document.getElementById('popupModal').style.display='none'">Close</button>
    </div>
  </div>

  <!-- Table of Requests -->
  <div class="item-requests-table-container">
    <table class="item-requests-table">
      <thead class="item-requests-thead">
        <tr>
          <th>REQUESTOR NAME</th>
          <th>ITEM REQUEST</th>
          <th>QUANTITY</th>
          <th>DATE REQUEST</th>
          <th>DATE NEEDED</th>
          <th>STATUS</th>
          <th>CONDITION</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody class="item-requests-tbody" id="requestsTableBody">
        <?php
        // ---------- PHP PROCESSING ----------
        
        // 1) ADD ITEM QUANTITY (NEW)
        if (isset($_POST['addItemQuantity'])) {
          $itemName = trim($_POST['itemName']);
          $quantityToAdd = (int) $_POST['quantityToAdd'];
          
          if ($quantityToAdd > 0) {
            $stmt = $conn->prepare("UPDATE inventory SET total_stock = total_stock + ? WHERE item_name = ?");
            $stmt->bind_param("is", $quantityToAdd, $itemName);
            
            if ($stmt->execute()) {
              $message = "Successfully added $quantityToAdd to $itemName inventory.";
            } else {
              $message = "Error updating inventory: " . $conn->error;
            }
            
            $stmt->close();
          } else {
            $message = "Invalid quantity. Please enter a positive number.";
          }
          
          echo "<script>document.addEventListener('DOMContentLoaded', function() { reloadItemRequestsPanel(" . json_encode($message) . "); });</script>";
        }

        // 2) NEW REQUEST
        if (isset($_POST['saveItemRequest'])) {
          $name = trim($_POST['residentName']);
          $item = trim($_POST['itemSelect']);
          $quantity = (int) $_POST['quantity'];
          $purpose = trim($_POST['purpose']);
          $eventDT = $_POST['eventDatetime'];

          $stmt = $conn->prepare("SELECT total_stock, on_loan FROM inventory WHERE item_name=?");
          $stmt->bind_param("s", $item);
          $stmt->execute();
          $inv = $stmt->get_result()->fetch_assoc();
          $stmt->close();

          $reserved = 0;
          $rs = $conn->prepare("SELECT SUM(quantity) AS r FROM tblitemrequest WHERE item=? AND RequestStatus IN ('Pending','Approved','On Loan') AND event_datetime=?");
          $rs->bind_param("ss", $item, $eventDT);
          $rs->execute();
          if ($row = $rs->get_result()->fetch_assoc()) $reserved = $row['r'] ?? 0;
          $rs->close();

          $available = $inv['total_stock'] - $inv['on_loan'] - $reserved;

          if ($quantity > $available) {
            $message = "Request denied: Only $available $item(s) available.";
          } else {
            $status = 'Pending';
            $ins = $conn->prepare("INSERT INTO tblitemrequest (name,Purpose,item,quantity,event_datetime,date,RequestStatus) VALUES (?,?,?,?,?,NOW(),?)");
            $ins->bind_param("sssiss", $name, $purpose, $item, $quantity, $eventDT, $status);
            $ins->execute();
            $ins->close();
            $message = "Request submitted successfully.";
          }

          echo "<script>document.addEventListener('DOMContentLoaded', function() { reloadItemRequestsPanel(" . json_encode($message) . "); });</script>";
        }

        // 3) PROCESS RETURN
        if (isset($_POST['processReturn'])) {
          $id = (int) $_POST['returnId'];
          $damageStatus = $_POST['damageStatus'];
          $returnDate = date('Y-m-d H:i:s');

          $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id AND RequestStatus='On Loan'")->fetch_assoc();
          if ($q) {
            $conn->query("UPDATE inventory SET on_loan = GREATEST(on_loan - {$q['quantity']}, 0) WHERE item_name = '{$q['item']}'");

            if ($damageStatus === 'Damaged') {
              $conn->query("UPDATE inventory SET total_stock = GREATEST(total_stock - {$q['quantity']}, 0) WHERE item_name = '{$q['item']}'");
            }

            $conn->query("UPDATE tblitemrequest SET RequestStatus='Returned', return_date='$returnDate', damage_status='$damageStatus' WHERE id=$id");
            $message = "Item returned successfully.";
          } else {
            $message = "Error: Invalid return request.";
          }

          echo "<script>document.addEventListener('DOMContentLoaded', function() { reloadItemRequestsPanel(" . json_encode($message) . "); });</script>";
        }

        // 4) OTHER ACTION BUTTONS
        if (isset($_POST['action'], $_POST['id'])) {
          $id = (int) $_POST['id'];
          $action = $_POST['action'];

          switch ($action) {
            case 'approve':
              $req = $conn->query("SELECT item, quantity, event_datetime FROM tblitemrequest WHERE id=$id")->fetch_assoc();
              $item = $req['item'];
              $quantity = $req['quantity'];
              $eventDT = $req['event_datetime'];

              $inv = $conn->query("SELECT total_stock, on_loan FROM inventory WHERE item_name='$item'")->fetch_assoc();

              $reserved = 0;
              $rs = $conn->query("SELECT SUM(quantity) AS r FROM tblitemrequest WHERE item='$item' AND RequestStatus IN ('Pending','Approved','On Loan') AND event_datetime='$eventDT' AND id != $id");
              if ($row = $rs->fetch_assoc()) $reserved = $row['r'] ?? 0;

              $available = $inv['total_stock'] - $inv['on_loan'] - $reserved;

              if ($quantity > $available) {
                $message = "Cannot approve: Only $available $item(s) available.";
              } else {
                $conn->query("UPDATE tblitemrequest SET RequestStatus='Approved' WHERE id=$id");
                $message = "Request approved successfully.";
              }

              echo "<script>document.addEventListener('DOMContentLoaded', function() { reloadItemRequestsPanel(" . json_encode($message) . "); });</script>";
              break;

            case 'reject':
              $reason = $conn->real_escape_string($_POST['reason'] ?? 'Not specified');
              $conn->query("UPDATE tblitemrequest SET RequestStatus='Cancelled', Reason='$reason' WHERE id=$id");
              $message = "Cancel Request.";
              echo "<script>document.addEventListener('DOMContentLoaded', function() { reloadItemRequestsPanel(" . json_encode($message) . "); });</script>";
              break;

            case 'release':
              $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id")->fetch_assoc();
              $conn->query("UPDATE inventory SET on_loan = on_loan + {$q['quantity']} WHERE item_name = '{$q['item']}'");
              $conn->query("UPDATE tblitemrequest SET RequestStatus='On Loan' WHERE id=$id");
              $message = "Item released successfully.";
              echo "<script>document.addEventListener('DOMContentLoaded', function() { reloadItemRequestsPanel(" . json_encode($message) . "); });</script>";
              break;
          }
        }

        // 5) DISPLAY TABLE
        $res = $conn->query("SELECT * FROM tblitemrequest ORDER BY date DESC");
        if ($res && $res->num_rows > 0) {
          while ($row = $res->fetch_assoc()) {
            echo "<tr>
              <td>" . htmlspecialchars($row['name']) . "</td>
              <td>" . htmlspecialchars($row['item']) . "</td>
              <td>" . htmlspecialchars($row['quantity']) . "</td>
              <td>" . htmlspecialchars($row['date']) . "</td>
              <td>" . date("Y-m-d", strtotime($row["event_datetime"])) . "</td>
              <td>" . htmlspecialchars($row['RequestStatus']) . "</td>
              <td>" . htmlspecialchars($row['damage_status'] ?? 'N/A') . "</td>
              <td>";

            echo "<button class='action-btn action-view' data-id='{$row['id']}' onclick='viewRequest(this)'><i class='fa fa-eye'></i> View</button>";

            if ($row['RequestStatus'] == 'Pending' || $row['RequestStatus'] == 'Pending/Waitlist') {
              echo actionBtn($row['id'], 'release', 'Release');
              echo actionBtn($row['id'], 'reject', 'Cancel');
            } elseif ($row['RequestStatus'] == 'Approved') {
              echo actionBtn($row['id'], 'release', 'Release');
            } elseif ($row['RequestStatus'] == 'On Loan') {
              echo "<button class='action-btn action-return' onclick=\"openReturnModal({$row['id']})\"><i class='fa fa-undo'></i> Return</button>";
            }
            echo "</td></tr>";
          }
        } else {
          echo "<tr><td colspan='7'>No item requests found.</td></tr>";
        }
        // Singleton connection closed by PHP

        function actionBtn($id, $action, $label) {
          $icons = [
            'approve' => 'fa-check',
            'reject'  => 'fa-times',
            'release' => 'fa-box-open'
          ];
          $class = "action-btn action-$action";
          $icon = isset($icons[$action]) ? "<i class='fa {$icons[$action]}'></i>" : "";
          return "<form method='post' style='display:inline'><input type='hidden' name='id' value='$id'><button name='action' value='$action' class='$class'>$icon $label</button></form>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
<script>
// Fetch verified residents from the database
// CONSOLIDATED JAVASCRIPT - Replace ALL your script tags with this single script

// Fetch verified residents from the database
let verifiedResidents = [];

// Load verified residents on page load
document.addEventListener('DOMContentLoaded', function() {
  fetchVerifiedResidents();
  initializeModalControls();
  initializeAddQuantityCalculation();
});

function fetchVerifiedResidents() {
  fetch('get_verified_residents.php')
    .then(response => response.json())
    .then(data => {
      verifiedResidents = data;
    })
    .catch(error => {
      console.error('Error fetching residents:', error);
    });
}

// Initialize all modal controls
function initializeModalControls() {
  // Request Modal
  const openRequestBtn = document.getElementById('openRequestModalBtn');
  const requestModal = document.getElementById('requestModal');
  const requestModalClose = document.querySelector('.request-modal-close');
  
  if (openRequestBtn) {
    openRequestBtn.onclick = function() {
      requestModal.style.display = 'block';
    };
  }
  
  if (requestModalClose) {
    requestModalClose.onclick = function() {
      requestModal.style.display = 'none';
      document.getElementById('requestForm').reset();
      document.getElementById('residentNameHidden').value = '';
      document.getElementById('residentUserId').value = '';
      document.getElementById('autocompleteDropdown').classList.remove('show');
    };
  }
  
  // Add Quantity Modal
  const openAddQuantityBtn = document.getElementById('openAddQuantityBtn');
  const addQuantityModal = document.getElementById('addQuantityModal');
  const addQuantityModalClose = document.querySelector('.add-quantity-modal-close');
  
  if (openAddQuantityBtn) {
    openAddQuantityBtn.onclick = function() {
      addQuantityModal.style.display = 'block';
    };
  }
  
  if (addQuantityModalClose) {
    addQuantityModalClose.onclick = function() {
      addQuantityModal.style.display = 'none';
      document.getElementById('addQuantityForm').reset();
      document.getElementById('newTotalDisplay').style.display = 'none';
    };
  }
  
  // Close modals when clicking outside
  window.onclick = function(event) {
    if (event.target == requestModal) {
      requestModal.style.display = 'none';
      document.getElementById('requestForm').reset();
      document.getElementById('residentNameHidden').value = '';
      document.getElementById('residentUserId').value = '';
      document.getElementById('autocompleteDropdown').classList.remove('show');
    }
    
    if (event.target == addQuantityModal) {
      addQuantityModal.style.display = 'none';
      document.getElementById('addQuantityForm').reset();
      document.getElementById('newTotalDisplay').style.display = 'none';
    }
  };
}

// Autocomplete functionality
function initializeAutocomplete() {
  const residentInput = document.getElementById('residentNameInput');
  const dropdown = document.getElementById('autocompleteDropdown');
  const hiddenNameInput = document.getElementById('residentNameHidden');
  const hiddenUserIdInput = document.getElementById('residentUserId');
  
  if (!residentInput) return;
  
  residentInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    
    if (searchTerm.length === 0) {
      dropdown.classList.remove('show');
      hiddenNameInput.value = '';
      hiddenUserIdInput.value = '';
      return;
    }

    const filtered = verifiedResidents.filter(resident => {
      const fullName = `${resident.Firstname} ${resident.Middlename} ${resident.Lastname}`.toLowerCase();
      return fullName.includes(searchTerm);
    });

    displayDropdown(filtered, dropdown, hiddenNameInput, hiddenUserIdInput);
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.autocomplete-container')) {
      dropdown.classList.remove('show');
    }
  });
}

function displayDropdown(residents, dropdown, hiddenNameInput, hiddenUserIdInput) {
  dropdown.innerHTML = '';

  if (residents.length === 0) {
    dropdown.innerHTML = '<div class="no-results">No verified residents found</div>';
    dropdown.classList.add('show');
    return;
  }

  residents.forEach(resident => {
    const item = document.createElement('div');
    item.className = 'autocomplete-item';
    
    const fullName = `${resident.Firstname} ${resident.Middlename} ${resident.Lastname}`;
    const initials = `${resident.Firstname.charAt(0)}${resident.Lastname.charAt(0)}`;
    
    item.innerHTML = `
      <div class="resident-info">
        <div class="resident-avatar">${initials}</div>
        <div class="resident-details">
          <div class="resident-name">${fullName}</div>
          <div class="resident-address">${resident.Address || 'No address'}</div>
        </div>
      </div>
    `;
    
    item.addEventListener('click', function() {
      selectResident(resident, fullName, hiddenNameInput, hiddenUserIdInput, dropdown);
    });
    
    dropdown.appendChild(item);
  });

  dropdown.classList.add('show');
}

function selectResident(resident, fullName, hiddenNameInput, hiddenUserIdInput, dropdown) {
  document.getElementById('residentNameInput').value = fullName;
  hiddenNameInput.value = fullName;
  hiddenUserIdInput.value = resident.UserID;
  dropdown.classList.remove('show');
}

// Initialize autocomplete after DOM is ready
document.addEventListener('DOMContentLoaded', initializeAutocomplete);

// Add Quantity Modal - Calculate new total
function initializeAddQuantityCalculation() {
  const quantityInput = document.getElementById('quantityToAdd');
  const itemSelect = document.getElementById('itemNameSelect');
  
  if (!quantityInput || !itemSelect) return;
  
  // Calculate when quantity changes
  quantityInput.addEventListener('input', function() {
    calculateNewTotal(itemSelect, quantityInput);
  });
  
  // Calculate when item changes
  itemSelect.addEventListener('change', function() {
    calculateNewTotal(itemSelect, quantityInput);
  });
}

function calculateNewTotal(itemSelect, quantityInput) {
  const selectedOption = itemSelect.options[itemSelect.selectedIndex];
  const newTotalDisplay = document.getElementById('newTotalDisplay');
  const newTotalValue = document.getElementById('newTotalValue');
  
  if (selectedOption.value && quantityInput.value) {
    const currentStock = parseInt(selectedOption.getAttribute('data-current-stock')) || 0;
    const quantityToAdd = parseInt(quantityInput.value) || 0;
    const newTotal = currentStock + quantityToAdd;
    
    newTotalValue.textContent = newTotal;
    newTotalDisplay.style.display = 'block';
  } else {
    newTotalDisplay.style.display = 'none';
  }
}

// Function to open return modal
function openReturnModal(id) {
  document.getElementById('returnId').value = id;
  document.getElementById('returnModal').style.display = 'block';
}

// Function to view request details
function viewRequest(btn) {
  var id = btn.getAttribute('data-id');
  fetch('get_request_details.php?id=' + id)
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        alert('Error: ' + data.error);
        return;
      }
      
      // Determine status color
      const statusColors = {
        'Pending': '#f39c12',
        'Approved': '#27ae60',
        'Rejected': '#e74c3c',
        'Returned': '#3498db',
        'Overdue': '#c0392b'
      };
      const statusColor = statusColors[data.RequestStatus] || '#95a5a6';
      
      // Populate modal with improved styling
      document.getElementById('viewDetails').innerHTML = `
        <div style="background: #27ae60; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;">
          <h2 style="color: white; margin: 0; font-size: 24px;">Request Details</h2>
          <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0 0; font-size: 14px;">ID: #${data.id}</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
          <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
            <p style="color: #6c757d; font-size: 12px; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 0.5px;">Requestor</p>
            <p style="color: #2c3e50; font-size: 16px; font-weight: 600; margin: 0;">${data.name}</p>
          </div>
          
          <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea;">
            <p style="color: #6c757d; font-size: 12px; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 0.5px;">Status</p>
            <p style="color: ${statusColor}; font-size: 16px; font-weight: 600; margin: 0;">
              <span style="background: ${statusColor}20; padding: 5px 12px; border-radius: 20px; display: inline-block;">
                ${data.RequestStatus}
              </span>
            </p>
          </div>
        </div>
        
        <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden;">
          <div style="background: #f8f9fa; padding: 12px 15px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; font-size: 16px; color: #2c3e50;">Item Information</h3>
          </div>
          <div style="padding: 15px;">
            <div style="margin-bottom: 15px;">
              <p style="color: #6c757d; font-size: 13px; margin: 0 0 5px 0;">Item Name</p>
              <p style="color: #2c3e50; font-size: 15px; margin: 0; font-weight: 500;">${data.item}</p>
            </div>
            <div style="margin-bottom: 15px;">
              <p style="color: #6c757d; font-size: 13px; margin: 0 0 5px 0;">Quantity</p>
              <p style="color: #2c3e50; font-size: 15px; margin: 0; font-weight: 500;">${data.quantity}</p>
            </div>
            <div>
              <p style="color: #6c757d; font-size: 13px; margin: 0 0 5px 0;">Purpose</p>
              <p style="color: #2c3e50; font-size: 15px; margin: 0; line-height: 1.5;">${data.Purpose}</p>
            </div>
          </div>
        </div>
        
        <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; margin-top: 15px;">
          <div style="background: #f8f9fa; padding: 12px 15px; border-bottom: 1px solid #e9ecef;">
            <h3 style="margin: 0; font-size: 16px; color: #2c3e50;">Timeline</h3>
          </div>
          <div style="padding: 15px;">
            <div style="display: flex; align-items: start; margin-bottom: 15px;">
              <div style="background: #667eea; width: 10px; height: 10px; border-radius: 50%; margin-right: 12px; margin-top: 5px;"></div>
              <div style="flex: 1;">
                <p style="color: #6c757d; font-size: 13px; margin: 0;">Date Requested</p>
                <p style="color: #2c3e50; font-size: 15px; margin: 5px 0 0 0; font-weight: 500;">${data.date}</p>
              </div>
            </div>
            <div style="display: flex; align-items: start; margin-bottom: 15px;">
              <div style="background: #f39c12; width: 10px; height: 10px; border-radius: 50%; margin-right: 12px; margin-top: 5px;"></div>
              <div style="flex: 1;">
                <p style="color: #6c757d; font-size: 13px; margin: 0;">Date Needed</p>
                <p style="color: #2c3e50; font-size: 15px; margin: 5px 0 0 0; font-weight: 500;">${data.event_datetime}</p>
              </div>
            </div>
            <div style="display: flex; align-items: start;">
              <div style="background: ${data.return_date ? '#27ae60' : '#95a5a6'}; width: 10px; height: 10px; border-radius: 50%; margin-right: 12px; margin-top: 5px;"></div>
              <div style="flex: 1;">
                <p style="color: #6c757d; font-size: 13px; margin: 0;">Return Date</p>
                <p style="color: #2c3e50; font-size: 15px; margin: 5px 0 0 0; font-weight: 500;">${data.return_date || 'Not yet returned'}</p>
              </div>
            </div>
          </div>
        </div>
        
        ${data.damage_status && data.damage_status !== 'N/A' ? `
          <div style="background: ${data.damage_status === 'No Damage' ? '#d4edda' : '#f8d7da'}; border: 1px solid ${data.damage_status === 'No Damage' ? '#c3e6cb' : '#f5c6cb'}; border-radius: 8px; padding: 15px; margin-top: 15px;">
            <p style="color: #6c757d; font-size: 13px; margin: 0 0 5px 0;">Damage Status</p>
            <p style="color: ${data.damage_status === 'No Damage' ? '#155724' : '#721c24'}; font-size: 15px; margin: 0; font-weight: 600;">${data.damage_status}</p>
          </div>
        ` : ''}
      `;
      document.getElementById('requestDetailsModal').style.display = 'block';
    })
    .catch(error => {
      alert('Failed to load details: ' + error);
    });
}

function reloadItemRequestsPanel(message) {
  var modal = document.getElementById('popupModal');
  var msg = document.getElementById('popupMessage');
  msg.textContent = message;
  modal.style.display = 'block';
  setTimeout(function () {
    window.location.href = window.location.pathname + "?panel=itemrequestsPanel";
  }, 1000);
}
</script>





          <!-- blotter complaint panel -->
          <div id="blotterComplaintPanel" class="panel-content">
            <h1>Blotter/Complaint</h1>

            <?php
            // Calculate statistics before displaying the form
            $stats_conn = new mysqli($servername, $username, $password, $database);
            if ($stats_conn->connect_error) {
              die("Connection failed: " . $stats_conn->connect_error);
            }

            // Build WHERE clause for stats (same as main query)
            $stats_where_clauses = [];
            if (isset($_GET['search_complainant']) && !empty(trim($_GET['search_complainant']))) {
              $stats_search = $stats_conn->real_escape_string($_GET['search_complainant']);
              $stats_where_clauses[] = "reported_by LIKE '%$stats_search%'";
            } else {
              $stats_date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'today';
              switch ($stats_date_filter) {
                case 'today':
                  $stats_where_clauses[] = "DATE(created_at) = CURDATE()";
                  break;
                case 'this_week':
                  $stats_where_clauses[] = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
                  break;
                case 'this_month':
                  $stats_where_clauses[] = "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
                  break;
                case 'this_year':
                  $stats_where_clauses[] = "YEAR(created_at) = YEAR(CURDATE())";
                  break;
                case 'all_time':
                default:
                  break;
              }
            }

            // Build stats SQL query
            $stats_sql = "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = 'hearing_scheduled' THEN 1 ELSE 0 END) as hearing_scheduled_count,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count,
                SUM(CASE WHEN status = 'closed_resolved' THEN 1 ELSE 0 END) as closed_resolved_count,
                SUM(CASE WHEN status = 'closed_unresolved' THEN 1 ELSE 0 END) as closed_unresolved_count
              FROM blottertbl";
            
            if (!empty($stats_where_clauses)) {
              $stats_sql .= " WHERE " . implode(" AND ", $stats_where_clauses);
            }

            $stats_result = $stats_conn->query($stats_sql);
            $stats = $stats_result->fetch_assoc();
            
            $total_records = $stats['total_records'] ?? 0;
            $active_count = $stats['active_count'] ?? 0;
            $hearing_scheduled_count = $stats['hearing_scheduled_count'] ?? 0;
            $open_total = $active_count + $hearing_scheduled_count;
            $closed_count = $stats['closed_count'] ?? 0;
            $closed_resolved_count = $stats['closed_resolved_count'] ?? 0;
            $closed_unresolved_count = $stats['closed_unresolved_count'] ?? 0;
            $closed_total = $closed_count + $closed_resolved_count + $closed_unresolved_count;
            
            $stats_conn->close();
            ?>

            <!-- Statistics Cards -->
            <div class="stat-card-container mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
              <!-- Card 1: Total Records -->
              <div class="stat-card scholarship-card" >
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-solid fa-database"></i></div>
                  <p>Total Records Displayed</p>
                </div>
                <h4><?php echo $total_records; ?></h4>
              </div>

              <!-- Card 2: Open Cases -->
              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-solid fa-folder-open"></i></div>
                  <p>Open Cases</p>
                </div>
                <h4><?php echo $open_total; ?></h4>
                <div style="font-size: 18px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 5px;">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span>Active:</span>
                    <span style="font-weight: 600;"><?php echo $active_count; ?></span>
                  </div>
                  <div style="display: flex; justify-content: space-between;">
                    <span>Hearing Scheduled: </span>
                    <span style="font-weight: 600;"><?php echo $hearing_scheduled_count; ?></span>
                  </div>
                </div>
              </div>

              <!-- Card 3: Closed Cases -->
              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-solid fa-folder-closed"></i></div>
                  <p>Closed Cases</p>
                </div>

                <!-- tight spacing: zero bottom margin on h4 so breakdown moves up -->
                <h4 style="margin: 0 0 1px 0;"><?php echo $closed_total; ?></h4>

                <!-- much closer: remove extra top padding, keep a thin divider -->
                <div style="font-size:18px; border-top:1px solid rgba(255,255,255,0.18); padding-top:4px; margin-top:0;">
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span>Closed:</span>
                    <span style="font-weight:600;"><?php echo $closed_count; ?></span>
                  </div>
                  <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span>Closed Unresolved:</span>
                    <span style="font-weight:600;"><?php echo $closed_unresolved_count; ?></span>
                  </div>
                  <div style="display:flex; justify-content:space-between;">
                    <span>Closed Resolved:</span>
                    <span style="font-weight:600;"><?php echo $closed_resolved_count; ?></span>
                  </div>
                </div>
              </div>
            </div>


            <form method="GET" action="" class="govdoc-search-form">
              <div class="govdoc-search-group">
                <input type="text" name="search_complainant" class="govdoc-search-input"
                  placeholder="Search by Complainant"
                  value="<?php echo isset($_GET['search_complainant']) ? htmlspecialchars($_GET['search_complainant']) : ''; ?>" />
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>

                <!-- NEW: Date Filter Dropdown -->
                <select name="date_filter" class="govdoc-status-filter" onchange="this.form.submit();">
                  <option value="today" <?php echo (!isset($_GET['date_filter']) || $_GET['date_filter'] === 'today') ? 'selected' : ''; ?>>Today</option>
                  <option value="this_week" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'this_week') ? 'selected' : ''; ?>>This Week</option>
                  <option value="this_month" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'this_month') ? 'selected' : ''; ?>>This Month</option>
                  <option value="this_year" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'this_year') ? 'selected' : ''; ?>>This Year</option>
                  <option value="all_time" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'all_time') ? 'selected' : ''; ?>>All Time</option>
                </select>


                <button class="add-user" type="button" onclick="openBlotterModal()">
                  <i class="fa-regular fa-user"></i> Add Blotter
                </button>
              </div>
            </form>

            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>NO</th>
                    <th>ID</th>
                    <th>REPORTED BY</th>
                    <th>DATE TIME</th>
                    <th>LOCATION</th>
                    <th>INCIDENT TYPE</th>
                    <th>STATUS</th>
                    <th>CREATED AT</th>
                    <th>CLOSED AT</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php


                  $conn = new mysqli($servername, $username, $password, $database);
                  if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                  }

                 // Build WHERE clause
                  $where_clauses = [];
                  // Search by complainant
                  if (isset($_GET['search_complainant']) && !empty(trim($_GET['search_complainant']))) {
                    $search = $conn->real_escape_string($_GET['search_complainant']);
                    $where_clauses[] = "reported_by LIKE '%$search%'";
                  } else {
                    // Only apply date filter if no search is performed
                    $date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'today';
                    switch ($date_filter) {
                      case 'today':
                        $where_clauses[] = "DATE(created_at) = CURDATE()";
                        break;
                      case 'this_week':
                        $where_clauses[] = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
                        break;
                      case 'this_month':
                        $where_clauses[] = "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
                        break;
                      case 'this_year':
                        $where_clauses[] = "YEAR(created_at) = YEAR(CURDATE())";
                        break;
                      case 'all_time':
                      default:
                        // No date filter
                        break;
                    }
                  }
                  // Build final SQL query
                  $sql = "SELECT * FROM blottertbl";
                  if (!empty($where_clauses)) {
                    $sql .= " WHERE " . implode(" AND ", $where_clauses);
                  }
                  $sql .= " ORDER BY created_at DESC";

                  $result = $conn->query($sql);
                  if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>
            <td>" . $counter . "</td>
            <td>" . htmlspecialchars($row['blotter_id']) . "</td>
            <td>" . htmlspecialchars($row['reported_by']) . "</td>
            <td>" . htmlspecialchars($row['datetime_of_incident']) . "</td>
            <td>" . htmlspecialchars($row['location_of_incident']) . "</td>
            <td>" . htmlspecialchars($row['incident_type']) . "</td>
            <td>" . htmlspecialchars($row['status']) . "</td>
            <td>" . htmlspecialchars(date('F d, Y h:i A', strtotime($row['created_at']))) . "</td>
            <td>" . ($row['closed_at'] ? htmlspecialchars(date('F d, Y h:i A', strtotime($row['closed_at']))) : 'N/A') . "</td>
            <td>";
                      echo "<button class='action-btn-2 view-blotter' data-id='" . htmlspecialchars($row['blotter_id']) . "' style='font-size:16px; background-color:#28a745; outline:none; border:none;'>
                    <i class='fas fa-eye'></i>
                </button> ";
                      if (!in_array($row['status'], ['closed', 'closed_resolved', 'closed_unresolved'], true)) {
                        echo "<button class='action-btn-2 update-blotter' data-id='" . htmlspecialchars($row['blotter_id']) . "' style='font-size:16px; background-color:#28a745; outline:none; border:none;'>
                    <i class='fas fa-edit'></i>
                </button>";
                      }
                      echo "</td></tr>";
                      $counter++;
                    }
                  } else {
                    echo "<tr><td colspan='10'>No blotter records found.</td></tr>";
                  }
                  // Singleton connection closed by PHP
                  ?>




                </tbody>
              </table>

            </div>

            <style>
              /* CSS to hide the up/down arrows for all browsers */
              input::-webkit-outer-spin-button,
              input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
              }

              input[type="number"] {
                appearance: textfield;
                -moz-appearance: textfield;
              }

              .action-btn-2.view-blotter:hover {
                background-color: #218838;
                transform: scale(1.1);
              }

              .action-btn-2.update-blotter:hover {
                background-color: #218838;
                transform: scale(1.1);
              }
            </style>
            <!-- Blotter Report Modal fill up form -->
            <div id="blotterModal" class="popup" style="display:none;">
              <div class="modal-popup" style="max-height: 90vh; overflow-y: auto;">
                <span class="close-btn" onclick="closeBlotterModal()">&times;</span>
                <div style="text-align: center;">
                  <img src="/BarangaySystem/BarangaySystem/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                    style="width: 70%; max-width: 120px; border-radius: 50%;" />
                </div>
                <h1 style="text-align:center;">Create Blotter Report</h1>
                <form id="blotterForm" method="POST" action="../Process/blotter/create_blotter.php" class="modal-form" enctype="multipart/form-data">
                  <!-- Complainant Details -->
                  <h3>Complainant Details</h3>
                  <div id="complainantContainer">
                    <div class="complainant-fields">
                      <h6>Full name of the Complainant</h6>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div class="form-group">
                          <label>Lastname</label>
                          <input type="text" name="complainant_lastname[]" placeholder="Lastname" required>
                        </div>
                        <div class="form-group">
                          <label>Firstname</label>
                          <input type="text" name="complainant_firstname[]" placeholder="Firstname" required>
                        </div>
                        <div class="form-group">
                          <label>Middlename</label>
                          <input type="text" name="complainant_middlename[]" placeholder="Middlename">
                        </div>
                      </div>
                      <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="complainant_address[]" required>
                      </div>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div class="form-group">
                          <label>Age</label>
                          <input type="number" name="complainant_age[]" maxlength="3" required>
                        </div>
                        <div class="form-group">
                          <label>Contact No</label>
                          <input type="number" min="0" name="complainant_contact_no[]" maxlength="11" required>
                        </div>
                        <div class="form-group">
                          <label>Email <span style="color: #888; font-style: italic;">(optional)</span></label>
                          <input type="email" name="complainant_email[]">
                        </div>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm remove-complainant-btn" style="margin-bottom:10px; display:none; margin-top: 10px;">Remove</button>
                      <hr>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" id="addComplainantBtn">+ Add Complainant</button>
                  <br>
                  <hr>

                  <h3>Blotter Details</h3>

                  <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div class="form-group">
                      <label>Date and Time of Incident</label>
                      <input type="datetime-local" name="incident_datetime" required>
                    </div>
                    <div class="form-group">
                      <label>Location of Incident</label>
                      <input type="text" name="incident_location" required>
                    </div>
                    <div class="form-group">
                      <label>Type of Incident</label>
                      <select name="incident_type" id="incident_type" required>
                        <option value="" disabled selected>Select Incident Type</option>
                        <option value="Theft">Theft</option>
                        <option value="Assault">Assault</option>
                        <option value="Vandalism">Vandalism</option>
                        <option value="Domestic Dispute">Domestic Dispute</option>
                        <option value="Noise Complaint">Noise Complaint</option>
                        <option value="Traffic Violation">Traffic Violation</option>
                        <option value="Grave Threat">Grave Threat</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                    <!-- Conditionally shown input for "Other" incident type -->
                    <div class="form-group" id="otherIncidentGroup" style="display:none; grid-column: span 3;">
                      <label>Please specify</label>
                      <input type="text" name="incident_type_other" id="incident_type_other" placeholder="Specify incident type">
                    </div>



                    <div class="form-group" style="grid-column: span 3;">
                      <label>Detailed Description of the Incident</label>
                      <textarea name="incident_description" rows="5" required></textarea>
                    </div>
                  </div>

                  <br>
                  <hr>


                  <!-- ...inside your blotterModal form... -->
                  <h3>Respondent Details</h3>
                  <div id="accusedContainer">
                    <div class="accused-fields">
                      <h6>Full name of the Respondent</h6>
                      <div class="form-grid" style="grid-template-columns:1fr 1.3fr 1fr .7fr; gap:10px;">


                        <div class="form-group">
                          <label>Lastname</label>
                          <input type="text" name="accused_lastname[]" placeholder="Lastname" required>
                        </div>

                        <div class="form-group">
                          <label>Firstname</label>
                          <input type="text" name="accused_firstname[]" placeholder="Firstname" required>
                        </div>


                        <div class="form-group">
                          <label>Middlename</label>
                          <input type="text" name="accused_middlename[]" placeholder="Middlename">
                        </div>


                        <div class="form-group">
                          <label>Alias <span style="color: #888; font-style: italic;">(optional)</span></label>
                          <input type="text" name="accused_alias[]" placeholder="Alias/Nickname">
                        </div>

                      </div>
                      <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="accused_address[]" required>
                      </div>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div class="form-group">
                          <label>Age</label>
                          <input type="number" name="accused_age[]" maxlength="3" required>
                        </div>
                        <div class="form-group">
                          <label>Contact No</label>
                          <input type="number" min="0" maxlength="11" name="accused_contact_no[]">
                        </div>
                        <div class="form-group">
                          <label>Email <span style="color: #888; font-style: italic;">(optional)</span></label>
                          <input type="email" name="accused_email[]">
                        </div>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm remove-accused-btn" style="margin-bottom:10px; display:none; margin-top: 10px;">Remove</button>
                      <hr>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" id="addAccusedBtn">+ Add Respondent</button>


                  <br>
                  <hr>

                  <h3>Witnesses Details</h3>
                  <div id="witnessesContainer">
                    <div class="witness-fields">
                      <h6>Full name of the Witness</h6>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">

                        <div class="form-group">
                          <label>Lastname</label>
                          <input type="text" name="witness_lastname[]" placeholder="Lastname">
                        </div>

                        <div class="form-group">
                          <label>Firstname</label>
                          <input type="text" name="witness_firstname[]" placeholder="Firstname">
                        </div>

                        <div class="form-group">
                          <label>Middlename</label>
                          <input type="text" name="witness_middlename[]" placeholder="Middlename">
                        </div>
                      </div>
                      <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="witness_address[]">
                      </div>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div class="form-group">
                          <label>Age</label>
                          <input type="number" name="witness_age[]">
                        </div>
                        <div class="form-group">
                          <label>Contact No</label>
                          <input type="number" min="0" maxlength="11" name="witness_contact_no[]">
                        </div>
                        <div class="form-group">
                          <label>Email <span style="color: #888; font-style: italic;">(optional)</span></label>
                          <input type="email" name="witness_email[]">
                        </div>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm remove-witness-btn" style="margin-bottom:10px; display:none; margin-top: 10px;">Remove</button>
                      <hr>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" id="addWitnessBtn">+ Add Witness</button>

                  <!-- Image Upload -->
                  <hr>
                  <div class="form-group">
                    <label>Upload Image(s) (.jpg, .jpeg, .png, .pdf, .doc, .docx) (Optional)</label>
                    <small class="text-muted">Hold ctrl or shift + click the images/files for multiple uploads.</small>

                    <input type="file" name="blotter_files[]" id="blotter_files" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" multiple>
                  </div>


                  <div style="margin-top:20px; text-align:right;">
                    <button type="submit" class="btn-save">Create</button>
                    <button type="button" class="btn-cancel" onclick="closeBlotterModal()">Close</button>
                  </div>
                </form>
              </div>

            </div>
          </div> <!--- end of modal -->




          <!-- View Blotter Modal -->
          <div id="viewBlotterModal" class="popup" style="display:none;">
            <div class="modal-popup" style="max-height: 90vh; overflow-y: auto;">
              <span class="close-btn" onclick="closeViewBlotterModal()">&times;</span>
              <div style="text-align: center;">
                <img src="/BarangaySystem/BarangaySystem/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h1 style="text-align:center;">Blotter Report Details</h1>
              <div style="font-size:16px; font-weight:bold; margin-bottom:10px;">
                Blotter ID: <span id="view_blotter_id"></span>
              </div>
              <div style="font-size:16px; font-weight:bold; margin-bottom:15px;">
                Officer on Duty: <span id="view_officer_on_duty"></span>
              </div>


              <form class="modal-form">
                <!-- Complainant -->
                <h3>Complainant Details</h3>
                <div id="view_complainantContainer"></div>

                <br>

                <!-- Blotter Details -->
                <h3>Blotter Details</h3>
                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                  <div class="form-group">
                    <label>Date and Time of Incident</label>
                    <input type="text" id="view_incident_datetime" readonly>
                  </div>
                  <div class="form-group">
                    <label>Location of Incident</label>
                    <input type="text" id="view_incident_location" readonly>
                  </div>
                  <div class="form-group">
                    <label>Type of Incident</label>
                    <input type="text" id="view_incident_type" readonly>
                  </div>
                  <div class="form-group" style="grid-column: span 3;">
                    <label>Detailed Description of the Incident</label>
                    <textarea id="view_incident_description" rows="5" readonly></textarea>
                  </div>
                </div>

                <hr>
                <!-- Respondent Details -->
                <h3>Respondent Details</h3>
                <div id="view_accusedContainer"></div>


                <!-- Witnesses Details -->
                <h3 id="witnessesDetailsHeader">Witnesses Details</h3>
                <div id="view_witnessesContainer"></div>



                <!-- NEW: Hearing History Section -->
                <h3 id="hearingHistoryHeader" style="display:none;">Hearing History</h3>
                <div id="hearingHistorySection" style="display:none;">
                  <!-- Hearings will be populated here dynamically -->
                </div>


                <!-- Hearing Details Section -->

                <h3 id="hearingDetailsHeader" style="display:none;">Hearing Details</h3>
                <div id="hearingDetailsSection" style="display:none;">
                  <label id="hearingNoLabel"></label>
                  <div>Date & Time: <span id="hearingDateTime"></span></div>
                  <div id="hearingParticipantsTableContainer"></div>
                  <input type="hidden" id="hearing_id" />
                  <!-- Mediator, Notes and Outcome inputs -->
                  <div style="margin-top:12px;">
                    <div class="form-group">
                      <label for="hearing_mediator" style="font-weight:600;">Mediator Name</label>
                      <input type="text" id="hearing_mediator" class="form-control" placeholder="Enter mediator name" required />
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                      <label for="hearing_notes" style="font-weight:600;">Hearing Notes</label>
                      <textarea id="hearing_notes" class="form-control" rows="7" placeholder="Enter hearing notes/comments" required></textarea>
                    </div>
                    <div class="form-group" style="margin-top:8px;">
                      <label for="hearing_outcome" style="font-weight:600;">Outcome</label>
                      <select id="hearing_outcome" class="form-control" required>
                        <option value="" disabled selected>Select Outcome</option>
                        <option value="agreement">Agreement</option>
                        <option value="no_agreement">No Agreement</option>
                      </select>
                    </div>

                    <div class="form-group" style="margin-top:8px;">
                      <label for="hearing_files" style="font-weight:600;">Upload Files (Optional)</label>
                      <input type="file" id="hearing_files" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" />
                      <small style="color: #666;">Allowed: .jpg, .jpeg, .png, .pdf, .doc, .docx (multiple files supported)</small>
                    </div>
                    <div style="text-align:right; margin-top:10px;">
                      <button id="saveHearingBtn" type="button" class="btn btn-success">Save Hearing</button>
                    </div>
                  </div>
                </div>
                <hr id="hearingDetailsHR" style="display:none;">


                <!-- Uploaded Files Section -->
                <h3>Uploaded Files</h3>
                <div class="scrollable-table-container">
                  <table class="styled-table" id="view_files_table">
                    <thead>
                      <tr>
                        <th>Thumbnail</th>
                        <th>File Name</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="view_filesContainer">
                      <!-- Files will be loaded here -->
                    </tbody>
                  </table>
                </div>


              </form>
              <div style="margin-top:20px; text-align:right; position:relative;">
                <!-- Action dropdown: shows Schedule Hearing and Close Blotter -->
                <div class="action-dropdown" style="display:inline-block; position:relative;">
                  <button id="actionDropdownBtn" class="btn btn-secondary">Action ▾</button>
                  <div id="actionDropdownMenu" class="action-dropdown-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:8px; box-shadow:0 4px 8px rgba(0,0,0,0.1); z-index:1000; min-width:180px;">
                    <button id="scheduleHearingBtn" class="btn btn-primary" style="display:block; width:100%; margin-bottom:8px;">Schedule Hearing</button>
                    <button type="button" id="closeBlotterBtn" class="btn btn-danger" style="display:block; width:100%;">Close Blotter</button>
                  </div>
                </div>
              </div>

              <!-- Toggle action dropdown menu on action button click in viewBlotterModal -->
              <script>
                (function() {
                  const actionBtn = document.getElementById('actionDropdownBtn');
                  const menu = document.getElementById('actionDropdownMenu');

                  if (actionBtn && menu) {
                    actionBtn.addEventListener('click', function(e) {
                      e.stopPropagation();
                      menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
                    });

                    // Close menu when clicking outside
                    document.addEventListener('click', function() {
                      if (menu.style.display === 'block') menu.style.display = 'none';
                    });

                    // Prevent clicks inside menu from closing it
                    menu.addEventListener('click', function(e) {
                      e.stopPropagation();
                    });
                  }
                })();
              </script>
            </div>



            <!-- Confirmation Popup -->
            <div id="closeBlotterConfirm" class="custom-confirm-overlay" style="display:none;">
              <div class="custom-confirm-box">
                <p>Are you sure you want to close this blotter?</p>
                <div class="custom-confirm-actions">
                  <button id="closeBlotterYes" class="custom-confirm-btn yes">Yes</button>
                  <button id="closeBlotterNo" class="custom-confirm-btn no">No</button>
                </div>
              </div>
            </div>

            <!-- Post-Hearing Outcome Modal -->
            <div id="postHearingModal" class="custom-confirm-overlay" style="display:none;">
              <div class="custom-confirm-box">
                <p>The hearing outcome is 'No Agreement'. What would you like to do?</p>
                <div class="custom-confirm-actions">
                  <button id="postHearingCloseBlotter" class="custom-confirm-btn yes">Close Blotter</button>
                  <button id="postHearingScheduleHearing" class="custom-confirm-btn no">Schedule Hearing</button>
                </div>
              </div>
            </div>










            <!-- JavaScript for modal functionality and dynamic fields -->
            <script>
              function openBlotterModal() {
                document.getElementById('blotterModal').style.display = 'flex';
              }

              function closeBlotterModal() {
                document.getElementById('blotterModal').style.display = 'none';
              }
              // Optional: close modal when clicking outside
              window.addEventListener('click', function(e) {
                const modal = document.getElementById('blotterModal');
                if (e.target === modal) modal.style.display = 'none';
              });

              document.getElementById('addWitnessBtn').addEventListener('click', function() {
                const container = document.getElementById('witnessesContainer');
                const witnessFields = container.querySelector('.witness-fields');
                const newFields = witnessFields.cloneNode(true);

                // Clear input values
                newFields.querySelectorAll('input').forEach(input => input.value = '');

                // Show remove button
                newFields.querySelector('.remove-witness-btn').style.display = 'inline-block';

                // Add event to remove button
                newFields.querySelector('.remove-witness-btn').onclick = function() {
                  newFields.remove();
                };

                container.appendChild(newFields);
              });

              // Show remove button only for additional witnesses
              document.querySelectorAll('.remove-witness-btn').forEach(btn => {
                btn.style.display = 'none';
              });

              document.getElementById('addAccusedBtn').addEventListener('click', function() {
                const container = document.getElementById('accusedContainer');
                const accusedFields = container.querySelector('.accused-fields');
                const newFields = accusedFields.cloneNode(true);

                // Clear input values
                newFields.querySelectorAll('input').forEach(input => input.value = '');

                // Show remove button
                newFields.querySelector('.remove-accused-btn').style.display = 'inline-block';

                // Add event to remove button
                newFields.querySelector('.remove-accused-btn').onclick = function() {
                  newFields.remove();
                };

                container.appendChild(newFields);
              });

              // Show remove button only for additional accused
              document.querySelectorAll('.remove-accused-btn').forEach(btn => {
                btn.style.display = 'none';
              });

              // NEW: Add event listener for adding complainants
              document.getElementById('addComplainantBtn').addEventListener('click', function() {
                const container = document.getElementById('complainantContainer');
                const complainantFields = container.querySelector('.complainant-fields');
                const newFields = complainantFields.cloneNode(true);

                // Clear input values
                newFields.querySelectorAll('input').forEach(input => input.value = '');

                // Show remove button
                newFields.querySelector('.remove-complainant-btn').style.display = 'inline-block';

                // Add event to remove button
                newFields.querySelector('.remove-complainant-btn').onclick = function() {
                  newFields.remove();
                };

                container.appendChild(newFields);
              });

              // Hide remove button for the first complainant
              document.querySelectorAll('.remove-complainant-btn').forEach(btn => {
                btn.style.display = 'none';
              });


              // Show/hide "Please specify" field based on incident type selection
              document.getElementById('incident_type').addEventListener('change', function() {
                const otherGroup = document.getElementById('otherIncidentGroup');
                if (this.value === 'Other') {
                  otherGroup.style.display = 'block';
                  document.getElementById('incident_type_other').required = true;
                } else {
                  otherGroup.style.display = 'none';
                  document.getElementById('incident_type_other').required = false;
                }
              });


              // NEW: Add form submission validation
              document.getElementById('blotterForm').addEventListener('submit', function(e) {
                const incidentType = document.getElementById('incident_type').value;

                // Check if incident_type is selected
                if (!incidentType) {
                  alert('Please select a type of incident.');
                  e.preventDefault(); // Prevent form submission
                  return;
                }

                // If "Other" is selected, ensure incident_type_other is filled
                if (incidentType === 'Other') {
                  const otherValue = document.getElementById('incident_type_other').value.trim();
                  if (!otherValue) {
                    alert('Please specify the incident type.');
                    e.preventDefault(); // Prevent form submission
                    return;
                  }
                }
              });
            </script>



          </div> <!-- end of blotter complaint panel-->

          <!-- Image Viewer Modal -->
          <div id="imageViewer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:10000; justify-content:center; align-items:center;">
            <img id="viewerImg" style="max-width:90%; max-height:90%;" />
            <span onclick="closeImageViewer()" style="position:absolute; top:10px; right:10px; color:white; font-size:30px; cursor:pointer;">&times;</span>
          </div>






          <!-- Update Blotter Modal -->
          <div id="updateBlotterModal" class="popup" style="display:none;">
            <div class="modal-popup" style="max-height: 90vh; overflow-y: auto;">
              <span class="close-btn" onclick="closeUpdateBlotterModal()">&times;</span>
              <div style="text-align: center;">
                <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4">
              </div>
              <h1 style="text-align:center;">Update Blotter Report</h1>
              <form id="updateBlotterForm" method="POST" action="../Process/blotter/update_blotter.php" class="modal-form" enctype="multipart/form-data">
                <!-- Fields will be filled by JS -->
                <div id="updateBlotterFields"></div>
                <div style="margin-top:20px; text-align:right;">
                  <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Hearing Modal -->
          <div id="scheduleHearingModal" class="popup" style="display:none;">
            <div class="modal-popup">
              <span class="close-btn" onclick="closeScheduleHearingModal()">&times;</span>
              <h2>Schedule Hearing</h2>
              <form id="scheduleHearingForm">
                <div class="form-group">
                  <label for="hearing_datetime">Date & Time of Hearing</label>
                  <input type="datetime-local" name="hearing_datetime" id="hearing_datetime" required>
                </div>
                <div style="margin-top:20px; text-align:right;">
                  <button type="submit" class="btn btn-success">Confirm</button>
                </div>
              </form>
            </div>
          </div>






          <!-- blottered individuals panel -->
          <div id="blotteredIndividualsPanel" class="panel-content">
            <h1>Blottered Individuals</h1>

            <form method="GET" action="" class="govdoc-search-form" style="display:flex;gap:10px;align-items:center;">
              <div class="govdoc-search-group">
                <input type="text" name="search_blottered" class="govdoc-search-input"
                  placeholder="Search by Name"
                  value="<?php echo isset($_GET['search_blottered']) ? htmlspecialchars($_GET['search_blottered']) : ''; ?>" />
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>
              </div>
              <!-- NEW: Date Filter Dropdown -->
              <select name="date_filter" class="govdoc-status-filter" onchange="this.form.submit();">
                <option value="today" <?php echo (!isset($_GET['date_filter']) || $_GET['date_filter'] === 'today') ? 'selected' : ''; ?>>Today</option>
                <option value="this_week" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'this_week') ? 'selected' : ''; ?>>This Week</option>
                <option value="this_month" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'this_month') ? 'selected' : ''; ?>>This Month</option>
                <option value="this_year" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'this_year') ? 'selected' : ''; ?>>This Year</option>
                <option value="all_time" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] === 'all_time') ? 'selected' : ''; ?>>All Time</option>
              </select>
              <label for="blotter_status" style="margin-left:10px;">Case Status:</label>
              <select name="blotter_status" class="govdoc-status-filter" id="blotter_status" onchange="this.form.submit()">
                <option value="active" <?php if (!isset($_GET['blotter_status']) || $_GET['blotter_status'] == 'active') echo 'selected'; ?>>Active</option>
                <option value="hearing_scheduled" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'hearing_scheduled') echo 'selected'; ?>>Hearing Scheduled</option>
                <option value="closed" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'closed') echo 'selected'; ?>>Closed</option>
                <option value="closed_resolved" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'closed_resolved') echo 'selected'; ?>>Closed Resolved</option>
                <option value="closed_unresolved" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'closed_unresolved') echo 'selected'; ?>>Closed Unresolved</option>
                <option value="all" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'all') echo 'selected'; ?>>All</option>
              </select>
            </form>
            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>NO</th>
                    <th>ID</th>
                    <th>BLOTTER ID</th>
                    <th>LAST NAME</th>
                    <th>FIRST NAME</th>
                    <th>MIDDLE NAME</th>
                    <th>AGE</th>
                    <th>ADDRESS</th>
                    <th>CONTACT NO</th>
                    <th>EMAIL</th>
                    <th>DATE CREATED</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php


                  $conn = new mysqli($servername, $username, $password, $database);
                  if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

                  // Build WHERE clauses
                  $where_clauses = ["bp.participant_type = 'accused'"];

                  // Search by name
                  if (isset($_GET['search_blottered']) && !empty(trim($_GET['search_blottered']))) {
                    $search = $conn->real_escape_string($_GET['search_blottered']);
                    $where_clauses[] = "(bp.lastname LIKE '%$search%' OR bp.firstname LIKE '%$search%')";
                  } else {
                    // Only apply date filter if no search is performed
                    $date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'today';
                    switch ($date_filter) {
                      case 'today':
                        $where_clauses[] = "DATE(b.created_at) = CURDATE()";
                        break;
                      case 'this_week':
                        $where_clauses[] = "YEARWEEK(b.created_at, 1) = YEARWEEK(CURDATE(), 1)";
                        break;
                      case 'this_month':
                        $where_clauses[] = "YEAR(b.created_at) = YEAR(CURDATE()) AND MONTH(b.created_at) = MONTH(CURDATE())";
                        break;
                      case 'this_year':
                        $where_clauses[] = "YEAR(b.created_at) = YEAR(CURDATE())";
                        break;
                      case 'all_time':
                      default:
                        // No date filter
                        break;
                    }

                    // Filter by status
                    $selected_status = isset($_GET['blotter_status']) ? $_GET['blotter_status'] : 'active';
                    switch ($selected_status) {
                      case 'all':
                        // No filter
                        break;
                      case 'active':
                        $where_clauses[] = "(b.status = 'active' OR b.status = 'hearing_scheduled')";
                        break;
                      case 'hearing_scheduled':
                        $where_clauses[] = "b.status = 'hearing_scheduled'";
                        break;
                      case 'closed':
                        $where_clauses[] = "(b.status = 'closed' OR b.status = 'closed_resolved' OR b.status = 'closed_unresolved')";
                        break;
                      case 'closed_resolved':
                        $where_clauses[] = "b.status = 'closed_resolved'";
                        break;
                      case 'closed_unresolved':
                        $where_clauses[] = "b.status = 'closed_unresolved'";
                        break;
                      default:
                        $where_clauses[] = "(b.status = 'active' OR b.status = 'hearing_scheduled')";
                    }

                  }

                  

                  $sql = "
                    SELECT 
                      bp.blotter_participant_id,
                      bp.lastname,
                      bp.firstname,
                      bp.middlename,
                      bp.age,
                      bp.address,
                      bp.contact_no,
                      bp.email,
                      bp.blotter_id,
                      b.created_at,
                      b.status
                    FROM blotter_participantstbl bp
                    JOIN blottertbl b ON bp.blotter_id = b.blotter_id
                    WHERE " . implode(" AND ", $where_clauses) . "
                    ORDER BY bp.blotter_participant_id DESC
                    ";

                  $result = $conn->query($sql);
                  if ($result && $result->num_rows > 0) {
                    $counter = 1;
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>
                      <td>" . $counter . "</td>
                      <td>" . htmlspecialchars($row['blotter_participant_id']) . "</td>
                      <td>" . htmlspecialchars($row['blotter_id']) . "</td>
                      <td>" . htmlspecialchars($row['lastname']) . "</td>
                      <td>" . htmlspecialchars($row['firstname']) . "</td>
                      <td>" . htmlspecialchars($row['middlename']) . "</td>
                      <td>" . htmlspecialchars($row['age']) . "</td>
                      <td>" . htmlspecialchars($row['address']) . "</td>
                      <td>" . htmlspecialchars($row['contact_no']) . "</td>
                      <td>" . htmlspecialchars($row['email']) . "</td>
                      <td>" . htmlspecialchars(date('F d, Y h:i A', strtotime($row['created_at']))) . "</td>
                      <td>" . htmlspecialchars($row['status']) . "</td>
                      <td>
                        <button class='action-btn-2 view-blottered-info' data-id='" . $row['blotter_participant_id'] . "' style='font-size:16px; background-color:#28a745; outline:none; border:none;' >
                          <i class='fas fa-eye'></i>
                        </button>
                      </td>
                      </tr>";
                      $counter++;
                    }
                  } else {
                    echo "<tr><td colspan='13'>No blottered individuals found.</td></tr>";
                  }
                  // Singleton connection closed by PHP
                  ?>
                </tbody>
              </table>
            </div>
          </div> <!-- end of blottered individuals panel-->

          <!-- Blottered Individual Modal -->
          <div id="viewBlotteredModal" class="popup" style="display:none;">
            <div class="modal-popup" style="max-height: 90vh; overflow-y: auto;">
              <span class="close-btn" onclick="closeViewBlotteredModal()">&times;</span>
              <div style="text-align: center;">
                <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h1 style="text-align:center;">Blottered Individual Details</h1>
              <br>
              <div style="font-size:16px; font-weight:bold; margin-bottom:10px;">
                Blotter ID: <span id="blottered_blotter_id"></span>
                <br>
                ID: <span id="blottered_participant_id"></span>
              </div>
              <form class="modal-form">
                <h3>Respondent Details</h3>
                <label style="font-weight:bold;">Full Name of Respondent</label>
                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                  <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" id="blottered_lastname" readonly>
                  </div>
                  <div class="form-group">
                    <label>First Name</label>
                    <input type="text" id="blottered_firstname" readonly>
                  </div>
                  <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" id="blottered_middlename" readonly>
                  </div>
                </div>
                <div class="form-group">
                  <label>Address</label>
                  <input type="text" id="blottered_address" readonly>
                </div>
                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                  <div class="form-group">
                    <label>Age</label>
                    <input type="number" id="blottered_age" readonly>
                  </div>
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" id="blottered_contact_no" readonly>
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="blottered_email" readonly>
                  </div>
                </div>
                <hr>
                <!-- Uploaded Files Section -->
                <h3>Uploaded Files</h3>
                <div class="scrollable-table-container">
                  <table class="styled-table" id="blottered_files_table">
                    <thead>
                      <tr>
                        <th>Thumbnail</th>
                        <th>File Name</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody id="blottered_filesContainer">
                      <!-- Files will be loaded here -->
                    </tbody>
                  </table>
                </div>
              </form>
            </div>
          </div>



    <div id="announcementPanel" class="panel-content">
            <h1>Announcements</h1>
            <p>Manage announcements with images and text for the community.</p>

            <?php
            // Display success messages
            if (isset($_GET['message'])) {
              if ($_GET['message'] === 'added') {
                echo "<div class='alert alert-success'>Announcement added successfully!</div>";
              } elseif ($_GET['message'] === 'updated') {
                echo "<div class='alert alert-success'>Announcement updated successfully!</div>";
              }
            }
            ?>

            <!-- Form to Add New Announcement or Edit Existing -->
            <div class="announcement-form">
              <h3><?php echo isset($_GET['edit']) ? 'Edit Announcement' : 'Add New Announcement'; ?></h3>
              <?php
              // Handle Form Submission for Add or Edit
              if (isset($_POST['saveAnnouncement']) || isset($_POST['updateAnnouncement'])) {
                // DEV: enable errors while debugging (remove in production)
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);

                $conn = new mysqli($servername, $username, $password, $database);
                if ($conn->connect_error) {
                  echo "<div class='alert alert-danger'>DB connection failed.</div>";
                } else {
                  $newsInfo = trim($_POST['NewsInfo'] ?? '');
                  $datedReported = date('Y-m-d H:i:s');
                  $isEdit = isset($_POST['updateAnnouncement']) && isset($_POST['announcementId']);
                  $announcementId = $isEdit ? intval($_POST['announcementId']) : null;

                  if ($newsInfo === '') {
                    echo "<div class='alert alert-danger'>Announcement text is required.</div>";
                  } else {
                    $newsImagePath = $isEdit ? null : null; // For edit, we'll handle separately
                    // upload dir relative to this file
                    $uploadDir = __DIR__ . '/../Assets/announcements/';
                    if (!is_dir($uploadDir))
                      mkdir($uploadDir, 0755, true);

                    if (!empty($_FILES['NewsImage']['name']) && $_FILES['NewsImage']['error'] === UPLOAD_ERR_OK) {
                      $tmp = $_FILES['NewsImage']['tmp_name'];
                      $finfo = finfo_open(FILEINFO_MIME_TYPE);
                      $mime = finfo_file($finfo, $tmp);
                      finfo_close($finfo);

                      $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                      if (!isset($allowed[$mime])) {
                        echo "<div class='alert alert-danger'>Invalid image type.</div>";
                      } elseif ($_FILES['NewsImage']['size'] > 5 * 1024 * 1024) {
                        echo "<div class='alert alert-danger'>Image too large (max 5MB).</div>";
                      } else {
                        $ext = $allowed[$mime];
                        $newFile = 'announcement_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $dest = $uploadDir . $newFile;
                        if (move_uploaded_file($tmp, $dest)) {
                          // store web relative path (adjust if your public path differs)
                          $newsImagePath = 'Assets/announcements/' . $newFile;
                        } else {
                          echo "<div class='alert alert-danger'>Failed to move uploaded file.</div>";
                        }
                      }
                    }

                    if ($isEdit) {
                      // For edit, fetch current image if no new upload
                      if (!$newsImagePath) {
                        $stmt = $conn->prepare("SELECT NewsImage FROM news WHERE id = ?");
                        $stmt->bind_param("i", $announcementId);
                        $stmt->execute();
                        $stmt->bind_result($currentImage);
                        $stmt->fetch();
                        $newsImagePath = $currentImage;
                        $stmt->close();
                      }
                      // Update query
                      $stmt = $conn->prepare("UPDATE news SET NewsInfo = ?, NewsImage = ? WHERE id = ?");
                      if ($stmt) {
                        $stmt->bind_param("ssi", $newsInfo, $newsImagePath, $announcementId);
                        if ($stmt->execute()) {
                          // JS redirect to keep user on the announcementPanel and force reload
                          echo "<script>window.location.href = " . json_encode($_SERVER['PHP_SELF'] . '?panel=announcementPanel&message=updated') . ";</script>";
                          exit;
                        } else {
                          echo "<div class='alert alert-danger'>DB error: " . htmlspecialchars($stmt->error) . "</div>";
                        }
                        $stmt->close();
                      } else {
                        echo "<div class='alert alert-danger'>Failed to prepare statement.</div>";
                      }
                    } else {
                      // Insert for new
                      $stmt = $conn->prepare("INSERT INTO news (NewsInfo, NewsImage, DatedReported) VALUES (?, ?, ?)");
                      if ($stmt) {
                        $stmt->bind_param("sss", $newsInfo, $newsImagePath, $datedReported);
                        if ($stmt->execute()) {
                          // JS redirect to keep user on the announcementPanel and force reload
                          echo "<script>window.location.href = " . json_encode($_SERVER['PHP_SELF'] . '?panel=announcementPanel&message=added') . ";</script>";
                          exit;
                        } else {
                          echo "<div class='alert alert-danger'>DB error: " . htmlspecialchars($stmt->error) . "</div>";
                        }
                        $stmt->close();
                      } else {
                        echo "<div class='alert alert-danger'>Failed to prepare statement.</div>";
                      }
                    }
                  }
                  // Singleton connection closed by PHP
                }
              }

              // If editing, fetch current data
              $editData = null;
              if (isset($_GET['edit'])) {
                $editId = intval($_GET['edit']);
                $conn = new mysqli($servername, $username, $password, $database);
                if (!$conn->connect_error) {
                  $stmt = $conn->prepare("SELECT NewsInfo, NewsImage FROM news WHERE id = ?");
                  $stmt->bind_param("i", $editId);
                  $stmt->execute();
                  $stmt->bind_result($editNewsInfo, $editNewsImage);
                  if ($stmt->fetch()) {
                    $editData = ['id' => $editId, 'NewsInfo' => $editNewsInfo, 'NewsImage' => $editNewsImage];
                  }
                  $stmt->close();
                  // Singleton connection closed by PHP
                }
              }
              ?>
              <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-8">
                  <label for="NewsInfo" class="form-label">Announcement Text</label>
                  <textarea name="NewsInfo" id="NewsInfo" class="form-control" rows="4"
                    placeholder="Enter the announcement text..." required><?php echo htmlspecialchars($editData['NewsInfo'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-4">
                  <label for="NewsImage" class="form-label">Upload Image (Optional)</label>
                  <input type="file" name="NewsImage" id="NewsImage" class="form-control" accept="image/*">
                  <small class="text-muted">Max 5MB. JPG, PNG, GIF only. Leave empty to keep current image.</small>
                  <?php if ($editData && $editData['NewsImage']): ?>
                    <div class="mt-2">
                      <small>Current Image:</small><br>
                      <img src="<?php echo htmlspecialchars('/Capstone/' . $editData['NewsImage']); ?>" alt="Current" style="max-width: 100px;">
                    </div>
                  <?php endif; ?>
                </div>
                <?php if ($editData): ?>
                  <input type="hidden" name="announcementId" value="<?php echo $editData['id']; ?>">
                <?php endif; ?>
                <div class="col-12 text-end">
                  <button type="submit" name="<?php echo $editData ? 'updateAnnouncement' : 'saveAnnouncement'; ?>" class="btn btn-primary">
                    <i class="bi bi-<?php echo $editData ? 'pencil' : 'plus-circle'; ?>"></i> <?php echo $editData ? 'Update Announcement' : 'Upload Announcement'; ?>
                  </button>
                  <?php if ($editData): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?panel=announcementPanel" class="btn btn-secondary">Cancel</a>
                  <?php endif; ?>
                </div>
              </form>
            </div>
            <div class="announcement-list">
              <h3>Recent Announcements</h3>
              <?php
              $conn = new mysqli('localhost', 'root', '', 'barangayDb');
              if ($conn->connect_error) {
                echo "<div class='alert alert-danger'>Can't load announcements.</div>";
              } else {
                $res = $conn->query("SELECT id, NewsInfo, NewsImage, DatedReported FROM news ORDER BY DatedReported DESC LIMIT 50");
                if ($res && $res->num_rows) {
                  echo "<div class='row g-4 mt-2'>";
                  while ($r = $res->fetch_assoc()) {
                    $img = $r['NewsImage'] ? htmlspecialchars('/Capstone/' . $r['NewsImage']) : null;
                    echo "<div class='col-md-4 col-sm-6'>
                          <div class='announcement-card card'>
                            " . ($img ? "<img src='" . $img . "' alt='Announcement image' />" : "") . "
                            <div class='card-body'>
                              <div class='announcement-text'>" . nl2br(htmlspecialchars($r['NewsInfo'])) . "</div>
                              <div class='announcement-date'>" . htmlspecialchars($r['DatedReported']) . "</div>
                              <div class='mt-2'>
                                <a href='" . $_SERVER['PHP_SELF'] . "?panel=announcementPanel&edit=" . $r['id'] . "' class='btn btn-sm btn-success'>Edit</a>
                              </div>
                            </div>
                          </div>
                        </div>";
                  }
                  echo "</div>";
                } else {
                  echo "<div class='alert alert-info mt-3'>No announcements yet.</div>";
                }
                // Singleton connection closed by PHP
              }
              ?>
            </div>
          </div> <!-- end of announcement panel-->








          <div id="reportsPanel" class="panel-content">
            <h1>Reports</h1>
            <p>Generate and view system reports for residents, documents, blotters, and more.</p>

            <!-- Filters Form -->
            <form method="GET" class="mb-4 search-form">
              <input type="hidden" name="panel" value="reportsPanel">
              <div class="row g-3 align-items-end">
                <div class="col-md-3">
                  <label for="reportType" class="form-label">Report Type</label>
                  <select name="report_type" id="reportType" class="form-select" required>
                    <option value="">Select a report</option>
                    <?php
                    $reportOptions = [
                      // 'residents' => 'Resident Demographics',
                      'documents' => 'Document Requests',
                      'business' => 'Business Permits',
                      'unemployment' => 'Unemployment Certificates',
                      'guardianship' => 'Guardianship Documents',
                      'items' => 'Item Requests',
                        'blotters' => 'Blotter/Complaints',
                        'collections' => 'Financial Collections',
                        'activity logs' => 'User Activity Logs',
                        'resident logs' => 'Resident Activity Logs'

                    ];
                    $selected = $_GET['report_type'] ?? '';
                    foreach ($reportOptions as $value => $label) {
                      $isSelected = $selected === $value ? 'selected' : '';
                      echo "<option value='$value' $isSelected>$label</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="col-md-2">
                  <label for="startDate" class="form-label">Start Date</label>
                  <input type="date" name="start_date" id="startDate" class="form-control"
                    value="<?= htmlspecialchars($_GET['start_date'] ?? date('Y-m-01')) ?>">
                </div>

                <div class="col-md-2">
                  <label for="endDate" class="form-label">End Date</label>
                  <input type="date" name="end_date" id="endDate" class="form-control"
                    value="<?= htmlspecialchars($_GET['end_date'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-md-2">
                  <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>

                <div class="col-md-3 d-flex gap-2">
                  <button type="button" id="exportCsv" class="btn btn-secondary flex-fill" style="display:none;">Export
                    CSV</button>
                  <button type="button" id="exportPdf" class="btn btn-secondary flex-fill" style="display:none;">Export
                    PDF</button>
                </div>
              </div>
            </form>

            <?php
            // Database connection
            

            $reportType = $_GET['report_type'] ?? '';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            if (empty($reportType)) {
              echo '<div class="alert alert-info">Please select a report type and date range to generate a report.</div>';
            } else {
              echo '<div id="reportContent">';
              switch ($reportType) {

                // RESIDENT DEMOGRAPHICS REPORT
            
                case 'residents':
                  echo '<h3>Resident Demographics Report</h3>';
                  $stmt = $connection->prepare("SELECT Gender, COUNT(*) as count FROM userloginfo WHERE Birthdate BETWEEN ? AND ? GROUP BY Gender");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                        <div class="stat-left"><i class="fas fa-user"></i><p>' . htmlspecialchars($row['Gender']) . '</p></div>
                        <h4>' . $row['count'] . '</h4>
                      </div>';
                    }
                    echo '</div>';

                    $stmt = $connection->prepare("SELECT UserID, CONCAT(Firstname, ' ', Lastname) AS Name, Gender 
                                          FROM userloginfo WHERE Birthdate BETWEEN ? AND ?");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                    <table class="styled-table">
                        <thead><tr><th>ID</th><th>Name</th><th>Gender</th></tr></thead><tbody>';
                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>
                        <td>' . htmlspecialchars($r['UserID']) . '</td>
                        <td>' . htmlspecialchars($r['Name']) . '</td>
                        <td>' . htmlspecialchars($r['Gender']) . '</td>
                      </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No records found for this date range.</div>';
                  }
                  break;


                // DOCUMENT REQUESTS REPORT
            
                case 'documents':
                  echo '<h3>Document Requests Report</h3>';
                  $stmt = $connection->prepare("SELECT RequestStatus, COUNT(*) as count 
                                      FROM docsreqtbl 
                                      WHERE DateRequested BETWEEN ? AND ? 
                                      GROUP BY RequestStatus");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                        <div class="stat-left"><i class="fas fa-file-alt"></i><p>' . htmlspecialchars($row['RequestStatus']) . '</p></div>
                        <h4>' . $row['count'] . '</h4>
                      </div>';
                    }
                    echo '</div>';

                    $stmt = $connection->prepare("SELECT ReqID, CONCAT(Firstname, ' ', Lastname) AS Name, DocuType, 
                                                 Address, DateRequested, RequestStatus, ReleasedBy
                                          FROM docsreqtbl 
                                          WHERE DateRequested BETWEEN ? AND ? 
                                          ORDER BY DateRequested DESC");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Resident Name</th>
                                <th>Document Type</th>
                                <th>Resident Address</th>
                                <th>Date Requested</th>
                                <th>Request Status</th>
                                <th>Released By</th>
                            </tr>
                        </thead>
                        <tbody>';
                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>
                        <td>' . htmlspecialchars($r['ReqID']) . '</td>
                        <td>' . htmlspecialchars($r['Name']) . '</td>
                        <td>' . htmlspecialchars($r['DocuType']) . '</td>
                        <td>' . htmlspecialchars($r['Address']) . '</td>
                        <td>' . htmlspecialchars($r['DateRequested']) . '</td>
                        <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
                        <td>' . htmlspecialchars($r['ReleasedBy']) . '</td>
                      </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No document requests found for this date range.</div>';
                  }
                  break;

                // --------------------------------------g
                // BUSINESS PERMITS REPORT
                // --------------------------------------
                case 'business':
                  echo '<h3>Business Permits Report</h3>';

                  $stmt = $connection->prepare("SELECT RequestStatus, COUNT(*) as count 
                                      FROM businesstbl 
                                      WHERE RequestedDate BETWEEN ? AND ? 
                                      GROUP BY RequestStatus");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                        <div class="stat-left"><i class="fas fa-briefcase"></i><p>' . htmlspecialchars($row['RequestStatus']) . '</p></div>
                        <h4>' . $row['count'] . '</h4>
                      </div>';
                    }
                    echo '</div>';

                    $stmt = $connection->prepare("SELECT BsnssID, BusinessName, OwnerName, RequestType, 
                                                 BusinessLoc, RequestedDate, RequestStatus, ReleasedBy 
                                          FROM businesstbl 
                                          WHERE RequestedDate BETWEEN ? AND ? 
                                          ORDER BY RequestedDate DESC");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Business ID</th>
                                <th>Business Name</th>
                                <th>Business owner</th>
                                <th>Document Type</th>
                                <th>Business Address</th>
                                <th>Date Applied</th>
                                <th>Request Status</th>
                                <th>Released By</th>
                            </tr>
                        </thead>
                        <tbody>';
                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>
                        <td>' . htmlspecialchars($r['BsnssID']) . '</td>
                        <td>' . htmlspecialchars($r['BusinessName']) . '</td>
                        <td>' . htmlspecialchars($r['OwnerName']) . '</td>
                        <td>' . htmlspecialchars($r['RequestType']) . '</td>
                        <td>' . htmlspecialchars($r['BusinessLoc']) . '</td>
                        <td>' . htmlspecialchars($r['RequestedDate']) . '</td>
                        <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
                        <td>' . htmlspecialchars($r['ReleasedBy']) . '</td>
                      </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No business permit records found for this date range.</div>';
                  }
                  break;

                // --------------------------------------
                // UNEMPLOYMENT CERTIFICATES REPORT (NEW)
                // --------------------------------------
                case 'unemployment':
                  echo '<h3>Unemployment Certificates Report</h3>';

                  $stmt = $connection->prepare("SELECT RequestStatus, COUNT(*) as count 
                                      FROM unemploymenttbl 
                                      WHERE request_date BETWEEN ? AND ? 
                                      GROUP BY RequestStatus");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                        <div class="stat-left"><i class="fas fa-user-slash"></i><p>' . htmlspecialchars($row['RequestStatus']) . '</p></div>
                        <h4>' . $row['count'] . '</h4>
                      </div>';
                    }
                    echo '</div>';

                    $stmt = $connection->prepare("SELECT id, refno, fullname, age, purpose, 
                                                 request_date, RequestStatus, ReleasedBy
                                          FROM unemploymenttbl 
                                          WHERE request_date BETWEEN ? AND ? 
                                          ORDER BY request_date DESC");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Reference No.</th>
                                <th>Full Name</th>
                                <th>Age</th>
                            
                                <th>Purpose</th>
                                <th>Request Date</th>
                                <th>Request Status</th>
                                <th>Released By</th>
                          
                            </tr>
                        </thead>
                        <tbody>';
                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>
                        <td>' . htmlspecialchars($r['id']) . '</td>
                        <td>' . htmlspecialchars($r['refno']) . '</td>
                        <td>' . htmlspecialchars($r['fullname']) . '</td>
                        <td>' . htmlspecialchars($r['age']) . '</td>
                        
                        <td>' . htmlspecialchars($r['purpose']) . '</td>
                        <td>' . htmlspecialchars($r['request_date']) . '</td>
                        <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
                        <td>' . htmlspecialchars($r['ReleasedBy']) . '</td>
                        
                      </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No unemployment certificate records found for this date range.</div>';
                  }
                  break;
                case 'guardianship':
                  echo '<h3>Guardianship Documents Report</h3>';

                  // Summary cards
                  $stmt = $connection->prepare("SELECT RequestStatus, COUNT(*) as count 
                                  FROM guardianshiptbl 
                                  WHERE request_date BETWEEN ? AND ? 
                                  GROUP BY RequestStatus");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                    <div class="stat-left"><i class="fas fa-user-shield"></i><p>' . htmlspecialchars($row['RequestStatus']) . '</p></div>
                    <h4>' . $row['count'] . '</h4>
                  </div>';
                    }
                    echo '</div>';

                    // Full details table
                    $stmt = $connection->prepare("SELECT id, refno, request_type, child_name, child_age,  applicant_name, 
                                           request_date, RequestStatus, ReleasedBy
                                      FROM guardianshiptbl 
                                      WHERE request_date BETWEEN ? AND ? 
                                      ORDER BY request_date DESC");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reference No.</th>
                            <th>Request Type</th>
                            <th>Child Name</th>
                            <th>Applicant Name</th>
                            <th>Request Date</th>
                            <th>Request Status</th>
                            <th>Released By</th>
                     
                        </tr>
                    </thead>
                    <tbody>';
                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>
                    <td>' . htmlspecialchars($r['id']) . '</td>
                    <td>' . htmlspecialchars($r['refno']) . '</td>
                    <td>' . htmlspecialchars($r['request_type']) . '</td>
                    <td>' . htmlspecialchars($r['child_name']) . '</td>              
                    <td>' . htmlspecialchars($r['applicant_name']) . '</td>
                    <td>' . htmlspecialchars($r['request_date']) . '</td>
                    <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
                    <td>' . htmlspecialchars($r['ReleasedBy']) . '</td>
             
                  </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No guardianship records found for this date range.</div>';
                  }
                  break;
                // --------------------------------------
// ITEM REQUESTS REPORT
// --------------------------------------
                case 'items':
                  echo '<h3>Item Requests Report</h3>';

                  // Summary cards: count per status
                  $stmt = $connection->prepare("SELECT RequestStatus, COUNT(*) as count 
                                  FROM tblitemrequest 
                                  WHERE date BETWEEN ? AND ? 
                                  GROUP BY RequestStatus");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                    <div class="stat-left"><i class="fas fa-box"></i><p>' . htmlspecialchars($row['RequestStatus']) . '</p></div>
                    <h4>' . $row['count'] . '</h4>
                  </div>';
                    }
                    echo '</div>';

                    // Full details table
                    $stmt = $connection->prepare("SELECT id, name, item, quantity, event_datetime, date, RequestStatus, damage_status
                                      FROM tblitemrequest 
                                      WHERE date BETWEEN ? AND ? 
                                      ORDER BY id ");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Resident Name</th>
                            <th>Item Request</th>
                            <th>Item Quantity</th>
                            <th>Event Date/Time</th>
                            <th>Date Requested</th>
                            <th>Request Status</th>
                            <th>Item condition</th>
                            
                 
                        </tr>
                    </thead>
                    <tbody>';
                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>
                    <td>' . htmlspecialchars($r['id']) . '</td>
                    <td>' . htmlspecialchars($r['name']) . '</td>
                    <td>' . htmlspecialchars($r['item']) . '</td>
                    <td>' . htmlspecialchars($r['quantity']) . '</td>
                    <td>' . htmlspecialchars($r['event_datetime']) . '</td>
                    <td>' . htmlspecialchars($r['date']) . '</td>
                    <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
                    <td>' . htmlspecialchars($r['damage_status']) . '</td>
               
                  </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No item requests found for this date range.</div>';
                  }
                  break;

                case 'resident logs':
                  echo '<h3>Resident Activity Logs</h3>';

                  // Query user_activity_logs between selected dates
                  $stmt = $connection->prepare("SELECT activity, category, details, ip_address, `timestamp` 
                                      FROM user_activity_logs 
                                      WHERE `timestamp` BETWEEN ? AND ? 
                                      ORDER BY `timestamp` DESC");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $details = $stmt->get_result();

                  if ($details && $details->num_rows > 0) {
                    echo '<div class="scrollable-table-container">'
                      . '<table class="styled-table">'
                      . '<thead>
                      <tr>
                      <th>Action</th>
                      <th>Category</th>
                      <th>Details</th>
                      <th>IP</th>
                      <th>Timestamp</th>
                      </tr>
                      </thead>
                      <tbody>';

                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>'
                        . '<td>' . htmlspecialchars($r['activity']) . '</td>'
                        . '<td>' . htmlspecialchars($r['category']) . '</td>'
                        . '<td>' . htmlspecialchars($r['details']) . '</td>'
                        . '<td>' . htmlspecialchars($r['ip_address']) . '</td>'
                        . '<td>' . htmlspecialchars($r['timestamp']) . '</td>'
                        . '</tr>';
                    }

                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No resident activity logs found for this date range.</div>';
                  }
                  $stmt->close();
                  break;

                case 'activity logs':
                  echo '<h3>User Activity Logs</h3>';

                  // Query audittrail between selected dates
                  $stmt = $connection->prepare("SELECT AuditID, username, TimeIn, TimeOut 
                                      FROM audittrail 
                                      WHERE TimeIn BETWEEN ? AND ? 
                                      ORDER BY TimeIn DESC");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $details = $stmt->get_result();

                  if ($details && $details->num_rows > 0) {
                    echo '<div class="scrollable-table-container">'
                      . '<table class="styled-table">'
                      . '<thead><tr><th>ID</th><th>USERNAME</th><th>TIME IN</th><th>TIME OUT</th></tr></thead><tbody>';

                    while ($r = $details->fetch_assoc()) {
                      echo '<tr>'
                        . '<td>' . htmlspecialchars($r['AuditID']) . '</td>'
                        . '<td>' . htmlspecialchars(strtoupper($r['username'])) . '</td>'
                        . '<td>' . htmlspecialchars($r['TimeIn']) . '</td>'
                        . '<td>' . htmlspecialchars($r['TimeOut']) . '</td>'
                        . '</tr>';
                    }

                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No activity logs found for this date range.</div>';
                  }
                  $stmt->close();
                  break;

                   case 'blotters':
                  echo '<h3>Blotter/Complaints Report</h3>';

                  // Summary cards: count per status
                  $stmt = $connection->prepare("SELECT status, COUNT(*) as count 
                                  FROM blottertbl 
                                  WHERE created_at BETWEEN ? AND ? 
                                  GROUP BY status");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      $status = $row['status'] ?: 'Pending';
                      echo '<div class="stat-card">
                    <div class="stat-left"><i class="fas fa-exclamation-triangle"></i><p>' . htmlspecialchars($status) . '</p></div>
                    <h4>' . $row['count'] . '</h4>
                  </div>';
                    }
                    echo '</div>';

                    // Full details table
                    $stmt = $connection->prepare("SELECT blotter_id, reported_by, datetime_of_incident, 
                                           location_of_incident, incident_type, created_at, 
                                           closed_at, status
                                      FROM blottertbl 
                                      WHERE created_at BETWEEN ? AND ? 
                                      ORDER BY created_at DESC");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Blotter ID</th>
                            <th>Reported By</th>
                            <th>Incident Date/Time</th>
                            <th>Location</th>
                            <th>Incident Type</th>
                            <th>Date Filed</th>
                            <th>Date Closed</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
                    while ($r = $details->fetch_assoc()) {
                      $status = $r['status'] ?: 'Pending';
                      $closedDate = $r['closed_at'] ?: 'N/A';
                      echo '<tr>
                    <td>' . htmlspecialchars($r['blotter_id']) . '</td>
                    <td>' . htmlspecialchars($r['reported_by']) . '</td>
                    <td>' . htmlspecialchars($r['datetime_of_incident']) . '</td>
                    <td>' . htmlspecialchars($r['location_of_incident']) . '</td>
                    <td>' . htmlspecialchars($r['incident_type']) . '</td>
                    <td>' . htmlspecialchars($r['created_at']) . '</td>
                    <td>' . htmlspecialchars($closedDate) . '</td>
                    <td>' . htmlspecialchars($status) . '</td>
                  </tr>';
                    }
                    echo '</tbody></table></div>';
                  } else {
                    echo '<div class="alert alert-warning">No blotter/complaint records found for this date range.</div>';
                  }
                  break;



                // --------------------------------------
// COLLECTIONS / PAYMENTS REPORT
// --------------------------------------
                case 'collections':
                  echo '<h3>Collections Report</h3>';

                  // Summary: total count per request status
                  $stmt = $connection->prepare("SELECT RequestStatus, COUNT(*) as count 
                                  FROM tblpayment 
                                  WHERE date BETWEEN ? AND ? 
                                  GROUP BY RequestStatus");
                  $stmt->bind_param("ss", $startDate, $endDate);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    echo '<div class="stat-card-container mb-3">';
                    while ($row = $result->fetch_assoc()) {
                      echo '<div class="stat-card">
                    <div class="stat-left"><i class="fas fa-coins"></i><p>' . htmlspecialchars($row['RequestStatus']) . '</p></div>
                    <h4>' . $row['count'] . '</h4>
                  </div>';
                    }
                    echo '</div>';

                    // Detailed table
                    $stmt = $connection->prepare("SELECT CollectionID, refno, name, type, date, amount, RequestStatus 
                                      FROM tblpayment 
                                      WHERE date BETWEEN ? AND ? 
                                      ORDER BY CollectionID ");
                    $stmt->bind_param("ss", $startDate, $endDate);
                    $stmt->execute();
                    $details = $stmt->get_result();

                    echo '<div class="scrollable-table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Collection ID</th>
                            <th>Reference No.</th>
                            <th>Payer Name</th>
                            <th>Type</th>
                            
                            <th>Date</th> 
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
                    $totalAmount = 0;
                    while ($r = $details->fetch_assoc()) {
                      $totalAmount += floatval($r['amount']);
                      echo '<tr>
                    <td>' . htmlspecialchars($r['CollectionID']) . '</td>
                    <td>' . htmlspecialchars($r['refno']) . '</td>
                    <td>' . htmlspecialchars($r['name']) . '</td>
                    <td>' . htmlspecialchars($r['type']) . '</td>
                 
                    <td>' . htmlspecialchars($r['date']) . '</td>
                    
                    <td>' . number_format($r['amount'], 2) . '</td>
                    <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
                  </tr>';
                    }
                    echo '</tbody>
              <tfoot>
                <tr style="font-weight:bold; background:#f0f0f0;">
                    <td colspan="5" style="text-align:right;">Total Collections:</td>
                    <td>' . number_format($totalAmount, 2) . '</td>
                    <td colspan="3"></td>
                </tr>
              </tfoot>
            </table>
          </div>';
                  } else {
                    echo '<div class="alert alert-warning">No collection/payment records found for this date range.</div>';
                  }
                  break;

                  

                default:
                  echo '<div class="alert alert-warning">This report type is not implemented yet.</div>';
              }


              echo '</div>';
            }
            $connection->close();
            ?>

            <script>
              // CSV Export
              document.getElementById('exportCsv').addEventListener('click', () => {
                const table = document.querySelector('#reportContent table');
                if (!table) return;
                let csv = Array.from(table.querySelectorAll('tr')).map(row =>
                  Array.from(row.querySelectorAll('th,td')).map(col => `"${col.innerText}"`).join(',')
                ).join('\n');
                const blob = new Blob([csv], { type: 'text/csv' });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'report.csv';
                a.click();
              });

              // PDF Export (requires jsPDF + autoTable)
              document.getElementById('exportPdf').addEventListener('click', async () => {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF('l', 'pt', 'a4');
                const title = document.querySelector('#reportContent h3')?.innerText || 'Report';

                // Load logo
                const logo = new Image();
                logo.src = '/Capstone/Assets/sampaguitalogo.png';
                await new Promise((resolve) => {
                  logo.onload = resolve;
                  logo.onerror = resolve;
                });

                const pageWidth = doc.internal.pageSize.getWidth();
                const pageHeight = doc.internal.pageSize.getHeight();

                // --- Header Design ---
                const headerHeight = 80;
                doc.setFillColor(212, 255, 221); // light background
                doc.rect(0, 0, pageWidth, headerHeight, 'F');
                doc.addImage(logo, 'PNG', 30, 15, 50, 50);
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(18);
                doc.text(title, 100, 45);
                doc.setFontSize(11);
                doc.setTextColor(100);
                doc.text(`Generated on: ${new Date().toLocaleString()}`, 100, 65);

                // --- Optional Watermark ---
                const watermarkSize = 200;
                doc.setGState(new doc.GState({ opacity: 0.1 }));
                doc.addImage(logo, 'PNG', (pageWidth - watermarkSize) / 2, (pageHeight - watermarkSize) / 2, watermarkSize, watermarkSize);
                doc.setGState(new doc.GState({ opacity: 1 }));

                // --- Table ---
                const table = document.querySelector('#reportContent table');
                if (!table) return;

                const rows = Array.from(table.querySelectorAll('tr')).map(tr =>
                  Array.from(tr.querySelectorAll('th, td')).map(td => td.innerText)
                );

                doc.autoTable({
                  head: [rows[0]],
                  body: rows.slice(1),
                  startY: headerHeight + 20,
                  styles: {
                    font: 'helvetica',
                    fontSize: 10,
                    cellPadding: 6,
                    lineColor: [200, 200, 200],
                    lineWidth: 0.5,
                  },
                  headStyles: {
                    fillColor: [20, 105, 29],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                  },
                  alternateRowStyles: { fillColor: [245, 245, 245] },
                  theme: 'grid',
                  margin: { left: 30, right: 30 },
                  didDrawPage: (data) => {
                    // Footer
                    const pageCount = doc.internal.getNumberOfPages();
                    doc.setFontSize(10);
                    doc.setTextColor(120);
                    doc.text(`Page ${data.pageNumber} of ${pageCount}`, pageWidth - 80, pageHeight - 20);
                  },
                });

                // --- Save PDF ---
                doc.save(`${title.replace(/\s+/g, '_')}.pdf`);
              });

              // Show export buttons if report table exists
              if (document.querySelector('#reportContent table')) {
                document.getElementById('exportCsv').style.display = 'block';
                document.getElementById('exportPdf').style.display = 'block';
              }
            </script>
            <script>
              // Animate stat cards on scroll
              const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                  if (entry.isIntersecting) entry.target.classList.add('visible');
                });
              }, { threshold: 0.2 });

              document.querySelectorAll('.stat-card').forEach(card => observer.observe(card));

              // Fade-in table on load
              window.addEventListener('load', () => {
                const table = document.querySelector('.scrollable-table-container');
                if (table) {
                  table.style.opacity = 0;
                  setTimeout(() => table.style.opacity = 1, 300);
                }
              });

              // Add icons to export buttons
              document.addEventListener('DOMContentLoaded', () => {
                const csvBtn = document.getElementById('exportCsv');
                const pdfBtn = document.getElementById('exportPdf');

                csvBtn.innerHTML = '<i class="fas fa-file-csv"></i> Export CSV';
                pdfBtn.innerHTML = '<i class="fas fa-file-pdf"></i> Export PDF';
              });
            </script>

          </div>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
          <script
            src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>



          <div id="auditTrailPanel" class="panel-content">
            <h3>Activity Logs</h3>
            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>USERNAME</th>
                    <th>TIME IN</th>
                    <th>TIME OUT</th>
                  </tr>
                </thead>
                <tbody>
                  <?php


                  $connection = new mysqli($servername, $username, $password, $database);

                  if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                  }

                  if (isset($_GET['search_username']) && !empty(trim($_GET['search_username']))) {
                    $search = $connection->real_escape_string($_GET['search_username']);
                    $sql = "SELECT AuditID, username, TimeIn, TimeOut FROM audittrail WHERE username LIKE '%$search%' ORDER BY TimeIn DESC";
                  } else {
                    $sql = "SELECT AuditID, username, TimeIn, TimeOut FROM audittrail ORDER BY TimeIn DESC";
                  }

                  $result = $connection->query($sql);

                  if (!$result) {
                    die("Invalid query: " . $connection->error);
                  }

                  while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                  <td>" . $row["AuditID"] . "</td>
                  <td>" . strtoupper($row["username"]) . "</td>
                  <td>" . $row["TimeIn"] . "</td>
                  <td>" . $row["TimeOut"] . "</td>
                </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>



            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

            <script>
              const ageLabels = <?php echo json_encode($ageLabels); ?>;
              const ageCounts = <?php echo json_encode($ageCounts); ?>;

              const civilLabels = <?php echo json_encode($civilLabels); ?>;
              const civilCounts = <?php echo json_encode($civilCounts); ?>;

              const DocutypeLabels = <?php echo json_encode($DocutypeLabels); ?>;
              const DocutypeCounts = <?php echo json_encode($DocutypeCounts); ?>;

              const ctx = document.getElementById('ageChart').getContext('2d');

              new Chart(ctx, {
                type: 'bar',
                data: {
                  labels: ageLabels,
                  datasets: [{
                    label: 'Residents',
                    data: ageCounts,
                    fill: true,
                    borderColor: '#2e7d32', // dark green line
                    backgroundColor: 'rgba(66, 209, 73, 0.2)', // soft green fill
                    pointBackgroundColor: '#42d149',
                    pointBorderColor: '#1c552b',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#42d149',
                    tension: 0.3, // smooth curves
                    borderWidth: 2,
                  }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    title: {
                      display: true,
                      font: {
                        size: 18,
                        weight: 'bold'
                      }
                    },
                    legend: {
                      display: true,
                      position: 'top'
                    },
                    tooltip: {
                      backgroundColor: '#1c552b',
                      titleColor: '#fff',
                      bodyColor: '#fff',
                      borderColor: '#42d149',
                      borderWidth: 1
                    }
                  },
                  scales: {
                    x: {
                      title: {
                        display: true,

                        font: {
                          weight: 'bold'
                        }
                      },
                      grid: {
                        color: '#e0e0e0'
                      }
                    },
                    y: {
                      beginAtZero: true,
                      title: {
                        display: true,

                        font: {
                          weight: 'bold'
                        }
                      },
                      grid: {
                        color: '#f0f0f0'
                      }
                    }
                  }
                }
              });
              new Chart(document.getElementById('civilChart'), {
                type: 'bar',
                data: {
                  labels: civilLabels,
                  datasets: [{
                    label: 'Residents',
                    data: civilCounts,
                    fill: true,
                    borderColor: '#2e7d32', // dark green line
                    backgroundColor: 'rgba(66, 209, 73, 0.2)', // soft green fill
                    pointBackgroundColor: '#42d149',
                    pointBorderColor: '#1c552b',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#42d149',
                    tension: 0.3, // smooth curves
                    borderWidth: 2,
                  }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    title: {
                      display: true,
                      font: {
                        size: 18,
                        weight: 'bold'
                      }
                    },
                    legend: {
                      display: true,
                      position: 'top'
                    },
                    tooltip: {
                      backgroundColor: '#1c552b',
                      titleColor: '#fff',
                      bodyColor: '#fff',
                      borderColor: '#42d149',
                      borderWidth: 1
                    }
                  },
                  scales: {
                    x: {
                      title: {
                        display: true,

                        font: {
                          weight: 'bold'
                        }
                      },
                      grid: {
                        color: '#e0e0e0'
                      }
                    },
                    y: {
                      beginAtZero: true,
                      title: {
                        display: true,

                        font: {
                          weight: 'bold'
                        }
                      },
                      grid: {
                        color: '#f0f0f0'
                      }
                    }
                  }
                }
              });
              new Chart(document.getElementById('documentChart'), {
                type: 'bar',
                data: {
                  labels: DocutypeLabels,
                  datasets: [{
                    label: 'Requests',
                    data: DocutypeCounts,
                    fill: true,
                    borderColor: '#2e7d32', // dark green line
                    backgroundColor: 'rgba(66, 209, 73, 0.2)', // soft green fill
                    pointBackgroundColor: '#42d149',
                    pointBorderColor: '#1c552b',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#42d149',
                    tension: 0.3, // smooth curves
                    borderWidth: 2,
                  }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    title: {
                      display: true,
                      font: {
                        size: 18,
                        weight: 'bold'
                      }
                    },
                    legend: {
                      display: true,
                      position: 'top'
                    },
                    tooltip: {
                      backgroundColor: '#1c552b',
                      titleColor: '#fff',
                      bodyColor: '#fff',
                      borderColor: '#42d149',
                      borderWidth: 1
                    }
                  },
                  scales: {
                    x: {
                      title: {
                        display: true,

                        font: {
                          weight: 'bold'
                        }
                      },
                      grid: {
                        color: '#e0e0e0'
                      }
                    },
                    y: {
                      beginAtZero: true,
                      title: {
                        display: true,

                        font: {
                          weight: 'bold'
                        }
                      },
                      grid: {
                        color: '#f0f0f0'
                      }
                    }
                  }
                }
              });
              const genderLabels = <?php echo json_encode($genderLabels); ?>;
              const genderCounts = <?php echo json_encode($genderCounts); ?>;


              new Chart(document.getElementById('genderChart'), {
                type: 'pie',
                data: {
                  labels: genderLabels,
                  datasets: [{
                    label: 'Residents',
                    data: genderCounts,
                    fill: true,
                    borderColor: '#2e7d32', // dark green line
                    backgroundColor: [
                      '#6acf6fff', // green
                      '#22843afb', // dark green
                    ],
                    pointBackgroundColor: '#1fff2b4f',
                    pointBorderColor: '#177f33ff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#42d149',
                    tension: 0.3, // smooth curves
                    borderWidth: 2,
                  }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    legend: {
                      position: 'bottom'
                    },
                    datalabels: {
                      formatter: (value, context) => {
                        const data = context.chart.data.datasets[0].data;
                        const total = data.reduce((acc, val) => acc + val, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                      },
                      color: '#fff',
                      font: {
                        weight: 'bold'
                      }
                    }
                  }
                },
                plugins: [ChartDataLabels]
              });

              const unemploymentLabels = <?php echo json_encode($unemploymentLabels); ?>;
              const unemploymentCounts = <?php echo json_encode($unemploymentCounts); ?>;


              new Chart(document.getElementById('unemploymentChart'), {
                type: 'pie',
                data: {
                  labels: unemploymentLabels,
                  datasets: [{
                    label: 'Residents',
                    data: unemploymentCounts,
                    fill: true,
                    borderColor: '#2e7d32', // dark green line
                    backgroundColor: [
                      '#6acf6fff', // green
                      '#22843afb', // dark green
                    ],
                    pointBackgroundColor: '#1fff2b4f',
                    pointBorderColor: '#177f33ff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#42d149',
                    tension: 0.3, // smooth curves
                    borderWidth: 2,
                  }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    legend: {
                      position: 'bottom'
                    },
                    datalabels: {
                      formatter: (value, context) => {
                        const data = context.chart.data.datasets[0].data;
                        const total = data.reduce((acc, val) => acc + val, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                      },
                      color: '#fff',
                      font: {
                        weight: 'bold'
                      }
                    }
                  }
                },
                plugins: [ChartDataLabels]
              });

              const businessLabels = <?php echo json_encode($businessLabels); ?>;
              const businessCounts = <?php echo json_encode($businessCounts); ?>;

              new Chart(document.getElementById('businessChart'), {
                type: 'pie',
                data: {
                  labels: businessLabels,
                  datasets: [{
                    label: 'Residents',
                    data: businessCounts,
                    fill: true,
                    borderColor: '#2e7d32', // dark green line
                    backgroundColor: [
                      '#6acf6fff', // green
                      '#22843afb', // dark green
                    ],
                    pointBackgroundColor: '#1fff2b4f',
                    pointBorderColor: '#177f33ff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#42d149',
                    tension: 0.3, // smooth curves
                    borderWidth: 2,
                  }]
                },
                options: {
                  responsive: true,
                  plugins: {
                    legend: {
                      position: 'bottom'
                    },
                    datalabels: {
                      formatter: (value, context) => {
                        const data = context.chart.data.datasets[0].data;
                        const total = data.reduce((acc, val) => acc + val, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return percentage + '%';
                      },
                      color: '#fff',
                      font: {
                        weight: 'bold'
                      }
                    }
                  }
                },
                plugins: [ChartDataLabels]
              });
            </script>

            

            <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
              function showPanel(panelId) {
                console.log("showPanel called with ID:", panelId); // Debug log
                const panels = document.querySelectorAll('.panel-content');
                panels.forEach(panel => panel.classList.remove('active'));
                const target = document.getElementById(panelId);
                if (target) {
                  target.classList.add("active");
                  console.log("Activated panel:", panelId); // Debug log
                } else {
                  console.error("Panel element not found:", panelId); // Error log
                }
              }

              function confirmLogout(event) {
                event.preventDefault();
                const confirmed = confirm("Are you sure you want to logout?");
                if (confirmed) {
                  window.location.href = "../Login/logout.php";
                }
              }

              // Modal Functions (for success popup) - Note: Renamed to showModal for generality, but kept as showSuccessModal for compatibility
              function showSuccessModal(message = "Operation successful!") {
                const modal = document.getElementById('successModal');
                const messageEl = document.getElementById('modalMessage');
                if (modal && messageEl) {
                  messageEl.textContent = message;
                  modal.style.display = 'flex';
                  console.log("Success modal shown with message:", message); // Debug log

                  // Auto-hide after 15 seconds (as per your change)
                  setTimeout(() => {
                    hideSuccessModal();
                  }, 15000);
                } else {
                  console.error("Success modal elements not found!");
                }
              }

              function hideSuccessModal() {
                const modal = document.getElementById('successModal');
                if (modal) {
                  modal.style.display = 'none';
                  console.log("Success modal hidden"); // Debug log
                }
              }

              // Event listeners for modal close
              document.addEventListener('DOMContentLoaded', function () {
                const closeBtn = document.getElementById('closeModal');
                if (closeBtn) {
                  closeBtn.addEventListener('click', hideSuccessModal);
                }

                // Close modal on outside click (optional)
                const modal = document.getElementById('successModal');
                if (modal) {
                  modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                      hideSuccessModal();
                    }
                  });
                }
              });

              window.addEventListener("DOMContentLoaded", function () {
                console.log("DOM loaded - Starting panel activation"); // Debug log
                const urlParams = new URLSearchParams(window.location.search);
                console.log("URL Params:", Object.fromEntries(urlParams)); // Debug: Log all params

                const panels = document.querySelectorAll(".panel-content");
                panels.forEach(p => p.classList.remove("active"));

                // Handle approved message - Uses modal with context-specific message
                if (urlParams.has("message") && urlParams.get("message") === "approved") {
                  // Check which panel to infer message (business or government)
                  if (urlParams.has("panel") && urlParams.get("panel") === "businessPermitPanel") {
                    showSuccessModal("Business permit request approved successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "businessUnemploymentCertificatePanel") {
                    showSuccessModal("Unemployment certificate request approved successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "guardianshipPanel") {
                    showSuccessModal("Guardianship request approved successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "residencePanel") {
                    showSuccessModal("Account verified successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "governmentDocumentPanel") {
                    showSuccessModal("Document request approved successfully!");
                  } else if (urlParams.has("search_refno") || (urlParams.has("panel") && urlParams.get("panel") === "businessPermitPanel")) {
                    showSuccessModal("Business permit request approved successfully!"); // Extra check for business
                  } else {
                    showSuccessModal("Document request approved successfully!");
                  }
                  // Optionally remove the message param from URL to clean it up
                  const newUrl = window.location.pathname + window.location.search.replace(/&?message=approved(?:&|$)/, '');
                  window.history.replaceState({}, document.title, newUrl);
                  console.log("URL cleaned up (approved):", newUrl); // Debug log
                }

                // Handle declined message - Uses modal with context-specific message
                if (urlParams.has("message") && urlParams.get("message") === "declined") {
                  // Check which panel to infer message (business or government)
                  if (urlParams.has("panel") && urlParams.get("panel") === "businessPermitPanel") {
                    showSuccessModal("Business permit request declined successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "businessUnemploymentCertificatePanel") {
                    showSuccessModal("Unemployment certificate request declined successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "guardianshipPanel") {
                    showSuccessModal("Guardianship request declined successfully!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "residencePanel") {
                    showSuccessModal("Account verification declined!");
                  } else if (urlParams.has("panel") && urlParams.get("panel") === "governmentDocumentPanel") {
                    showSuccessModal("Document request declined successfully!");

                  } else if (urlParams.has("search_refno") || (urlParams.has("panel") && urlParams.get("panel") === "businessPermitPanel")) {
                    showSuccessModal("Business permit request declined successfully!"); // Extra check for business
                  } else {
                    showSuccessModal("Document request declined successfully!");
                  }
                  // Optionally remove the message param from URL to clean it up (fixed regex for "declined")
                  const newUrl = window.location.pathname + window.location.search.replace(/&?message=declined(?:&|$)/, '');
                  window.history.replaceState({}, document.title, newUrl);
                  console.log("URL cleaned up (declined):", newUrl); // Debug log
                }

                // Activate panel based on URL params (panel takes ABSOLUTE priority - check first and return early)
                if (urlParams.has("panel")) {
                  const panelName = urlParams.get("panel");
                  console.log("Panel param detected:", panelName); // Debug log
                  const panel = document.getElementById(panelName);
                  if (panel) {
                    showPanel(panelName);
                    return; // Exit early - no other checks
                  } else {
                    console.error("Panel ID not found in DOM:", panelName);
                    // Fallback: If businessPermitPanel param but element missing, try direct activation
                    if (panelName === "businessPermitPanel") {
                      const businessFallback = document.getElementById("businessPermitPanel");
                      if (businessFallback) {
                        showPanel("businessPermitPanel");
                        return;
                      }
                    }
                  }
                }

                // Other search-based activations (only if no panel param)
                console.log("No panel param or element not found - checking search params"); // Debug log

                if (urlParams.has("search_complainant")) {
                  const blotterPanel = document.getElementById("blotterComplaintPanel");
                  if (blotterPanel) {
                    showPanel("blotterComplaintPanel");
                    return;
                  }
                } else if (urlParams.has("search_blottered") || urlParams.has("blotter_status")) {
                  const blotteredPanel = document.getElementById("blotteredIndividualsPanel");
                  if (blotteredPanel) {
                    showPanel("blotteredIndividualsPanel");
                    return;
                  }
                } else if (urlParams.has("search_lastname")) {
                  const governmentDocumentPanel = document.getElementById("governmentDocumentPanel");
                  if (governmentDocumentPanel) {
                    showPanel("governmentDocumentPanel");
                    return;
                  } else {
                    // Fallback to business unemployment certificate panel if government panel not found
                    const businessUnemploymentCertificatePanel = document.getElementById("businessUnemploymentCertificatePanel");
                    if (businessUnemploymentCertificatePanel) {
                      showPanel("businessUnemploymentCertificatePanel");
                      return;
                    } else {
                      // Further fallback to guardianship panel if unemployment panel not found
                      const guardianshipPanel = document.getElementById("guardianshipPanel");
                      if (guardianshipPanel) {
                        showPanel("guardianshipPanel");
                        return;
                      }
                    }
                  }
                } else if (urlParams.has("search_refno")) {
                  // Dedicated check for business panel
                  const businessPermitPanel = document.getElementById("businessPermitPanel");
                  if (businessPermitPanel) {
                    showPanel("businessPermitPanel");
                    console.log("Activated business panel via search_refno"); // Debug log
                    return;
                  } else {
                    console.error("businessPermitPanel element not found for search_refno");
                  }
                }

                // Default: Only activate governmentDocumentPanel if NOTHING else matches
                console.log("No matching params - defaulting to governmentDocumentPanel"); // Debug log
                const defaultPanel = document.getElementById("governmentDocumentPanel");
                if (defaultPanel) {
                  showPanel("governmentDocumentPanel");
                } else {
                  console.error("Default panel (governmentDocumentPanel) not found!");
                }
              });
            </script>


            <script>
              function printTable() {
                const originalTable = document.querySelector("#residencePanel table");

                // Clone the table
                const clonedTable = originalTable.cloneNode(true);

                // Remove "Action" column
                const theadRow = clonedTable.querySelector("thead tr");
                if (theadRow) {
                  theadRow.removeChild(theadRow.lastElementChild);
                }

                const bodyRows = clonedTable.querySelectorAll("tbody tr");
                bodyRows.forEach(row => {
                  row.removeChild(row.lastElementChild);
                });

                const currentDate = new Date().toLocaleDateString();

                const printWindow = window.open('', '', 'height=700,width=1000');

                printWindow.document.write(`
      <html>
      <head>
        <title>Barangay Sampaguita Resident</title>
        <style>
          body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px;
            color: #333;
                position: relative;

          }
                
  .watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.10;
    z-index: 0;
    pointer-events: none;
    width: 400px;
    height: auto;
  }

  .content {
    position: relative;
    z-index: 1;
  }
          .header {
            text-align: center;
            margin-bottom: 20px;
          }
          .logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
            margin-bottom: 10px;
          }
          .logos img {
            width: 80px;
            height: 80px;
            object-fit: contain;
          }
          .header h1 {
            margin: 0;
            font-size: 22px;
          }
          .header h2 {
            margin: 0;
            font-size: 18px;
            color: #666;
          }
          .print-date {
            text-align: right;
            font-size: 12px;
            margin-bottom: 10px;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 20px;
          }
          th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
          }
          th {
            background-color: #5CB25D;
            color: white;
          }
          .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 12px;
          }
        </style>
      </head>
      <body>
        <div class="header">
        <img class="watermark" src="${location.origin}/Capstone/Assets/sampaguitalogo.png" alt="Watermark">

  <div class="content">
    <div class="header">
      <div class="logos">
        <img src="${location.origin}/Capstone/Assets/sampaguitalogo.png" alt="Sampaguita Logo"/>
        <img src="${location.origin}/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"/>
      </div>
      <h1>Barangay Sampaguita</h1>
      <h2>San Pedro, Laguna</h2>
    </div>

    <div class="print-date">Date Printed: ${currentDate}</div>

    ${clonedTable.outerHTML}

    <div class="footer">
      <p>__________________________</p>
      <p>Barangay Clerk Signature</p>
    </div>
  </div>
</body>
      </html>
    `);

                // 🛠 Fix about:blank by waiting for the window to load
                printWindow.document.close();
                printWindow.onload = () => {
                  printWindow.focus();
                  printWindow.print();
                  printWindow.close();
                };
              }
            </script>

            <script>
              // function toggleDropdown(event) {
              //   event.stopPropagation();
              //   const menu = document.getElementById("dropdownMenu");
              //   menu.style.display = (menu.style.display === "block") ? "none" : "block";
              // }

              function toggleDropdown(event, id = "dropdownMenu") {
                event.stopPropagation();
                document.querySelectorAll('.dropdown-content-custom').forEach(menu => {
                  if (menu.id !== id) menu.style.display = "none";
                });
                const menu = document.getElementById(id);
                if (menu) menu.style.display = (menu.style.display === "block") ? "none" : "block";
              }

              // function showPanel(panelId) {
              //   // Hide all panels
              //   document.querySelectorAll('.panel-content').forEach(panel => panel.classList.remove('active'));

              //   // Show target
              //   const panel = document.getElementById(panelId);
              //   if (panel) {
              //     panel.classList.add('active');
              //   }

              //   // Hide dropdown menu
              //   const dropdown = document.getElementById("dropdownMenu");
              //   if (dropdown) dropdown.style.display = "none";
              // }

              function showPanel(panelId) {
                // Hide all panels
                document.querySelectorAll('.panel-content').forEach(panel => panel.classList.remove('active'));

                // Show target
                const panel = document.getElementById(panelId);
                if (panel) {
                  panel.classList.add('active');
                }

                // Hide dropdown menu
                // Hide all dropdown menus
                document.querySelectorAll('.dropdown-content-custom').forEach(menu => menu.style.display = "none");
              }

              // Close dropdown if clicking outside
              window.addEventListener('click', function (e) {
                const menu = document.getElementById("dropdownMenu");
                if (menu && !e.target.closest('.dropdown')) {
                  menu.style.display = "none";
                }
              });
            </script>

            <div id="certificateContainer" style="display:none;"></div>

            <script>
              function generateCertificate(data) {
                const container = document.getElementById("certificateContainer");

                const today = new Date();
                const formattedDate = today.toLocaleDateString('en-US', {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric'
                });

                let htmlContent = '';

                if (data.Docutype === "Barangay Certificate") {
                  htmlContent = `
      <img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
         
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-weight: normal;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:18px; margin-top:10px;">
        <p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>

      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        BARANGAY CERTIFICATE
      </div>

      <div style="font-size:18px; text-align:justify; line-height:1.8;">
        <br>TO WHOM IT MAY CONCERN:<br><br>
        This is to certify that <span style="text-transform:uppercase;">${data.Firstname + " " + data.Lastname}</span>,
        a Filipino citizen, single/married/widow/widower, whose signature appears below is a bonafide resident of this Barangay with postal address at 
        <span>${data.Address || "Barangay Sampaguita"}</span>, Barangay Sampaguita, City of San Pedro, Laguna.

        <br><br>He/She is of good moral character and a law-abiding citizen with no derogatory record in the blotter of the Barangay as of this date.

        <br><br>This certificate is being issued upon the request of the above-named person for whatever legal intent and purpose it may serve.
      </div>
      <br>Reference No. ${data.refno}.

      <div class="form-group" style="margin-top:20px;">
        <label><input type="checkbox"> FOR EMPLOYMENT</label><br>
        <label><input type="checkbox"> FOR OTHER PURPOSES (Specify): ___________________________</label>
      </div>

      <div style="margin-top:60px; text-align:right;">
        <br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
      </div>
    </div>
  </div>
    `;
                } else if (data.Docutype === "Cedula") {
                  htmlContent = `
      <div style="font-family:'Arial'; max-width:800px; margin:auto; padding:30px;">
        <h2 style="text-align:center;">COMMUNITY TAX CERTIFICATE (CEDULA)</h2>
        <p>Date Issued: ${formattedDate}</p>
        <p>Issued to: <strong>${data.Firstname} ${data.Lastname}</strong></p>
        <p>Address: ${data.Address || 'Barangay Sampaguita, San Pedro, Laguna'}</p>
        <p>Amount Paid: ₱5.00</p>
        <p>This certificate is issued for identification and tax purposes.</p>
        <p style="text-align:right; margin-top:60px;">Authorized Barangay Treasurer</p>
      </div>
      
    `;
                } else if  (data.Docutype === "Indigency Form") {
                  htmlContent = `
      <img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-weight: normal;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:18px; margin-top:10px;">
        <p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>

      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        CERTIFICATE of INDIGENCY
      </div>

      <div style="font-size:18px; text-align:justify; line-height:1.8;">
        <br>TO WHOM IT MAY CONCERN:<br><br>
        <p>This is to certify that <span style="text-transform:uppercase;">${data.Firstname + " " + data.Lastname}</span>,a Filipino citizen, of legal age, 
        in a resident of ${data.Address || "Barangay Sampaguita, San Pedro, Laguna"}. </p>
        <p>member of Indigent person in the BARANGAY.</p>

        <br>This Certification is issued to <span style="text-transform:uppercase;">${data.Firstname + " " + data.Lastname}</span>,

        <br>Given this ${formattedDate} at Barangay Sampaguita, City of San Pedro, Laguna 
        <br>Reference No. ${data.refno}.

     

      <div style="margin-top:60px; text-align:right;">
        <br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
      </div>
    </div>
  </div>
    `;
    } else if  (data.Docutype === "Employment Form") {
                  htmlContent = `
      <img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
      
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-weight: normal;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:18px; margin-top:10px;">
        <p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>

      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        CERTIFICATE of EMPLOYMENT
      </div>

      <div style="font-size:18px; text-align:justify; line-height:1.8;">
        <br>TO WHOM IT MAY CONCERN:<br><br>
        <p>This is to certify that <span style="text-transform:uppercase;">${data.Firstname + " " + data.Lastname}</span>, a Filipino citizen, of legal age, 
        in a resident of ${data.Address || "Barangay Sampaguita, San Pedro, Laguna"}. 
        he has possessed of a GOOD MORAL CHARACTER and no derogatory record in the blotter of the Barangay.</p>

       <p>This certifies futhermore that the name person-mentioned above is requesting
        this certification for employment for whatever legal purpose it may serve.</p>

        

        <br>Given this ${formattedDate} at Barangay Sampaguita, City of San Pedro, Laguna 
        <br>Reference No.${data.refno}.

     

      <div style="margin-top:60px; text-align:right;">
        <br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
      </div>
    </div>
  </div>
    `;
               } else if (data.certificate_type === "No Income") {
                  htmlContent = `
     <br><br><img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
         
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-size: 18px;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
 
      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        CERTIFICATE OF NO INCOME
      </div>
   <div style="margin-top:30px; font-size:18px;">
  <p>TO WHOM IT MAY CONCERN</p>
    <p>This is to certify that ${data.fullname}, a resident of Barangay Sampaguita, San Pedro, Laguna.
    That the above mentioned generates minimal income, enough for the famil'y sustenance
    and have not been gainfully employed.</p>
   
    

    <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.
    <br>Reference No. ${data.refno}.

      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
  </div>
</div>
    `;
                } else if (data.RequestType === "closure") {
                  htmlContent = `
     <br><br><img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-size: 18px;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>

      </div>

      <div style="text-align:center; font-size:22px; text-decoration:underline; margin:30px 0 20px;">
        BUSINESS CLOSURE CERTIFICATE
      </div>
  <div style="margin-top:30px; font-size:18px;">
    <p>This certifies that <strong>${data.OwnerName}</strong> has been granted a permit to operate a business of type <strong>${data.RequestType}</strong> in Barangay Sampaguita, San Pedro, Laguna.</p>
    <p>Reference No.: <strong>${data.refno}</strong></p>
   <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.


      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
  </div>
</div>
    `;
                } else if (data.RequestType === "permit") {
                  htmlContent = `
     <br><br><img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
         
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-size: 18px;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
    
      </div>

      <div style="text-align:center; font-size:22px; text-decoration:underline; margin:30px 0 20px;">
        BUSINESS PERMIT
      </div>
  <div style="margin-top:30px; font-size:18px;">
    <p>This certifies that <strong>${data.OwnerName}</strong> has been granted a permit to operate a business of type <strong>${data.RequestType}</strong> in Barangay Sampaguita, San Pedro, Laguna.</p>
    <p>Reference No.: <strong>${data.refno}</strong></p>
   <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.


      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
  </div>
</div>
    `;
                } else if (data.certificate_type === "No Fixed Income") {
                  htmlContent = `
     <br><br><img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
         
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-size: 18px;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
 
      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        CERTIFICATE OF NO FIXED INCOME
      </div>
   <div style="margin-top:30px; font-size:18px;">
  <p>TO WHOM IT MAY CONCERN</p>
    <p>This is to certify that ${data.fullname}, a resident of Barangay Sampaguita, San Pedro, Laguna.</p>
   
    <p>This certification is being issued upon the request of above-named person for
    whatever legal purpose it may serve.</p>

    <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.
    <br>Reference No. ${data.refno}.

      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
  </div>
</div>
    `;
                } else if (data.request_type === "Guardianship") {
                  htmlContent = `
     <br><br><img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-size: 18px;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
      </div>

      <div style="text-align:center; font-size:22px; text-decoration:underline; margin:30px 0 20px;">
        CERTIFICATE OF GUARDIANSHIP
      </div>
  <div style="margin-top:30px; font-size:18px;">
  <p>TO WHOM IT MAY CONCERN</p>
    <p>This is to certify that <strong>${data.applicant_name}</strong>, of legal age, single/married is a bonafied resident of barangay Sampaguita, San Pedro, Laguna</p>
   
    <p>This further certifies that the above-named is the legal Guardian of;</p>
    <p>Name:<strong>${data.child_name}</strong></p>
    <p>Age:<strong>${data.child_age}</strong></p>
    <p>Address:<strong>${data.child_address}</strong></p>
    <p>Reference No.: <strong>${data.refno}</strong></p>
   <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.


      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
  </div>
</div>
    `;
                } else if (data.request_type === "Solo Parent") {
                  htmlContent = `
     <br><br><img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
         
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-size: 18px;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>

      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        BARANGAY CERTIFICATION
      </div>
  <div style="margin-top:30px; font-size:18px;">
    <p>This certify that <strong>${data.applicant_name}</strong> is a bonafide resident of Barangay Sampaguita, San Pedro, Laguna.</p>

    <br> This further certifies that the above name-mentioned is a SOLO PARENT.
    <p>Reference No.: <strong>${data.refno}</strong></p>
   <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.


      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
  </div>
</div>
    `;
                } else if (data.Docutype === "First Time Job Seeker") {
                  htmlContent = `
      <img src="/Capstone/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          
        </div>

        <div style="flex: 2; text-align: center;">
          <h3 style="margin: 0; font-weight: normal;">REPUBLIC OF THE PHILIPPINES</h3>
          <p style="margin: 0;">Province of Laguna</p>
          <p style="margin: 0;">City of San Pedro</p>
          <h2 style="margin: 10px 0; font-weight: normal;">BARANGAY SAMPAGUITA</h2>
          <p style="margin: 0;">Tel. No. 8638-0301</p>
        </div>

        <div style="flex: 1; text-align: right;">
          <img src="/Capstone/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:18px; margin-top:10px;">
        <p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
      </div>

      <div style="text-align:center; font-size:22px; margin:30px 0 20px;">
        CERTIFICATE OF FIRST TIME JOB SEEKER
      </div>

      <div style="font-size:18px; text-align:justify; line-height:1.8;">
        <br>TO WHOM IT MAY CONCERN:<br><br>
        This is to certify <span style="text-transform:uppercase;">${data.Firstname + " " + data.Lastname}</span>,
        resident of <span>${data.Address || "Barangay Sampaguita"}</span>, City of San Pedro, Laguna,  R.A. No.11261
        or the First Time Jobseekers Assistance Act f 2019.

        <br>I further certify that the holder/bearer was informed of his/here rights,
        including the duties and responsibilities accorded by R.A. No.11261 through the 
        Oath of Undertaking he/she has signed and executed in the presence of Barangay
        Offical/s.
       
        <br><br>Issued this <span>${formattedDate}</span> at Barangay Sampaguita, City of San Pedro, Laguna.
        <br>Reference No. ${data.refno}.


      <div style="margin-top:60px; text-align:right;">
        <br><br><br><p>HON. RHYXTER S. LABAY</p>
        <p>Punong Barangay</p>
      </div>
    </div>
  </div>
    `;

                } else {
                  alert("Unknown document type: " + data.Docutype);
                  return;
                }

                container.innerHTML = htmlContent;

                const printWindow = window.open('', '_blank', 'width=900,height=600');
                printWindow.document.write(`
    <html>
      <head>
        <title>Print ${data.Docutype}</title>
        <style>
          body { font-family: 'Times New Roman', serif; }
        </style>
      </head>
      <body onload="window.print(); window.close();">
        ${container.innerHTML}
      </body>
    </html>
  `);
                printWindow.document.close();
              }
            </script>

         <script>
let selectedDocData = null; // Store selected document data globally

// 🧩 Open Print Modal
function openPrintModal(data) {
  selectedDocData = data; // Save for later use (for printing + status update)

  document.getElementById("modal_refno").value = data.refno;
  document.getElementById("modal_name").value = data.Firstname + ' ' + data.Lastname;
  document.getElementById("modal_type").value = data.Docutype;
  document.getElementById("modal_address").value = data.Address || "N/A";
  document.getElementById("modal_date").value = data.DateRequested || new Date().toLocaleDateString();

  // ✅ Automatically set amount based on document type
  const amountField = document.getElementById("modal_amount");
  const docType = data.Docutype ? data.Docutype.toLowerCase().trim() : "";

  // ₱100 documents
  const hundredPesoDocs = ["barangay certificate", "cedula", "employment form"];

  // Free documents
  const freeDocs = ["indigency form", "first time job seeker"];

  if (hundredPesoDocs.includes(docType)) {
    amountField.value = 100;
    amountField.readOnly = true;
  } else if (freeDocs.includes(docType)) {
    amountField.value = 0;
    amountField.readOnly = true;
  } else {
    amountField.value = "";
    amountField.readOnly = false;
  }

  // Show the modal
  const modal = new bootstrap.Modal(document.getElementById('printFormModal'));
  modal.show();
}

// 🧩 Handle Payment Form Submission + Printing
document.getElementById("printForm").addEventListener("submit", function (event) {
  event.preventDefault(); // Prevent normal form submit

  const formData = new FormData(this);
  const docType = selectedDocData && selectedDocData.Docutype ? selectedDocData.Docutype.toLowerCase().trim() : "";

  fetch("Payment.php", {
    method: "POST",
    body: formData,
  })
  .then((response) => response.text())
  .then((response) => {
    if (response.trim() === "success") {
      alert("Payment recorded.");
      bootstrap.Modal.getInstance(document.getElementById('printFormModal')).hide();

      if (selectedDocData) {
        if (docType === "cedula") {
          // For Cedula, set status to Released and do NOT print
          fetch("update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(selectedDocData.ReqId)}&status=Released`,
          })
          .then(res => res.text())
          .then(res => {
            if (res.trim() === "success") {
              alert("Cedula marked as Released.");
              location.reload();
            } else {
              console.error("Failed to update status:", res);
              alert("Failed to mark as Released. Please refresh and try again.");
            }
          })
          .catch(err => {
            console.error("Status update error:", err);
            alert("Error updating document status.");
          });
        } else {
          // For other types, print and set to Printed
          generateCertificate(selectedDocData);
          fetch("update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(selectedDocData.ReqId)}&status=Printed`,
          })
          .then(res => res.text())
          .then(res => {
            if (res.trim() === "success") {
              alert("Document marked as Printed. You can now release it.");
              location.reload();
            } else {
              console.error("Failed to update status:", res);
              alert("Failed to mark as Printed. Please refresh and try again.");
            }
          })
          .catch(err => {
            console.error("Status update error:", err);
            alert("Error updating document status.");
          });
        }
      } else {
        alert("No document selected for printing.");
      }
    } else {
      alert("Error: " + response);
    }
  })
  .catch((error) => {
    console.error("Error submitting payment:", error);
    alert("Something went wrong. Please try again.");
  });
});

// 🧩 Handle Release Button Action
function releaseDocument(reqId) {
  if (!confirm("Are you sure you want to release this document?")) return;

  fetch("update_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(reqId)}&status=Released`,
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "success") {
      alert("Document successfully released!");
      location.reload(); // Refresh to show the final Released state
    } else {
      alert("Error: " + res);
    }
  })
  .catch(err => {
    console.error("Release error:", err);
    alert("Something went wrong while releasing.");
  });
}
</script>


 <script>
// let selectedDocData = null; // Global variable to store selected document data

// Function to open the modal and save the selected document's data
function openNoBirthPrintModal(data) {
  selectedDocData = data; // Store for later use

  // Fill in the modal fields
  document.getElementById("nobirth_modal_refno").value = data.refno;
  document.getElementById("nobirth_modal_name").value = data.requestor_name;
  document.getElementById("nobirth_modal_date").value = data.request_date;
  
  // Amount is fixed at 100
  document.getElementById("nobirth_modal_amount").value = 100;

  // Show the modal
  const modal = new bootstrap.Modal(document.getElementById('nobirthCertPrintModal'));
  modal.show();
}

// Handle No Birth Certificate Payment Form Submission
document.getElementById("nobirthCertPrintForm").addEventListener("submit", function(event) {
  event.preventDefault();

  const formData = new FormData(this);

  fetch("PaymentNoBirthCert.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.text())
  .then(response => {
    if (response.trim() === "success") {
      alert("Payment recorded. Now printing...");
      bootstrap.Modal.getInstance(document.getElementById('nobirthCertPrintModal')).hide();

      if (selectedDocData) {
        // Update status to Printed
        fetch("update_status_nobirth.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(selectedDocData.id)}&status=Printed`
        })
        .then(res => res.text())
        .then(res => {
          if (res.trim() === "success") {
            alert("Document marked as Printed. You can now release it.");
            location.reload();
          } else {
            console.error("Failed to update status:", res);
            alert("Failed to mark as Printed. Please refresh and try again.");
          }
        })
        .catch(err => {
          console.error("Status update error:", err);
          alert("Error updating document status.");
        });
      }
    } else {
      alert("Error: " + response);
    }
  })
  .catch(error => {
    console.error("Error submitting payment:", error);
    alert("Something went wrong. Please try again.");
  });
});

// Handle No Birth Certificate Release
function releaseNoBirthDocument(id) {
  if (!confirm("Are you sure you want to release this document?")) return;

  fetch("update_status_nobirth.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(id)}&status=Released`
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "success") {
      alert("Document successfully released!");
      location.reload();
    } else {
      alert("Error: " + res);
    }
  })
  .catch(err => {
    console.error("Release error:", err);
    alert("Something went wrong while releasing.");
  });
}

// Handle form submission
document.getElementById("printForm").addEventListener("submit", function (event) {
  event.preventDefault(); // Prevent default form submission

  const formData = new FormData(this);

  fetch("PaymentNoBirthCert.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((response) => {
      if (response.trim() === "success") {
        alert("Payment recorded. Now printing...");
        bootstrap.Modal.getInstance(document.getElementById('printFormModal')).hide();

        if (selectedDocData) {
          // Update status to Printed
          fetch("update_status_nobirth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(selectedDocData.id)}&status=Printed`,
          })
          .then(res => res.text())
          .then(res => {
            if (res.trim() === "success") {
              alert("Document marked as Printed. You can now release it.");
              location.reload(); // Refresh to show updated status
            } else {
              console.error("Failed to update status:", res);
              alert("Failed to mark as Printed. Please refresh and try again.");
            }
          })
          .catch(err => {
            console.error("Status update error:", err);
            alert("Error updating document status.");
          });
        } else {
          alert("No document selected for printing.");
        }
      } else {
        alert("Error: " + response);
      }
    })
    .catch((error) => {
      console.error("Error submitting payment:", error);
      alert("Something went wrong. Please try again.");
    });
});
</script>



           <script>
  document.addEventListener("DOMContentLoaded", function () {
    const viewButtons = document.querySelectorAll(".action-btn-2.view");
    const modal = document.getElementById("viewModal");
    const closeBtn = document.querySelector(".close-btn");

    viewButtons.forEach(button => {
      button.addEventListener("click", function () {
        // Fill in text fields
        document.getElementById("modalId").value = this.dataset.id;
        document.getElementById("modalFirstname").value = this.dataset.firstname;
        document.getElementById("modalLastname").value = this.dataset.lastname;
        document.getElementById("modalMiddlename").value = this.dataset.middlename;
        document.getElementById("modalEmail").value = this.dataset.email;
        document.getElementById("modalContact").value = this.dataset.contact;
        document.getElementById("modalBirthday").value = this.dataset.birthdate;
        document.getElementById("modalGender").value = this.dataset.gender;
        document.getElementById("modalAddress").value = this.dataset.address;
        document.getElementById("modalBirthplace").value = this.dataset.birthplace;
        document.getElementById("modalCivilStat").value = this.dataset.civilstatus;
        document.getElementById("modalNationality").value = this.dataset.nationality;
        document.getElementById("modalAccountStatus").value = this.dataset.accountstatus;

        // Display the Valid ID (Base64)
        const validIDImg = document.getElementById("modalValidID");
        if (this.dataset.validid && this.dataset.validid.trim() !== "") {
          validIDImg.src = "data:image/jpeg;base64," + this.dataset.validid;
        } else {
          validIDImg.src = "/Capstone/Assets/no-valid-id.png"; // fallback image
        }

        // Show modal
        modal.style.display = "block";
      });
    });

    // Close modal on click (X)
    closeBtn.addEventListener("click", () => {
      modal.style.display = "none";
    });

    // Close when clicking outside the modal
    window.addEventListener("click", (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
  });
</script>


            <script>
              function openModal() {
                document.getElementById('addUserModal').style.display = 'flex';
              }

              function closeModal() {
                document.getElementById('addUserModal').style.display = 'none';
              }
            </script>
            <script>
              function openDocumentModal() {
                const modal = document.getElementById('addDocumentModal');
                const refInput = modal.querySelector('input[name="Reference"]');

                // Generate reference: YYYYMMDD + random 4-digit number
                const now = new Date();
                const datePart = now.getFullYear().toString() +
                  String(now.getMonth() + 1).padStart(2, '0') +
                  String(now.getDate()).padStart(2, '0');
                const randomPart = Math.floor(1000 + Math.random() * 9000); // 4 digits
                const referenceNumber = datePart + randomPart;

                refInput.value = referenceNumber; // fill the input
                modal.style.display = 'flex'; // show modal
              }

              function closeDocumentModal() {
                document.getElementById('addDocumentModal').style.display = 'none';
              }
            </script>
            <script>
              function openBusinessModal() {
                const modal = document.getElementById('addBusinessModal');
                const refInput = modal.querySelector('input[name="refno"]');

                // Generate reference: YYYYMMDD + random 4-digit number
                const now = new Date();
                const datePart = now.getFullYear().toString() +
                  String(now.getMonth() + 1).padStart(2, '0') +
                  String(now.getDate()).padStart(2, '0');
                const randomPart = Math.floor(1000 + Math.random() * 9000); // 4 digits
                const referenceNumber = datePart + randomPart;

                refInput.value = referenceNumber; // fill the input
                modal.style.display = 'flex'; // show modal
              }

              function closeBusinessModal() {
                document.getElementById('addBusinessModal').style.display = 'none';
              }
            </script>

            <script>
              function openUnemploymentModal() {
                const modal = document.getElementById('addUnemploymentModal');
                const refInput = modal.querySelector('input[name="refno"]');

                // Generate reference: YYYYMMDD + random 4-digit number
                const now = new Date();
                const datePart = now.getFullYear().toString() +
                  String(now.getMonth() + 1).padStart(2, '0') +
                  String(now.getDate()).padStart(2, '0');
                const randomPart = Math.floor(1000 + Math.random() * 9000); // 4 digits
                const referenceNumber = datePart + randomPart;

                refInput.value = referenceNumber; // fill the input
                modal.style.display = 'flex'; // show modal
              }

              function closeUnemployment() {
                document.getElementById('addUnemploymentModal').style.display = 'none';
              }
            </script>
            <script>
              function openGuardianshipModal() {
                const modal = document.getElementById('addOpenGuardianship');
                const refInput = modal.querySelector('input[name="refno"]');

                // Generate reference: YYYYMMDD + random 4-digit number
                const now = new Date();
                const datePart = now.getFullYear().toString() +
                  String(now.getMonth() + 1).padStart(2, '0') +
                  String(now.getDate()).padStart(2, '0');
                const randomPart = Math.floor(1000 + Math.random() * 9000); // 4 digits
                const referenceNumber = datePart + randomPart;

                refInput.value = referenceNumber; // fill the input
                modal.style.display = 'flex'; // show modal
              }

              function closeGuardianship() {
                document.getElementById('addOpenGuardianship').style.display = 'none';
              }
            </script>
             <script>
              function openbirthcertificateModal() {
                const modal = document.getElementById('addOpenbirthcertificate');
                const refInput = modal.querySelector('input[name="refno"]');

                // Generate reference: YYYYMMDD + random 4-digit number
                const now = new Date();
                const datePart = now.getFullYear().toString() +
                  String(now.getMonth() + 1).padStart(2, '0') +
                  String(now.getDate()).padStart(2, '0');
                const randomPart = Math.floor(1000 + Math.random() * 9000); // 4 digits
                const referenceNumber = datePart + randomPart;

                refInput.value = referenceNumber; // fill the input
                modal.style.display = 'flex'; // show modal
              }

              function closebithcertificate() {
                document.getElementById('addOpenbirthcertificate').style.display = 'none';
              }
            </script>

            <script>
              const modal = document.getElementById("requestModal");
              const openBtn = document.getElementById("openRequestModalBtn");
              const closeBtn = document.querySelector(".request-modal-close");

              openBtn.onclick = () => modal.style.display = "block";
              closeBtn.onclick = () => modal.style.display = "none";
              window.onclick = e => {
                if (e.target === modal) modal.style.display = "none";
              };
            </script>
            <script>
              document.querySelectorAll('.spectate').forEach(button => {
                button.addEventListener('click', function () {
                  document.getElementById('govdocReqId').value = this.dataset.reqid;
                  document.getElementById('govdocFirstname').value = this.dataset.firstname;
                  document.getElementById('govdocLastname').value = this.dataset.lastname;
                  document.getElementById('govdocContact').value = this.dataset.contact;
                  document.getElementById('govdocAddress').value = this.dataset.address;
                  document.getElementById('govdocRefno').value = this.dataset.refno;
                  document.getElementById('govdocDocutype').value = this.dataset.docutype;
                  document.getElementById('govdocDateRequested').value = this.dataset.daterequested;

                  // Set Gender and Purpose
                  if (document.getElementById('govdocGender')) {
                    document.getElementById('govdocGender').value = this.dataset.gender || '';
                  }
                  if (document.getElementById('govdocReqPurpose')) {
                    document.getElementById('govdocReqPurpose').value = this.dataset.reqpurpose || '';
                  }

                  // Set Certificate Image
                  const certImg = document.getElementById('govdocCertificateImage');
                  if (certImg) {
                    if (this.dataset.certificateimage && this.dataset.certificateimage.trim() !== '') {
                      certImg.src = this.dataset.certificateimage;
                      certImg.style.display = 'block';
                    } else {
                      certImg.src = '';
                      certImg.style.display = 'none';
                    }
                  }

                  document.getElementById('govdocViewModal').style.display = 'flex';
                });
              });
            </script>


            <!-- // PARA S POPUP MESSAGE APPROVE NG GOVERNMENT DOCUMENTS -->
            <script>
              let approveUrl = null; // store clicked approve link

              function showCustomConfirm(event, url) {
                event.preventDefault();
                approveUrl = url;
                document.getElementById("customConfirm").style.display = "flex";
              }

              document.getElementById("customConfirmYes").addEventListener("click", function () {
                if (approveUrl) {
                  window.location.href = approveUrl; // proceed with approval
                }
              });

              document.getElementById("customConfirmNo").addEventListener("click", function () {
                document.getElementById("customConfirm").style.display = "none";
                approveUrl = null;
              });
            </script>

            <!-- // PARA S POPUP MESSAGE DECLINE NG GOVERNMENT DOCUMENTS -->
            <script>
              let declineUrl = null;

              function showCustomDeclineConfirm(event, url) {
                event.preventDefault();
                declineUrl = url;
                document.getElementById("customDeclineConfirm").style.display = "flex";
              }

              document.getElementById("customDeclineConfirmYes").addEventListener("click", function () {
                document.getElementById("customDeclineConfirm").style.display = "none";
                document.getElementById("declineReasonModal").style.display = "flex";
              });

              document.getElementById("customDeclineConfirmNo").addEventListener("click", function () {
                document.getElementById("customDeclineConfirm").style.display = "none";
                declineUrl = null;
              });

              document.getElementById("declineReasonCancel").addEventListener("click", function () {
                document.getElementById("declineReasonModal").style.display = "none";
                declineUrl = null;
                document.getElementById("declineReasonText").value = "";
              });

              document.getElementById("declineReasonSubmit").addEventListener("click", function () {
                const reason = document.getElementById("declineReasonText").value.trim();
                if (!reason) {
                  alert("Please enter a reason for declining.");
                  return;
                }
                // Send reason via GET (or POST if you prefer)
                const url = new URL(declineUrl, window.location.origin);
                url.searchParams.append("reason", encodeURIComponent(reason));
                window.location.href = url.toString();
              });
            </script>
            <script>
              document.querySelector('.close-btn-govdoc').addEventListener('click', function () {
                document.getElementById('govdocViewModal').style.display = 'none';
              });

              window.addEventListener('click', function (e) {
                if (e.target == document.getElementById('govdocViewModal')) {
                  document.getElementById('govdocViewModal').style.display = 'none';
                }
              });
            </script>

           <script>
let selectedBusinessDocData = null;

function openBusinessPrintModal(data) {
  if (typeof data === "string") {
    data = JSON.parse(data);
  }
  selectedBusinessDocData = data;

  document.getElementById("business_modal_refno").value = data.refno;
  document.getElementById("business_modal_owner").value = data.OwnerName;
  document.getElementById("business_modal_type").value = data.RequestType;
  document.getElementById("business_modal_date").value = data.RequestedDate || new Date().toLocaleDateString();

  // ✅ Automatically set amount based on business document type
  const amountField = document.getElementById("business_modal_amount");
  const docType = data.RequestType ? data.RequestType.toLowerCase().trim() : "";

  // ₱200 document types
  const twoHundredDocs = ["permit", "closure"];

  if (twoHundredDocs.includes(docType)) {
    amountField.value = 200;
    amountField.readOnly = true; // Optional: lock field so user can’t change it
  } else {
    amountField.value = "";
    amountField.readOnly = false;
  }

  const modal = new bootstrap.Modal(document.getElementById('businessPrintFormModal'));
  modal.show();
}

document.getElementById("businessPrintForm").addEventListener("submit", function (event) {
  event.preventDefault();

  const formData = new FormData(this);

  fetch("Paymentbusiness.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((response) => {
      if (response.trim() === "success") {
        alert("Payment recorded. Now printing...");
        bootstrap.Modal.getInstance(document.getElementById('businessPrintFormModal')).hide();

        if (selectedBusinessDocData) {
          generateCertificate(selectedBusinessDocData);
          fetch("updatestatus_business.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(selectedBusinessDocData.BsnssID)}&status=Printed`,
          })
          .then(res => res.text())
          .then(res => {
            if (res.trim() === "success") {
              alert("Business permit marked as Printed. You can now release it.");
              location.reload(); // Refresh the table to show the new Release button
            } else {
              console.error("Failed to update status:", res);
              alert("Failed to mark as Printed. Please refresh and try again.");
            }
          })
          .catch(err => {
            console.error("Status update error:", err);
            alert("Error updating business permit status.");
          });

        } else {
          alert("No business permit selected for printing.");
        }
      } else {
        alert("Error: " + response);
      }
    })
    .catch((error) => {
      console.error("Error submitting payment:", error);
      alert("Something went wrong. Please try again.");
    });
});

function releaseBusinessDocument(BsnssID) {
  if (!confirm("Are you sure you want to release this business document?")) return;

  fetch("updatestatus_business.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(BsnssID)}&status=Released`,
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "success") {
      alert("Business document successfully released!");
      location.reload(); // Refresh to show the final Released state
    } else {
      alert("Error: " + res);
    }
  })
  .catch(err => {
    console.error("Release error:", err);
    alert("Something went wrong while releasing.");
  });
}
</script>


          <script>
let selectedUnemploymentDocData = null;

function openUnemploymentPrintModal(data) {
  if (typeof data === "string") {
    data = JSON.parse(data);
  }
  selectedUnemploymentDocData = data;

  document.getElementById("unemployment_modal_refno").value = data.refno;
  document.getElementById("unemployment_modal_name").value = data.fullname;
  document.getElementById("unemployment_modal_type").value = data.certificate_type;
  document.getElementById("unemployment_modal_date").value = data.request_date || new Date().toLocaleDateString();

  // ✅ Automatically set amount based on certificate type
  const amountField = document.getElementById("unemployment_modal_amount");
  const docType = data.certificate_type ? data.certificate_type.toLowerCase().trim() : "";

  // ₱100 document types
  const oneHundredDocs = ["no income", "no fixed income"];

  if (oneHundredDocs.includes(docType)) {
    amountField.value = 100;
    amountField.readOnly = true; // lock field
  } else {
    amountField.value = "";
    amountField.readOnly = false;
  }

  const modal = new bootstrap.Modal(document.getElementById('unemploymentPrintFormModal'));
  modal.show();
}

document.getElementById("unemploymentPrintForm").addEventListener("submit", function (event) {
  event.preventDefault();

  const formData = new FormData(this);

  fetch("Paymentunemployment.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((response) => {
      if (response.trim() === "success") {
        alert("Payment recorded. Now printing...");
        bootstrap.Modal.getInstance(document.getElementById('unemploymentPrintFormModal')).hide();

        if (selectedUnemploymentDocData) {
          generateCertificate(selectedUnemploymentDocData);

          // ✅ After printing, update the RequestStatus to "Printed"
          fetch("updatestatus_unemployment.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(selectedUnemploymentDocData.id)}&status=Printed`,
          })
          .then(res => res.text())
          .then(res => {
            if (res.trim() === "success") {
              alert("Unemployment certificate marked as Printed. You can now release it.");
              location.reload(); // Refresh the table to show the new Release button
            } else {
              console.error("Failed to update status:", res);
              alert("Failed to mark as Printed. Please refresh and try again.");
            }
          })
          .catch(err => {
            console.error("Status update error:", err);
            alert("Error updating unemployment certificate status.");
          });
        } else {
          alert("No unemployment certificate selected for printing.");
        }
      } else {
        alert("Error: " + response);
      }
    })
    .catch((error) => {
      console.error("Error submitting payment:", error);
      alert("Something went wrong. Please try again.");
    });
});

function releaseUnemploymentDocument(id) {
  if (!confirm("Are you sure you want to release this unemployment document?")) return;

  fetch("updatestatus_unemployment.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(id)}&status=Released`,
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "success") {
      alert("Unemployment document successfully released!");
      location.reload(); // Refresh to show the final Released state
    } else {
      alert("Error: " + res);
    }
  })
  .catch(err => {
    console.error("Release error:", err);
    alert("Something went wrong while releasing.");
  });
}
</script>


           <script>
let selectedGuardianShipDocData = null;

function openGuardianshipPrintModal(data) {
  if (typeof data === "string") {
    data = JSON.parse(data);
  }
  selectedGuardianShipDocData = data;

  document.getElementById("guardianship_modal_refno").value = data.refno;
  document.getElementById("guardianship_modal_name").value = data.applicant_name;
  document.getElementById("guardianship_modal_type").value = data.request_type;
  document.getElementById("guardianship_modal_date").value =
    data.request_date || new Date().toLocaleDateString();

  // ✅ Automatically set amount based on document type
  const amountField = document.getElementById("guardianship_modal_amount");
  const docType = data.request_type ? data.request_type.toLowerCase().trim() : "";

  if (docType === "solo parent") {
    amountField.value = 0;
    amountField.readOnly = true;
  } else if (docType === "guardianship") {
    amountField.value = 50;
    amountField.readOnly = true;
  } else {
    amountField.value = "";
    amountField.readOnly = false;
  }

  const modal = new bootstrap.Modal(document.getElementById("guardianshipPrintFormModal"));
  modal.show();
}

document.getElementById("guardianshipPrintForm").addEventListener("submit", function (event) {
  event.preventDefault();

  const formData = new FormData(this);

  fetch("PaymentGuardianship.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((response) => {
      if (response.trim() === "success") {
        alert("Payment recorded. Now printing...");
        bootstrap.Modal.getInstance(document.getElementById("guardianshipPrintFormModal")).hide();

        if (selectedGuardianShipDocData) {
          generateCertificate(selectedGuardianShipDocData);

          // ✅ After printing, update the RequestStatus to "Printed"
          fetch("updatestatus_guardianship.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(selectedGuardianShipDocData.id)}&status=Printed`,
          })
          .then((res) => res.text())
          .then((res) => {
            if (res.trim() === "success") {
              alert("Guardianship document marked as Printed. You can now release it.");
              location.reload(); // Refresh the table to show the new Release button
            } else {
              console.error("Failed to update status:", res);
              alert("Failed to mark as Printed. Please refresh and try again.");
            }
          })
          .catch((err) => {
            console.error("Status update error:", err);
            alert("Error updating guardianship document status.");
          });
        } else {
          alert("No guardianship document selected for printing.");
        }
      } else {
        alert("Error: " + response);
      }
    })
    .catch((error) => {
      console.error("Error submitting payment:", error);
      alert("Something went wrong. Please try again.");
    });
});

function releaseGuardianshipDocument(id) {
  if (!confirm("Are you sure you want to release this guardianship document?")) return;

  fetch("updatestatus_guardianship.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(id)}&status=Released`,
  })
  .then((res) => res.text())
  .then((res) => {
    if (res.trim() === "success") {
      alert("Guardianship document successfully released!");
      location.reload(); // Refresh to show the final Released state
    } else {
      alert("Error: " + res);
    }
  })
  .catch((err) => {
    console.error("Release error:", err);
    alert("Something went wrong while releasing.");
  });
}
</script>

<script>
let selectedNoBirthCertDocData = null;

function openNoBirthCertModal() {
  const modal = document.getElementById('addNoBirthCertModal');
  const refInput = modal.querySelector('input[name="refno"]');

  // Generate reference: YYYYMMDD + random 4-digit number
  const now = new Date();
  const datePart = now.getFullYear().toString() +
    String(now.getMonth() + 1).padStart(2, '0') +
    String(now.getDate()).padStart(2, '0');
  const randomPart = Math.floor(1000 + Math.random() * 9000);
  const referenceNumber = datePart + randomPart;

  refInput.value = referenceNumber;
  modal.style.display = 'flex';
}

function closeNoBirthCertModal() {
  document.getElementById('addNoBirthCertModal').style.display = 'none';
}

function openNoBirthCertPrintModal(data) {
  if (typeof data === "string") {
    data = JSON.parse(data);
  }
  selectedNoBirthCertDocData = data;

  document.getElementById("nobirth_modal_refno").value = data.refno;
  document.getElementById("nobirth_modal_name").value = data.requestor_name;
  document.getElementById("nobirth_modal_type").value = data.DocuType;
  document.getElementById("nobirth_modal_date").value = data.request_date || new Date().toLocaleDateString();

  // Set amount based on document type
  const amountField = document.getElementById("nobirth_modal_amount");
  const docType = data.DocuType ? data.DocuType.toLowerCase().trim() : "";

  // Set standard fees
  if (docType === "no birth certificate" || docType === "late registration") {
    amountField.value = 100;
    amountField.readOnly = true;
  } else {
    amountField.value = "";
    amountField.readOnly = false;
  }

  const modal = new bootstrap.Modal(document.getElementById('nobirthCertPrintFormModal'));
  modal.show();
}

document.getElementById("nobirthCertPrintForm")?.addEventListener("submit", function(event) {
  event.preventDefault();

  const formData = new FormData(this);

  fetch("PaymentNoBirthCert.php", {
    method: "POST",
    body: formData,
  })
  .then((response) => response.text())
  .then((response) => {
    if (response.trim() === "success") {
      alert("Payment recorded. Now printing...");
      bootstrap.Modal.getInstance(document.getElementById('nobirthCertPrintFormModal')).hide();

      if (selectedNoBirthCertDocData) {
        generateCertificate(selectedNoBirthCertDocData);

        // Update status to "Printed"
        fetch("updatestatus_nobirth.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `id=${encodeURIComponent(selectedNoBirthCertDocData.id)}&status=Printed`,
        })
        .then(res => res.text())
        .then(res => {
          if (res.trim() === "success") {
            alert("Document marked as Printed. You can now release it.");
            location.reload();
          } else {
            console.error("Failed to update status:", res);
            alert("Failed to mark as Printed. Please refresh and try again.");
          }
        })
        .catch(err => {
          console.error("Status update error:", err);
          alert("Error updating document status.");
        });
      } else {
        alert("No document selected for printing.");
      }
    } else {
      alert("Error: " + response);
    }
  })
  .catch((error) => {
    console.error("Error submitting payment:", error);
    alert("Something went wrong. Please try again.");
  });
});

function releaseNoBirthCertDocument(id) {
  if (!confirm("Are you sure you want to release this document?")) return;

  fetch("updatestatus_nobirth.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(id)}&status=Released`,
  })
  .then(res => res.text())
  .then(res => {
    if (res.trim() === "success") {
      alert("Document successfully released!");
      location.reload();
    } else {
      alert("Error: " + res);
    }
  })
  .catch(err => {
    console.error("Release error:", err);
    alert("Something went wrong while releasing.");
  });
}
</script>

            <script>
              function openLogoutModal(e) {
                e.preventDefault();
                document.getElementById('logoutModal').style.display = 'flex';
              }

              function closeLogoutModal() {
                document.getElementById('logoutModal').style.display = 'none';
              }

              function confirmLogout() {
                window.location.href = "../Login/logout.php";
              }
            </script>


            <!-- View Blotter Modal Script -->
            <script>
              let currentBlotterId = null;

              function closeViewBlotterModal() {
                document.getElementById('viewBlotterModal').style.display = 'none';
              }

              document.querySelectorAll('.view-blotter').forEach(btn => {
                btn.addEventListener('click', function() {
                  currentBlotterId = this.getAttribute('data-id');
                  const blotterId = this.getAttribute('data-id');
                  fetch('../Process/blotter/viewblotter.php?id=' + encodeURIComponent(blotterId))
                    .then(response => response.json())
                    .then(data => {
                      if (data.error) {
                        alert(data.error);
                        return;
                      }

                      document.getElementById('view_blotter_id').textContent = data.blotter.blotter_id;
                      document.getElementById('view_officer_on_duty').textContent = data.blotter.officer_on_duty;


                      // Fill complainant fields
                      // Fill ALL complainants (instead of just one)
                      const complainantsContainer = document.getElementById('view_complainantContainer');
                      complainantsContainer.innerHTML = '';
                      data.participants.filter(p => p.participant_type === 'complainant').forEach(complainant => {
                        complainantsContainer.innerHTML += `
                          <div class="complainant-fields">
                            <h6>Full name of the Complainant</h6>
                            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                              <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" value="${complainant.lastname}" readonly>
                              </div>
                              <div class="form-group">
                                <label>First Name</label>
                                <input type="text" value="${complainant.firstname}" readonly>
                              </div>
                              <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" value="${complainant.middlename}" readonly>
                              </div>
                            </div>
                            <div class="form-group">
                              <label>Address</label>
                              <input type="text" value="${complainant.address}" readonly>
                            </div>
                            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                              <div class="form-group">
                                <label>Age</label>
                                <input type="number" value="${complainant.age === null ? '' : complainant.age}" readonly>
                              </div>
                              <div class="form-group">
                                <label>Contact No</label>
                                <input type="text" value="${complainant.contact_no === null ? '' : complainant.contact_no}" readonly>
                              </div>
                              <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="${complainant.email}" readonly>
                              </div>
                            </div>
                            <hr>
                          </div>
                        `;
                      });
                      // Fill blotter details
                      document.getElementById('view_incident_datetime').value = data.blotter.datetime_of_incident;
                      document.getElementById('view_incident_location').value = data.blotter.location_of_incident;
                      document.getElementById('view_incident_type').value = data.blotter.incident_type;
                      document.getElementById('view_incident_description').value = data.blotter.blotter_details;

                      // Fill accused
                      const accusedContainer = document.getElementById('view_accusedContainer');
                      accusedContainer.innerHTML = '';
                      data.participants.filter(p => p.participant_type === 'accused').forEach(accused => {
                        accusedContainer.innerHTML += `
                          <div class="accused-fields">
                            <h6>Full name of the Respondent</h6>
                            <div class="form-grid" style="grid-template-columns:1fr 1.7fr 1fr .7fr; gap:10px;">
                              <div class="form-group">
                                <label>Last Name</label>
                              <input type="text" value="${accused.lastname}" readonly>
                              </div>
                              <div class="form-group">
                                <label>First Name</label>
                                <input type="text" value="${accused.firstname}" readonly style="grid-column: span 2;">
                              </div>
                              <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" value="${accused.middlename}" readonly>
                              </div>
                              <div class="form-group">
                                <label>Alias</label>
                                <input type="text" value="${accused.alias}" size="3" readonly>
                              </div>

                            </div>
                            <div class="form-group">
                              <label>Address</label>
                              <input type="text" value="${accused.address}" readonly>
                            </div>
                            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                              <div class="form-group">
                                <label>Age</label>
                                <input type="number" value="${accused.age === null ? '' : accused.age}" readonly>
                              </div>
                              <div class="form-group">
                                <label>Contact No</label>
                                <input type="text" value="${accused.contact_no === null ? '' : accused.contact_no}" readonly>
                              </div>
                              <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="${accused.email}" readonly>
                              </div>
                            </div>
                            <hr>
                          </div>
                        `;
                      });

                      // Fill witnesses
                      const witnessesContainer = document.getElementById('view_witnessesContainer');
                      witnessesContainer.innerHTML = '';
                      // data.participants.filter(p => p.participant_type === 'witness');
                      const witnessesHeader = document.querySelector('#viewBlotterModal h3#witnessesDetailsHeader');

                      if (data.participants.filter(p => p.participant_type === 'witness').length > 0) {
                        witnessesHeader.style.display = 'block';
                      } else {
                        witnessesHeader.style.display = 'none';
                      }
                      data.participants.filter(p => p.participant_type === 'witness').forEach(witness => {
                        witnessesContainer.innerHTML += `
                            <div class="witness-fields">
                              <h6>Full name of the Witness</h6>
                              <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                                <div class="form-group">
                                  <label>Last Name</label>
                                  <input type="text" value="${witness.lastname}" readonly>
                                </div>
                                <div class="form-group">
                                  <label>First Name</label>
                                  <input type="text" value="${witness.firstname}" readonly>
                                </div>
                                <div class="form-group">
                                  <label>Middle Name</label>
                                  <input type="text" value="${witness.middlename}" readonly>
                                </div>
                              </div>
                              <div class="form-group">
                                <label>Address</label>
                                <input type="text" value="${witness.address}" readonly>
                              </div>
                              <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                                <div class="form-group">
                                  <label>Age</label>
                                  <input type="number" value="${witness.age === null ? '' : witness.age}" readonly>
                                </div>
                                <div class="form-group">
                                  <label>Contact No</label>
                                  <input type="text" value="${witness.contact_no === null ? '' : witness.contact_no}" readonly>
                                </div>
                                <div class="form-group">
                                  <label>Email</label>
                                  <input type="email" value="${witness.email}" readonly>
                                </div>
                              </div>
                              <hr>
                            </div>
                          `;
                      });

                      // Fetch hearing details (latest for editing, all for history)
                      if (data.hearings && data.hearings.length > 0) {
                        // Filter hearings to only include those that are fully recorded (mediator_name, hearing_notes, and outcome are not null/empty)
                        const recordedHearings = data.hearings.filter(hearing =>
                          hearing.mediator_name && hearing.mediator_name.trim() !== '' &&
                          hearing.hearing_notes && hearing.hearing_notes.trim() !== '' &&
                          hearing.outcome && hearing.outcome.trim() !== ''
                        );


                        if (recordedHearings.length > 0) {
                          // Show history section and populate it only with recorded hearings
                          document.getElementById('hearingHistoryHeader').style.display = 'block';
                          document.getElementById('hearingHistorySection').style.display = 'block';
                          const historyContainer = document.getElementById('hearingHistorySection');
                          historyContainer.innerHTML = ''; // Clear any existing content

                          recordedHearings.forEach((hearing, index) => {
                            historyContainer.innerHTML += `
                            <div>
                              <label>Hearing No: ${hearing.hearing_no}</label>
                              <div class="form-group">
                              Hearing Date & Time: ${hearing.schedule_start.replace('T', ' ')}
                              </div>
                              <div class="form-group">
                              <label style="font-weight:600;">Hearing Mediator:</label>
                              <input type="text" class="form-control" value="${hearing.mediator_name || 'N/A'}" readonly>
                              </div>
                              <div class="form-group" style="margin-top:8px;">
                              <label style="font-weight:600;">Hearing Notes:</label>
                              <textarea rows="7" class="form-control" readonly>${hearing.hearing_notes || 'N/A'}</textarea>
                              </div>
                              <div class="form-group">
                              <label style="font-weight:600;">Outcome:</label>
                              <input type="text" class="form-control" value="${hearing.outcome || 'N/A'}" readonly>
                              </div>
                            </div>
                          `;
                            // Add <hr> between hearings (but not after the last one)
                            if (index < recordedHearings.length - 1) {
                              historyContainer.innerHTML += '<hr>';
                            }
                          });
                        } else {
                          // Hide history if no recorded hearings
                          document.getElementById('hearingHistoryHeader').style.display = 'none';
                          document.getElementById('hearingHistorySection').style.display = 'none';
                        }
                      } else {
                        // Hide history if no hearings at all
                        document.getElementById('hearingHistoryHeader').style.display = 'none';
                        document.getElementById('hearingHistorySection').style.display = 'none';
                      }


                      // Hearing Details Section: Only show if hearing exists but is NOT fully recorded (e.g., missing mediator, notes, or outcome)
                      if (data.hearing && (!data.hearing.mediator_name || !data.hearing.hearing_notes || !data.hearing.outcome)) {
                        document.getElementById('hearingDetailsHR').style.display = 'block';
                        document.getElementById('hearingDetailsHeader').style.display = 'block';
                        document.getElementById('hearingDetailsSection').style.display = 'block';
                        document.getElementById('hearingNoLabel').textContent = 'Hearing No. ' + data.hearing.hearing_no;
                        document.getElementById('hearingDateTime').textContent = data.hearing.schedule_start.replace('T', ' ');
                        // store hearing_id in hidden field for updates
                        if (document.getElementById('hearing_id')) document.getElementById('hearing_id').value = data.hearing.hearing_id || '';
                        // populate mediator/notes/outcome if present
                        if (data.hearing.mediator_name && document.getElementById('hearing_mediator')) document.getElementById('hearing_mediator').value = data.hearing.mediator_name;
                        if (data.hearing.hearing_notes && document.getElementById('hearing_notes')) document.getElementById('hearing_notes').value = data.hearing.hearing_notes;
                        if (data.hearing.outcome && document.getElementById('hearing_outcome')) document.getElementById('hearing_outcome').value = data.hearing.outcome;
                      } else {
                        document.getElementById('hearingDetailsHeader').style.display = 'none';
                        document.getElementById('hearingDetailsSection').style.display = 'none';
                        document.getElementById('hearingDetailsHR').style.display = 'none';
                      }

                      // Fill hearing participants table
                      const participantsTableContainer = document.getElementById('hearingParticipantsTableContainer');
                      if (data.participants && data.participants.length > 0) {
                        let tableHTML = `
                            <table class="styled-table" style="margin-top:15px;">
                              <thead>
                                <tr>
                                  <th>Full Name</th>
                                  <th>Type</th>
                                  <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                          `;
                        data.participants.forEach(p => {
                          const fullname = [p.lastname, p.firstname, p.middlename].filter(Boolean).join(', ');
                          tableHTML += `
                                <tr>
                                  <td>${fullname}</td>
                                  <td>${p.participant_type.charAt(0).toUpperCase() + p.participant_type.slice(1)}</td>
                                  <td>
                                    <button class="btn btn-secondary btn-print-summon" 
                                      data-blotter-id="${data.blotter.blotter_id}" 
                                      data-participant-id="${p.blotter_participant_id}" 
                                      data-type="${p.participant_type}">
                                      <i class="fas fa-print"></i> Print Summon
                                    </button>
                                  </td>
                                </tr>
                              `;
                        });
                        tableHTML += '</tbody></table>';
                        participantsTableContainer.innerHTML = tableHTML;
                      } else {
                        participantsTableContainer.innerHTML = '<div style="margin-top:10px;">No participants found.</div>';
                      }




                      // FILL UPLOADED Files
                      const filesContainer = document.getElementById('view_filesContainer');
                      filesContainer.innerHTML = '';

                      if (data.files && data.files.length > 0) {
                        data.files.forEach(file => {
                          let thumbHTML = '';

                          // Show image preview if it's an image
                          if (file.file_type.startsWith('image/')) {
                            thumbHTML = `<img src="/BarangaySystem/BarangaySystem/${file.file_path}" alt="thumbnail" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">`;
                          } else {
                            // Default file icon if not image
                            thumbHTML = `<i class="fas fa-file" style="font-size:30px;color:#666;"></i>`;
                          }

                          if (file.file_type.startsWith('image/')) {
                            filesContainer.innerHTML += `
                                  <tr>
                                    <td style="text-align:center;">${thumbHTML}</td>
                                    <td>${file.file_name}</td>
                                    <td>
                                    <a href="/BarangaySystem/BarangaySystem/${file.file_path}" download="${file.file_name}" class="btn btn-primary" style="padding:5px 10px;">
                                    <i class="fas fa-download"></i> Download
                                    </a>
                                    <button onclick="viewImage('/BarangaySystem/BarangaySystem/${file.file_path}')" class="btn btn-primary" style="padding:5px 10px;">
                                      <i class="fas fa-eye"></i> View
                                    </button>
                                      
                                    </td>
                                  </tr>
                                `;
                          } else {
                            filesContainer.innerHTML += `
                                <tr>
                                  <td style="text-align:center;">${thumbHTML}</td>
                                  <td>${file.file_name}</td>
                                  <td>
                                    <a href="/BarangaySystem/BarangaySystem/${file.file_path}" download="${file.file_name}" class="btn btn-primary" style="padding:5px 10px;">
                                      <i class="fas fa-download"></i> Download
                                    </a>
                                    
                                  </td>
                                </tr>
                              `;
                          }
                        });
                      } else {
                        filesContainer.innerHTML = `
                            <tr>
                              <td colspan="3" style="text-align:center;">No uploaded files found.</td>
                            </tr>
                          `;
                      }


                      if (data.blotter && (data.blotter.status === 'closed_resolved' || data.blotter.status === 'closed_unresolved' || data.blotter.status === 'closed')) {
                        const actionBtn = document.getElementById('actionDropdownBtn');
                        if (actionBtn) {
                          actionBtn.style.display = 'none';
                        }
                      } else {
                        const actionBtn = document.getElementById('actionDropdownBtn');
                        if (actionBtn) {
                          actionBtn.style.display = 'inline-block';
                        }
                      }






                      document.getElementById('viewBlotterModal').style.display = 'flex';
                    })
                    .catch(() => alert('Failed to fetch blotter details.'));
                });
              });


              // Schedule Hearing
              document.getElementById('scheduleHearingBtn').onclick = function() {
                document.getElementById('scheduleHearingModal').style.display = 'flex';
              };

              function closeScheduleHearingModal() {
                document.getElementById('scheduleHearingModal').style.display = 'none';
              }

              document.getElementById('scheduleHearingForm').onsubmit = function(e) {
                e.preventDefault();
                const dt = document.getElementById('hearing_datetime').value;
                if (!dt) return alert('Please select date and time.');
                fetch('../Process/blotter/create_hearing.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'blotter_id=' + encodeURIComponent(currentBlotterId) + '&hearing_datetime=' + encodeURIComponent(dt)
                  })
                  .then(r => r.json())
                  .then(res => {
                    if (res.success) {
                      closeScheduleHearingModal();
                      location.reload(); // Or re-fetch modal data to show hearing info
                    } else {
                      alert(res.error || 'Failed to schedule hearing.');
                    }
                  });
              };

              // Print Summon Buttons
              document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-print-summon')) {
                  const btn = e.target.closest('.btn-print-summon');
                  const blotterId = btn.getAttribute('data-blotter-id');
                  const participantId = btn.getAttribute('data-participant-id');
                  const type = btn.getAttribute('data-type');
                  window.open(
                    `../Process/blotter/print_summon.php?blotter_id=${encodeURIComponent(blotterId)}&participant_id=${encodeURIComponent(participantId)}&type=${encodeURIComponent(type)}`,
                    '_blank'
                  );
                }
              });


              // Add these functions at the end of the script or in a suitable place
              function viewImage(src) {
                document.getElementById('viewerImg').src = src;
                document.getElementById('imageViewer').style.display = 'flex';
              }

              function closeImageViewer() {
                document.getElementById('imageViewer').style.display = 'none';
              }



              // Show confirmation popup when "Close Blotter" is clicked
              document.getElementById('closeBlotterBtn').addEventListener('click', function() {
                document.getElementById('closeBlotterConfirm').style.display = 'flex';
              });

              // If "No" is clicked, hide the popup
              document.getElementById('closeBlotterNo').addEventListener('click', function() {
                document.getElementById('closeBlotterConfirm').style.display = 'none';
              });

              // If "Yes" is clicked, send AJAX request to update status
              document.getElementById('closeBlotterYes').addEventListener('click', function() {
                if (!currentBlotterId) return;
                fetch('../Process/blotter/closeblotter.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(currentBlotterId)
                  })
                  .then(response => response.text())
                  .then(result => {
                    if (result.trim() === 'success') {
                      alert('Blotter closed successfully.');
                      document.getElementById('closeBlotterConfirm').style.display = 'none';
                      document.getElementById('viewBlotterModal').style.display = 'none';
                      location.reload(); // Refresh to update table
                    } else {
                      alert('Failed to close blotter: ' + result);
                    }
                  })
                  .catch(() => alert('Error closing blotter.'));
              });

              // ... (rest of your script remains unchanged)

              // Save Hearing (mediator, notes, outcome)
              const saveHearingBtn = document.getElementById('saveHearingBtn');
              if (saveHearingBtn) {
                saveHearingBtn.addEventListener('click', function() {
                  // Determine blotter id
                  const blotterId = (typeof currentBlotterId !== '' && currentBlotterId) ? currentBlotterId : (document.getElementById('view_blotter_id') ? document.getElementById('view_blotter_id').textContent.trim() : null);
                  if (!blotterId) return alert('Unable to determine blotter id.');
                  const mediator = document.getElementById('hearing_mediator') ? document.getElementById('hearing_mediator').value.trim() : '';
                  const notes = document.getElementById('hearing_notes') ? document.getElementById('hearing_notes').value.trim() : '';
                  const outcome = document.getElementById('hearing_outcome') ? document.getElementById('hearing_outcome').value : '';
                  const schedule = document.getElementById('hearingDateTime') ? document.getElementById('hearingDateTime').textContent.trim() : '';
                  const hearingId = document.getElementById('hearing_id') ? document.getElementById('hearing_id').value.trim() : '';

                  // Prepare FormData for multipart submission
                  const formData = new FormData();
                  formData.append('blotter_id', blotterId);
                  formData.append('schedule_start', schedule || '');
                  formData.append('mediator_name', mediator || '');
                  formData.append('hearing_notes', notes || '');
                  formData.append('outcome', outcome || '');
                  formData.append('hearing_id', hearingId || '');

                  // Append files if selected
                  const filesInput = document.getElementById('hearing_files');
                  if (filesInput && filesInput.files && filesInput.files.length > 0) {
                    for (let i = 0; i < filesInput.files.length; i++) {
                      formData.append('hearing_files[]', filesInput.files[i]);
                    }
                  }

                  saveHearingBtn.disabled = true;
                  saveHearingBtn.textContent = 'Saving...';

                  fetch('../Process/blotter/record_hearing.php', {
                    method: 'POST',
                    body: formData // No Content-Type header needed; browser sets it for FormData
                  }).then(r => r.json()).then(res => {
                    saveHearingBtn.disabled = false;
                    saveHearingBtn.textContent = 'Save Hearing';
                    if (res && res.success) {
                      alert(res.message || 'Hearing saved');
                      // Clear the file input after success
                      if (filesInput) filesInput.value = '';
                      // Disable and make readonly the inputs after saving (keep section visible)
                      const mediatorInput = document.getElementById('hearing_mediator');
                      const notesInput = document.getElementById('hearing_notes');
                      const outcomeInput = document.getElementById('hearing_outcome');
                      if (mediatorInput) {
                        mediatorInput.disabled = true;
                        mediatorInput.readOnly = true;
                      }
                      if (notesInput) {
                        notesInput.disabled = true;
                        notesInput.readOnly = true;
                      }
                      if (outcomeInput) {
                        outcomeInput.disabled = true;
                      }
                      if (filesInput) {
                        filesInput.disabled = true;
                      }
                      // Disable the save button as well to prevent further saves
                      saveHearingBtn.disabled = true;
                      saveHearingBtn.textContent = 'Saved';

                      // NEW: Check outcome and show modal if 'no_agreement', else reload
                      if (outcome === 'no_agreement') {
                        document.getElementById('postHearingModal').style.display = 'flex';
                      } else {
                        location.reload();
                      }
                    } else {
                      alert((res && res.message) ? res.message : 'Failed to save hearing');
                    }
                  }).catch(err => {
                    saveHearingBtn.disabled = false;
                    saveHearingBtn.textContent = 'Save Hearing';
                    alert('Error saving hearing');
                    console.error(err);
                  });
                });
              }

              // NEW: Event listeners for post-hearing modal buttons
              document.getElementById('postHearingCloseBlotter').addEventListener('click', function() {
                if (!currentBlotterId) return alert('Unable to determine blotter ID.');
                fetch('../Process/blotter/closeblotter.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + encodeURIComponent(currentBlotterId) + '&status=closed_unresolved'
                  })
                  .then(response => response.text())
                  .then(result => {
                    if (result.trim() === 'success') {
                      alert('Blotter closed as unresolved.');
                      document.getElementById('postHearingModal').style.display = 'none';
                      document.getElementById('viewBlotterModal').style.display = 'none';
                      location.reload();
                    } else {
                      alert('Failed to close blotter: ' + result);
                    }
                  })
                  .catch(() => alert('Error closing blotter.'));
              });

              document.getElementById('postHearingScheduleHearing').addEventListener('click', function() {
                document.getElementById('postHearingModal').style.display = 'none';
                document.getElementById('scheduleHearingModal').style.display = 'flex';
              });
            </script>




            <!--Update blotter script-->
            <script>
              let currentUpdateBlotterId = null;

              function closeUpdateBlotterModal() {
                document.getElementById('updateBlotterModal').style.display = 'none';
              }

              // Open update modal and populate fields
              document.querySelectorAll('.update-blotter').forEach(btn => {
                btn.addEventListener('click', function() {
                  currentUpdateBlotterId = this.getAttribute('data-id');
                  fetch('../Process/blotter/viewblotter.php?id=' + encodeURIComponent(currentUpdateBlotterId))
                    .then(response => response.json())
                    .then(data => {
                      let html = '';

                      // Complainant
                      html += `
                    <div style="font-size:16px; font-weight:bold; margin-bottom:10px; margin-top:30px;">
                      Blotter ID: ${data.blotter.blotter_id}
                    </div>`;
                      html += `<h3>Complainant Details</h3>
                      <div id="update_complainantContainer">`;

                      data.participants.filter(p => p.participant_type === 'complainant').forEach((complainant, i) => {
                        html += `
                        <div class="complainant-fields">
                          <input type="hidden" name="complainant_id[]" value="${complainant.blotter_participant_id}">
                          <h6>Full name of the Complainant</h6>
                          <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="form-group">
                              <label>Last Name</label>
                              <input type="text" name="complainant_lastname[]" value="${complainant.lastname}" required>
                            </div>
                            <div class="form-group">
                              <label>First Name</label>
                              <input type="text" name="complainant_firstname[]" value="${complainant.firstname}" required>
                            </div>
                            <div class="form-group">
                              <label>Middle Name</label>
                              <input type="text" name="complainant_middlename[]" value="${complainant.middlename}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="complainant_address[]" value="${complainant.address}" required>
                          </div>
                          <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="form-group">
                              <label>Age</label>
                              <input type="number" name="complainant_age[]" value="${complainant.age === null ? '' : complainant.age}">
                            </div>
                            <div class="form-group">
                              <label>Contact No</label>
                              <input type="text" name="complainant_contact_no[]" value="${complainant.contact_no === null ? '' : complainant.contact_no}">
                            </div>
                            <div class="form-group">
                              <label>Email</label>
                              <input type="email" name="complainant_email[]" value="${complainant.email}">
                            </div>
                          </div>
                          <button type="button" class="btn btn-danger btn-sm remove-complainant-btn" onclick="removeComplainantRow(this)" style="margin-bottom:10px; margin-top: 10px;">Remove</button>
                          <hr>
                        </div>
                      `;
                      });
                      html += `</div>
                      <button type="button" class="btn btn-success btn-sm" id="updateAddComplainantBtn">+ Add Complainant</button>
                      <br><hr>`;

                      // Blotter Details
                      html += `<h3>Blotter Details</h3>
        <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
          <div class="form-group">
            <label>Date & Time of Incident</label>
            <input type="datetime-local" name="incident_datetime" value="${data.blotter.datetime_of_incident.replace(' ','T')}" readonly>
          </div>
          <div class="form-group">
            <label>Location of Incident</label>
            <input type="text" name="incident_location" value="${data.blotter.location_of_incident}" required>
          </div>
          <div class="form-group">
            <label>Type of Incident</label>
            <input type="text" name="incident_type" value="${data.blotter.incident_type}" readonly>
          </div>
        </div>
        <div class="form-group" style="grid-column: span 3;">
          <label>Description</label>
          <textarea name="incident_description" rows="5" required>${data.blotter.blotter_details}</textarea>
        </div>
        <br><hr>`;

                      // Accused
                      html += `<h3>Respondent Details</h3>
        <div id="update_accusedContainer">`;
                      data.participants.filter(p => p.participant_type === 'accused').forEach((accused, i) => {
                        html += `
          <div class="accused-fields">
            <input type="hidden" name="accused_id[]" value="${accused.blotter_participant_id}">
            <h6>Full name of the Respondent</h6>
            <div class="form-grid" style="grid-template-columns:1fr 1.3fr 1fr .7fr; gap:10px;">
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="accused_lastname[]" value="${accused.lastname}" required>
              </div>
              <div class="form-group">
                <label>First Name</label>
                <input type="text" name="accused_firstname[]" value="${accused.firstname}" required>
              </div>
              <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="accused_middlename[]" value="${accused.middlename}">
              </div>
              <div class="form-group">
                <label>Alias</label>
                <input type="text" name="accused_alias[]" value="${accused.alias}">
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="accused_address[]" value="${accused.address}">
            </div>
            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
              <div class="form-group">
                <label>Age</label>
                <input type="number" name="accused_age[]" value="${accused.age === null ? '' : accused.age}">
              </div>
              <div class="form-group">
                <label>Contact No</label>
                <input type="text" name="accused_contact_no[]" value="${accused.contact_no === null ? '' : accused.contact_no}">
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="accused_email[]" value="${accused.email}">
              </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-accused-btn" onclick="removeAccusedRow(this)" style="margin-bottom:10px; margin-top: 10px;">Remove</button>
            <hr>
          </div>
          `;
                      });
                      html += `</div>
        <button type="button" class="btn btn-success btn-sm" id="updateAddAccusedBtn">+ Add Respondent</button>
        <br><hr>`;

                      // Witnesses
                      html += `<h3>Witnesses Details</h3>
        <div id="update_witnessesContainer">`;
                      data.participants.filter(p => p.participant_type === 'witness').forEach((witness, i) => {
                        html += `
          <div class="witness-fields">
            <input type="hidden" name="witness_id[]" value="${witness.blotter_participant_id}">
            <h6>Full name of the Witness</h6>
            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="witness_lastname[]" value="${witness.lastname}">
              </div>
              <div class="form-group">
                <label>First Name</label>
                <input type="text" name="witness_firstname[]" value="${witness.firstname}">
              </div>
              <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="witness_middlename[]" value="${witness.middlename}">
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="witness_address[]" value="${witness.address}">
            </div>
            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
              <div class="form-group">
                <label>Age</label>
                <input type="number" name="witness_age[]" value="${witness.age === null ? '' : witness.age}">
              </div>
              <div class="form-group">
                <label>Contact No</label>
                <input type="text" name="witness_contact_no[]" value="${witness.contact_no === null ? '' : witness.contact_no}">
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="witness_email[]" value="${witness.email}">
              </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-witness-btn" onclick="removeWitnessRow(this)" style="margin-bottom:10px; margin-top: 10px;">Remove</button>
            <hr>
          </div>
          `;
                      });
                      html += `</div>
        <button type="button" class="btn btn-success btn-sm" id="updateAddWitnessBtn">+ Add Witness</button>
        <br><hr>`;

                      // File Upload
                      html += `
                        <div>
                          <h4>Existing Files</h4>
                          <div class="scrollable-table-container">
                            <table class="styled-table">
                              <thead>
                                <tr>
                                  <th>Thumbnail</th>
                                  <th>File Name</th>
                                  
                                </tr>
                              </thead>
                              <tbody>`;
                      if (data.files && data.files.length > 0) {
                        data.files.forEach(file => {
                          let thumbHTML = '';
                          if (file.file_type.startsWith('image/')) {
                            thumbHTML = `<img src="/BarangaySystem/BarangaySystem/${file.file_path}" alt="thumbnail" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">`;
                          } else {
                            thumbHTML = `<i class="fas fa-file" style="font-size:30px;color:#666;"></i>`;
                          }
                          html += `
                              <tr>
                                <td style="text-align:center;">${thumbHTML}</td>
                                <td>${file.file_name}</td>
                          
                                
                              </tr>
                            `;
                        });
                      } else {
                        html += `
                              <tr>
                                <td colspan="3" style="text-align:center;">No uploaded files found.</td>
                              </tr>
                            `;
                      }
                      html += `</tbody>
                          </table>
                        </div>
                      </div>
                      <br>
                      <hr>
                      <h3>Upload Files</h3>
                        <div class="form-group">
                          <label>Upload Image(s) (jpg, jpeg, png, pdf, docx only) (Optional)</label>
                          <small class="text-muted">Hold ctrl or shift + click the images/files for multiple uploads.</small>
                          <input type="file" name="blotter_files[]" multiple accept="image/*,application/pdf,.doc,.docx">
                        </div> 
                      `;

                      // Hidden blotter_id
                      html += `<input type="hidden" name="blotter_id" value="${data.blotter.blotter_id}">`;

                      document.getElementById('updateBlotterFields').innerHTML = html;
                      document.getElementById('updateBlotterModal').style.display = 'flex';



                      // Add function to manage complainant remove buttons
                      function updateComplainantRemoveButtons() {
                        const complainantContainer = document.getElementById('update_complainantContainer');
                        if (!complainantContainer) return;

                        const complainantRows = complainantContainer.querySelectorAll('.complainant-fields');
                        const removeButtons = complainantContainer.querySelectorAll('.remove-complainant-btn');

                        if (complainantRows.length === 1) {
                          removeButtons.forEach(btn => {
                            btn.disabled = true;
                            btn.style.opacity = '0.5';
                            btn.style.cursor = 'not-allowed';
                          });
                        } else {
                          removeButtons.forEach(btn => {
                            btn.disabled = false;
                            btn.style.opacity = '1';
                            btn.style.cursor = 'pointer';
                          });
                        }
                      }
                      updateComplainantRemoveButtons(); // Initial call

                      // Remove complainant row
                      window.removeComplainantRow = function(btn) {
                        const container = document.getElementById('update_complainantContainer');
                        const complainantRows = container.querySelectorAll('.complainant-fields');

                        if (complainantRows.length <= 1) {
                          alert('At least one complainant must remain.');
                          return;
                        }

                        btn.closest('.complainant-fields').remove();
                        updateComplainantRemoveButtons();
                      };

                      // Add complainant button
                      document.getElementById('updateAddComplainantBtn').onclick = function() {
                        const container = document.getElementById('update_complainantContainer');
                        const div = document.createElement('div');
                        div.className = 'complainant-fields';
                        div.innerHTML = `
                            <h6>Full name of the Complainant</h6>
                            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                              <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="complainant_lastname[]" required>
                              </div>
                              <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="complainant_firstname[]" required>
                              </div>
                              <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="complainant_middlename[]">
                              </div>
                            </div>
                            <div class="form-group">
                              <label>Address</label>
                              <input type="text" name="complainant_address[]" required>
                            </div>
                            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                              <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="complainant_age[]">
                              </div>
                              <div class="form-group">
                                <label>Contact No</label>
                                <input type="text" name="complainant_contact_no[]">
                              </div>
                              <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="complainant_email[]">
                              </div>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-complainant-btn" onclick="removeComplainantRow(this)" style="margin-bottom:10px; margin-top: 10px;">Remove</button>
                            <hr>
                          `;
                        container.appendChild(div);
                        updateComplainantRemoveButtons();
                      };


                      // Add this function to check and update remove button states for accused
                      function updateAccusedRemoveButtons() {
                        const accusedContainer = document.getElementById('update_accusedContainer');
                        if (!accusedContainer) return;

                        const accusedRows = accusedContainer.querySelectorAll('.accused-fields');
                        const removeButtons = accusedContainer.querySelectorAll('.remove-accused-btn');

                        // Disable all remove buttons if there's only one accused
                        if (accusedRows.length === 1) {
                          removeButtons.forEach(btn => {
                            btn.disabled = true;
                            btn.style.opacity = '0.5';
                            btn.style.cursor = 'not-allowed';
                          });
                        } else {
                          removeButtons.forEach(btn => {
                            btn.disabled = false;
                            btn.style.opacity = '1';
                            btn.style.cursor = 'pointer';
                          });
                        }
                      }
                      // Initial call to set button states
                      updateAccusedRemoveButtons();



                      // Remove accused row (removes hidden input so it will be deleted in DB)
                      window.removeAccusedRow = function(btn) {
                        const container = document.getElementById('update_accusedContainer');
                        const accusedRows = container.querySelectorAll('.accused-fields');

                        // Prevent removal if only one accused remains
                        if (accusedRows.length <= 1) {
                          alert('At least one accused must remain.');
                          return;
                        }

                        btn.closest('.accused-fields').remove();
                        updateAccusedRemoveButtons(); // Update button states after removal
                      };

                      // Remove witness row (removes hidden input so it will be deleted in DB)
                      window.removeWitnessRow = function(btn) {
                        btn.closest('.witness-fields').remove();
                      };

                      // Add accused/witness dynamic add
                      // Modify the updateAddAccusedBtn.onclick function
                      document.getElementById('updateAddAccusedBtn').onclick = function() {
                        const container = document.getElementById('update_accusedContainer');
                        const div = document.createElement('div');
                        div.className = 'accused-fields';
                        div.innerHTML = `
                          <h6>Full name of the Respondent</h6>
                          <div class="form-grid" style="grid-template-columns:1fr 1.7fr 1fr .7fr; gap:10px;">
                            <div class="form-group">
                              <label>Last Name</label>
                              <input type="text" name="accused_lastname[]" required>
                            </div>
                            <div class="form-group">
                              <label>First Name</label>
                              <input type="text" name="accused_firstname[]" required>
                            </div>
                            <div class="form-group">
                              <label>Middle Name</label>
                              <input type="text" name="accused_middlename[]">
                            </div>
                            <div class="form-group">
                              <label>Alias</label>
                              <input type="text" name="accused_alias[]">
                            </div>
                          </div>
                          <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="accused_address[]">
                          </div>
                          <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="form-group">
                              <label>Age</label>
                              <input type="number" name="accused_age[]">
                            </div>
                            <div class="form-group">
                              <label>Contact No</label>
                              <input type="number" name="accused_contact_no[]">
                            </div>
                            <div class="form-group">
                              <label>Email</label>
                              <input type="email" name="accused_email[]">
                            </div>
                          </div>
                          <button type="button" class="btn btn-danger btn-sm remove-accused-btn" onclick="removeAccusedRow(this)" style="margin-bottom:10px; margin-top: 10px;">Remove</button>
                          <hr>
                        `;
                        container.appendChild(div);
                        updateAccusedRemoveButtons(); // Update button states after adding
                      };

                      document.getElementById('updateAddWitnessBtn').onclick = function() {
                        const container = document.getElementById('update_witnessesContainer');
                        const div = document.createElement('div');
                        div.className = 'witness-fields';
                        div.innerHTML = `
            <h6>Full name of the Witness</h6>
            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="witness_lastname[]">
              </div>
              <div class="form-group">
                <label>First Name</label>
                <input type="text" name="witness_firstname[]">
              </div>
              <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="witness_middlename[]">
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="witness_address[]">
            </div>
            <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
              <div class="form-group">
                <label>Age</label>
                <input type="number" name="witness_age[]">
              </div>
              <div class="form-group">
                <label>Contact No</label>
                <input type="number" name="witness_contact_no[]">
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="witness_email[]">
              </div>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-witness-btn" onclick="removeWitnessRow(this)" style="margin-bottom:10px; margin-top: 10px;">Remove</button>
            <hr>
          `;
                        container.appendChild(div);
                      };
                    })
                    .catch(() => alert('Failed to fetch blotter details.'));
                });
              });
            </script>

            <!-- Set max date for incident datetime input to prevent future dates on blotterModal  -->
            <!-- Set min date for hearing datetime input to prevent past dates on scheduleHearingModal  -->
            <script>
              document.addEventListener('DOMContentLoaded', function() {
                // Set max for incident_datetime to now (prevents future selection)
                const incidentInput = document.querySelector('#blotterModal input[name="incident_datetime"]');
                if (incidentInput) {
                  const now = new Date();
                  // Format as yyyy-MM-ddTHH:mm for input[type="datetime-local"]
                  const pad = n => n.toString().padStart(2, '0');
                  const maxVal = now.getFullYear() + '-' +
                    pad(now.getMonth() + 1) + '-' +
                    pad(now.getDate()) + 'T' +
                    pad(now.getHours()) + ':' +
                    pad(now.getMinutes());
                  incidentInput.max = maxVal;
                }
              });



              // Set min for hearing_datetime to now (prevents past selection)
              document.addEventListener('DOMContentLoaded', function() {
                const hearingInput = document.getElementById('hearing_datetime');
                if (hearingInput) {
                  const now = new Date();
                  // Format as yyyy-MM-ddTHH:mm for input[type="datetime-local"]
                  const pad = n => n.toString().padStart(2, '0');
                  const minVal = now.getFullYear() + '-' +
                    pad(now.getMonth() + 1) + '-' +
                    pad(now.getDate()) + 'T' +
                    pad(now.getHours()) + ':' +
                    pad(now.getMinutes());
                  hearingInput.min = minVal;
                }
              });
            </script>

            <!-- View Blottered Individual Script -->
            <script>
              function closeViewBlotteredModal() {
                document.getElementById('viewBlotteredModal').style.display = 'none';
              }

              document.querySelectorAll('.view-blottered-info').forEach(btn => {
                btn.addEventListener('click', function() {
                  const participantId = this.getAttribute('data-id');
                  // Fetch participant and files info
                  fetch('../Process/blotter/viewblottered.php?id=' + encodeURIComponent(participantId))
                    .then(response => response.json())
                    .then(data => {
                      if (data.error) {
                        alert(data.error);
                        return;
                      }
                      // Fill respondent fields
                      document.getElementById('blottered_blotter_id').textContent = data.participant.blotter_id;
                      document.getElementById('blottered_participant_id').textContent = data.participant.blotter_participant_id;
                      document.getElementById('blottered_lastname').value = data.participant.lastname;
                      document.getElementById('blottered_firstname').value = data.participant.firstname;
                      document.getElementById('blottered_middlename').value = data.participant.middlename;
                      document.getElementById('blottered_address').value = data.participant.address;
                      document.getElementById('blottered_age').value = data.participant.age;
                      document.getElementById('blottered_contact_no').value = data.participant.contact_no;
                      document.getElementById('blottered_email').value = data.participant.email;

                      // Fill files table
                      const filesContainer = document.getElementById('blottered_filesContainer');
                      filesContainer.innerHTML = '';
                      if (data.files && data.files.length > 0) {
                        data.files.forEach(file => {
                          let thumbHTML = '';
                          if (file.file_type.startsWith('image/')) {
                            thumbHTML = `<img src="/BarangaySystem/BarangaySystem/${file.file_path}" alt="thumbnail" style="width:60px;height:60px;object-fit:cover;border-radius:4px;">`;
                            filesContainer.innerHTML += `
                            <tr>
                              <td style="text-align:center;">${thumbHTML}</td>
                              <td>${file.file_name}</td>
                              <td>
                                <button onclick="viewImage('/BarangaySystem/BarangaySystem/${file.file_path}')" class="btn btn-primary" style="padding:5px 10px;">
                                  <i class="fas fa-eye"></i> View
                                </button>
                              </td>
                            </tr>
                          `;
                          } else {
                            thumbHTML = `<i class="fas fa-file" style="font-size:30px;color:#666;"></i>`;
                            filesContainer.innerHTML += `
                        <tr>
                          <td style="text-align:center;">${thumbHTML}</td>
                          <td>${file.file_name}</td>
                          <td>
                            <a href="/BarangaySystem/BarangaySystem/${file.file_path}" download="${file.file_name}" class="btn btn-primary" style="padding:5px 10px;">
                              <i class="fas fa-download"></i> Download
                            </a>
                          </td>
                        </tr>
                      `;
                          }
                        });
                      } else {
                        filesContainer.innerHTML = `
                      <tr>
                        <td colspan="3" style="text-align:center;">No uploaded files found.</td>
                      </tr>
                    `;
                      }

                      document.getElementById('viewBlotteredModal').style.display = 'flex';
                    })
                    .catch(() => alert('Failed to fetch blottered individual details.'));
                });
              });
            </script>

            <script>
              (function () {
                // utility to hide known background modals/popups
                function hideBackgroundModals() {
                  const selectors = [
                    '.modal', '.business-popup', '.document-popup', '.unemployment-popup',
                    '.view-modal', '#viewBusinessModal', '.request-modal', '.popupModal', '.business-modal-box'
                  ];
                  selectors.forEach(sel => {
                    document.querySelectorAll(sel).forEach(el => {
                      if (el.style) el.style.display = 'none';
                      el.classList.remove('show');
                    });
                  });
                }

                function escapeHtml(s) {
                  if (s === null || s === undefined) return '';
                  return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                }

                // create / remove a full-screen image viewer overlay
                function showImageViewer(src, alt) {
                  const prev = document.getElementById('vb_image_modal');
                  if (prev) prev.remove();

                  const overlay = document.createElement('div');
                  overlay.id = 'vb_image_modal';
                  overlay.className = 'vb-overlay';
                  overlay.innerHTML = `
      <div class="vb-img-wrapper">
        <img id="vb_image_full" src="${src}" alt="${alt || ''}" />
        <button id="vb_image_close" aria-label="Close image">&times;</button>
      </div>
    `;
                  document.body.appendChild(overlay);
                  document.body.classList.add('modal-open');

                  function removeViewer() {
                    const v = document.getElementById('vb_image_modal');
                    if (v) v.remove();
                    document.body.classList.remove('modal-open');
                  }

                  overlay.addEventListener('click', function (ev) {
                    if (ev.target === overlay || ev.target.id === 'vb_image_close') removeViewer();
                  });
                  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') removeViewer(); }, { once: true });
                }

                document.addEventListener('click', function (e) {
                  const a = e.target.closest('a.action-btn-2.view');
                  if (!a) return;
                  const href = a.getAttribute('href') || '';
                  if (!href.includes('viewbusiness.php')) return;
                  e.preventDefault();
                  hideBackgroundModals();

                  const prev = document.getElementById('viewBusinessModal');
                  if (prev) prev.remove();

                  const modal = document.createElement('div');
                  modal.id = 'viewBusinessModal';
                  modal.className = 'vb-modal';
                  modal.innerHTML = `
      <div class="vb-card">
        <button id="vb_close" class="vb-close" aria-label="Close">&times;</button>
        <div class="vb-header">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
          <h2>Business Request Details</h2>
        </div>

        <div id="vb_media" class="vb-media">
          <img id="vb_proof_img" src="" alt="Proof Image" class="vb-proof" />
        </div>

        <div id="vb_content" class="vb-content"></div>
        <div class="vb-footer">
          <button id="vb_close2" class="vb-btn">Close</button>
        </div>
      </div>
    `;
                  document.body.classList.add('modal-open');
                  if (!document.getElementById('modalOpenStyle')) {
                    const style = document.createElement('style');
                    style.id = 'modalOpenStyle';
                    style.innerHTML = 'body.modal-open{overflow:hidden;height:100%;}';
                    document.head.appendChild(style);
                  }
                  document.body.appendChild(modal);

                  function closeModal() {
                    const m = document.getElementById('viewBusinessModal');
                    if (m) m.remove();
                    document.body.classList.remove('modal-open');
                  }
                  modal.addEventListener('click', ev => { if (ev.target === modal) closeModal(); });
                  modal.querySelector('#vb_close').addEventListener('click', closeModal);
                  modal.querySelector('#vb_close2').addEventListener('click', closeModal);

                  fetch(href, { credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(json => {
                      if (json.error) {
                        closeModal();
                        alert(json.error || 'Failed to load record.');
                        return;
                      }
                      const d = json.data || {};
                      const content = document.getElementById('vb_content');

                      const img = document.getElementById('vb_proof_img');
                      if (d.ProofPath) {
                        img.src = d.ProofPath;
                        img.alt = d.BusinessName ? 'Proof for ' + d.BusinessName : 'Proof Image';
                        img.style.display = 'block';
                        img.onclick = ev => { ev.stopPropagation(); showImageViewer(img.src, img.alt); };
                      } else {
                        img.style.display = 'none';
                      }

                      const fields = [
                        ["Business ID", d.BsnssID],
                        ["Reference No.", d.refno],
                        ["Owner Name", d.OwnerName],
                        ["Business Name", d.BusinessName],
                        ["Business Location", d.BusinessLoc],
                        ["Request Type", d.RequestType],
                        ["Requested Date", d.RequestedDate],
                        ["Status", d.RequestStatus],
                      ];
                      content.innerHTML = fields.map(([label, value]) => `
          <label class="vb-field">
            <span>${escapeHtml(label)}</span>
            <input readonly value="${escapeHtml(value || '')}" />
          </label>
        `).join('');
                    })
                    .catch(err => {
                      console.error(err);
                      closeModal();
                      alert('Failed to load business details.');
                    });
                });
              })();
            </script>

            <style>
              .vb-modal {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                z-index: 99999;
                animation: fadeIn 0.25s ease-out;
              }

              .vb-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 16px;
                width: 100%;
                max-width: 720px;
                box-shadow: 0 8px 30px rgba(87, 87, 87, 0.65);
                padding: 24px 28px;
                position: relative;
                animation: scaleIn 0.25s ease-out;
              }

              .vb-header {
                text-align: center;
                margin-bottom: 18px;
              }

              .vb-logo {
                width: 90px;
                height: 90px;
                border-radius: 50%;
                margin-bottom: 10px;
                object-fit: cover;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
              }

              .vb-close {
                position: absolute;
                top: 14px;
                right: 14px;
                font-size: 26px;
                background: none;
                border: none;
                color: #555;
                cursor: pointer;
                transition: 0.2s;
              }

              .vb-close:hover {
                color: #000;
                transform: scale(1.1);
              }

              .vb-media {
                text-align: center;
                margin-bottom: 16px;
              }

              .vb-proof {
                display: none;
                max-width: 100%;
                max-height: 260px;
                border-radius: 12px;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.15);
                cursor: zoom-in;
                transition: transform 0.25s ease;
              }

              .vb-proof:hover {
                transform: scale(1.02);
              }

              .vb-content {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 14px;
              }

              .vb-field span {
                display: block;
                font-size: 13px;
                color: #555;
                margin-bottom: 4px;
                font-weight: 500;
              }

              .vb-field input {
                width: 100%;
                padding: 8px 10px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background: #f9f9f9;
                font-size: 14px;
                color: #333;
              }

              .vb-btn {
                padding: 8px 16px;
                border: none;
                border-radius: 8px;
                background: #28a745;
                color: #fff;
                font-size: 14px;
                cursor: pointer;
                transition: background 0.2s ease;
              }

              .vb-btn:hover {
                background: #15803d;
              }

              .vb-footer {
                text-align: right;
                margin-top: 18px;
              }

              /* Fullscreen image viewer */
              .vb-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.9);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 100000;
                animation: fadeIn 0.3s ease-out;
              }

              .vb-img-wrapper {
                position: relative;
                max-width: 95%;
                max-height: 95%;
              }

              .vb-img-wrapper img {
                max-width: 100%;
                max-height: 100%;
                border-radius: 12px;
                box-shadow: 0 8px 40px rgba(0, 0, 0, 0.5);
              }

              .vb-img-wrapper button {
                position: absolute;
                top: 8px;
                right: 10px;
                background: rgba(0, 0, 0, 0.5);
                color: #fff;
                border: none;
                font-size: 24px;
                border-radius: 8px;
                cursor: pointer;
                padding: 4px 10px;
                transition: background 0.2s;
              }

              .vb-img-wrapper button:hover {
                background: rgba(0, 0, 0, 0.7);
              }

              @keyframes fadeIn {
                from {
                  opacity: 0;
                }

                to {
                  opacity: 1;
                }
              }

              @keyframes scaleIn {
                from {
                  transform: scale(0.95);
                  opacity: 0;
                }

                to {
                  transform: scale(1);
                  opacity: 1;
                }
              }
            </style>


            <script>
              (function () {
                function escapeHtml(s) { if (s === null || s === undefined) return ''; return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

                // hide known popups so background forms don't remain visible/interactive
                function hideBackgroundModals() {
                  const selectors = [
                    '#viewModal', '.view-modal', '.popup', '.document-popup', '.business-popup',
                    '#addDocumentModal', '#addBusinessModal', '.modal', '.modal-popup', '#viewBlotterModal'
                  ];
                  selectors.forEach(sel => document.querySelectorAll(sel).forEach(el => {
                    if (!el) return;
                    el.style.display = 'none';
                    el.classList.remove('show', 'open', 'active');
                  }));
                }

                // fullscreen image viewer used by both document & business viewers
                function showImageViewer(src, alt) {
                  const prev = document.getElementById('doc_image_modal');
                  if (prev) prev.remove();
                  const overlay = document.createElement('div');
                  overlay.id = 'doc_image_modal';
                  overlay.className = 'vb-overlay';
                  overlay.innerHTML = `<div class="vb-img-wrapper"><img src="${escapeHtml(src)}" alt="${escapeHtml(alt || '')}" /><button id="doc_img_close">&times;</button></div>`;
                  document.body.appendChild(overlay);
                  document.body.classList.add('modal-open');
                  overlay.addEventListener('click', function (e) { if (e.target === overlay || e.target.id === 'doc_img_close') { overlay.remove(); document.body.classList.remove('modal-open'); } });
                  document.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { overlay.remove(); document.body.classList.remove('modal-open'); } }, { once: true });
                }

                document.addEventListener('click', function (e) {
                  const a = e.target.closest('a[href*="viewdocument.php"], button[data-viewdocument-id]');
                  if (!a) return;

                  // get href or data-id
                  let href = a.getAttribute('href') || '';
                  let id = a.dataset.viewdocumentId || null;
                  if (!href && id) href = 'viewdocument.php?id=' + encodeURIComponent(id);
                  if (!href.includes('viewdocument.php')) return;

                  e.preventDefault();

                  // hide any existing in-page popups that could show behind modal
                  hideBackgroundModals();

                  // remove existing modal if any
                  const prev = document.getElementById('viewDocumentModal');
                  if (prev) prev.remove();

                  // create modal container (higher z-index to ensure it sits on top)
                  const modal = document.createElement('div');
                  modal.id = 'viewDocumentModal';
                  modal.style.position = 'fixed';
                  modal.style.inset = '0';
                  modal.style.display = 'flex';
                  modal.style.alignItems = 'center';
                  modal.style.justifyContent = 'center';
                  modal.style.background = 'rgba(0, 0, 0, 0.36)'; /* darker overlay to hide background form */
                  modal.style.zIndex = '120000';
                  modal.style.padding = '20px';

                  modal.innerHTML = `
      <div style="background:#fff;border-radius:12px;max-width:720px;width:100%;padding:20px;position:relative;box-shadow:0 8px 30px rgba(0,0,0,0.4);">
        <button id="vd_close" aria-label="Close" style="position:absolute;right:12px;top:10px;border:none;background:transparent;font-size:22px;cursor:pointer;">&times;</button>
        <div style="text-align:center;margin-bottom:10px;">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" style="width:90px;height:90px;border-radius:50%;object-fit:cover;"/>
          <h3 style="margin:8px 0 0;">Document Request Information</h3>
        </div>
        <div id="vd_media" style="text-align:center;margin-bottom:12px;">
          <img id="vd_cert_img" src='' alt='Certificate Image' style="max-width:100%;max-height:260px;border-radius:10px;display:none;cursor:zoom-in;box-shadow:0 6px 20px rgba(0,0,0,0.15)" />
        </div>
        <div id="vd_content" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"></div>
        <div style="text-align:right;margin-top:14px;"><button id="vd_close2" style="padding:8px 14px;border-radius:8px;border:none;background:#28a745;color:#fff;cursor:pointer;">Close</button></div>
      </div>
    `;

                  // prevent page scrolling & interactions
                  document.body.classList.add('modal-open');
                  if (!document.getElementById('modalOpenStyle')) {
                    const s = document.createElement('style');
                    s.id = 'modalOpenStyle';
                    s.innerHTML = 'body.modal-open{overflow:hidden;height:100%;} body.modal-open .panel-content, body.modal-open .sidebar, body.modal-open .main-content-scroll{filter:blur(4px);pointer-events:none;user-select:none;}';
                    document.head.appendChild(s);
                  }

                  document.body.appendChild(modal);

                  function closeModal() { const m = document.getElementById('viewDocumentModal'); if (m) m.remove(); document.body.classList.remove('modal-open'); }
                  modal.addEventListener('click', function (ev) { if (ev.target === modal) closeModal(); });
                  modal.querySelector('#vd_close').addEventListener('click', closeModal);
                  modal.querySelector('#vd_close2').addEventListener('click', closeModal);

                  fetch(href, { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(json => {
                      if (json.error) { closeModal(); alert(json.error || 'Failed to load'); return; }
                      const d = json.data || {};
                      const content = document.getElementById('vd_content');
                      const img = document.getElementById('vd_cert_img');

                      if (d.CertificateImage) {
                        img.src = d.CertificateImage;
                        img.alt = d.Docutype ? 'Certificate - ' + d.Docutype : 'Certificate Image';
                        img.style.display = 'block';
                        img.onclick = function (ev) { ev.stopPropagation(); showImageViewer(img.src, img.alt); };
                      } else {
                        img.style.display = 'none';
                      }

                      const fields = [
                        ['Request ID', d.ReqId],
                        ['Reference No.', d.refno],
                        ['Firstname', d.Firstname],
                        ['Lastname', d.Lastname],
                        ['Gender', d.Gender],
                        ['Contact No.', d.ContactNo],
                        ['Address', d.Address],
                        ['Document Type', d.Docutype],
                        ['Requested Date', d.DateRequested],
                        ['Status', d.RequestStatus],
                      ];
                      content.innerHTML = fields.map(f => `
          <label style="display:block;font-size:13px;color:#333;">
            <div style="font-size:12px;color:#666;margin-bottom:4px;">${escapeHtml(f[0])}</div>
            <input readonly value="${escapeHtml(f[1] || '')}" style="width:100%;padding:8px;border:1px solid #e1e1e1;border-radius:8px;background:#fafafa;" />
          </label>
        `).join('');
                    })
                    .catch(err => { console.error(err); closeModal(); alert('Failed to load document details.'); });
                });
              })();
            </script>

            <script>
              (function () {
                function escapeHtml(s) { if (s === null || s === undefined) return ''; return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

                // reuse helper that hides other popups so background forms won't show
                function hideBackgroundModals() {
                  const selectors = [
                    '#viewModal', '.view-modal', '.popup', '.document-popup', '.business-popup',
                    '#addDocumentModal', '#addBusinessModal', '.modal', '.modal-popup', '#viewBlotterModal', '#addUnemploymentModal'
                  ];
                  selectors.forEach(sel => document.querySelectorAll(sel).forEach(el => {
                    if (!el) return;
                    el.style.display = 'none';
                    el.classList.remove('show', 'open', 'active');
                  }));
                }

                // open full-screen image viewer (if you ever add images)
                function showImageViewer(src, alt) {
                  const prev = document.getElementById('unemp_image_modal');
                  if (prev) prev.remove();
                  const overlay = document.createElement('div');
                  overlay.id = 'unemp_image_modal';
                  overlay.className = 'vb-overlay';
                  overlay.innerHTML = `<div class="vb-img-wrapper"><img src="${escapeHtml(src)}" alt="${escapeHtml(alt || '')}" /><button id="unemp_img_close">&times;</button></div>`;
                  document.body.appendChild(overlay);
                  document.body.classList.add('modal-open');
                  overlay.addEventListener('click', function (e) { if (e.target === overlay || e.target.id === 'unemp_img_close') { overlay.remove(); document.body.classList.remove('modal-open'); } });
                  document.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { overlay.remove(); document.body.classList.remove('modal-open'); } }, { once: true });
                }

                // click handler for unemployment view links/buttons
                document.addEventListener('click', function (e) {
                  const trigger = e.target.closest('a[href*="viewunemployment.php"], button[data-viewunemployment-id]');
                  if (!trigger) return;

                  // resolve href or data-id
                  let href = trigger.getAttribute('href') || '';
                  const id = trigger.dataset.viewunemploymentId || null;
                  if (!href && id) href = 'viewunemployment.php?id=' + encodeURIComponent(id);
                  if (!href.includes('viewunemployment.php')) return;

                  e.preventDefault();
                  hideBackgroundModals();

                  // remove any previous modal
                  const prev = document.getElementById('viewUnemploymentModal');
                  if (prev) prev.remove();

                  // create modal container (uses existing vb styles)
                  const modal = document.createElement('div');
                  modal.id = 'viewUnemploymentModal';
                  modal.className = 'vb-modal';
                  modal.innerHTML = `
      <div class="vb-card">
        <button id="vunemp_close" class="vb-close" aria-label="Close">&times;</button>
        <div class="vb-header">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
          <h2>Unemployment Request Details</h2>
        </div>

        <div id="vunemp_content" class="vb-content" style="margin-top:6px;"></div>

        <div class="vb-footer">
          <button id="vunemp_close2" class="vb-btn">Close</button>
        </div>
      </div>
    `;

                  document.body.appendChild(modal);
                  document.body.classList.add('modal-open');
                  if (!document.getElementById('modalOpenStyle')) {
                    const style = document.createElement('style');
                    style.id = 'modalOpenStyle';
                    style.innerHTML = 'body.modal-open{overflow:hidden;height:100%;}';
                    document.head.appendChild(style);
                  }

                  function closeModal() { const m = document.getElementById('viewUnemploymentModal'); if (m) m.remove(); document.body.classList.remove('modal-open'); }
                  modal.addEventListener('click', function (ev) { if (ev.target === modal) closeModal(); });
                  modal.querySelector('#vunemp_close').addEventListener('click', closeModal);
                  modal.querySelector('#vunemp_close2').addEventListener('click', closeModal);

                  // fetch data and populate fields
                  fetch(href, { credentials: 'same-origin' })
                    .then(r => r.json())
                    .then(json => {
                      if (json.error) { closeModal(); alert(json.error || 'Failed to load record.'); return; }
                      const d = json.data || {};
                      const content = document.getElementById('vunemp_content');

                      // build readonly field set (no image)
                      const fields = [
                        ['Request ID', d.id || d.ReqId || ''],
                        ['Reference No.', d.refno || ''],
                        ['Fullname', d.fullname || d.Fullname || ''],
                        ['Age', d.age || ''],
                        ['Address', d.address || ''],
                        ['Unemployed Since', d.unemployed_since || d.unemployedSince || ''],
                        ['Certificate Type', d.certificate_type || d.certificateType || ''],
                        ['Requested Date', d.request_date || d.requestDate || ''],
                        ['Status', d.RequestStatus || d.Requeststatus || ''],
                      ];

                      content.innerHTML = fields.map(f => `
          <label class="vb-field">
            <span>${escapeHtml(f[0])}</span>
            <input readonly value="${escapeHtml(f[1] || '')}" />
          </label>
        `).join('');
                    })
                    .catch(err => {
                      console.error(err);
                      closeModal();
                      alert('Failed to load unemployment details.');
                    });
                });
              })();
            </script>

            <script>
              (function () {
                function escapeHtml(s) {
                  if (s === null || s === undefined) return '';
                  return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                }

                // Hide known background modals/popups to prevent overlap
                function hideBackgroundModals() {
                  const selectors = [
                    '.modal', '.business-popup', '.document-popup', '.unemployment-popup', '.guardianship-popup',
                    '.view-modal', '#viewBusinessModal', '#viewDocumentModal', '#viewUnemploymentModal', '.request-modal', '.popupModal', '.business-modal-box'
                  ];
                  selectors.forEach(sel => {
                    document.querySelectorAll(sel).forEach(el => {
                      if (el.style) el.style.display = 'none';
                      el.classList.remove('show');
                    });
                  });
                }

                // Click handler for guardianship view links
                document.addEventListener('click', function (e) {
                  const a = e.target.closest('a.action-btn-2.view');
                  if (!a) return;
                  const href = a.getAttribute('href') || '';
                  if (!href.includes('viewguardianship.php')) return;
                  e.preventDefault();
                  hideBackgroundModals();

                  // Remove any existing modal
                  const prev = document.getElementById('viewGuardianshipModal');
                  if (prev) prev.remove();

                  // Create modal container (reuses vb styles for consistency)
                  const modal = document.createElement('div');
                  modal.id = 'viewGuardianshipModal';
                  modal.className = 'vb-modal';
                  modal.innerHTML = `
      <div class="vb-card">
        <button id="vg_close" class="vb-close" aria-label="Close">&times;</button>
        <div class="vb-header">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
          <h2>Guardianship Request Details</h2>
        </div>
        <div id="vg_content" class="vb-content"></div>
        <div class="vb-footer">
          <button id="vg_close2" class="vb-btn">Close</button>
        </div>
      </div>
    `;

                  document.body.appendChild(modal);
                  document.body.classList.add('modal-open');
                  if (!document.getElementById('modalOpenStyle')) {
                    const style = document.createElement('style');
                    style.id = 'modalOpenStyle';
                    style.innerHTML = 'body.modal-open{overflow:hidden;height:100%;}';
                    document.head.appendChild(style);
                  }

                  function closeModal() {
                    const m = document.getElementById('viewGuardianshipModal');
                    if (m) m.remove();
                    document.body.classList.remove('modal-open');
                  }
                  modal.addEventListener('click', ev => { if (ev.target === modal) closeModal(); });
                  modal.querySelector('#vg_close').addEventListener('click', closeModal);
                  modal.querySelector('#vg_close2').addEventListener('click', closeModal);

                  // Fetch data and populate modal
                  fetch(href, { credentials: 'same-origin' })
                    .then(response => response.json())
                    .then(json => {
                      if (json.error) {
                        closeModal();
                        alert(json.error || 'Failed to load record.');
                        return;
                      }
                      const d = json.data || {};
                      const content = document.getElementById('vg_content');

                      // Define fields to display (based on PHP endpoint)
                      const fields = [
                        ['Request ID', d.id],
                        ['Reference No.', d.refno],
                        ['Applicant Name', d.applicant_name],
                        ['Request Type', d.request_type],
                        ['Child Name', d.child_name],
                        ['Child Age', d.child_age],
                        ['Child Address', d.child_address],
                        ['Request Date', d.request_date],
                        ['Status', d.RequestStatus],
                      ];

                      // Render fields as readonly inputs
                      content.innerHTML = fields.map(([label, value]) => `
          <label class="vb-field">
            <span>${escapeHtml(label)}</span>
            <input readonly value="${escapeHtml(value || '')}" />
          </label>
        `).join('');
                    })
                    .catch(err => {
                      console.error(err);
                      closeModal();
                      alert('Failed to load guardianship details.');
                    });
                });
              })();
            </script>
       <script>
(function () {
  function escapeHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function hideBackgroundModals() {
    const selectors = [
      '.modal', '.document-popup', '.business-popup', '.unemployment-popup', '.guardianship-popup',
      '#viewDocumentModal', '#viewUnemploymentModal', '#viewGuardianshipModal'
    ];
    selectors.forEach(sel => {
      document.querySelectorAll(sel).forEach(el => {
        if (el.style) el.style.display = 'none';
        el.classList.remove('show');
      });
    });
  }

  function showImageViewer(src, alt) {
    const prev = document.getElementById('user_image_modal');
    if (prev) prev.remove();
    const overlay = document.createElement('div');
    overlay.id = 'user_image_modal';
    overlay.className = 'vb-overlay';
    overlay.innerHTML = `
      <div class="vb-img-wrapper">
        <img src="${escapeHtml(src)}" alt="${escapeHtml(alt || '')}" />
        <button id="user_img_close">&times;</button>
      </div>`;
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay || e.target.id === 'user_img_close') {
        overlay.remove();
        document.body.classList.remove('modal-open');
      }
    });
    document.addEventListener('keydown', function esc(e) {
      if (e.key === 'Escape') {
        overlay.remove();
        document.body.classList.remove('modal-open');
      }
    }, { once: true });
  }

  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[href*="viewusers.php"], button[data-viewuser-id]');
    if (!a) return;

    let href = a.getAttribute('href') || '';
    const id = a.dataset.viewuserId || null;
    if (!href && id) href = 'viewusers.php?id=' + encodeURIComponent(id);
    if (!href.includes('viewusers.php')) return;

    e.preventDefault();
    hideBackgroundModals();

    // Remove existing modal if open
    const prev = document.getElementById('viewUserModal');
    if (prev) prev.remove();

    const modal = document.createElement('div');
    modal.id = 'viewUserModal';
    modal.className = 'vb-modal';
    modal.innerHTML = `
      <div class="vb-card" style="max-width:720px;">
        <button id="vu_close" class="vb-close" aria-label="Close">&times;</button>
        <div class="vb-header">
          <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
          <h2>User Information</h2>
        </div>
        <div id="vu_alert" style="display:none;padding:10px;margin:10px 20px;border-radius:5px;"></div>
        <div id="vu_media" class="vb-content" style="text-align:center;margin-bottom:12px;">
          <img id="vu_valid_img" src='' alt='Valid ID' style="max-width:100%;max-height:260px;border-radius:10px;display:none;cursor:zoom-in;box-shadow:0 6px 20px rgba(0,0,0,0.15)" />
        </div>
        <form id="vu_form">
          <div id="vu_content" class="vb-content" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"></div>
        </form>
        <div class="vb-footer" style="display:flex;gap:10px;justify-content:space-between;">
          <button id="vu_delete_btn" class="vb-btn" type="button" style="background:#dc3545;color:white;">Delete Account</button>
          <div style="display:flex;gap:10px;">
            <button id="vu_edit_btn" class="vb-btn" type="button" style="background:#667eea;color:white;">Edit</button>
            <button id="vu_save_btn" class="vb-btn" type="button" style="background:#28a745;color:white;display:none;">Save Changes</button>
            <button id="vu_cancel_btn" class="vb-btn" type="button" style="background:#6c757d;color:white;display:none;">Cancel</button>
            <button id="vu_close2" class="vb-btn">Close</button>
          </div>
        </div>
      </div>
    `;

    document.body.appendChild(modal);
    document.body.classList.add('modal-open');
    if (!document.getElementById('modalOpenStyle')) {
      const style = document.createElement('style');
      style.id = 'modalOpenStyle';
      style.innerHTML = 'body.modal-open{overflow:hidden;height:100%;}';
      document.head.appendChild(style);
    }

    let userData = {};
    let isEditMode = false;

    function closeModal() {
      const m = document.getElementById('viewUserModal');
      if (m) m.remove();
      document.body.classList.remove('modal-open');
    }

    function showAlert(message, type) {
      const alert = document.getElementById('vu_alert');
      alert.style.display = 'block';
      alert.style.background = type === 'success' ? '#d4edda' : '#f8d7da';
      alert.style.color = type === 'success' ? '#155724' : '#721c24';
      alert.style.border = type === 'success' ? '1px solid #c3e6cb' : '1px solid #f5c6cb';
      alert.textContent = message;
      setTimeout(() => { alert.style.display = 'none'; }, 5000);
    }

    function updateEditButtonState() {
      const editBtn = document.getElementById('vu_edit_btn');
      const accountStatus = (userData.AccountStatus || '').toLowerCase();
      
      // Only allow editing for 'pending' or 'verified' status
      const canEdit = accountStatus === 'pending' || accountStatus === 'verified';
      
      if (canEdit) {
        editBtn.disabled = false;
        editBtn.style.opacity = '1';
        editBtn.style.cursor = 'pointer';
        editBtn.title = '';
      } else {
        editBtn.disabled = true;
        editBtn.style.opacity = '0.5';
        editBtn.style.cursor = 'not-allowed';
        editBtn.title = 'Edit is not available for unverified accounts';
      }
    }

    function renderFields(editMode) {
      const content = document.getElementById('vu_content');
      
      const fields = [
        { label: 'User ID', key: 'UserID', readonly: true },
        { label: 'Firstname', key: 'Firstname', readonly: false },
        { label: 'Lastname', key: 'Lastname', readonly: false },
        { label: 'Middlename', key: 'Middlename', readonly: false },
        { label: 'Gender', key: 'Gender', readonly: false, type: 'select', options: ['Male', 'Female'] },
        { label: 'Birthdate', key: 'Birthdate', readonly: false, type: 'date' },
        { label: 'Email', key: 'Email', readonly: false, type: 'email' },
        { label: 'Contact No.', key: 'ContactNo', readonly: false },
        { label: 'Address', key: 'Address', readonly: false, fullWidth: true },
        { label: 'Birthplace', key: 'Birthplace', readonly: false },
        { label: 'Civil Status', key: 'CivilStatus', readonly: false, type: 'select', options: ['Single', 'Married', 'Widowed', 'Separated'] },
        { label: 'Nationality', key: 'Nationality', readonly: false },
        { label: 'Account Status', key: 'AccountStatus', readonly: true }
      ];

      content.innerHTML = fields.map(field => {
        const value = userData[field.key] || '';
        const isReadonly = field.readonly || !editMode;
        const style = field.fullWidth ? 'grid-column:1/-1;' : '';
        
        let inputHtml;
        if (field.type === 'select' && editMode && !field.readonly) {
          inputHtml = `
            <select name="${field.key}" ${isReadonly ? 'disabled' : ''} style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;background:white;">
              ${field.options.map(opt => `<option value="${opt}" ${opt === value ? 'selected' : ''}>${opt}</option>`).join('')}
            </select>
          `;
        } else {
          const inputType = field.type || 'text';
          inputHtml = `<input 
            type="${inputType}" 
            name="${field.key}" 
            value="${escapeHtml(value)}" 
            ${isReadonly ? 'readonly' : ''} 
            style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;${isReadonly ? 'background:#f5f5f5;' : 'background:white;'}"
          />`;
        }

        return `
          <label class="vb-field" style="${style}">
            <span style="font-weight:600;color:#555;font-size:12px;text-transform:uppercase;margin-bottom:5px;display:block;">${escapeHtml(field.label)}</span>
            ${inputHtml}
          </label>
        `;
      }).join('');
    }

    function toggleEditMode(edit) {
      isEditMode = edit;
      document.getElementById('vu_edit_btn').style.display = edit ? 'none' : 'inline-block';
      document.getElementById('vu_save_btn').style.display = edit ? 'inline-block' : 'none';
      document.getElementById('vu_cancel_btn').style.display = edit ? 'inline-block' : 'none';
      renderFields(edit);
    }

    modal.addEventListener('click', ev => { if (ev.target === modal) closeModal(); });
    modal.querySelector('#vu_close').addEventListener('click', closeModal);
    modal.querySelector('#vu_close2').addEventListener('click', closeModal);
    
    modal.querySelector('#vu_edit_btn').addEventListener('click', () => {
      const accountStatus = (userData.AccountStatus || '').toLowerCase();
      if (accountStatus !== 'pending' && accountStatus !== 'verified') {
        showAlert('Edit is not available for unverified accounts', 'error');
        return;
      }
      toggleEditMode(true);
    });

    modal.querySelector('#vu_cancel_btn').addEventListener('click', () => {
      toggleEditMode(false);
    });

    modal.querySelector('#vu_save_btn').addEventListener('click', () => {
      const form = document.getElementById('vu_form');
      const formData = new FormData(form);
      const updateData = {};
      
      for (let [key, value] of formData.entries()) {
        updateData[key] = value;
      }
      
      // Send update request
      fetch(href.split('?')[0], {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(updateData)
      })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          showAlert('User information updated successfully!', 'success');
          // Update userData with new values
          Object.assign(userData, updateData);
          toggleEditMode(false);
        } else {
          showAlert(json.error || 'Failed to update user information', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        showAlert('Error updating user information', 'error');
      });
    });

    modal.querySelector('#vu_delete_btn').addEventListener('click', () => {
      if (!confirm('Are you sure you want to delete this user account? This action cannot be undone.')) {
        return;
      }
      
      // Send delete request
      fetch('viewusers.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          UserID: userData.UserID,
          action: 'delete'
        })
      })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          alert('User account deleted successfully!');
          closeModal();
          // Reload the page to refresh the user list
          window.location.reload();
        } else {
          showAlert(json.error || 'Failed to delete user account', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        showAlert('Error deleting user account', 'error');
      });
    });

    // Fetch user data
    fetch(href, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (json.error) {
          closeModal();
          alert(json.error || 'Failed to load user record.');
          return;
        }
        userData = json.data || {};
        const img = document.getElementById('vu_valid_img');

        // Show Valid ID image if available
        if (userData.ValidID) {
          img.src = userData.ValidID;
          img.style.display = 'block';
          img.onclick = function (ev) { ev.stopPropagation(); showImageViewer(img.src, 'Valid ID'); };
        } else {
          img.style.display = 'none';
        }

        // Render user info in view mode
        renderFields(false);
        
        // Update edit button state based on account status
        updateEditButtonState();
      })
      .catch(err => {
        console.error(err);
        closeModal();
        alert('Failed to load user details.');
      });
  });
})();
</script>
</body>

</html>
