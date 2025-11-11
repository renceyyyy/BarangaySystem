<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "barangayDb");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->connect_error]);
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ---------- 1) NEW REQUEST ----------
    if (isset($_POST['saveItemRequest'])) {
        $name = trim($_POST['residentName']);
        $item = trim($_POST['itemSelect']);
        $quantity = (int) $_POST['quantity'];
        $purpose = trim($_POST['purpose']);
        $eventDT = $_POST['eventDatetime'];

        // Check stock & existing reservations (same logic as before)
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
        if ($row = $rs->get_result()->fetch_assoc()) {
            $reserved = $row['r'] ?? 0;
        }
        $rs->close();

        $available = $inv['total_stock'] - $inv['on_loan'] - $reserved;

        if ($quantity > $available) {
            $response['message'] = "Request denied: Only $available $item(s) available.";
        } else {
            $status = 'Pending';
            $ins = $conn->prepare(
                "INSERT INTO tblitemrequest
                 (name,Purpose,item,quantity,event_datetime,date,RequestStatus)
                 VALUES (?,?,?,?,?,NOW(),?)"
            );
            $ins->bind_param("sssiss", $name, $purpose, $item, $quantity, $eventDT, $status);
            if ($ins->execute()) {
                $response['success'] = true;
                $response['message'] = "Request submitted successfully.";
            } else {
                $response['message'] = "Error submitting request.";
            }
            $ins->close();
        }
    }

    // ---------- 2) ACTION BUTTONS ----------
    elseif (isset($_POST['action'], $_POST['id'])) {
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
                $rs = $conn->query("SELECT SUM(quantity) AS r FROM tblitemrequest
                      WHERE item='$item' AND RequestStatus IN ('Pending','Approved','On Loan')
                      AND event_datetime='$eventDT' AND id != $id");
                if ($row = $rs->fetch_assoc()) {
                    $reserved = $row['r'] ?? 0;
                }

                $available = $inv['total_stock'] - $inv['on_loan'] - $reserved;

                if ($quantity > $available) {
                    $response['message'] = "Cannot approve: Only $available $item(s) available.";
                } else {
                    if ($conn->query("UPDATE tblitemrequest SET RequestStatus='Approved' WHERE id=$id")) {
                        $response['success'] = true;
                        $response['message'] = "Request approved successfully.";
                    } else {
                        $response['message'] = "Error approving request.";
                    }
                }
                break;

            case 'reject':
                $reason = $conn->real_escape_string($_POST['reason'] ?? 'Not specified');
                if ($conn->query("UPDATE tblitemrequest SET RequestStatus='Rejected', Reason='$reason' WHERE id=$id")) {
                    $response['success'] = true;
                    $response['message'] = "Request rejected.";
                } else {
                    $response['message'] = "Error rejecting request.";
                }
                break;

            case 'release':
                $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id")->fetch_assoc();
                if ($conn->query("UPDATE inventory SET on_loan = on_loan + {$q['quantity']} WHERE item_name = '{$q['item']}'") &&
                    $conn->query("UPDATE tblitemrequest SET RequestStatus='On Loan' WHERE id=$id")) {
                    $response['success'] = true;
                    $response['message'] = "Item released.";
                } else {
                    $response['message'] = "Error releasing item.";
                }
                break;

            case 'return':
                $q = $conn->query("SELECT item, quantity FROM tblitemrequest WHERE id=$id")->fetch_assoc();
                if ($conn->query("UPDATE inventory SET on_loan = GREATEST(on_loan - {$q['quantity']},0) WHERE item_name = '{$q['item']}'") &&
                    $conn->query("UPDATE tblitemrequest SET RequestStatus='Returned' WHERE id=$id")) {
                    $response['success'] = true;
                    $response['message'] = "Item returned.";
                } else {
                    $response['message'] = "Error returning item.";
                }
                break;
        }
    }
}

// Don't close singleton connection
echo json_encode($response);
?>
