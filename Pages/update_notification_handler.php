<?php
// DEPRECATED: This file is no longer used with the new real-time notification system
// The new system uses: Process/check_new_updates.php
// This file can be safely removed or kept for backwards compatibility

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'This handler is deprecated. Please use the new real-time notification system.']);
exit;
?>
