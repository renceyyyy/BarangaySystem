<?php
// Set staff session name BEFORE starting session
session_name('BarangayStaffSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security check — only finance users allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'finance') {
    header("Location: ../Login/login.php");
    exit();
}

// Debug logs — helps you confirm session data
error_log("FinancePage.php — Username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("FinancePage.php — Fullname: " . ($_SESSION['fullname'] ?? 'NOT SET'));
error_log("FinancePage.php — Role: " . ($_SESSION['role'] ?? 'NOT SET'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Finance Dashboard - Barangay Sampaguita</title>
  <link rel="stylesheet" href="../Styles/admin.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

</head>

<body>
  <?php include 'dashboard.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-12 col-md-2 sidebar">
        <img src="/Capstone/Assets/sampaguitalogo.png" alt="Logo" class="mb-4"
          style="width: 100%; max-width: 160px; border-radius: 50%;" />
        <button class="sidebar-btn" type="button" onclick="showPanel('dashboardPanel')">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </button>

        <button class="sidebar-btn" type="button" onclick="showPanel('collectionPanel')">
          <i class="fas fa-money-bill"></i> Collection
        </button>


        <a href="javascript:void(0);" class="logout-link mt-auto" onclick="confirmLogout(event); return false;">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>

      <!-- Main Content -->
      <div class="col-12 col-md-10 p-0">
        <div class="main-content-scroll p-3">
          <div class="admin-header">
            <h1>BARANGAY SAMPAGUITA COLLECTION</h1>
          </div>

          <!-- Panels -->
          <div id="dashboardPanel" class="panel-content ">

            <h3>Dashboard</h3>
            <div class="stat-card-container mb-3">

              

              

              <?php include 'collectiontotal.php'; ?>
              <div class="stat-card scholarship-card">
                <div class="stat-left">
                  <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
                  <p>COLLECTION</p>
                </div>
                <h4>₱<?php echo number_format($totalApproved); ?></h4>
              </div>


            </div>

           <div class="chart-container mt-4">
  <div class="chart-card-modern">
    <div class="chart-header">
      <div class="chart-title">
        <i class="fa-solid fa-chart-column"></i>
        <h4>Collection Overview</h4>
      </div>
     
    </div>

    <canvas id="CollectionChart"></canvas>
  </div>
</div>
</div>



<style>
  /* ===== Modern Collection Panel Styling ===== */
  #collectionPanel {
    background: linear-gradient(180deg, #f8fafc, #f1f5f9);
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    animation: fadeIn 0.8s ease-in-out;
  }

  #collectionPanel h3 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
  }

  .approved-total {
    font-size: 1rem;
    color: #0ea5e9;
    font-weight: 600;
  }

  /* ===== Search Form ===== */
  .search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    animation: slideUp 0.7s ease;
  }

  .search-form-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    width: 100%;
    align-items: center;
  }

  .search-input {
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    padding: 0.6rem 0.8rem;
    flex: 1;
    transition: all 0.2s ease;
  }

  .search-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37,99,235,0.2);
  }

  .search-btn, .print-btn {
     padding: 8px 16px;
      font-size: 14px;
      font-weight: 500;
     
      cursor: pointer;
     
      display: inline-flex;
      align-items: center;
      gap: 6px;
     background-color: #d9f2dc;       /* light green fill */
  border: 2px solid #2d7a3e;       /* darker green border line */
  border-radius: 6px;

  color: #2d7a3e;                  /* dark green text */
  box-shadow: none;                /* remove heavy shadow for clean look */
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  }

  .search-btn {
     padding: 8px 16px;
      font-size: 14px;
      font-weight: 500;
     
      cursor: pointer;
     
      display: inline-flex;
      align-items: center;
      gap: 6px;
     background-color: #d9f2dc;       /* light green fill */
  border: 2px solid #2d7a3e;       /* darker green border line */
  border-radius: 6px;

  color: #2d7a3e;                  /* dark green text */
  box-shadow: none;                /* remove heavy shadow for clean look */
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  }

  .print-btn {
     padding: 8px 16px;
      font-size: 14px;
      font-weight: 500;
     
      cursor: pointer;
     
      display: inline-flex;
      align-items: center;
      gap: 6px;
     background-color: #d9f2dc;       /* light green fill */
  border: 2px solid #2d7a3e;       /* darker green border line */
  border-radius: 6px;

  color: #2d7a3e;                  /* dark green text */
  box-shadow: none;                /* remove heavy shadow for clean look */
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  }

  .search-btn:hover {
     padding: 8px 16px;
      font-size: 14px;
      font-weight: 500;
     
      cursor: pointer;
     
      display: inline-flex;
      align-items: center;
      gap: 6px;
     background-color: #d9f2dc;       /* light green fill */
  border: 2px solid #2d7a3e;       /* darker green border line */
  border-radius: 6px;

  color: #17af37ff;                  /* dark green text */
  box-shadow: none;                /* remove heavy shadow for clean look */
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  }

  .print-btn:hover {
    padding: 8px 16px;
      font-size: 14px;
      font-weight: 500;
     
      cursor: pointer;
     
      display: inline-flex;
      align-items: center;
      gap: 6px;
     background-color: #d9f2dc;       /* light green fill */
  border: 2px solid #2d7a3e;       /* darker green border line */
  border-radius: 6px;

  color: #21af40ff;                  /* dark green text */
  box-shadow: none;                /* remove heavy shadow for clean look */
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  }

  /* ===== Tables ===== */
  .scrollable-table-container {
    max-height: 700px;
    overflow-y: auto;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    background: white;
    animation: fadeInUp 0.8s ease both;
  }

  .styled-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
  }

  .styled-table thead {
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    color: white;
  }

  .styled-table th, .styled-table td {
    padding: 0.8rem;
    text-align: center;
    font-size: 0.9rem;
  }

  .styled-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
  }

  .styled-table tbody tr:hover {
    background: #f8fafc;
    transform: scale(1.01);
  }

  /* ===== Action Buttons ===== */
  .action-btn-2 {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .action-btn-2.approve {
    background: #22c55e;
  }

  .action-btn-2.decline {
    background: #ef4444;
  }

  .action-btn-2:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  }

  /* ===== Layout ===== */
  .collection-layout {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 1.5rem;
  }

  .collection-column {
    flex: 1 1 450px;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    animation: slideUp 0.7s ease both;
  }

  .collection-column h4 {
    font-size: 1.2rem;
    color: #0f172a;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  /* ===== Animations ===== */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* Scrollbar styling */
  .scrollable-table-container::-webkit-scrollbar {
    width: 8px;
  }
  .scrollable-table-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }
  .scrollable-table-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  /* ===== Modern Chart Card Design ===== */
