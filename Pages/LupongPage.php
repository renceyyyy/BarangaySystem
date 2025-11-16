<?php
// Set staff session name BEFORE starting session
session_name('BarangayStaffSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security check — only lupong users allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lupong') {
    header("Location: ../Login/login.php");
    exit();
}

// Debug logs
error_log("LupongPage.php — Username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("LupongPage.php — Fullname: " . ($_SESSION['fullname'] ?? 'NOT SET'));
error_log("LupongPage.php — Role: " . ($_SESSION['role'] ?? 'NOT SET'));

require_once '../Process/db_connection.php';
$conn = getDBConnection();

// Get escalated blotters count
$escalatedCount = 0;
$escalatedQuery = "SELECT COUNT(*) as total FROM blottertbl WHERE escalated_to_lupong = 1 AND status = 'closed_unresolved'";
$escalatedResult = $conn->query($escalatedQuery);
if ($escalatedResult && $row = $escalatedResult->fetch_assoc()) {
    $escalatedCount = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupong Tagapamayapa - Barangay System</title>
    <link rel="stylesheet" href="../Styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <style>
        .escalated-badge {
            background: #ff6b6b;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-escalated {
            background: #ff6b6b;
            color: white;
        }

        .status-in-progress {
            background: #4dabf7;
            color: white;
        }

        .status-resolved {
            background: #51cf66;
            color: white;
        }

        .status-unresolved {
            background: #868e96;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Keep the same Adminpage sidebar + layout structure -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (same style as Adminpage, only show lupong-specific link set) -->
            <div class="col-12 col-md-2 sidebar">
                <img src="/BarangaySampaguita/BarangaySystem/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                    style="width: 100%; max-width: 160px; border-radius: 50%;" />
                <button class="sidebar-btn" type="button" onclick="showPanel('escalatedPanel')">
                    <i class="fas fa-gavel"></i> Escalated Cases
                </button>

                <div style="margin-top:20px;">
                    <a href="../Login/logout.php" class="logout-link" onclick="openLogoutModal(event); return false;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content (wrap Lupong content to match Adminpage look) -->
            <div class="col-12 col-md-10 p-0">
                <div class="main-content-scroll p-3">
                    <div id="escalatedPanel" class="panel-content active">
                        <h1>Lupong Tagapamayapa Dashboard</h1>
                        <p><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['fullname'] ?? $_SESSION['username']); ?></p>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h4>Escalated Blotter Cases (<?php echo $escalatedCount; ?>)</h4>

                                <form id="lupongSearchForm" method="GET" class="form-inline mb-2">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Search by Blotter ID or Reported By..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    <select id="status_filter" name="status_filter" class="form-select me-2" style="width:auto;" onchange="document.getElementById('lupongSearchForm').submit();">
                                        <?php $statusFilter = $_GET['status_filter'] ?? 'closed_unresolved'; ?>
                                        <option value="closed_unresolved" <?php echo ($statusFilter === 'closed_unresolved') ? 'selected' : ''; ?>>Escalated (Pending)</option>
                                        <option value="lupong_in_progress" <?php echo ($statusFilter === 'lupong_in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="lupong_resolved" <?php echo ($statusFilter === 'lupong_resolved') ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="lupong_unresolved" <?php echo ($statusFilter === 'lupong_unresolved') ? 'selected' : ''; ?>>Unresolved</option>
                                        <option value="all" <?php echo ($statusFilter === 'all') ? 'selected' : ''; ?>>All</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">Search</button>
                                </form>

                                <div class="scrollable-table-container">
                                    <table class="styled-table table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Blotter ID</th>
                                                <th>Reported By</th>
                                                <th>Incident Type</th>
                                                <th>Date Escalated</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $search = $_GET['search'] ?? '';
                                            $statusFilter = $_GET['status_filter'] ?? 'closed_unresolved';
                                            $whereClauses = ["escalated_to_lupong = 1"];
                                            if (!empty($search)) {
                                                $searchEscaped = $conn->real_escape_string($search);
                                                $whereClauses[] = "(blotter_id LIKE '%$searchEscaped%' OR reported_by LIKE '%$searchEscaped%')";
                                            }
                                            if ($statusFilter !== 'all') {
                                                $whereClauses[] = "status = '" . $conn->real_escape_string($statusFilter) . "'";
                                            }
                                            $whereSQL = implode(' AND ', $whereClauses);
                                            $sql = "SELECT blotter_id, reported_by, incident_type, escalation_date, status FROM blottertbl WHERE $whereSQL ORDER BY escalation_date DESC";
                                            $result = $conn->query($sql);
                                            if ($result && $result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    $statusClass = 'badge bg-danger';
                                                    $statusText = 'Pending';
                                                    switch ($row['status']) {
                                                        case 'lupong_in_progress':
                                                            $statusClass = 'badge bg-info';
                                                            $statusText = 'In Progress';
                                                            break;
                                                        case 'lupong_resolved':
                                                            $statusClass = 'badge bg-success';
                                                            $statusText = 'Resolved';
                                                            break;
                                                        case 'lupong_unresolved':
                                                            $statusClass = 'badge bg-secondary';
                                                            $statusText = 'Unresolved';
                                                            break;
                                                        case 'closed_unresolved':
                                                            $statusClass = 'badge bg-warning text-dark';
                                                            $statusText = 'Pending';
                                                            break;
                                                    }
                                                    echo "<tr>
                                  <td>" . htmlspecialchars($row['blotter_id']) . "</td>
                                  <td>" . htmlspecialchars($row['reported_by']) . "</td>
                                  <td>" . htmlspecialchars($row['incident_type']) . "</td>
                                  <td>" . ($row['escalation_date'] ? date('M d, Y', strtotime($row['escalation_date'])) : 'N/A') . "</td>
                                  <td><span class=\"$statusClass\">$statusText</span></td>
                                  <td><button class='btn btn-primary view-escalated-blotter' data-id='" . htmlspecialchars($row['blotter_id']) . "'><i class='fas fa-eye'></i> View</button></td>
                                </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='6' class='text-center'>No escalated cases found.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> <!-- end card -->
                    </div> <!-- end escalatedPanel -->
                </div> <!-- end main-content-scroll -->
            </div> <!-- end main col -->
        </div> <!-- end row -->
    </div> <!-- end container -->

    <!-- View Escalated Blotter Modal (similar to Adminpage.php viewBlotterModal but for Lupong) -->
    <div id="viewEscalatedBlotterModal" class="popup" style="display:none;">
        <div class="modal-popup" style="max-height: 90vh; overflow-y: auto;">
            <span class="close-btn" onclick="closeViewEscalatedBlotterModal()">&times;</span>
            <div style="text-align: center;">
                <img src="../Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
                    style="width: 70%; max-width: 120px; border-radius: 50%;" />
            </div>
            <h1 style="text-align:center;">Escalated Blotter Details</h1>
            <div style="font-size:16px; font-weight:bold; margin-bottom:10px;">
                Blotter ID: <span id="escalated_blotter_id"></span>
            </div>
            <div style="font-size:16px; font-weight:bold; margin-bottom:10px; color:#ff6b6b;">
                <i class="fas fa-exclamation-triangle"></i> Case Escalated from Barangay Level
            </div>
            <div style="font-size:14px; margin-bottom:15px;">
                Escalation Date: <span id="escalated_date"></span>
            </div>

            <form class="modal-form">
                <!-- Blotter details will be populated here via AJAX -->
                <div id="escalated_blotter_content"></div>

                <!-- Barangay Hearing History (Read-Only) -->
                <h3 id="barangayHearingHistoryHeader" style="display:none;">Barangay Hearing History</h3>
                <div id="barangayHearingHistorySection" style="display:none; background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:20px;">
                    <!-- Populated via AJAX -->
                </div>

                <!-- Hearing Details Section (for recording current hearing) -->
                <h3 id="hearingDetailsHeader" style="display:none;">Hearing Details</h3>
                <div id="hearingDetailsSection" style="display:none;">
                    <label id="hearingNoLabel"></label>
                    <div>Date & Time: <span id="hearingDateTime"></span></div>
                    <div id="hearingParticipantsTableContainer"></div>
                    <input type="hidden" id="lupong_hearing_id" />
                    <!-- Mediator, Notes and Outcome inputs -->
                    <div style="margin-top:12px;">
                        <div class="form-group">
                            <label for="lupong_hearing_mediator" style="font-weight:600;">Mediator Name</label>
                            <input type="text" id="lupong_hearing_mediator" class="form-control" placeholder="Enter mediator name" required />
                        </div>
                        <div class="form-group" style="margin-top:8px;">
                            <label for="lupong_hearing_notes" style="font-weight:600;">Hearing Notes</label>
                            <textarea id="lupong_hearing_notes" class="form-control" rows="7" placeholder="Enter hearing notes/comments" required></textarea>
                        </div>
                        <div class="form-group" style="margin-top:8px;">
                            <label for="lupong_hearing_outcome" style="font-weight:600;">Outcome</label>
                            <select id="lupong_hearing_outcome" class="form-control" required>
                                <option value="" disabled selected>Select Outcome</option>
                                <option value="amicably_settled">Amicably Settled</option>
                                <option value="not_settled">Not Settled</option>
                            </select>
                        </div>
                        <div style="text-align:right; margin-top:10px;">
                            <button id="saveLupongHearingBtn" type="button" class="btn btn-success">Save Hearing</button>
                        </div>
                    </div>
                </div>
                <hr id="hearingDetailsHR" style="display:none;">

                <!-- Action Buttons -->
                <div style="margin-top:20px; text-align:right; position:relative;">
                    <div class="action-dropdown" style="display:inline-block; position:relative;">
                        <button id="lupongActionDropdownBtn" class="btn btn-secondary">Action ▾</button>
                        <div id="lupongActionDropdownMenu" class="action-dropdown-menu" style="display:none; position:absolute; right:0; background:#fff; border:1px solid #ccc; padding:8px; box-shadow:0 4px 8px rgba(0,0,0,0.1); z-index:1000; min-width:180px;">
                            <button id="lupongScheduleHearingBtn" class="btn btn-primary" style="display:block; width:100%; margin-bottom:8px;">Schedule Hearing</button>
                            <button type="button" id="lupongCloseCaseBtn" class="btn btn-danger" style="display:block; width:100%;">Close Case</button>
                        </div>
                    </div>
                </div>

                <!-- Lupong Actions -->
                <div style="margin-top:20px; text-align:right;">
                    <button type="button" id="reopenCaseBtn" class="btn btn-success">Re-open Case</button>
                    <button type="button" id="closeLupongCaseBtn" class="btn btn-danger">Close Case</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Lupong Hearing Modal -->
    <div id="scheduleLupongHearingModal" class="popup" style="display:none;">
        <div class="modal-popup">
            <span class="close-btn" onclick="closeScheduleLupongHearingModal()">&times;</span>
            <h2>Schedule Lupong Hearing</h2>
            <form id="scheduleLupongHearingForm">
                <div class="form-group">
                    <label for="lupong_hearing_datetime">Date & Time of Hearing</label>
                    <input type="datetime-local" name="lupong_hearing_datetime" id="lupong_hearing_datetime" required>
                </div>
                <div style="margin-top:20px; text-align:right;">
                    <button type="submit" class="btn btn-success">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Close Case Confirmation Modal -->
    <div id="lupongCloseCaseConfirm" class="custom-confirm-overlay" style="display:none;">
        <div class="custom-confirm-box">
            <p>Please select the outcome before closing this case:</p>
            <div class="custom-confirm-actions">
                <button id="lupongCloseResolved" class="custom-confirm-btn yes">Resolved</button>
                <button id="lupongCloseUnresolved" class="custom-confirm-btn no">Unresolved</button>
                <button id="lupongCloseCancel" class="custom-confirm-btn" style="background:#6c757d;">Cancel</button>
            </div>
        </div>
    </div>


    <!-- include same JS as Adminpage -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showPanel(panelId) {
            document.querySelectorAll('.panel-content').forEach(p => p.classList.remove('active'));
            const target = document.getElementById(panelId);
            if (target) target.classList.add('active');
        }


    
        // Auto-submit status filter
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status_filter');
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    document.getElementById('lupongSearchForm').submit();
                });
            }

            // Action dropdown toggle
            const actionBtn = document.getElementById('lupongActionDropdownBtn');
            const actionMenu = document.getElementById('lupongActionDropdownMenu');
            
            if (actionBtn && actionMenu) {
                actionBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    actionMenu.style.display = actionMenu.style.display === 'none' ? 'block' : 'none';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    actionMenu.style.display = 'none';
                });
            }
        });
    </script>

    <!-- view escalated blotter modal script -->
    

    <script>
        let currentEscalatedBlotterId = null;

        function closeViewEscalatedBlotterModal() {
            document.getElementById('viewEscalatedBlotterModal').style.display = 'none';
        }

        document.querySelectorAll('.view-escalated-blotter').forEach(btn => {
            btn.addEventListener('click', function() {
                currentEscalatedBlotterId = this.getAttribute('data-id');
                const blotterId = this.getAttribute('data-id');

                fetch('../Process/blotter/viewblotter.php?id=' + encodeURIComponent(blotterId))
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        document.getElementById('escalated_blotter_id').textContent = data.blotter.blotter_id;
                        document.getElementById('escalated_date').textContent = data.blotter.escalation_date ?
                            new Date(data.blotter.escalation_date).toLocaleString() :
                            'N/A';

                        // Populate blotter content (complainant, blotter details, respondent, witnesses)
                        let contentHTML = `<h3>Complainant Details</h3>`;
                        
                        data.participants.filter(p => p.participant_type === 'complainant').forEach(complainant => {
                            contentHTML += `
                            <div class="complainant-fields">
                                <h6>Full name of the Complainant</h6>
                                <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" value="${complainant.lastname || ''}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" value="${complainant.firstname || ''}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" value="${complainant.middlename || ''}" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" value="${complainant.address || ''}" readonly>
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
                                        <input type="email" value="${complainant.email || ''}" readonly>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        `;
                        });

                        contentHTML += `<h3>Blotter Details</h3>`;
                        contentHTML += `
                        <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div class="form-group">
                                <label>Date/Time of Incident</label>
                                <input type="text" value="${data.blotter.datetime_of_incident}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" value="${data.blotter.location_of_incident}" readonly>
                            </div>
                            <div class="form-group">
                                <label>Incident Type</label>
                                <input type="text" value="${data.blotter.incident_type}" readonly>
                            </div>
                            <div class="form-group" style="grid-column: span 3;">
                                <label>Description</label>
                                <textarea rows="5" readonly>${data.blotter.blotter_details}</textarea>
                            </div>
                        </div>
                        <hr>
                    `;

                        contentHTML += `<h3>Respondent Details</h3>`;
                        data.participants.filter(p => p.participant_type === 'accused').forEach(accused => {
                            contentHTML += `
                            <div class="accused-fields">
                                <h6>Full name of the Respondent</h6>
                                <div class="form-grid" style="grid-template-columns:1fr 1.7fr 1fr .7fr; gap:10px;">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" value="${accused.lastname || ''}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" value="${accused.firstname || ''}" readonly style="grid-column: span 2;">
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text" value="${accused.middlename || ''}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Alias</label>
                                        <input type="text" value="${accused.alias || ''}" size="3" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" value="${accused.address || ''}" readonly>
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
                                        <input type="email" value="${accused.email || ''}" readonly>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        `;
                        });

                        const witnesses = data.participants.filter(p => p.participant_type === 'witness');
                        if (witnesses.length > 0) {
                            contentHTML += `<h3>Witnesses Details</h3>`;
                            witnesses.forEach(witness => {
                                contentHTML += `
                                <div class="witness-fields">
                                    <h6>Full name of the Witness</h6>
                                    <div class="form-grid" style="grid-template-columns:1fr 1fr 1fr; gap:10px;">
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" value="${witness.lastname || ''}" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" value="${witness.firstname || ''}" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Middle Name</label>
                                            <input type="text" value="${witness.middlename || ''}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" value="${witness.address || ''}" readonly>
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
                                            <input type="email" value="${witness.email || ''}" readonly>
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            `;
                            });
                        }

                        document.getElementById('escalated_blotter_content').innerHTML = contentHTML;

                        // Show barangay hearing history
                        if (data.hearings && data.hearings.length > 0) {
                            const recordedHearings = data.hearings.filter(h =>
                                h.mediator_name && h.hearing_notes && h.outcome
                            );

                            if (recordedHearings.length > 0) {
                                document.getElementById('barangayHearingHistoryHeader').style.display = 'block';
                                document.getElementById('barangayHearingHistorySection').style.display = 'block';

                                let historyHTML = '<p style="font-weight:600; color:#495057;">Previous Barangay Hearings:</p>';
                                recordedHearings.forEach((hearing, index) => {
                                    historyHTML += `
                                    <div style="background:white; padding:12px; border-radius:6px; margin-bottom:10px;">
                                        <label style="font-weight:600;">Hearing ${hearing.hearing_no}</label>
                                        <div>Date: ${hearing.schedule_start.replace('T', ' ')}</div>
                                        <div>Mediator: ${hearing.mediator_name}</div>
                                        <div>Outcome: <strong>${hearing.outcome}</strong></div>
                                        <div style="margin-top:8px;">
                                            <label>Notes:</label>
                                            <textarea rows="3" readonly style="width:100%;">${hearing.hearing_notes}</textarea>
                                        </div>
                                    </div>
                                `;
                                });
                                document.getElementById('barangayHearingHistorySection').innerHTML = historyHTML;
                            }
                        }

                        // Fetch lupong hearings
                        fetch('../Process/lupong/get_lupong_hearings.php?blotter_id=' + encodeURIComponent(blotterId))
                            .then(response => response.json())
                            .then(lupongData => {
                                if (lupongData.hearings && lupongData.hearings.length > 0) {
                                    // Filter recorded hearings
                                    const recordedLupongHearings = lupongData.hearings.filter(h =>
                                        h.mediator_name && h.hearing_notes && h.outcome
                                    );

                                    if (recordedLupongHearings.length > 0) {
                                        document.getElementById('lupongHearingHistoryHeader').style.display = 'block';
                                        document.getElementById('lupongHearingHistorySection').style.display = 'block';

                                        let lupongHistoryHTML = '<p style="font-weight:600; color:#495057;">Lupong Hearings:</p>';
                                        recordedLupongHearings.forEach((hearing, index) => {
                                            lupongHistoryHTML += `
                                            <div style="background:#f0f8ff; padding:12px; border-radius:6px; margin-bottom:10px;">
                                                <label style="font-weight:600;">Lupong Hearing ${hearing.hearing_no}</label>
                                                <div>Date: ${hearing.schedule_start.replace('T', ' ')}</div>
                                                <div>Mediator: ${hearing.mediator_name}</div>
                                                <div>Outcome: <strong>${hearing.outcome}</strong></div>
                                                <div style="margin-top:8px;">
                                                    <label>Notes:</label>
                                                    <textarea rows="3" readonly style="width:100%;">${hearing.hearing_notes}</textarea>
                                                </div>
                                            </div>
                                        `;
                                        });
                                        document.getElementById('lupongHearingHistorySection').innerHTML = lupongHistoryHTML;
                                    }
                                }

                                // Check if there's a current hearing to record
                                if (lupongData.current_hearing && !lupongData.current_hearing.mediator_name) {
                                    document.getElementById('hearingDetailsHR').style.display = 'block';
                                    document.getElementById('hearingDetailsHeader').style.display = 'block';
                                    document.getElementById('hearingDetailsSection').style.display = 'block';
                                    document.getElementById('hearingNoLabel').textContent = 'Lupong Hearing No. ' + lupongData.current_hearing.hearing_no;
                                    document.getElementById('hearingDateTime').textContent = lupongData.current_hearing.schedule_start.replace('T', ' ');
                                    document.getElementById('lupong_hearing_id').value = lupongData.current_hearing.lupong_hearing_id;

                                    // Populate participants table
                                    const participantsTableContainer = document.getElementById('hearingParticipantsTableContainer');
                                    if (data.participants && data.participants.length > 0) {
                                        let tableHTML = `
                                            <table class="styled-table" style="margin-top:15px;">
                                                <thead>
                                                    <tr>
                                                        <th>Full Name</th>
                                                        <th>Type</th>
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
                                                </tr>
                                            `;
                                        });
                                        tableHTML += '</tbody></table>';
                                        participantsTableContainer.innerHTML = tableHTML;
                                    }
                                } else {
                                    document.getElementById('hearingDetailsHeader').style.display = 'none';
                                    document.getElementById('hearingDetailsSection').style.display = 'none';
                                    document.getElementById('hearingDetailsHR').style.display = 'none';
                                }

                                // Show/hide action dropdown based on status
                                const actionBtn = document.getElementById('lupongActionDropdownBtn');
                                if (data.blotter.status === 'lupong_resolved' || data.blotter.status === 'lupong_unresolved') {
                                    if (actionBtn) actionBtn.style.display = 'none';
                                } else {
                                    if (actionBtn) actionBtn.style.display = 'inline-block';
                                }
                            })
                            .catch(() => console.error('Failed to fetch lupong hearings'));

                        document.getElementById('viewEscalatedBlotterModal').style.display = 'flex';
                    })
                    .catch(() => alert('Failed to fetch blotter details.'));
            });
        });

        // Schedule Lupong Hearing
        document.getElementById('lupongScheduleHearingBtn')?.addEventListener('click', function() {
            document.getElementById('scheduleLupongHearingModal').style.display = 'flex';
            document.getElementById('lupongActionDropdownMenu').style.display = 'none';
        });

        function closeScheduleLupongHearingModal() {
            document.getElementById('scheduleLupongHearingModal').style.display = 'none';
        }

        document.getElementById('scheduleLupongHearingForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const dt = document.getElementById('lupong_hearing_datetime').value;
            if (!dt) return alert('Please select date and time.');
            
            fetch('../Process/lupong/lupong_create_hearing.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `blotter_id=${encodeURIComponent(currentEscalatedBlotterId)}&hearing_datetime=${encodeURIComponent(dt)}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(res.message || 'Hearing scheduled successfully.');
                    closeScheduleLupongHearingModal();
                    location.reload();
                } else {
                    alert(res.error || 'Failed to schedule hearing.');
                }
            })
            .catch(() => alert('Error scheduling hearing.'));
        });

        // Save Lupong Hearing
        document.getElementById('saveLupongHearingBtn')?.addEventListener('click', function() {
            const blotterId = currentEscalatedBlotterId;
            const hearingId = document.getElementById('lupong_hearing_id')?.value;
            const mediator = document.getElementById('lupong_hearing_mediator')?.value.trim();
            const notes = document.getElementById('lupong_hearing_notes')?.value.trim();
            const outcome = document.getElementById('lupong_hearing_outcome')?.value;

            if (!mediator || !notes || !outcome) {
                return alert('Please fill in all required fields.');
            }

            const formData = new FormData();
            formData.append('blotter_id', blotterId);
            formData.append('lupong_hearing_id', hearingId);
            formData.append('mediator_name', mediator);
            formData.append('hearing_notes', notes);
            formData.append('outcome', outcome);

            const btn = document.getElementById('saveLupongHearingBtn');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            fetch('../Process/lupong/lupong_record_hearing.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.textContent = 'Save Hearing';
                
                if (res.success) {
                    alert(res.message || 'Hearing saved successfully.');
                    location.reload();
                } else {
                    alert(res.error || 'Failed to save hearing.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'Save Hearing';
                alert('Error saving hearing.');
                console.error(err);
            });
        });

        // Close Case Button
        document.getElementById('lupongCloseCaseBtn')?.addEventListener('click', function() {
            document.getElementById('lupongCloseCaseConfirm').style.display = 'flex';
            document.getElementById('lupongActionDropdownMenu').style.display = 'none';
        });

        document.getElementById('lupongCloseResolved')?.addEventListener('click', function() {
            closeLupongCase('resolved');
        });

        document.getElementById('lupongCloseUnresolved')?.addEventListener('click', function() {
            closeLupongCase('unresolved');
        });

        document.getElementById('lupongCloseCancel')?.addEventListener('click', function() {
            document.getElementById('lupongCloseCaseConfirm').style.display = 'none';
        });

        function closeLupongCase(outcome) {
            if (!currentEscalatedBlotterId) return alert('Unable to determine blotter ID.');

            fetch('../Process/lupong/close_case.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `blotter_id=${encodeURIComponent(currentEscalatedBlotterId)}&outcome=${outcome}`
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(res.message || 'Case closed successfully.');
                    document.getElementById('lupongCloseCaseConfirm').style.display = 'none';
                    location.reload();
                } else {
                    alert(res.error || 'Failed to close case.');
                }
            })
            .catch(() => alert('Error closing case.'));
        }
    </script>
</body>

</html>