<?php
require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php'; // adjust path if you placed TCPDF elsewhere
require_once '../db_connection.php';

$blotter_id = $_GET['blotter_id'] ?? '';
$participant_id = $_GET['participant_id'] ?? '';
$type = $_GET['type'] ?? '';


$conn = getDBConnection();


// Fetch blotter
$stmt = $conn->prepare("SELECT * FROM blottertbl WHERE blotter_id = ?");
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$blotter = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch latest hearing for the blotter
$stmt = $conn->prepare("SELECT * FROM blotter_hearingstbl WHERE blotter_id = ? ORDER BY hearing_no DESC LIMIT 1");
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$hearing = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch participant
$stmt = $conn->prepare("SELECT * FROM blotter_participantstbl WHERE blotter_participant_id = ?");
$stmt->bind_param("s", $participant_id);
$stmt->execute();
$participant = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();

if (!$blotter || !$participant) {
    die("Invalid summon request.");
}

function fullname($p) {
    return trim(($p['lastname'] ?? '') . ', ' . ($p['firstname'] ?? '') . ' ' . ($p['middlename'] ?? ''));
}

// Format dates safely
$hearing_dt = $hearing['schedule_start'] ?? null;
$hearingDate = $hearing_dt ? date('jS', strtotime($hearing_dt)) : '';
$hearingMonth = $hearing_dt ? date('F', strtotime($hearing_dt)) : '';
$hearingYear = $hearing_dt ? date('Y', strtotime($hearing_dt)) : '';
$hearingTime = $hearing_dt ? date('g:i A', strtotime($hearing_dt)) : '';

$created_at = $hearing['created_at'] ?? ($blotter['created_at'] ?? date('Y-m-d H:i:s'));
$createdDay = date('jS', strtotime($created_at));
$createdMonth = date('F', strtotime($created_at));
$createdYear = date('Y', strtotime($created_at));

$toName = fullname($participant);
$toType = ucfirst($type);
$blotter_incident_type = htmlspecialchars($blotter['incident_type'] ?? '', ENT_QUOTES);

// Build HTML for PDF
$html = <<<EOD
<style>
  body { font-family: "times", "Times New Roman", serif; font-size: 12pt; }
  .center { text-align:center; }
  .header-small { margin:0; }
  .summon-title { font-size:16pt; text-decoration:underline; margin:18px 0; }
  .section { margin:12px 0; }
  .right { text-align:right; }
</style>

<div class="center">
  <h3 class="header-small">REPUBLIC OF THE PHILIPPINES</h3>
  <p class="header-small">Province of Laguna</p>
  <p class="header-small">City of San Pedro</p>
  <h2 class="header-small">BARANGAY SAMPAGUITA</h2>
  <p class="header-small">Tel. No. 8638-0301</p>
  <br>
  <h4 class="header-small">OFFICE OF THE LUPONG TAGAMAYAPA</h4>
</div>

<div class="section">
  <table width="100%">
    <tr>
      <td width="60%">
        <strong>{$toName}</strong><br/>
        Barangay Case No. {$blotter['blotter_id']}
      </td>
      <td width="40%" style="text-align:right;">
        For: {$blotter_incident_type}
      </td>
    </tr>
  </table>
</div>

<div class="section">
  Complainant<br>
  __________________________________
</div>

<div class="section">
  --against--<br>
  <strong>{$toName}</strong><br>
  Respondent/s
</div>

<div class="summon-title center">SUMMONS</div>

<div class="section">
  <p>
    TO: <strong>{$toName}</strong><br/>
    [{$toType}]
  </p>

  <p>
    You are hereby summoned to appear before me in person, together with your witnesses, on the {$hearingDate} day of {$hearingMonth}, {$hearingYear} at {$hearingTime}, then and there to answer the complaint made before me, a copy of which is attached hereto, for mediation/conciliation for your dispute with the complainant. You are hereby warned that if you refuse or willfully fail to appear in obedience to this summons, you may be barred from filing any counterclaim arising from said complaint. FAIL NOT or else face punishment as for contempt of court.
  </p>

  <p>This {$createdDay} day of {$createdMonth}, {$createdYear}.</p>
</div>

<div class="right">
  <p>_______HON. RHYXTER S. LABAY_______</p>
  <p>Punong Barangay/Pangkat Chairman</p>
</div>
EOD;

// create TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Barangay System');
$pdf->SetAuthor('Barangay Sampaguita');
$pdf->SetTitle('Summon Letter - ' . $blotter['blotter_id']);
$pdf->SetMargins(18, 18, 18);
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();
$pdf->writeHTML($html, true, false, true, false, '');
$filename = 'Summon_' . $blotter['blotter_id'] . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $participant_id) . '.pdf';

// Output inline so browser displays PDF viewer (user can print or download)
$pdf->Output($filename, 'I');
exit;
?>