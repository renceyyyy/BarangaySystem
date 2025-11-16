<?php
// filepath: d:\xampp\htdocs\BarangaySampaguita\BarangaySystem\Process\blotter\print_summon.php
session_name('BarangayStaffSession');
session_start();
require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php';
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

// Fetch latest hearing
$stmt = $conn->prepare("SELECT * FROM blotter_hearingstbl WHERE blotter_id = ? ORDER BY hearing_no DESC LIMIT 1");
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$hearing = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch participant (the one we're sending summon to)
$stmt = $conn->prepare("SELECT * FROM blotter_participantstbl WHERE blotter_participant_id = ?");
$stmt->bind_param("s", $participant_id);
$stmt->execute();
$participant = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch complainant for this blotter
$stmt = $conn->prepare("SELECT * FROM blotter_participantstbl WHERE blotter_id = ? AND participant_type = 'complainant'");
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$complainantResult = $stmt->get_result();
$complainantList = [];
while ($row = $complainantResult->fetch_assoc()) {
  $complainantList[] = $row;
}
$stmt->close();

// Fetch all accused for this blotter (removed LIMIT 1)
$stmt = $conn->prepare("SELECT * FROM blotter_participantstbl WHERE blotter_id = ? AND participant_type = 'accused'");
$stmt->bind_param("s", $blotter_id);
$stmt->execute();
$accusedResult = $stmt->get_result();
$accusedList = [];
while ($row = $accusedResult->fetch_assoc()) {
  $accusedList[] = $row;
}
$stmt->close();

// Singleton connection closed by PHP

if (!$blotter || !$participant) {
  die("Invalid summon request.");
}

function fullname($p)
{
  if (!$p) return '';
  return trim(($p['lastname'] ?? '') . ', ' . ($p['firstname'] ?? '') . ' ' . ($p['middlename'] ?? ''));
}

// Prepare safe variables
$complainantNames = '';
if (!empty($complainantList)) {
  foreach ($complainantList as $complainant) {
    $complainantNames .= fullname($complainant) . '<br>';
  }
} else {
  $complainantNames = 'Complainant';
}
// Build accused names dynamically
$accusedNames = '';
if (!empty($accusedList)) {
  foreach ($accusedList as $accused) {
    $accusedNames .= fullname($accused) . '<br>';
  }
} else {
  $accusedNames = 'Respondent';
}
$toName = fullname($participant);
$toType = ($type === 'accused') ? 'Respondent' : ucfirst($type);
$blotter_incident_type = htmlspecialchars($blotter['incident_type'] ?? '', ENT_QUOTES);

// Format dates
$hearing_dt = $hearing['schedule_start'] ?? null;
$hearingDate = $hearing_dt ? date('jS', strtotime($hearing_dt)) : '';
$hearingMonth = $hearing_dt ? date('F', strtotime($hearing_dt)) : '';
$hearingYear = $hearing_dt ? date('Y', strtotime($hearing_dt)) : '';
$hearingTime = $hearing_dt ? date('g:i A', strtotime($hearing_dt)) : '';

$created_at = $hearing['created_at'] ?? ($blotter['created_at'] ?? date('Y-m-d H:i:s'));
$createdDay = date('jS', strtotime($created_at));
$createdMonth = date('F', strtotime($created_at));
$createdYear = date('Y', strtotime($created_at));

// Define image paths (adjust if necessary based on your server setup; these are relative to the script's location)
$leftImagePath = __DIR__ . '/../../Assets/sampaguitalogo.jpg';
$rightImagePath = __DIR__ . '/../../Assets/SanPedroLogo.jpg';

// ✅ HTML with tight header spacing and images added via table layout
$html = <<<EOD
<style>
  body { font-family: "Times New Roman", serif; font-size: 12pt; margin: 0; padding: 0; text-align: justify; }
  .header { text-align:center; margin-bottom:10px; line-height:1; } /* ✅ Tight spacing */
  .header h3 { font-size:12pt; font-weight:bold; margin:0; text-transform:uppercase; }
  .header p { font-size:11pt; margin:0; }
  .header h2 { font-size:13pt; font-weight:bold; margin:2px 0; text-transform:uppercase; }
  .header h4 { font-size:12pt; font-weight:bold; margin:2px 0; text-transform:uppercase; }
  .summon-title { text-align:center; font-size:14pt; font-weight:bold; margin:12px 0; }
  .section { margin:8px 0; }
  .body-text { line-height: 1.5; }
  .body-text p { text-indent: 20px; margin-bottom: 8px; }
</style>

<div class="header">
  <table style="width:100%; border: none;">
    <tr>
      <td style="width:20%; text-align:left; vertical-align:top;">
        <img src="{$leftImagePath}" width="50" height="50" style="max-width:50px; max-height:50px;">
      </td>
      <td style="width:60%; text-align:center; vertical-align:top;">
        <h3>REPUBLIC OF THE PHILIPPINES</h3>
        <p>Province of Laguna</p>
        <p>City of San Pedro</p>
        <h2>BARANGAY SAMPAGUITA</h2>
        <p>Tel. No. 8638-0301</p>
        <h4>OFFICE OF THE PUNONG BARANGAY</h4>
      </td>
      <td style="width:20%; text-align:right; vertical-align:top;">
        <img src="{$rightImagePath}" width="50" height="50" style="max-width:50px; max-height:50px;">
      </td>
    </tr>
  </table>
</div>

<div class="section" style="margin-top:15px;">
  <table style="width:100%;">
    <tr>
      <td width="64%">
        <strong>{$complainantNames}</strong>
        Complainant/s<br>
        -- against --<br>
        <strong>{$accusedNames}</strong>
        Respondent/s
      </td>
      <td width="36%" style="text-align:left;">
        Barangay Case No. {$blotter['blotter_id']}<br>
        For: {$blotter_incident_type}
      </td>
    </tr>
  </table>
</div>

<div class="summon-title">SUMMONS</div>

<div class="section body-text">
  <p>TO: <strong>{$toName}</strong><br/>[{$toType}]</p>
  <p style="text-align:justify;">
    You are hereby summoned to appear before me in person, together with your witnesses, on the {$hearingDate} day of {$hearingMonth}, {$hearingYear} at {$hearingTime}, then and there to answer the complaint made before me, a copy of which is attached hereto, for mediation/conciliation for your dispute with the complainant. You are hereby warned that if you refuse or willfully fail to appear in obedience to this summons, you may be barred from filing any counterclaim arising from said complaint. FAIL NOT or else face punishment as for contempt of court.
  </p>
  <p>This {$createdDay} day of {$createdMonth}, {$createdYear}.</p>
</div>

  <p style="font-weight:bold; text-decoration:underline; font-size:12pt; display:inline-block; margin:0; text-align:right; margin-top:1px;">
    HON. RHYXTER S. LABAY
  </p>
  <p style="margin:1px 0 0; font-size:11pt; text-align:right; margin: top 0.1em;">
    Punong Barangay / Pangkat Chairman
  </p>

EOD;

// ✅ TCPDF settings with smaller top margin
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->SetCreator('Barangay System');
$pdf->SetAuthor('Barangay Sampaguita');
$pdf->SetTitle('Summon Letter - ' . $blotter['blotter_id']);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(20, 10, 20); // ✅ Reduced top margin
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetFont('times', '', 12);
$pdf->AddPage();

// Place images manually
$pdf->Image($leftImagePath, 21, 18, 31, 31, '', '', '', false, 300, '', false, false, 0);
$pdf->Image($rightImagePath, 160, 18, 30, 30, '', '', '', false, 300, '', false, false, 0);

// ✅ Write HTML content
$pdf->writeHTML($html, true, false, true, false, '');
$filename = 'Summon_' . $blotter['blotter_id'] . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $participant_id) . '.pdf';

$pdf->Output($filename, 'I');
exit;
?>