.chart-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.chart-card-modern {
  width: 100%;
  max-width: 1600px;
  background:  #e0edd93a;
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.07);
  padding: 6rem;
  animation: fadeIn 0.8s ease-in-out;
  transition: all 0.3s ease;
}

.chart-card-modern:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1);
}

/* ===== Header ===== */
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.8rem;
}

.chart-title {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  color: #1e293b;
}

.chart-title h4 {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  letter-spacing: 0.3px;
}

.chart-title i {
  color: #22c55e;
  font-size: 1.4rem;
}

/* ===== Dropdown Filter ===== */
.chart-select {
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 0.4rem 0.8rem;
  font-size: 0.9rem;
  background-color: #ffffff;
  cursor: pointer;
  transition: all 0.2s ease;
}

.chart-select:hover {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
}

/* ===== Chart Canvas ===== */
#CollectionChart {
  width: 100%;
  max-height: 400px;
  margin-top: 10px;
}

/* ===== Animation ===== */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

</style>



          <div id="collectionPanel" class="panel-content">
  <?php include 'collectiontotal.php'; ?>
  <h3>
    💰 Collection
    <br>
    <span class="approved-total">
      Total Amount of Collection: ₱<?php echo number_format($totalApproved, 2); ?>
    </span>
  </h3>

  <!-- Search Section -->
   <form method="GET" action="" class="mb-3 search-form">
    <div class="search-form-group">
      <input type="text" name="search_refno" class="form-control search-input"
             placeholder=" Search by Reference Number"
             value="<?php echo isset($_GET['search_refno']) ? htmlspecialchars($_GET['search_refno']) : ''; ?>">
               <button type="submit" class="search-btn">
        <i class="fas fa-search"></i> Search
      </button>

      <input type="date" name="search_date" class="form-control search-input"
             value="<?php echo isset($_GET['search_date']) ? htmlspecialchars($_GET['search_date']) : ''; ?>">

     
      <button type="submit" name="date_search" value="1" class="search-btn">
        <i class="fas fa-calendar-alt"></i> Search Date
      </button>
      <button class="print-btn" type="button" onclick="printApprovedTable()">
        <i class="fas fa-print"></i> Print
      </button>
    </div>
  </form>
            <?php
            $connection = new mysqli("localhost", "root", "", "barangayDb");
            if ($connection->connect_error) {
              die("Connection failed: " . $connection->connect_error);
            }

            $searchQuery = "";
            if (isset($_GET['search_refno']) && !empty(trim($_GET['search_refno']))) {
              $search = $connection->real_escape_string($_GET['search_refno']);
              $searchQuery = " AND refno LIKE '%$search%'";
            }
            if (isset($_GET['search_date']) && !empty($_GET['search_date']) && isset($_GET['date_search'])) {
              $searchDate = $connection->real_escape_string($_GET['search_date']);
              $searchQuery .= " AND DATE(PaymentDateReceived) = '$searchDate'";
            }

            $pendingSql = "SELECT CollectionID, name, type, refno, date, amount 
                 FROM tblpayment 
                 WHERE RequestStatus = 'Pending' $searchQuery
                 ORDER BY date DESC";
            $pendingResult = $connection->query($pendingSql);

                  $approvedSql = "SELECT CollectionID, refno, name, type, amount, date, PaymentReceivedBy, PaymentDateReceived"
                    . " FROM tblpayment"
                    . " WHERE RequestStatus = 'Paid' $searchQuery"
                    . " ORDER BY date DESC";
            $approvedResult = $connection->query($approvedSql);

            ?>

            <!-- Side by Side Layout -->
             <div class="collection-layout">
   

    <!-- Approved -->
    <div class="collection-column">
      <h4>✅ Paid</h4>
      <div class="scrollable-table-container">
        <table class="styled-table" id="approvedTable">
          <thead>
  <tr>
    <th>ID</th>
    <th>NAME</th>
    <th>TYPE</th>
    <th>REFERENCE</th>
    <th>PAYMENT DATE RECEIVED</th>
    <th>PAYMENT RECEIVED BY</th>
    <th>AMOUNT</th>
  </tr>
