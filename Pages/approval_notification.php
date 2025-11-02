<?php
// approval_notification.php - Enhanced popup module for approved requests

function checkApprovedRequests($conn, $userId) {
    $approvedRequests = [];
    
    // Initialize session tracking for popup display count
    if (!isset($_SESSION['popup_display_count'])) {
        $_SESSION['popup_display_count'] = 0;
    }
    
    // Don't show popup if already displayed 3 times
    if ($_SESSION['popup_display_count'] >= 3) {
        return [];
    }
    
    // Check if user has logged in before and get the last notification check time
    if (!isset($_SESSION['last_notification_check'])) {
        $_SESSION['last_notification_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
    }
    
    $lastCheck = $_SESSION['last_notification_check'];
    
    // Get the most recent approval for each request type (limit to latest only)
    $queries = [
        [
            'table' => 'docsreqtbl',
            'select' => 'DocuType as description, refno, DateRequested as request_date',
            'type' => 'Document Request',
            'user_col' => 'UserId',
            'date_col' => 'DateRequested'
        ],
        [
            'table' => 'businesstbl', 
            'select' => 'CONCAT(BusinessName, " - ", RequestType) as description, refno, RequestedDate as request_date',
            'type' => 'Business Request',
            'user_col' => 'UserId',
            'date_col' => 'RequestedDate'
        ],
        [
            'table' => 'scholarship',
            'select' => '"Scholarship Application" as description, ApplicationID as refno, DateApplied as request_date',
            'type' => 'Scholarship Application',
            'user_col' => 'UserID',
            'date_col' => 'DateApplied'
        ],
        [
            'table' => 'unemploymenttbl',
            'select' => 'certificate_type as description, refno, request_date',
            'type' => 'Unemployment Certificate',
            'user_col' => 'user_id',
            'date_col' => 'request_date'
        ],
        [
            'table' => 'guardianshiptbl',
            'select' => 'request_type as description, refno, request_date',
            'type' => 'Guardianship/Solo Parent',
            'user_col' => 'user_id', 
            'date_col' => 'request_date'
        ],
        [
            'table' => 'no_birthcert_tbl',
            'select' => '"No Birth Certificate Request" as description, refno, request_date',
            'type' => 'No Birth Certificate',
            'user_col' => 'user_id',
            'date_col' => 'request_date'
        ],
        [
            'table' => 'complaintbl',
            'select' => 'Complain as description, refno, DateComplained as request_date',
            'type' => 'Complaint',
            'user_col' => 'Userid',
            'date_col' => 'DateComplained'
        ]
    ];
    
    foreach ($queries as $query) {
        // Get only the most recent approved request for each type
        $sql = "SELECT {$query['select']}, '{$query['type']}' as request_type
                FROM {$query['table']} 
                WHERE {$query['user_col']} = ? 
                AND RequestStatus = 'approved' 
                AND {$query['date_col']} >= ?
                ORDER BY {$query['date_col']} DESC 
                LIMIT 1";
                
        $stmt = db_prepare($sql);
        if ($stmt) {
            $stmt->bind_param("is", $userId, $lastCheck);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $approvedRequests[] = [
                    'type' => $row['request_type'],
                    'description' => $row['description'],
                    'refno' => $row['refno'],
                    'request_date' => $row['request_date']
                ];
            }
            $stmt->close();
        }
    }
    
    // Sort by request date (most recent first) and limit to 3 most recent
    usort($approvedRequests, function($a, $b) {
        return strtotime($b['request_date']) - strtotime($a['request_date']);
    });
    
    // Limit to maximum 3 notifications
    $approvedRequests = array_slice($approvedRequests, 0, 3);
    
    // Only update session and increment counter if there are notifications to show
    if (!empty($approvedRequests)) {
        $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
        $_SESSION['popup_display_count']++;
    }
    
    return $approvedRequests;
}

