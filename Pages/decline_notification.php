<?php
function checkDeclinedRequests($conn, $userId) {
    $declinedRequests = [];
    
    // Initialize session tracking for decline popup display count
    if (!isset($_SESSION['decline_popup_display_count'])) {
        $_SESSION['decline_popup_display_count'] = 0;
    }
    
    // Don't show popup if already displayed 3 times
    if ($_SESSION['decline_popup_display_count'] >= 3) {
        return [];
    }
    
    // Check if user has logged in before and get the last decline notification check time
    if (!isset($_SESSION['last_decline_notification_check'])) {
        $_SESSION['last_decline_notification_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
    }
    
    $lastCheck = $_SESSION['last_decline_notification_check'];
    
    // Get the most recent decline for each request type (limit to latest only)
    $queries = [
        [
            'table' => 'docsreqtbl',
            'select' => 'DocuType as description, refno, DateRequested as request_date, Reason',
            'type' => 'Document Request',
            'user_col' => 'UserId',
            'date_col' => 'DateRequested'
        ],
        [
            'table' => 'businesstbl', 
            'select' => 'CONCAT(BusinessName, " - ", RequestType) as description, refno, RequestedDate as request_date, Reason',
            'type' => 'Business Request',
            'user_col' => 'UserId',
            'date_col' => 'RequestedDate'
        ],
        [
            'table' => 'scholarship',
            'select' => '"Scholarship Application" as description, ApplicationID as refno, DateApplied as request_date, Reason',
            'type' => 'Scholarship Application',
            'user_col' => 'UserID',
            'date_col' => 'DateApplied'
        ],
        [
            'table' => 'unemploymenttbl',
            'select' => 'certificate_type as description, refno, request_date, Reason',
            'type' => 'Unemployment Certificate',
            'user_col' => 'user_id',
            'date_col' => 'request_date'
        ],
        [
            'table' => 'guardianshiptbl',
            'select' => 'request_type as description, refno, request_date, Reason',
            'type' => 'Guardianship/Solo Parent',
            'user_col' => 'user_id', 
            'date_col' => 'request_date'
        ],
        [
            'table' => 'no_birthcert_tbl',
            'select' => '"No Birth Certificate Request" as description, refno, request_date, Reason',
            'type' => 'No Birth Certificate',
            'user_col' => 'user_id',
            'date_col' => 'request_date'
        ],
        [
            'table' => 'complaintbl',
            'select' => 'Complain as description, refno, DateComplained as request_date, Reason',
            'type' => 'Complaint',
            'user_col' => 'Userid',
            'date_col' => 'DateComplained'
        ]
    ];
    
    foreach ($queries as $query) {
        // Get only the most recent declined request for each type
        $sql = "SELECT {$query['select']}, '{$query['type']}' as request_type
                FROM {$query['table']} 
                WHERE {$query['user_col']} = ? 
                AND RequestStatus = 'declined'
                AND {$query['date_col']} >= ?
                ORDER BY {$query['date_col']} DESC 
                LIMIT 1";
                
        $stmt = db_prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $userId, $lastCheck);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $declinedRequests[] = [
                    'type' => $row['request_type'],
                    'description' => $row['description'],
                    'refno' => $row['refno'],
                    'request_date' => $row['request_date'],
                    'reason' => $row['Reason'] ?? 'No reason provided'
                ];
            }
            $stmt->close();
        }
    }
    
    // Sort by request date (most recent first) and limit to 3 most recent
    usort($declinedRequests, function($a, $b) {
        return strtotime($b['request_date']) - strtotime($a['request_date']);
    });
    
    // Limit to maximum 3 notifications
    $declinedRequests = array_slice($declinedRequests, 0, 3);
    
    // Only update session and increment counter if there are notifications to show
    if (!empty($declinedRequests)) {
        $_SESSION['last_decline_notification_check'] = date('Y-m-d H:i:s');
        $_SESSION['decline_popup_display_count']++;
    }
    
    return $declinedRequests;
}