</thead>
<tbody>
  <?php
  if ($approvedResult && $approvedResult->num_rows > 0) {
    while ($row = $approvedResult->fetch_assoc()) {
      $paymentDateReceived = !empty($row['PaymentDateReceived']) ? date('Y-m-d', strtotime($row['PaymentDateReceived'])) : '-';
      echo "<tr>
              <td>{$row["CollectionID"]}</td>
              <td>" . strtoupper($row["name"]) . "</td>
              <td>" . strtoupper($row["type"]) . "</td>
              <td>{$row["refno"]}</td>
              <td>" . htmlspecialchars($paymentDateReceived) . "</td>
              <td>" . htmlspecialchars($row["PaymentReceivedBy"]) . "</td>
              <td>" . number_format($row["amount"], 2) . "</td>
            </tr>";
    }
  } else {
    echo "<tr><td colspan='8'>No approved requests found.</td></tr>";
  }
  ?>
</tbody>

        </table>
      </div>
    </div>
  </div>
</div>






          <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
          <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
          <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

          <script>
            

            const paymentMonthlyLabels = <?php echo json_encode($paymentMonthlyLabels); ?>;
            const paymentMonthlyCounts = <?php echo json_encode($paymentMonthlyCounts); ?>;

           
           new Chart(document.getElementById('CollectionChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($paymentMonthlyLabels); ?>,
    datasets: [{
      label: 'Collections',
      data: <?php echo json_encode($paymentMonthlyCounts); ?>,
      backgroundColor: [
        '#4ade80', '#22c55e', '#16a34a', '#15803d'
      ],
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#1e293b',
        titleColor: '#fff',
        bodyColor: '#e2e8f0',
        cornerRadius: 6,
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: {
          color: '#f1f5f9'
        },
        ticks: {
          color: '#64748b',
          font: { size: 12 }
        }
      },
      x: {
        grid: { display: false },
        ticks: {
          color: '#64748b',
          font: { size: 12 }
        }
      }
    }
  }
});

           
            

          </script>

          <!-- Scripts -->
          <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
          <script>
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

            window.addEventListener("DOMContentLoaded", function () {
              const urlParams = new URLSearchParams(window.location.search);
              const panels = document.querySelectorAll(".panel-content");
              panels.forEach(p => p.classList.remove("active"));

              if (urlParams.has("search_lastname")) {
                // Decide which panel to show when 'search_lastname' is present
                const govPanel = document.getElementById("governmentDocumentPanel");
                if (govPanel) {
                  govPanel.classList.add("active");
                  return;
                }
              } else if (urlParams.has("search_refno")) {
                const collectionPanel = document.getElementById("collectionPanel");
                if (collectionPanel) {
                  collectionPanel.classList.add("active");
                  return;
                }

              } else if (urlParams.has("panel")) {
                const panelId = urlParams.get("panel");
                const targetPanel = document.getElementById(panelId);
                if (targetPanel) {
                  targetPanel.classList.add("active");
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
            function printApprovedTable() {
  const originalTable = document.querySelector("#approvedTable");

  // Clone the approved table
  const clonedTable = originalTable.cloneNode(true);

  // Remove the first column (ID)
  const theadRow = clonedTable.querySelector("thead tr");
  if (theadRow) {
    theadRow.removeChild(theadRow.firstElementChild);
  }
  clonedTable.querySelectorAll("tbody tr").forEach(row => {
    row.removeChild(row.firstElementChild);
  });

  // --- 🧮 Calculate Total Amount ---
  let totalAmount = 0;
  clonedTable.querySelectorAll("tbody tr").forEach(row => {
    const amountCell = row.lastElementChild;
    if (amountCell) {
      const amount = parseFloat(amountCell.textContent.replace(/,/g, '')) || 0;
      totalAmount += amount;
    }
  });

  // --- ➕ Add Total Row ---
  const totalRow = document.createElement("tr");
  // Compute colspan dynamically: leave last column for the amount
  const headerCells = clonedTable.querySelectorAll("thead th");
  const remainingCols = headerCells.length;
  const colspan = Math.max(1, remainingCols - 1);
  totalRow.innerHTML = `
    <td colspan="${colspan}" style="text-align:right; font-weight:bold;">Total Collections:</td>
    <td style="font-weight:bold;">₱ ${totalAmount.toFixed(2)}</td>
  `;
  clonedTable.querySelector("tbody").appendChild(totalRow);

  const currentDate = new Date().toLocaleDateString();

  const printWindow = window.open('', '', 'height=700,width=1000');
  printWindow.document.write(`
    <html>
    <head>
      <title>Collection Report</title>
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
        .header h1 { margin: 0; font-size: 22px; }
        .header h2 { margin: 0; font-size: 18px; color: #666; }
        .header h3 { margin: 0; font-size: 16px; color: #666; }
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
      <img class="watermark" src="${location.origin}/Capstone/Assets/sampaguitalogo.png" alt="Watermark">
      <div class="content">
        <div class="header">
          <h1>Barangay Sampaguita</h1>
          <h2>Collection Report</h2>
          <h3>San Pedro, Laguna</h3>
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

  printWindow.document.close();
  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
}

          </script>

</body>

</html>