function displayApprovalNotifications($approvedRequests) {
    if (empty($approvedRequests)) {
        return;
    }
    ?>
    <div id="approvalNotification" class="approval-notification">
        <div class="notification-content">
            <div class="notification-header">
                <i class="fas fa-check-circle"></i>
                <span>Recent Approvals</span>
                <button class="notification-close" onclick="closeNotification()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="notification-body">
                <?php foreach ($approvedRequests as $index => $request): ?>
                    <div class="notification-item <?= $index === 0 ? 'latest' : '' ?>">
                        <div class="item-header">
                            <strong><?= htmlspecialchars($request['type']) ?></strong>
                            <?php if ($index === 0): ?>
                                <span class="latest-badge">Latest</span>
                            <?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars($request['description']) ?></p>
                        <div class="item-footer">
                            <small>Ref: <?= htmlspecialchars($request['refno']) ?></small>
                            <small class="approval-date"><?= date('M j, Y g:i A', strtotime($request['request_date'])) ?></small>
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
                    $remaining = 3 - $_SESSION['popup_display_count'];
                    echo $remaining > 0 ? "Will show {$remaining} more times" : "Last notification";
                    ?>
                </small>
            </div>
        </div>
    </div>
    
    <style>
    .approval-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        width: 380px;
        border-left: 5px solid #4CAF50;
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
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .notification-content {
        padding: 25px;
    }
    
    .notification-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        color: #4CAF50;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .notification-header i {
        font-size: 1.4rem;
        animation: pulse 2s infinite;
    }
    
    .notification-close {
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
    
    .notification-close:hover {
        background: #f0f0f0;
        color: #666;
        transform: rotate(90deg);
    }
    
    .notification-item {
        margin-bottom: 16px;
        padding: 15px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
        border: 1px solid #e9ecef;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .notification-item.latest {
        border-left: 4px solid #4CAF50;
        background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
    }
    
    .notification-item:last-child {
        margin-bottom: 0;
    }
    
    .item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .item-header strong {
        color: #2c5f2d;
        font-size: 0.95rem;
    }
    
    .latest-badge {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .notification-item p {
        margin: 8px 0;
        color: #555;
        font-size: 0.9rem;
        line-height: 1.4;
    }
    
    .item-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }
    
    .item-footer small {
        color: #888;
        font-size: 0.8rem;
    }
    
    .approval-date {
        font-weight: 500;
        color: #4CAF50 !important;
    }
    
    .notification-footer {
        margin-top: 20px;
        text-align: center;
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
    }
    
    .btn-view-requests {
        background: linear-gradient(135deg, #4CAF50, #2c5f2d);
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
    
    .btn-view-requests:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
    }
    
    .display-count {
        color: #888;
        font-size: 0.75rem;
        font-style: italic;
    }
    
    @media (max-width: 480px) {
        .approval-notification {
            width: 95%;
            right: 2.5%;
            top: 10px;
            max-height: 80vh;
        }
        
        .notification-content {
            padding: 20px;
        }
    }
    </style>
    
    <script>
    function closeNotification() {
        const notification = document.getElementById('approvalNotification');
        notification.style.animation = 'slideOutRight 0.4s ease-out forwards';
        setTimeout(() => {
            notification.remove();
        }, 400);
    }
    
    // Auto close after 15 seconds (increased time due to more content)
    setTimeout(() => {
        const notification = document.getElementById('approvalNotification');
        if (notification) {
            closeNotification();
        }
    }, 15000);
    
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
    
    // Add real-time checking functionality
    function checkForNewApprovals() {
        fetch('../Process/check_new_approvals.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'check_approvals=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.hasNewApprovals && data.displayCount < 3) {
                // Reload page to show new notifications
                location.reload();
            }
        })
        .catch(error => console.log('Approval check error:', error));
    }
    
    // Check for new approvals every 30 seconds
    setInterval(checkForNewApprovals, 30000);
    </script>
    <?php
}

// Function to reset notification counter (can be called when needed)
function resetNotificationCounter() {
    $_SESSION['popup_display_count'] = 0;
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
}

?>