function displayDeclineNotifications($declinedRequests) {
    if (empty($declinedRequests)) {
        return;
    }
    ?>
    <div id="declineNotification" class="decline-notification">
        <div class="notification-content">
            <div class="notification-header">
                <i class="fas fa-exclamation-circle"></i>
                <span>Request Declined</span>
                <button class="notification-close" onclick="closeDeclineNotification()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="notification-body">
                <?php foreach ($declinedRequests as $index => $request): ?>
                    <div class="notification-item <?= $index === 0 ? 'latest' : '' ?>">
                        <div class="item-header">
                            <div class="status-indicator">
                                <i class="fas fa-times-circle declined-icon"></i>
                                <strong><?= htmlspecialchars($request['type']) ?></strong>
                            </div>
                            <?php if ($index === 0): ?>
                                <span class="latest-badge">Latest</span>
                            <?php endif; ?>
                        </div>
                        <p class="request-description"><?= htmlspecialchars($request['description']) ?></p>
                        
                        <div class="decline-reason">
                            <strong><i class="fas fa-comment-alt"></i> Reason for Decline:</strong>
                            <p class="reason-text"><?= htmlspecialchars($request['reason']) ?></p>
                        </div>
                        
                        <div class="item-footer">
                            <small>Ref: <?= htmlspecialchars($request['refno']) ?></small>
                            <small class="declined-date"><?= date('M j, Y g:i A', strtotime($request['request_date'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="notification-footer">
                <button class="btn-view-requests" onclick="window.location.href='UserReports.php'">
                    View All Requests
                </button>
                <small class="display-count">
                    <?php 
                    $remaining = 3 - $_SESSION['decline_popup_display_count'];
                    echo $remaining > 0 ? "Will show {$remaining} more times" : "Last notification";
                    ?>
                </small>
            </div>
        </div>
    </div>
    
    <style>
    .decline-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 40px rgba(220, 53, 69, 0.3);
        z-index: 10001;
        width: 420px;
        border-left: 5px solid #dc3545;
        animation: slideInRight 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        max-height: 70vh;
        overflow-y: auto;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%) scale(0.8);
        }
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }
    
    @keyframes pulseRed {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .decline-notification .notification-content {
        padding: 25px;
    }
    
    .decline-notification .notification-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        color: #dc3545;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .decline-notification .notification-header i {
        font-size: 1.4rem;
        animation: pulseRed 2s infinite;
    }
    
    .decline-notification .notification-close {
        margin-left: auto;
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: all 0.2s ease;
        font-size: 1.2rem;
    }
    
    .decline-notification .notification-close:hover {
        background: #f0f0f0;
        color: #666;
        transform: rotate(90deg);
    }
    
    .decline-notification .notification-item {
        margin-bottom: 16px;
        padding: 15px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fdf0f0, #fef7f7);
        border: 1px solid #f8d7da;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .decline-notification .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.1);
    }
    
    .decline-notification .notification-item.latest {
        border-left: 4px solid #dc3545;
        background: linear-gradient(135deg, #f8e8e8, #fdf0f0);
    }
    
    .decline-notification .notification-item:last-child {
        margin-bottom: 0;
    }
    
    .decline-notification .item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .decline-notification .status-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .decline-notification .item-header strong {
        color: #721c24;
        font-size: 0.95rem;
    }
    
    .decline-notification .declined-icon {
        color: #dc3545;
        font-size: 1.1rem;
    }
    
    .decline-notification .latest-badge {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .decline-notification .request-description {
        margin: 8px 0;
        color: #555;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .decline-notification .decline-reason {
        margin: 12px 0;
        padding: 12px;
        background: rgba(220, 53, 69, 0.08);
        border-radius: 8px;
        border-left: 3px solid #dc3545;
    }
    
    .decline-notification .decline-reason strong {
        color: #dc3545;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }
    
    .decline-notification .reason-text {
        color: #721c24 !important;
        font-size: 0.85rem !important;
        margin: 0 !important;
        line-height: 1.4;
        padding-left: 20px;
    }
    
    .decline-notification .item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }
    
    .decline-notification .item-footer small {
        color: #888;
        font-size: 0.8rem;
    }
    
    .decline-notification .declined-date {
        font-weight: 500;
        color: #dc3545 !important;
    }
    
    .decline-notification .notification-footer {
        margin-top: 20px;
        text-align: center;
        border-top: 1px solid #f8d7da;
        padding-top: 15px;
    }
    
    .decline-notification .btn-view-requests {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-bottom: 8px;
        display: block;
        width: 100%;
    }
    
    .decline-notification .btn-view-requests:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
    }
    
    .decline-notification .display-count {
        color: #888;
        font-size: 0.75rem;
        font-style: italic;
    }
    
    @media (max-width: 480px) {
        .decline-notification {
            width: 95%;
            right: 2.5%;
            top: 10px;
            max-height: 80vh;
        }
        
        .decline-notification .notification-content {
            padding: 20px;
        }
    }
    </style>
    
    <script>
    function closeDeclineNotification() {
        const notification = document.getElementById('declineNotification');
        notification.style.animation = 'slideOutRight 0.4s ease-out forwards';
        setTimeout(() => {
            notification.remove();
        }, 400);
    }
    
    // Auto close after 20 seconds (longer for decline notifications)
    setTimeout(() => {
        const notification = document.getElementById('declineNotification');
        if (notification) {
            closeDeclineNotification();
        }
    }, 20000);
    
    // Add slide out animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateX(100%) scale(0.8);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Real-time notification system is now handled by navbar.php
    // Removed automatic page reload to prevent continuous refreshing
    </script>
    <?php
}

// Function to reset decline notification counter
function resetDeclineNotificationCounter() {
    $_SESSION['decline_popup_display_count'] = 0;
    $_SESSION['last_decline_notification_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
}
?>
