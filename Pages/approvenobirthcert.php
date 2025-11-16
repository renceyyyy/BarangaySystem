<?php



require_once '../Process/db_connection.php';


$connection = getDBConnection();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    

    $stmt = $connection->prepare("UPDATE no_birthcert_tbl SET RequestStatus = 'Approved' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
       if($stmt->affected_rows > 0) {
           
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
           parse_str(parse_url($referrer, PHP_URL_QUERY) ?? '', $urlParams);

          
           $redirectUrl = 'Adminpage.php';
           if (!empty($urlParams)) {
               // Preserve existing params but override/ensure panel
               $urlParams['panel'] = 'nobirthCertPanel';
               $redirectUrl .= '?' . http_build_query($urlParams);
           } else {
               $redirectUrl .= '?panel=nobirthCertPanel';
           }
              $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'message=approved';
              
              header("Location: " . $redirectUrl);
              exit();
         } else {
             echo "No record found to approve.";
         }  
    } else {
        echo "Error updating record: " . $connection->error;    
    }
    $stmt->close();
} else {
    echo "No request ID provided.";
}

$connection->close();
?>