<?php
// Staff (Admin/Finance/SK/SuperAdmin) session initialization
// Include this at the top of ALL staff pages
if (session_status() === PHP_SESSION_NONE) {
    session_name('BarangayStaffSession');
    session_start();
}
?>