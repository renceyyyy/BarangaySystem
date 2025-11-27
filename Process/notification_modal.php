<?php
function showNotificationModal($message, $type = 'info', $customTitle = null) {
    $_SESSION['notification_modal'] = [
        'message' => $message,
        'type' => $type,
        'title' => $customTitle
    ];
}

// Display the modal if notification exists
function renderNotificationModal() {
    if (!isset($_SESSION['notification_modal'])) {
        return;
    }
    
    $notification = $_SESSION['notification_modal'];
    $message = htmlspecialchars($notification['message']);
    $type = $notification['type'];
    $title = $notification['title'];
    
    // Set icon and colors based on type
    $icons = [
        'success' => 'fa-check-circle',
        'info' => 'fa-info-circle',
        'warning' => 'fa-exclamation-triangle',
        'error' => 'fa-times-circle'
    ];
    
    $colors = [
        'success' => '#5CB25D',
        'info' => '#5CB25D', 
        'warning' => '#f39c12',
        'error' => '#e74c3c'
    ];
    
    $titles = [
        'success' => 'Success!',
        'info' => 'Information',
        'warning' => 'Warning',
        'error' => 'Error'
    ];
    
    $icon = $icons[$type] ?? $icons['info'];
    $color = $colors[$type] ?? $colors['info'];
    $defaultTitle = $titles[$type] ?? $titles['info'];
    $displayTitle = $title ?? $defaultTitle;
    
    // Clear the notification from session
    unset($_SESSION['notification_modal']);
    
    ?>
    <style>
        .notification-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 99999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        .notification-modal-overlay.active {
            display: flex;
        }
        
        .notification-modal {
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
            overflow: hidden;
        }
        
        .notification-modal-header {
            background: linear-gradient(135deg, <?php echo $color; ?> 0%, <?php echo $color; ?>dd 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .notification-modal-header i {
            font-size: 2rem;
            opacity: 0.9;
        }
        
        .notification-modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .notification-modal-body {
            padding: 2rem;
            color: #2c3e50;
            font-size: 1rem;
            line-height: 1.6;
            text-align: center;
        }
        
        .notification-modal-footer {
            padding: 1rem 2rem 1.5rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .notification-modal-btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .notification-modal-btn-primary {
            background: <?php echo $color; ?>;
            color: white;
        }
        
        .notification-modal-btn-primary:hover {
            background: <?php echo $color; ?>dd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .notification-modal-btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
        }
        
        .notification-modal-btn-secondary:hover {
            background: #bdc3c7;
            transform: translateY(-2px);
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        @media (max-width: 768px) {
            .notification-modal {
                width: 95%;
                max-width: none;
            }
            
            .notification-modal-header {
                padding: 1.25rem 1.5rem;
            }
            
            .notification-modal-header h3 {
                font-size: 1.1rem;
            }
            
            .notification-modal-header i {
                font-size: 1.5rem;
            }
            
            .notification-modal-body {
                padding: 1.5rem;
                font-size: 0.95rem;
            }
            
            .notification-modal-footer {
                padding: 0.75rem 1.5rem 1.25rem;
                flex-direction: column;
            }
            
            .notification-modal-btn {
                width: 100%;
                padding: 0.8rem 1.5rem;
            }
        }
    </style>
    
    <div class="notification-modal-overlay" id="notificationModalOverlay">
        <div class="notification-modal">
            <div class="notification-modal-header">
                <i class="fas <?php echo $icon; ?>"></i>
                <h3><?php echo htmlspecialchars($displayTitle); ?></h3>
            </div>
            <div class="notification-modal-body">
                <?php echo nl2br($message); ?>
            </div>
            <div class="notification-modal-footer">
                <button class="notification-modal-btn notification-modal-btn-primary" onclick="closeNotificationModal()">
                    OK, Got it!
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Show modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('notificationModalOverlay');
            if (modal) {
                setTimeout(function() {
                    modal.classList.add('active');
                }, 100);
            }
        });
        
        // Close modal function
        function closeNotificationModal() {
            const modal = document.getElementById('notificationModalOverlay');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(function() {
                    modal.classList.remove('active');
                    modal.style.display = 'none';
                }, 300);
            }
        }
        
        // Close on overlay click
        document.getElementById('notificationModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificationModal();
            }
        });
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeNotificationModal();
            }
        });
    </script>
    <?php
}
?>