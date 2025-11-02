<?php
$conn = new mysqli("localhost","root","","barangayDb");
if ($conn->connect_error) { die("DB error: ".$conn->connect_error); }

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'], $_POST['action'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    switch ($action) {
        case 'approve':
            $status = 'Approved';
            $stmt = $conn->prepare("UPDATE tblitemrequest SET RequestStatus=? WHERE id=?");
            $stmt->bind_param("si",$status,$id);
            $stmt->execute();
            break;

        case 'reject':
            $status = 'Rejected';
            $reason = 'Not available'; // or get from a form/modal
            $stmt = $conn->prepare("UPDATE tblitemrequest SET RequestStatus=?, Reason=? WHERE id=?");
            $stmt->bind_param("ssi",$status,$reason,$id);
            $stmt->execute();
            break;

        case 'release':
            // mark On Loan and update inventory
            $status = 'On Loan';
            $stmt = $conn->prepare("UPDATE tblitemrequest SET RequestStatus=? WHERE id=?");
            $stmt->bind_param("si",$status,$id);
            $stmt->execute();

            // deduct from inventory
            $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id");
            if ($r = $q->fetch_assoc()) {
                $conn->query("UPDATE inventory
                              SET on_loan = on_loan + {$r['quantity']}
                              WHERE item_name = '{$r['item']}'");
            }
            break;

        case 'return':
            // mark Returned and reduce on_loan
            $status = 'Returned';
            $stmt = $conn->prepare("UPDATE tblitemrequest SET RequestStatus=? WHERE id=?");
            $stmt->bind_param("si",$status,$id);
            $stmt->execute();

            $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id");
            if ($r = $q->fetch_assoc()) {
                $conn->query("UPDATE inventory
                              SET on_loan = on_loan - {$r['quantity']}
                              WHERE item_name = '{$r['item']}'");
            }
            break;
    }
}
header("Location: itemrequestsPanel.php");
exit;
?>
