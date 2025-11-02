<?php
// Reusable alerts include. Place <?php include 'alerts.php'; ?> inside the <body> of pages.
?>

<div id="global-alerts" aria-live="polite" aria-atomic="true"></div>

<script>
(function(){
  const container = document.getElementById('global-alerts');

  function formatMessage(msg) {
    if (!msg) return '';
    // Replace newlines with <br>
    return String(msg).replace(/\n/g, '<br>');
  }

  function createAlert(type, message, timeout = 5000) {
    const el = document.createElement('div');
    el.className = 'global-alert ' + (type || 'info');

    const icon = document.createElement('div');
    icon.className = 'alert-icon';
    icon.innerHTML = type === 'success' ? '✔' : type === 'error' ? '✖' : type === 'warning' ? '⚠' : 'ℹ';

    const content = document.createElement('div');
    content.className = 'alert-content';
    content.innerHTML = formatMessage(message);

    const closeBtn = document.createElement('button');
    closeBtn.className = 'alert-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', () => hideAlert(el));

    el.appendChild(icon);
    el.appendChild(content);
    el.appendChild(closeBtn);

    container.appendChild(el);

    if (timeout > 0) {
      setTimeout(() => hideAlert(el), timeout);
    }

    return el;
  }

  function hideAlert(el) {
    if (!el) return;
    el.classList.add('hide');
    setTimeout(() => { try { el.remove(); } catch(e){} }, 220);
  }

  // Expose global method
  window.showAlert = function(type, message, timeout) {
    // Normalize type
    const t = (type || 'info').toLowerCase();
    createAlert(t, message, typeof timeout === 'number' ? timeout : 5000);
  }

  // Parse server-side URL messages (common query params used in project)
  document.addEventListener('DOMContentLoaded', function(){
    try {
      const params = new URLSearchParams(window.location.search);
      if (params.has('success')) {
        const val = params.get('success');
        const map = {
          'rejected': 'Application rejected successfully.',
          'saved': 'Saved successfully.',
          'approved': 'Application approved successfully.'
        };
        const msg = map[val] || (val.charAt(0).toUpperCase() + val.slice(1) + '');
        showAlert('success', msg);
        return;
      }
      if (params.has('error')) {
        const val = params.get('error');
        const map = {
          'database_error': 'A database error occurred. Please try again.',
          'not_found': 'Record not found or already processed.',
          'invalid_data': 'Invalid data provided.'
        };
        const msg = map[val] || val;
        showAlert('error', msg);
      }
    } catch(e) {
      // silent
    }
  });
})();
</script>
