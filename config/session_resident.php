<?php
// Resident session initialization
// Include this at the top of ALL resident pages
if (session_status() === PHP_SESSION_NONE) {
    session_name('BarangayResidentSession');
    session_start();
}
?>