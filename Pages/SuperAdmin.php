<?php include 'dashboard.php';

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
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-12 col-md-2 sidebar">
        <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
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
             <a href="#" onclick="showPanel('nobirthcertificatePanel')">Birth Certificate</a>
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
          <i class="fas fa-history"></i> Audit Trail
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
              <div class="chart-box">
                <h4>Civil Status Distribution</h4>
                <canvas id="civilChart"></canvas>
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

            <!-- Search Form -->
            <form method="GET" action="" class="mb-3 search-form">
              <div class="search-form-group">
                <input type="text" name="search_lastname" class="form-control search-input"
                  placeholder="Search by Lastname"
                  value="<?php echo isset($_GET['search_lastname']) ? htmlspecialchars($_GET['search_lastname']) : ''; ?>">
                <button type="submit" class="search-btn">
                  <i class="fas fa-search"></i> Search
                </button>
                <!-- <button class="add-user" type="button" onclick="openModal()">
                  <i class="fa-regular fa-user"></i> Add
                </button> -->
                <button class="print-btn" type="button" onclick="printTable()">
                  <i class="fas fa-print"></i> Print
                </button>
              </div>
            </form>

            <!-- Table -->
            <div class="scrollable-table-container">
              <table class="styled-table">
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
                  <?php
                  $servername = "localhost";
                  $username = "root";
                  $password = "";
                  $database = "barangayDb";

                  $connection = new mysqli($servername, $username, $password, $database);

                  if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                  }

                  if (isset($_GET['search_lastname']) && !empty(trim($_GET['search_lastname']))) {
                    $search = $connection->real_escape_string($_GET['search_lastname']);
                    $sql = "SELECT UserID, Firstname, Lastname, Middlename, Email, ContactNo, Address, Birthdate, Gender, Birthplace, CivilStatus, Nationality, AccountStatus, ValidID
                            FROM userloginfo
                            WHERE Lastname LIKE '%$search%'";
                } else {
                    $sql = "SELECT UserID, Firstname, Lastname, Middlename, Email, ContactNo, Address, Birthdate, Gender, Birthplace, CivilStatus, Nationality, AccountStatus, ValidID
                            FROM userloginfo";
                }

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

                $result = $connection->query($sql);

                if (!$result) {
                    die("Invalid query: " . $connection->error);
                }

                while ($row = $result->fetch_assoc()) {
                    // Encode the ValidID (blob) as base64 for the data attribute
                    $validIDBase64 = base64_encode($row["ValidID"]);  // Assuming ValidID is the column name

                    echo "<tr>
                        <td>" . $row["UserID"] . "</td>
                        <td>" . strtoupper($row["Firstname"]) . "</td>
                        <td>" . strtoupper($row["Lastname"]) . "</td>
                        <td>" . strtoupper($row["Middlename"]) . "</td>
                        <td>" . $row["Email"] . "</td>
                        <td>" . $row["AccountStatus"] . "</td>
                        <td>
                            <button 
                                class='action-btn-2 view' 
                                data-id='" . $row["UserID"] . "'
                                data-firstname='" . strtoupper($row["Firstname"]) . "'
                                data-lastname='" . strtoupper($row["Lastname"]) . "'
                                data-middlename='" . strtoupper($row["Middlename"]) . "'
                                data-email='" . $row["Email"] . "'
                                data-contact='" . $row["ContactNo"] . "'
                                data-address='" . htmlspecialchars($row["Address"]) . "'
                                data-birthdate='" . $row["Birthdate"] . "'
                                data-gender='" . $row["Gender"] . "'
                                data-birthplace='" . htmlspecialchars($row["Birthplace"]) . "'
                                data-civilstatus='" . $row["CivilStatus"] . "'
                                data-nationality='" . $row["Nationality"] . "'
                                data-accountstatus='" . htmlspecialchars($row["AccountStatus"]) . "'
                                data-validid='" . $validIDBase64 . "'>  <!-- Added data attribute for ValidID -->
                                <i class='fas fa-eye'></i>
                            </button>";

                    if ($row["AccountStatus"] == "pending") {
                        echo "<a href='approveaccount.php?id=" . $row["UserID"] . "' 
                                class='action-btn-2 approve' 
                                onclick=\"showCustomConfirm(event, this.href);\">
                                <i class='fas fa-check'></i>
                            </a>";
                    }

                    echo "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="viewModal" class="view-modal" style="display:none;">
    <div class="view-modal-content">
        <span class="close-btn">&times;</span>
        <div style="text-align: center;">
            <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                style="width: 70%; max-width: 120px; border-radius: 50%;" />
        </div>
        <h2>User Information</h2>

        <form class="modal-form">
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label>ID</label>
                        <input type="text" id="modalId" readonly>
                    </div>
                    <div class="form-group">
                        <label>Firstname</label>
                        <input type="text" id="modalFirstname" readonly>
                    </div>
                    <div class="form-group">
                        <label>Lastname</label>
                        <input type="text" id="modalLastname" readonly>
                    </div>
                    <div class="form-group">
                        <label>Middlename</label>
                        <input type="text" id="modalMiddlename" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" id="modalEmail" readonly>
                    </div>
                    <div class="form-group">
                        <label>Contact No</label>
                        <input type="text" id="modalContact" readonly>
                    </div>
                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="text" id="modalBirthday" readonly>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <input type="text" id="modalGender" readonly>
                    </div>
                    <!-- New section for ValidID image -->
                    <div class="form-group">
                        <label>Valid ID</label>
                        <img id="modalValidID" src="" alt="Valid ID" style="max-width: 100%; height: auto; border: 1px solid #ccc;">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" id="modalAddress" readonly>
                    </div>
                    <div class="form-group">
                        <label>Birthplace</label>
                        <input type="text" id="modalBirthplace" readonly>
                    </div>
                    <div class="form-group">
                        <label>Civil Status</label>
                        <input type="text" id="modalCivilStat" readonly>
                    </div>
                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" id="modalNationality" readonly>
                    </div>
                    <div class="form-group">
                        <label>Account Status</label>
                        <input type="text" id="modalAccountStatus" readonly>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
         


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
          <th>CONTACT</th>
          <th>ADDRESS</th>
          <th>REFERENCE</th>
          <th>TYPE</th>
          <th>DATE</th>
          <th>STATUS</th> <!-- Added STATUS column -->
        </tr>
      </thead>
      <tbody>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "barangayDb";

        $connection = new mysqli($servername, $username, $password, $database);

        if ($connection->connect_error) {
          die("Connection failed: " . $connection->connect_error);
        }

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
        $sql = "SELECT ReqId, Firstname, Lastname, Gender, ReqPurpose, ContactNo, Address, refno, Docutype, DateRequested, RequestStatus, CertificateImage 
                FROM docsreqtbl WHERE RequestStatus != 'Declined' AND 1=1"; // Base query: Exclude Declined always

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
            <td>" . strtoupper(htmlspecialchars($row["ContactNo"])) . "</td>
            <td>" . strtoupper(htmlspecialchars($row["Address"])) . "</td>
            <td>" . htmlspecialchars($row["refno"]) . "</td>
            <td>" . strtoupper(htmlspecialchars($row["Docutype"])) . "</td>
            <td>" . date("Y-m-d", strtotime($row["DateRequested"])) . "</td>
            <td>" . strtoupper(htmlspecialchars($row['RequestStatus'])) . "</td> <!-- Status Column -->
            <td>";

          $docData = json_encode([
            "refno" => $row['refno'],
            "Firstname" => $row['Firstname'],
            "Lastname" => $row['Lastname'],
            "Docutype" => $row['Docutype'],
            "Address" => $row['Address'],
            "DateRequested" => $row['DateRequested'],
          ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

          // Show PRINT button only if already approved
          // if ($row["RequestStatus"] === "Approved") {
          //   // echo "<button type='button' class='action-btn-2 print' onclick='openPrintModal(JSON.parse(`$docData`))'>
          //   //   <i class='fas fa-print'></i>
          //   // </button>";
          // } else {
          //   // Show APPROVE button if not yet approved/declined (Declined rows won't reach here)
          //   if ($row["RequestStatus"] !== "Declined") {
          //     // echo "<a href='approve.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          //     //   class='action-btn-2 approve' 
          //     //   onclick=\"showCustomConfirm(event, this.href);\">
          //     //   <i class='fas fa-check'></i>
          //     // </a>";
          //   }
          // }

      //    echo "<a href='viewdocument.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
      //       class='action-btn-2 view'> 
    
      //   <i class='fas fa-eye'></i>
      // </button>";


          // Show DECLINE button only if not already Declined/Approved (Declined rows won't reach here)
          // if ($row["RequestStatus"] !== "Declined" && $row["RequestStatus"] !== "Approved") {
          //   // echo "<a href='decline.php?id=" . htmlspecialchars($row["ReqId"]) . "' 
          //   //   class='action-btn-2 decline' 
          //   //   onclick=\"showCustomDeclineConfirm(event, this.href);\">
          //   //   <i class='fas fa-xmark'></i>
          //   // </a>";
          // }

          echo "</td></tr>";
        }

        // Show message if no rows (helps debug empty results)
        // if (!$hasRows) {
        //   echo "<tr><td colspan='10' style='text-align: center; padding: 20px;'>No records found matching the filters.</td></tr>";
        // }

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
                      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
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
                <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
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
                      <option value="First Time Job Seeker Form">First Time Job Seeker Form</option>
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
      <input type="text" name="search_refno" class="govdoc-search-input" placeholder="Search by reference number" autocomplete="off"
        value="<?php echo isset($_GET['search_refno']) ? htmlspecialchars($_GET['search_refno']) : ''; ?>" />
      <button type="submit" class="govdoc-search-button">
        <i class="fas fa-search"></i> Search
      </button>
      <select name="status_filter" class="govdoc-status-filter" onchange="this.form.submit();">
        <option value="all" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'all') ? 'selected' : ''; ?>>All Status</option>
        <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
        <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
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
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "barangayDb";

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
        $sql = "SELECT BsnssID, BusinessName, BusinessLoc, OwnerName, RequestType, refno, RequestedDate, RequestStatus 
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

            <td>";

          // Data for print modal (escaped properly)
          $printData = json_encode([
            "refno" => $row['refno'],
            "OwnerName" => $row['OwnerName'],
            "RequestType" => $row['RequestType'],
            "RequestedDate" => $row['RequestedDate'],
          ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

          if ($row["RequestStatus"] === "Approved") {
            echo "<button type='button' class='action-btn-2 print' onclick='openBusinessPrintModal(" . $printData . ")'>  <!-- Fixed: Removed extra ) -->
              <i class='fas fa-print'></i>
            </button>";
          } else {
            // Show APPROVE button if not yet approved (Declined rows won't reach here due to query)
            echo "<a href='approvebusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
              class='action-btn-2 approve' 
              onclick=\"showCustomConfirm(event, this.href);\">
              <i class='fas fa-check'></i>
            </a>";
          }

          // Business-specific VIEW button (simplified; adjust data attributes to match business schema if needed)
          echo "<a href='viewbusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' class='action-btn-2 view'>  <!-- Fixed: Use business-specific view link and fields -->
            <i class='fas fa-eye'></i>
          </a>";

          // Show DECLINE button only if Pending (not Approved or Declined)
          if ($row["RequestStatus"] === "Pending") {  // Simplified logic since Declined is excluded
            echo "<a href='declinebusiness.php?id=" . htmlspecialchars($row["BsnssID"]) . "' 
               class='action-btn-2 decline' 
               onclick=\"showCustomDeclineConfirm(event, this.href);\">
              <i class='fas fa-xmark'></i>
            </a>";  // Fixed: Removed invalid semicolon after </a>
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
          <div class="modal fade" id="businessPrintFormModal" tabindex="-1" aria-labelledby="businessPrintFormModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="businessPrintForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
                      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
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
                <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
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
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $database = "barangayDb";

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

                $sql = "SELECT id, fullname, certificate_type, refno, request_date, RequestStatus 
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

                        <td>";

                    $docData = json_encode([
                        "refno" => $row['refno'],
                        "fullname" => $row['fullname'],
                        "certificate_type" => $row['certificate_type'],
                        "request_date" => $row['request_date'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                    if ($row["RequestStatus"] === "Approved") {
                        echo "<button type='button' class='action-btn-2 print' onclick='openUnemploymentPrintModal(JSON.parse(`$docData`))'>    
                            <i class='fas fa-print'></i>
                        </button>";
                    } else {
                        // Show APPROVE button if not yet approved
                        if ($row["RequestStatus"] !== "Declined") {
                            echo "<a href='approveunemployement.php?id=" . htmlspecialchars($row["id"]) . "'
                                class='action-btn-2 approve'
                                onclick=\"showCustomConfirm(event, this.href);\">
                                <i class='fas fa-check'></i>
                            </a> ";
                        }
                    }
                    echo "<a href='viewunemployment.php?id=" . htmlspecialchars($row["id"]) . "' class='action-btn-2 view'>
                        <i class='fas fa-eye'></i></a>";

                    // Show DECLINE button only if not already Declined/Approved (Declined rows won't reach here)
                    if ($row["RequestStatus"] !== "Declined" && $row["RequestStatus"] !== "Approved") {
                        echo "<a href='decline.php?id=" . htmlspecialchars($row["id"]) . "' 
                            class='action-btn-2 decline' 
                            onclick=\"showCustomDeclineConfirm(event, this.href);\">
                            <i class='fas fa-xmark'></i>
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

          <div class="modal fade" id="unemploymentPrintFormModal" tabindex="-1" aria-labelledby="unemploymentPrintFormModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="unemploymentPrintForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
                      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                        style="width: 70%; max-width: 120px; border-radius: 50%;" />
                    </div>
                    <h2>Unemployment Certificate Form</h2>
                    <label for="unemployment_modal_refno" class="form-label">Reference No.</label>
                    <input type="text" name="refno" id="unemployment_modal_refno" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_name" class="form-label">Name</label>
                    <input type="text" name="fullname" id="unemployment_modal_name" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_type" class="form-label">Request Type</label>
                    <input type="text" name="certificate_type" id="unemployment_modal_type" readonly required class="form-control" />
                  </div>

                  <div class="mb-3">
                    <label for="unemployment_modal_date" class="form-label">Date</label>
                    <input type="text" name="request_date" id="unemployment_modal_date" readonly required class="form-control" />
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
                <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
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
                  <th>ACTION</th>
                </tr>
                </thead>
                <tbody>
                  <?php
                  $servername = "localhost";
                  $username = "root";
                  $password = "";
                  $database = "barangayDb";

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
    $child_age = (int)$_POST['child_age'];
    $child_address = $_POST['child_address'];
    $request_date = $_POST['request_date'];

    // Prepare statement (prevents SQL injection)
    $insertstmt = $connection->prepare("INSERT INTO guardianshiptbl 
        (refno, applicant_name, request_type, child_name, child_age, child_address, request_date, RequestStatus) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending)");
    $insertstmt->bind_param("ssssiss", 
        $refno, $applicant_name, $request_type, $child_name, $child_age, $child_address, $request_date, $request_status);

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
                  $sql = "SELECT id, applicant_name, request_type, refno, request_date, RequestStatus 
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
             <td>";
                    $docData = json_encode([
                      "refno" => $row['refno'],
                      "applicant_name" => $row['applicant_name'],
                      "request_type" => $row['request_type'],
                      "request_date" => $row['request_date'],
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                    if ($row["RequestStatus"] === "Approved") {

                      echo "<button type='button' class='action-btn-2 print' onclick='openGuardianshipPrintModal(" . htmlspecialchars(json_encode([
                        "refno" => $row['refno'],
                        "applicant_name" => $row['applicant_name'],
                        "request_type" => $row['request_type'],
                        "request_date" => $row['request_date'],
                      ]), ENT_QUOTES, 'UTF-8') . ")'>
    <i class='fas fa-print'></i>
  </button>";
                    } else {
                      // Show APPROVE button if not yet approved
                      echo "<a href='approveguardianship.php?id=" . $row["id"] . "'
    class='action-btn-2 approve'
    onclick=\"showCustomConfirm(event, this.href);\">
    <i class='fas fa-check'></i></a>
  ";
                    }



                    echo "<a href='viewguardianship.php?id=" .htmlspecialchars( $row["id"]) . "' class='action-btn-2 view'>
                    <i class='fas fa-eye'></i></a>";

                    // Show DECLINE button only if not already Declined/Approved (Declined rows won't reach here)
                    if ($row["RequestStatus"] !== "Declined" && $row["RequestStatus"] !== "Approved") {
                      echo "<a href='declineguardianship.php?id=" . htmlspecialchars($row["id"]) . "'
    class='action-btn-2 decline'
    onclick=\"showCustomDeclineConfirm(event, this.href);\">
    <i class='fas fa-xmark'></i>
  </a>";
                    }

                    echo "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal fade" id="guardianshipPrintFormModal" tabindex="-1" aria-labelledby="guardianshipPrintFormModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content p-3">
                <form id="guardianshipPrintForm">
                  <div class="mb-3">
                    <div style="text-align: center;">
                      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                        style="width: 70%; max-width: 120px; border-radius: 50%;" />
                    </div>
                    <h2>Guardianship Document Form</h2>
                    <label for="guardianship_modal_refno" class="form-label
">Reference No.</label>
                    <input type="text" name="refno" id="guardianship_modal_refno" readonly required class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label for="guardianship_modal_name" class="form-label
">Applicant Name</label>
                    <input type="text" name="applicant_name" id="guardianship_modal_name" readonly required class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label for="guardianship_modal_type" class="form-label
">Request Type</label>
                    <input type="text" name="request_type" id="guardianship_modal_type" readonly required class="form-control" />
                  </div>
                  <div class="mb-3">
                    <label for="guardianship_modal_date" class="form-label
">Date</label>
                    <input type="text" name="request_date" id="guardianship_modal_date" readonly required class="form-control" />
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
    <span class="close-btn" onclick="closeGuardianship()">&times;</span>
    <div style="text-align: center;">
      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
        style="width: 70%; max-width: 120px; border-radius: 50%;" />
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


<div id="nobirthcertificatePanel" class="panel-content">
            <h1>No Birth Certificate Request</h1>
            <p>Content for Birth Certificate Request goes here.</p>
          </div>
        



          <div id="itemrequestsPanel" class="panel-content">
            <div class="item-requests-header">
              <h1>Item Requests</h1>
              <button id="openRequestModalBtn" class="item-requests-btn">+ Add Request</button>
            </div>

            <!-- Modal: Add Request -->
            <div id="requestModal" class="request-modal">
              <div class="request-modal-content">
                <span class="request-modal-close">&times;</span>
                <h4>Request Item</h4>
                <form method="POST">
                  <label>Resident Name:</label><br>
                  <input type="text" name="residentName" required><br><br>

                  <label>Select Item:</label><br>
                  <select name="itemSelect" required>
                    <option value="">--Choose an item--</option>
                    <option value="Tent">Tent</option>
                    <option value="Monoblock Chair">Monoblock Chair</option>
                    <option value="Table">Table</option>
                  </select><br><br>

                  <label>Quantity:</label><br>
                  <input type="number" name="quantity" min="1" required><br><br>

                  <label>Purpose of Request:</label><br>
                  <textarea name="purpose" rows="3" required></textarea><br><br>

                  <label>Date & Time Needed:</label><br>
                  <input type="datetime-local" name="eventDatetime" required><br><br>

                  <button type="submit" name="saveItemRequest">Submit Request</button>
                </form>
              </div>
            </div>


            <!-- Popup Modal for messages -->
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
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody class="item-requests-tbody" id="requestsTableBody">
                  <?php
                  $conn = new mysqli("localhost", "root", "", "barangayDb");
                  if ($conn->connect_error) die("DB error: " . $conn->connect_error);

                  // ---------- 1) NEW REQUEST ----------
                  if (isset($_POST['saveItemRequest'])) {
                    $name     = trim($_POST['residentName']);
                    $item     = trim($_POST['itemSelect']);
                    $quantity = (int)$_POST['quantity'];
                    $purpose  = trim($_POST['purpose']);
                    $eventDT  = $_POST['eventDatetime'];

                    // check stock & existing reservations
                    $stmt = $conn->prepare("SELECT total_stock, on_loan FROM inventory WHERE item_name=?");
                    $stmt->bind_param("s", $item);
                    $stmt->execute();
                    $inv = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    $reserved = 0;
                    $rs = $conn->prepare("SELECT SUM(quantity) AS r FROM tblitemrequest
                          WHERE item=? AND RequestStatus IN ('Pending','Approved','On Loan')
                          AND event_datetime=?");
                    $rs->bind_param("ss", $item, $eventDT);
                    $rs->execute();
                    if ($row = $rs->get_result()->fetch_assoc()) $reserved = $row['r'] ?? 0;
                    $rs->close();

                    $available = $inv['total_stock'] - $inv['on_loan'] - $reserved;

                    if ($quantity > $available) {
                      $message = "Request denied: Only $available $item(s) available.";
                    } else {
                      $status = 'Pending';
                      $ins = $conn->prepare(
                        "INSERT INTO tblitemrequest
           (name,Purpose,item,quantity,event_datetime,date,RequestStatus)
           VALUES (?,?,?,?,?,NOW(),?)"
                      );
                      $ins->bind_param("sssiss", $name, $purpose, $item, $quantity, $eventDT, $status);
                      $ins->execute();
                      $ins->close();

                      $message = "Request submitted successfully.";
                    }

                    // Output JS to show popup modal with message
                    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('popupModal');
        var msg = document.getElementById('popupMessage');
        msg.textContent = " . json_encode($message) . ";
        modal.style.display = 'block';
      });
    </script>";
                  }

                  // ---------- 2) ACTION BUTTONS ----------
                  if (isset($_POST['action'], $_POST['id'])) {
                    $id = (int)$_POST['id'];
                    $action = $_POST['action'];

                    switch ($action) {
                      case 'approve':
                        // Get request details
                        $req = $conn->query("SELECT item, quantity, event_datetime FROM tblitemrequest WHERE id=$id")->fetch_assoc();
                        $item = $req['item'];
                        $quantity = $req['quantity'];
                        $eventDT = $req['event_datetime'];

                        // Get inventory
                        $inv = $conn->query("SELECT total_stock, on_loan FROM inventory WHERE item_name='$item'")->fetch_assoc();

                        // Calculate reserved (pending/approved/on loan for same item and datetime)
                        $reserved = 0;
                        $rs = $conn->query("SELECT SUM(quantity) AS r FROM tblitemrequest
                              WHERE item='$item' AND RequestStatus IN ('Pending','Approved','On Loan')
                              AND event_datetime='$eventDT' AND id != $id");
                        if ($row = $rs->fetch_assoc()) $reserved = $row['r'] ?? 0;

                        $available = $inv['total_stock'] - $inv['on_loan'] - $reserved;

                        if ($quantity > $available) {
                          $message = "Cannot approve: Only $available $item(s) available.";
                        } else {
                          $conn->query("UPDATE tblitemrequest SET RequestStatus='Approved' WHERE id=$id");
                          $message = "Request approved successfully.";
                        }

                        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
              var modal = document.getElementById('popupModal');
              var msg = document.getElementById('popupMessage');
              msg.textContent = " . json_encode($message) . ";
              modal.style.display = 'block';
            });
          </script>";
                        break;

                      case 'reject':
                        $reason = $conn->real_escape_string($_POST['reason'] ?? 'Not specified');
                        $conn->query("UPDATE tblitemrequest
                        SET RequestStatus='Rejected', Reason='$reason'
                        WHERE id=$id");
                        break;

                      case 'release': // mark as on loan and increment inventory
                        $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id")->fetch_assoc();
                        $conn->query("UPDATE inventory
                        SET on_loan = on_loan + {$q['quantity']}
                        WHERE item_name = '{$q['item']}'");
                        $conn->query("UPDATE tblitemrequest SET RequestStatus='On Loan' WHERE id=$id");
                        break;

                      case 'return':  // decrement inventory and mark returned
                        $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id")->fetch_assoc();
                        $conn->query("UPDATE inventory
                        SET on_loan = GREATEST(on_loan - {$q['quantity']},0)
                        WHERE item_name = '{$q['item']}'");
                        $conn->query("UPDATE tblitemrequest SET RequestStatus='Returned' WHERE id=$id");
                        break;
                    }
                  }

                  // ---------- 3) DISPLAY TABLE ----------
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
                <td>";

                      // action buttons depend on current status
                      if ($row['RequestStatus'] == 'Pending' || $row['RequestStatus'] == 'Pending/Waitlist') {
                        echo actionBtn($row['id'], 'approve', 'Approve');
                        echo actionBtn($row['id'], 'reject', 'Reject');
                      } elseif ($row['RequestStatus'] == 'Approved') {
                        echo actionBtn($row['id'], 'release', 'Release');
                      } elseif ($row['RequestStatus'] == 'On Loan') {
                        echo actionBtn($row['id'], 'return', 'Return');
                      }
                      echo "</td></tr>";
                    }
                  } else {
                    echo "<tr><td colspan='7'>No item requests found.</td></tr>";
                  }
                  // Singleton connection closed by PHP

                  // helper to render a small form-button
                  function actionBtn($id, $action, $label)
                  {
                    return "<form method='post' style='display:inline'>
                <input type='hidden' name='id' value='$id'>
                <button name='action' value='$action'>$label</button>
            </form>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>





          <!-- blotter complaint panel -->
          <div id="blotterComplaintPanel" class="panel-content">
            <h1>Blotter/Complaint</h1>


            <form method="GET" action="" class="govdoc-search-form">
              <div class="govdoc-search-group">
                <input type="text" name="search_complainant" class="govdoc-search-input"
                  placeholder="Search by Complainant"
                  value="<?php echo isset($_GET['search_complainant']) ? htmlspecialchars($_GET['search_complainant']) : ''; ?>" />
                <button type="submit" class="govdoc-search-button">
                  <i class="fas fa-search"></i> Search
                </button>

                <button class="add-user" type="button" onclick="openBlotterModal()">
                  <i class="fa-regular fa-user"></i> Add Blotter
                </button>
              </div>
            </form>

            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
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
                  $servername = "localhost";
                  $username = "root";
                  $password = "";
                  $database = "barangayDb";

                  $conn = new mysqli($servername, $username, $password, $database);
                  if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                  }

                  $search = "";
                  if (isset($_GET['search_complainant']) && !empty(trim($_GET['search_complainant']))) {
                    $search = $conn->real_escape_string($_GET['search_complainant']);
                    $sql = "SELECT * FROM blottertbl WHERE reported_by LIKE '%$search%' ORDER BY created_at DESC";
                  } else {
                    $sql = "SELECT * FROM blottertbl ORDER BY created_at DESC";
                  }

                  $result = $conn->query($sql);
                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>
            <td>" . htmlspecialchars($row['blotter_id']) . "</td>
            <td>" . htmlspecialchars($row['reported_by']) . "</td>
            <td>" . htmlspecialchars($row['datetime_of_incident']) . "</td>
            <td>" . htmlspecialchars($row['location_of_incident']) . "</td>
            <td>" . htmlspecialchars($row['incident_type']) . "</td>
            <td>" . htmlspecialchars($row['status']) . "</td>
            <td>" . htmlspecialchars($row['created_at']) . "</td>
            <td>" . htmlspecialchars($row['closed_at']) . "</td>
            <td>
                <button class='action-btn-2 view-blotter' data-id='" . $row['blotter_id'] . "' style='font-size:16px; background-color:#28a745; outline:none; border:none;'>
                    <i class='fas fa-eye'></i>
                </button>
                <button class='action-btn-2 update-blotter' data-id='" . $row['blotter_id'] . "' style='font-size:16px; background-color:#28a745; outline:none; border:none;' >
                    <i class='fas fa-edit'></i>
                </button>
            </td>
        </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='9'>No blotter records found.</td></tr>";
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
                  <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                    style="width: 70%; max-width: 120px; border-radius: 50%;" />
                </div>
                <h1 style="text-align:center;">Create Blotter Report</h1>
                <form id="blotterForm" method="POST" action="../Process/blotter/create_blotter.php" class="modal-form">
                  <label style="font-weight:bold;">Full Name of Complainant</label>
                  <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <input type="text" name="complainant_lastname" placeholder="Lastname" required>
                    <input type="text" name="complainant_firstname" placeholder="Firstname" required>
                    <input type="text" name="complainant_middlename" placeholder="Middlename">
                  </div>
                  <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="complainant_address" required>
                  </div>
                  <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                    <div class="form-group">
                      <label>Age</label>
                      <input type="number" name="complainant_age" required>
                    </div>
                    <div class="form-group">
                      <label>Contact No</label>
                      <input type="number" min="0" maxlength="11" name="complainant_contact_no" required>
                    </div>
                    <div class="form-group">
                      <label>Email</label>
                      <input type="email" name="complainant_email">
                    </div>
                  </div>
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
                      <select name="incident_type" id="incident_type">
                        <option value="" disabled selected>Select Incident Type</option>
                        <option value="Theft">Theft</option>
                        <option value="Assault">Assault</option>
                        <option value="Vandalism">Vandalism</option>
                        <option value="Domestic Dispute">Domestic Dispute</option>
                        <option value="Noise Complaint">Noise Complaint</option>
                        <option value="Traffic Violation">Traffic Violation</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                    <div class="form-group" style="grid-column: span 3;">
                      <label>Detailed Description of the Incident</label>
                      <textarea name="incident_description" rows="5" required></textarea>
                    </div>
                  </div>

                  <br>
                  <hr>


                  <!-- ...inside your blotterModal form... -->
                  <h3>Accused Details</h3>
                  <div id="accusedContainer">
                    <div class="accused-fields">
                      <h6>Full name of the Accused</h6>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <input type="text" name="accused_lastname[]" placeholder="Lastname" required>
                        <input type="text" name="accused_firstname[]" placeholder="Firstname" required>
                        <input type="text" name="accused_middlename[]" placeholder="Middlename">
                      </div>
                      <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="accused_address[]" required>
                      </div>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div class="form-group">
                          <label>Age</label>
                          <input type="number" name="accused_age[]" required>
                        </div>
                        <div class="form-group">
                          <label>Contact No</label>
                          <input type="number" min="0" maxlength="11" name="accused_contact_no[]" required>
                        </div>
                        <div class="form-group">
                          <label>Email</label>
                          <input type="email" name="accused_email[]">
                        </div>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm remove-accused-btn" style="margin-bottom:10px; display:none;">Remove</button>
                      <hr>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" id="addAccusedBtn">+ Add Accused</button>


                  <br>
                  <hr>

                  <h3>Witnesses Details</h3>
                  <div id="witnessesContainer">
                    <div class="witness-fields">
                      <h6>Full name of the Witness</h6>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <input type="text" name="witness_lastname[]" placeholder="Lastname" required>
                        <input type="text" name="witness_firstname[]" placeholder="Firstname" required>
                        <input type="text" name="witness_middlename[]" placeholder="Middlename">
                      </div>
                      <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="witness_address[]" required>
                      </div>
                      <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                        <div class="form-group">
                          <label>Age</label>
                          <input type="number" name="witness_age[]" required>
                        </div>
                        <div class="form-group">
                          <label>Contact No</label>
                          <input type="number" min="0" maxlength="11" name="witness_contact_no[]" required>
                        </div>
                        <div class="form-group">
                          <label>Email</label>
                          <input type="email" name="witness_email[]">
                        </div>
                      </div>
                      <button type="button" class="btn btn-danger btn-sm remove-witness-btn" style="margin-bottom:10px; display:none;">Remove</button>
                      <hr>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" id="addWitnessBtn">+ Add Witness</button>
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
                <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                  style="width: 70%; max-width: 120px; border-radius: 50%;" />
              </div>
              <h1 style="text-align:center;">Blotter Report Details</h1>
              <form class="modal-form">
                <!-- Complainant -->
                <label style="font-weight:bold;">Full Name of Complainant</label>
                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                  <input type="text" id="view_complainant_lastname" readonly>
                  <input type="text" id="view_complainant_firstname" readonly>
                  <input type="text" id="view_complainant_middlename" readonly>
                </div>
                <div class="form-group">
                  <label>Address</label>
                  <input type="text" id="view_complainant_address" readonly>
                </div>
                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                  <div class="form-group">
                    <label>Age</label>
                    <input type="number" id="view_complainant_age" readonly>
                  </div>
                  <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" id="view_complainant_contact_no" readonly>
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="view_complainant_email" readonly>
                  </div>
                </div>
                <br>
                <hr>
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
                <!-- Accused Details -->
                <h3>Accused Details</h3>
                <div id="view_accusedContainer"></div>


                <!-- Witnesses Details -->
                <h3>Witnesses Details</h3>
                <div id="view_witnessesContainer"></div>
              </form>
              <div style="margin-top:20px; text-align:right;">
                <button type="button" id="closeBlotterBtn" class="btn btn-danger">Blotter Case Close</button>
              </div>
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
            </script>



          </div> <!-- end of blotter complaint panel-->

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
              <label for="blotter_status" style="margin-left:10px;">Case Status:</label>
              <select name="blotter_status" id="blotter_status" onchange="this.form.submit()">
                <option value="both" <?php if (!isset($_GET['blotter_status']) || $_GET['blotter_status'] == 'both') echo 'selected'; ?>>Both</option>
                <option value="active" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'active') echo 'selected'; ?>>Active</option>
                <option value="closed" <?php if (isset($_GET['blotter_status']) && $_GET['blotter_status'] == 'closed') echo 'selected'; ?>>Closed</option>
              </select>
            </form>
            <div class="scrollable-table-container">
              <table class="styled-table">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>LAST NAME</th>
                    <th>FIRST NAME</th>
                    <th>MIDDLE NAME</th>
                    <th>AGE</th>
                    <th>ADDRESS</th>
                    <th>CONTACT NO</th>
                    <th>EMAIL</th>
                    <th>BLOTTER ID</th>
                    <th>ACTION</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $servername = "localhost";
                  $username = "root";
                  $password = "";
                  $database = "barangayDb";

                  $conn = new mysqli($servername, $username, $password, $database);
                  if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

                  // Search by name
                  $search_sql = "";
                  if (isset($_GET['search_blottered']) && !empty(trim($_GET['search_blottered']))) {
                    $search = $conn->real_escape_string($_GET['search_blottered']);
                    $search_sql = "AND (bp.lastname LIKE '%$search%' OR bp.firstname LIKE '%$search%')";
                  }

                  // Filter by status
                  $status_filter = "";
                  if (isset($_GET['blotter_status']) && $_GET['blotter_status'] !== 'both') {
                    $status = $_GET['blotter_status'] === 'closed' ? 'closed' : 'active';
                    $status_filter = "AND b.status = '$status'";
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
            bp.blotter_id
          FROM blotter_participantstbl bp
          JOIN blottertbl b ON bp.blotter_id = b.blotter_id
          WHERE bp.participant_type = 'accused'
            $status_filter
            $search_sql
          ORDER BY bp.blotter_participant_id DESC
        ";

                  $result = $conn->query($sql);
                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>
              <td>" . htmlspecialchars($row['blotter_participant_id']) . "</td>
              <td>" . htmlspecialchars($row['lastname']) . "</td>
              <td>" . htmlspecialchars($row['firstname']) . "</td>
              <td>" . htmlspecialchars($row['middlename']) . "</td>
              <td>" . htmlspecialchars($row['age']) . "</td>
              <td>" . htmlspecialchars($row['address']) . "</td>
              <td>" . htmlspecialchars($row['contact_no']) . "</td>
              <td>" . htmlspecialchars($row['email']) . "</td>
              <td>" . htmlspecialchars($row['blotter_id']) . "</td>
              <td>
                <button class='action-btn-2 view' data-id='" . $row['blotter_participant_id'] . "' style='font-size:16px; background-color:#28a745; outline:none; border:none;' >
                  <i class='fas fa-eye'></i>
                </button>
              </td>
            </tr>";
                    }
                  } else {
                    echo "<tr><td colspan='10'>No blottered individuals found.</td></tr>";
                  }
                  // Singleton connection closed by PHP
                  ?>
                </tbody>
              </table>
            </div>
          </div> <!-- end of blottered individuals panel-->

          

           <div id="announcementPanel" class="panel-content">
            <h1>Announcements</h1>
            <p>Manage announcements with images and text for the community.</p>

            <!-- Form to Add New Announcement -->
            <div class="announcement-form">
              <h3>Add New Announcement</h3>
              <?php
              // Handle Form Submission
            if (isset($_POST['saveAnnouncement'])) {
    // DEV: enable errors while debugging (remove in production)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "barangayDb";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        echo "<div class='alert alert-danger'>DB connection failed.</div>";
    } else {
        $newsInfo = trim($_POST['NewsInfo'] ?? '');
        $datedReported = date('Y-m-d H:i:s');

        if ($newsInfo === '') {
            echo "<div class='alert alert-danger'>Announcement text is required.</div>";
        } else {
            $newsImagePath = null;
            // upload dir relative to this file
            $uploadDir = __DIR__ . '/../Assets/announcements/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            if (!empty($_FILES['NewsImage']['name']) && $_FILES['NewsImage']['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['NewsImage']['tmp_name'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp);
                finfo_close($finfo);

                $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
                if (!isset($allowed[$mime])) {
                    echo "<div class='alert alert-danger'>Invalid image type.</div>";
                } elseif ($_FILES['NewsImage']['size'] > 5*1024*1024) {
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

            // prepared insert
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
        // Singleton connection closed by PHP
    }
}
?>
                <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-8">
        <label for="NewsInfo" class="form-label">Announcement Text</label>
        <textarea name="NewsInfo" id="NewsInfo" class="form-control" rows="4" placeholder="Enter the announcement text..." required></textarea>
      </div>
      <div class="col-md-4">
        <label for="NewsImage" class="form-label">Upload Image (Optional)</label>
        <input type="file" name="NewsImage" id="NewsImage" class="form-control" accept="image/*">
        <small class="text-muted">Max 5MB. JPG, PNG, GIF only.</small>
      </div>
      <div class="col-12 text-end">
        <button type="submit" name="saveAnnouncement" class="btn btn-primary">
          <i class="bi bi-plus-circle"></i> Upload Announcement
        </button>
      </div>
    </form>
  </div>
             <div class="announcement-list">
    <h3>Recent Announcements</h3>
    <?php
      $conn = new mysqli('localhost','root','','barangayDb');
      if ($conn->connect_error) {
          echo "<div class='alert alert-danger'>Can't load announcements.</div>";
      } else {
          $res = $conn->query("SELECT NewsInfo, NewsImage, DatedReported FROM news ORDER BY DatedReported DESC LIMIT 50");
          if ($res && $res->num_rows) {
              echo "<div class='row g-4 mt-2'>";
              while ($r = $res->fetch_assoc()) {
                  $img = $r['NewsImage'] ? htmlspecialchars('/Capston/Capstones/Capstones/' . $r['NewsImage']) : null;
                  echo "<div class='col-md-4 col-sm-6'>
                          <div class='announcement-card card'>
                            ".($img ? "<img src='". $img ."' alt='Announcement image' />" : "")."
                            <div class='card-body'>
                              <div class='announcement-text'>" . nl2br(htmlspecialchars($r['NewsInfo'])) . "</div>
                              <div class='announcement-date'>" . htmlspecialchars($r['DatedReported']) . "</div>
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
                        'residents' => 'Resident Demographics',
                        'documents' => 'Document Requests',
                        'business' => 'Business Permits',
                        'unemployment' => 'Unemployment Certificates',
                        'guardianship' => 'Guardianship Documents',
                        'items' => 'Item Requests',
                        'blotters' => 'Blotter/Complaints',
                        'collections' => 'Financial Collections'
             
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
                <button type="button" id="exportCsv" class="btn btn-secondary flex-fill" style="display:none;">Export CSV</button>
                <button type="button" id="exportPdf" class="btn btn-secondary flex-fill" style="display:none;">Export PDF</button>
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

            $stmt = $connection->prepare("SELECT ReqID, CONCAT(Firstname, ' ', Lastname) AS Name, DocuType, ReqPurpose, 
                                                 Address, DateRequested, RequestStatus 
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
                                <th>Name</th>
                                <th>Document Type</th>
                                <th>Purpose</th>
                                <th>Address</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';
            while ($r = $details->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($r['ReqID']) . '</td>
                        <td>' . htmlspecialchars($r['Name']) . '</td>
                        <td>' . htmlspecialchars($r['DocuType']) . '</td>
                        <td>' . htmlspecialchars($r['ReqPurpose']) . '</td>
                        <td>' . htmlspecialchars($r['Address']) . '</td>
                        <td>' . htmlspecialchars($r['DateRequested']) . '</td>
                        <td>' . htmlspecialchars($r['RequestStatus']) . '</td>
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
                                                 BusinessLoc, RequestedDate, RequestStatus 
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
                                <th>Owner</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th>Date Applied</th>
                                <th>Status</th>
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
                                                 request_date, RequestStatus
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
                                <th>Status</th>
                          
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
                                           request_date, RequestStatus
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
                            <th>Status</th>
                     
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
        $stmt = $connection->prepare("SELECT id, name, item, quantity, event_datetime, date, RequestStatus
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
                            <th>Requester Name</th>
                            <th>Item</th>

                            <th>Quantity</th>
                            <th>Event Date/Time</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                 
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
               
                  </tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div class="alert alert-warning">No item requests found for this date range.</div>';
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
                    
                    <td>₱' . number_format($r['amount'], 2) . '</td>
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
        logo.src = '/Capston/Capstones/Capstones/Assets/sampaguitalogo.png';
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>



          <div id="auditTrailPanel" class="panel-content">
            <h3>Audit Trail Logs</h3>
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
                  $servername = "localhost";
                  $username = "root";
                  $password = "";
                  $database = "barangayDb";

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
document.addEventListener('DOMContentLoaded', function() {
  const closeBtn = document.getElementById('closeModal');
  if (closeBtn) {
    closeBtn.addEventListener('click', hideSuccessModal);
  }
  
  // Close modal on outside click (optional)
  const modal = document.getElementById('successModal');
  if (modal) {
    modal.addEventListener('click', function(event) {
      if (event.target === modal) {
        hideSuccessModal();
      }
    });
  }
});

window.addEventListener("DOMContentLoaded", function() {
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
    } else if (urlParams.has("search_refno")) {
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
        <img class="watermark" src="${location.origin}/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Watermark">

  <div class="content">
    <div class="header">
      <div class="logos">
        <img src="${location.origin}/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"/>
        <img src="${location.origin}/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"/>
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
              window.addEventListener('click', function(e) {
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
      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:18px; margin-top:10px;">
        <p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
      </div>

      <div style="text-align:center; font-size:22px; text-decoration:underline; margin:30px 0 20px;">
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
                } else if (data.certificate_type === "No Income") {
                  htmlContent = `
      <div style="font-family:'Arial'; max-width:800px; margin:auto; padding:30px;">
        <h2 style="text-align:center;">NO INCOME CERTIFICATE</h2>
        <p>Date: ${formattedDate}</p>
        <p>TO WHOM IT MAY CONCERN:</p>
        <p>This is to certify that <strong>${data.Firstname} ${data.Lastname}</strong>, 
        a resident of <strong>${data.Address || "Barangay Sampaguita, San Pedro, Laguna"}</strong>, 
        currently has no permanent or fixed source of income as of this date.</p>
        <p>This certification is issued upon request of the concerned individual 
        for whatever legal purpose it may serve.</p>

        <div style="margin-top:60px; text-align:right;">
          <p>HON. RHYXTER S. LABAY</p>
          <p>Punong Barangay</p>
        </div>
      </div>
    `;
                } else if (data.RequestType === "closure") {
                  htmlContent = `
     <br><br><img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
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
     <br><br><img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
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
     <br><br><img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
      </div>

      <div style="text-align:center; font-size:22px; text-decoration:underline; margin:30px 0 20px;">
        NO FIXED INCOME CERTIFICATE
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
                } else if (data.request_type === "Guardianship") {
                  htmlContent = `
     <br><br><img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
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
     <br><br><img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:20px; margin-top:10px;">
        <b><p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
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
                } else if (data.Docutype === "First Time Job Seeker Form") {
                  htmlContent = `
      <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png"
         style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);
                opacity:0.2; width:400px; height:auto; z-index:0; pointer-events:none;" />


       <div style="position:relative; z-index:1;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <div style="flex: 1; text-align: left;">
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Sampaguita Logo"
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
          <img src="/Capston/Capstones/Capstones/Assets/SanPedroLogo.png" alt="San Pedro Logo"
               style="width: 100px; height: 100px; object-fit: contain; border-radius: 50%;">
        </div>
      </div>

      <div style="text-align:center; font-size:18px; margin-top:10px;">
        <p style="margin:0;">OFFICE OF THE PUNONG BARANGAY</p>
        <p style="margin:0;">Date: ${formattedDate}</p>
        <p style="margin:0;">No.: ___________</p>
      </div>

      <div style="text-align:center; font-size:22px; text-decoration:underline; margin:30px 0 20px;">
        CERTIFICATE OF FIRST TIME JOB SEEKERS
      </div>

      <div style="font-size:18px; text-align:justify; line-height:1.8;">
        <br>TO WHOM IT MAY CONCERN:<br><br>
        This is to certify <span style="text-transform:uppercase;">${data.Firstname + " " + data.Lastname}</span>,
        resident of, <span>${data.Address || "Barangay Sampaguita"}</span>, City of San Pedro, Laguna for,  <span>${data.DateRequested}</span> years, is a qualified availee of R.A. No.11261
        or the First Time JObseekers Assistance Act f 2019.

        <br><br>I further certify that the holder/bearer was informed of his/here rights,
        including the duties and responsibilities accorded by R.A. No.11261 through the 
        Oath of Undertaking he/she has signed and executed in the presence of Barangay
        Offical/s.
       
        <br><br>Issued this <span>${data.DateRequested}</span> at Barangay Sampaguita, City of San Pedro, Laguna.


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
              let selectedDocData = null; // Global variable to store selected document data

              // Function to open the modal and save the selected document's data
              function openPrintModal(data) {
                selectedDocData = data; // Store the selected document for later printing

                document.getElementById("modal_refno").value = data.refno;
                document.getElementById("modal_name").value = data.Firstname + ' ' + data.Lastname;
                document.getElementById("modal_type").value = data.Docutype;
                document.getElementById("modal_address").value = data.Address || "N/A";
                document.getElementById("modal_date").value = data.DateRequested || new Date().toLocaleDateString();

                const modal = new bootstrap.Modal(document.getElementById('printFormModal'));
                modal.show();
              }

              // Handle form submission
              document.getElementById("printForm").addEventListener("submit", function(event) {
                event.preventDefault(); // Prevent default form submission

                const formData = new FormData(this);

                fetch("Payment.php", {
                    method: "POST",
                    body: formData,
                  })
                  .then((response) => response.text())
                  .then((response) => {
                    if (response.trim() === "success") {
                      alert("Payment recorded. Now printing...");
                      bootstrap.Modal.getInstance(document.getElementById('printFormModal')).hide();

                      if (selectedDocData) {
                        generateCertificate(selectedDocData); // This handles different Docutype values
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
              document.querySelectorAll('.view').forEach(button => {
                button.addEventListener('click', function() {
                  document.getElementById('modalId').value = this.getAttribute('data-id');
            document.getElementById('modalFirstname').value = this.getAttribute('data-firstname');
            document.getElementById('modalLastname').value = this.getAttribute('data-lastname');
            document.getElementById('modalMiddlename').value = this.getAttribute('data-middlename');
            document.getElementById('modalEmail').value = this.getAttribute('data-email');
            document.getElementById('modalContact').value = this.getAttribute('data-contact');
            document.getElementById('modalBirthday').value = this.getAttribute('data-birthdate');
            document.getElementById('modalGender').value = this.getAttribute('data-gender');
            document.getElementById('modalAddress').value = this.getAttribute('data-address');
            document.getElementById('modalBirthplace').value = this.getAttribute('data-birthplace');
            document.getElementById('modalCivilStat').value = this.getAttribute('data-civilstatus');
            document.getElementById('modalNationality').value = this.getAttribute('data-nationality');
            document.getElementById('modalAccountStatus').value = this.getAttribute('data-accountstatus');


                  document.getElementById('viewModal').style.display = 'flex';
                });
              });



              document.querySelector('.close-btn').addEventListener('click', function() {
                document.getElementById('viewModal').style.display = 'none';
              });

              window.addEventListener('click', function(e) {
                if (e.target == document.getElementById('viewModal')) {
                  document.getElementById('viewModal').style.display = 'none';
                }
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
                button.addEventListener('click', function() {
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

              document.getElementById("customConfirmYes").addEventListener("click", function() {
                if (approveUrl) {
                  window.location.href = approveUrl; // proceed with approval
                }
              });

              document.getElementById("customConfirmNo").addEventListener("click", function() {
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
                declineUrl = null;
                document.getElementById("declineReasonText").value = "";
              });

              document.getElementById("declineReasonSubmit").addEventListener("click", function() {
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
              document.querySelector('.close-btn-govdoc').addEventListener('click', function() {
                document.getElementById('govdocViewModal').style.display = 'none';
              });

              window.addEventListener('click', function(e) {
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

                const modal = new bootstrap.Modal(document.getElementById('businessPrintFormModal'));
                modal.show();
              }

              document.getElementById("businessPrintForm").addEventListener("submit", function(event) {
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

                const modal = new bootstrap.Modal(document.getElementById('unemploymentPrintFormModal'));
                modal.show();
              }

              document.getElementById("unemploymentPrintForm").addEventListener("submit", function(event) {
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

                document.getElementById("guardianship_modal_date").value = data.request_date || new Date().toLocaleDateString();

                const modal = new bootstrap.Modal(document.getElementById('guardianshipPrintFormModal'));
                modal.show();
              }

              document.getElementById("guardianshipPrintForm").addEventListener("submit", function(event) {
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
                      bootstrap.Modal.getInstance(document.getElementById('guardianshipPrintFormModal')).hide();

                      if (selectedGuardianShipDocData) {
                        generateCertificate(selectedGuardianShipDocData);
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
                      // Fill complainant fields
                      const complainant = data.participants.find(p => p.participant_type === 'complainant');
                      if (complainant) {
                        document.getElementById('view_complainant_lastname').value = complainant.lastname;
                        document.getElementById('view_complainant_firstname').value = complainant.firstname;
                        document.getElementById('view_complainant_middlename').value = complainant.middlename;
                        document.getElementById('view_complainant_address').value = complainant.address;
                        document.getElementById('view_complainant_age').value = complainant.age;
                        document.getElementById('view_complainant_contact_no').value = complainant.contact_no;
                        document.getElementById('view_complainant_email').value = complainant.email;
                      }
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
              <h6>Full name of the Accused</h6>
              <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                <input type="text" value="${accused.lastname}" readonly>
                <input type="text" value="${accused.firstname}" readonly>
                <input type="text" value="${accused.middlename}" readonly>
              </div>
              <div class="form-group">
                <label>Address</label>
                <input type="text" value="${accused.address}" readonly>
              </div>
              <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                <div class="form-group">
                  <label>Age</label>
                  <input type="number" value="${accused.age}" readonly>
                </div>
                <div class="form-group">
                  <label>Contact No</label>
                  <input type="text" value="${accused.contact_no}" readonly>
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
                      data.participants.filter(p => p.participant_type === 'witness').forEach(witness => {
                        witnessesContainer.innerHTML += `
            <div class="witness-fields">
              <h6>Full name of the Witness</h6>
              <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                <input type="text" value="${witness.lastname}" readonly>
                <input type="text" value="${witness.firstname}" readonly>
                <input type="text" value="${witness.middlename}" readonly>
              </div>
              <div class="form-group">
                <label>Address</label>
                <input type="text" value="${witness.address}" readonly>
              </div>
              <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                <div class="form-group">
                  <label>Age</label>
                  <input type="number" value="${witness.age}" readonly>
                </div>
                <div class="form-group">
                  <label>Contact No</label>
                  <input type="text" value="${witness.contact_no}" readonly>
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

                      document.getElementById('viewBlotterModal').style.display = 'flex';
                    })
                    .catch(() => alert('Failed to fetch blotter details.'));
                });
              });



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
            </script>

<script>
(function(){
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
        <img id="vb_image_full" src="${src}" alt="${alt||''}" />
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

    overlay.addEventListener('click', function(ev){
      if (ev.target === overlay || ev.target.id === 'vb_image_close') removeViewer();
    });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') removeViewer(); }, { once: true });
  }

  document.addEventListener('click', function(e) {
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
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
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

    fetch(href, {credentials: 'same-origin'})
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
            <input readonly value="${escapeHtml(value||'')}" />
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
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  padding: 20px; z-index: 99999;
  animation: fadeIn 0.25s ease-out;
}
.vb-card {
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  width: 100%; max-width: 720px;
  box-shadow: 0 8px 30px rgba(87, 87, 87, 0.65);
  padding: 24px 28px;
  position: relative;
  animation: scaleIn 0.25s ease-out;
}
.vb-header {
  text-align: center; margin-bottom: 18px;
}
.vb-logo {
  width: 90px; height: 90px;
  border-radius: 50%; margin-bottom: 10px;
  object-fit: cover; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}
.vb-close {
  position: absolute; top: 14px; right: 14px;
  font-size: 26px; background: none; border: none;
  color: #555; cursor: pointer; transition: 0.2s;
}
.vb-close:hover { color: #000; transform: scale(1.1); }
.vb-media { text-align:center; margin-bottom: 16px; }
.vb-proof {
  display:none; max-width:100%; max-height:260px;
  border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,0.15);
  cursor: zoom-in; transition: transform 0.25s ease;
}
.vb-proof:hover { transform: scale(1.02); }
.vb-content {
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 14px;
}
.vb-field span {
  display:block;
  font-size: 13px; color:#555; margin-bottom:4px;
  font-weight: 500;
}
.vb-field input {
  width:100%; padding:8px 10px;
  border:1px solid #ddd; border-radius:8px;
  background:#f9f9f9; font-size:14px; color:#333;
}
.vb-btn {
  padding:8px 16px;
  border:none; border-radius:8px;
  background:#28a745; color:#fff;
  font-size:14px; cursor:pointer;
  transition: background 0.2s ease;
}
.vb-btn:hover { background:#15803d; }
.vb-footer { text-align:right; margin-top: 18px; }

/* Fullscreen image viewer */
.vb-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.9);
  display: flex; align-items:center; justify-content:center;
  z-index: 100000;
  animation: fadeIn 0.3s ease-out;
}
.vb-img-wrapper {
  position:relative; max-width:95%; max-height:95%;
}
.vb-img-wrapper img {
  max-width:100%; max-height:100%;
  border-radius:12px; box-shadow:0 8px 40px rgba(0,0,0,0.5);
}
.vb-img-wrapper button {
  position:absolute; top:8px; right:10px;
  background:rgba(0,0,0,0.5); color:#fff;
  border:none; font-size:24px; border-radius:8px;
  cursor:pointer; padding:4px 10px;
  transition:background 0.2s;
}
.vb-img-wrapper button:hover { background:rgba(0,0,0,0.7); }

@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes scaleIn { from { transform:scale(0.95); opacity:0; } to { transform:scale(1); opacity:1; } }
</style>


<script>
(function(){
  function escapeHtml(s){ if (s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  // hide known popups so background forms don't remain visible/interactive
  function hideBackgroundModals(){
    const selectors = [
      '#viewModal', '.view-modal', '.popup', '.document-popup', '.business-popup',
      '#addDocumentModal', '#addBusinessModal', '.modal', '.modal-popup', '#viewBlotterModal'
    ];
    selectors.forEach(sel => document.querySelectorAll(sel).forEach(el => {
      if (!el) return;
      el.style.display = 'none';
      el.classList.remove('show','open','active');
    }));
  }

  // fullscreen image viewer used by both document & business viewers
  function showImageViewer(src, alt){
    const prev = document.getElementById('doc_image_modal');
    if (prev) prev.remove();
    const overlay = document.createElement('div');
    overlay.id = 'doc_image_modal';
    overlay.className = 'vb-overlay';
    overlay.innerHTML = `<div class="vb-img-wrapper"><img src="${escapeHtml(src)}" alt="${escapeHtml(alt||'')}" /><button id="doc_img_close">&times;</button></div>`;
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    overlay.addEventListener('click', function(e){ if (e.target === overlay || e.target.id === 'doc_img_close') { overlay.remove(); document.body.classList.remove('modal-open'); } });
    document.addEventListener('keydown', function esc(e){ if (e.key === 'Escape') { overlay.remove(); document.body.classList.remove('modal-open'); } }, { once: true });
  }

  document.addEventListener('click', function(e){
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
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" style="width:90px;height:90px;border-radius:50%;object-fit:cover;"/>
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

    function closeModal(){ const m = document.getElementById('viewDocumentModal'); if (m) m.remove(); document.body.classList.remove('modal-open'); }
    modal.addEventListener('click', function(ev){ if (ev.target === modal) closeModal(); });
    modal.querySelector('#vd_close').addEventListener('click', closeModal);
    modal.querySelector('#vd_close2').addEventListener('click', closeModal);

    fetch(href, { credentials: 'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (json.error) { closeModal(); alert(json.error||'Failed to load'); return; }
        const d = json.data || {};
        const content = document.getElementById('vd_content');
        const img = document.getElementById('vd_cert_img');

        if (d.CertificateImage) {
          img.src = d.CertificateImage;
          img.alt = d.Docutype ? 'Certificate - ' + d.Docutype : 'Certificate Image';
          img.style.display = 'block';
          img.onclick = function(ev){ ev.stopPropagation(); showImageViewer(img.src, img.alt); };
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
            <input readonly value="${escapeHtml(f[1]||'')}" style="width:100%;padding:8px;border:1px solid #e1e1e1;border-radius:8px;background:#fafafa;" />
          </label>
        `).join('');
      })
      .catch(err => { console.error(err); closeModal(); alert('Failed to load document details.'); });
  });
})();
</script>

<script>
(function(){
  function escapeHtml(s){ if (s===null||s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  // reuse helper that hides other popups so background forms won't show
  function hideBackgroundModals(){
    const selectors = [
      '#viewModal', '.view-modal', '.popup', '.document-popup', '.business-popup',
      '#addDocumentModal', '#addBusinessModal', '.modal', '.modal-popup', '#viewBlotterModal', '#addUnemploymentModal'
    ];
    selectors.forEach(sel => document.querySelectorAll(sel).forEach(el => {
      if (!el) return;
      el.style.display = 'none';
      el.classList.remove('show','open','active');
    }));
  }

  // open full-screen image viewer (if you ever add images)
  function showImageViewer(src, alt){
    const prev = document.getElementById('unemp_image_modal');
    if (prev) prev.remove();
    const overlay = document.createElement('div');
    overlay.id = 'unemp_image_modal';
    overlay.className = 'vb-overlay';
    overlay.innerHTML = `<div class="vb-img-wrapper"><img src="${escapeHtml(src)}" alt="${escapeHtml(alt||'')}" /><button id="unemp_img_close">&times;</button></div>`;
    document.body.appendChild(overlay);
    document.body.classList.add('modal-open');
    overlay.addEventListener('click', function(e){ if (e.target === overlay || e.target.id === 'unemp_img_close') { overlay.remove(); document.body.classList.remove('modal-open'); } });
    document.addEventListener('keydown', function esc(e){ if (e.key === 'Escape') { overlay.remove(); document.body.classList.remove('modal-open'); } }, { once: true });
  }

  // click handler for unemployment view links/buttons
  document.addEventListener('click', function(e){
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
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
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

    function closeModal(){ const m = document.getElementById('viewUnemploymentModal'); if (m) m.remove(); document.body.classList.remove('modal-open'); }
    modal.addEventListener('click', function(ev){ if (ev.target === modal) closeModal(); });
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
            <input readonly value="${escapeHtml(f[1]||'')}" />
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
(function(){
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
  document.addEventListener('click', function(e) {
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
          <img src="/Capston/Capstones/Capstones/Assets/sampaguitalogo.png" alt="Logo" class="vb-logo" />
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
</body>

</html>
