document.addEventListener('DOMContentLoaded', function () {
    // Popup handling
    const popupOverlay = document.getElementById('popupOverlay');
    const openPopup = document.getElementById('openPopup');
    const closePopup = document.getElementById('closePopup');

    const businessPopupOverlay = document.getElementById('businessPopupOverlay');
    const openBusinessPopup = document.getElementById('openBusinessPopup');
    const closeBusinessPopup = document.getElementById('closeBusinessPopup');

    const complaintPopupOverlay = document.getElementById('complaintPopupOverlay');
    const openComplaintPopup = document.getElementById('openComplaintPopup');
    const closeComplaintPopup = document.getElementById('closeComplaintPopup');

    const scholarPopupOverlay = document.getElementById('scholarPopupOverlay');
    const openScholarPopup = document.getElementById('openScholarPopup');
    const closeScholarPopup = document.getElementById('closeScholarPopup');

    // Document request popup
    if (openPopup && popupOverlay && closePopup) {
        openPopup.addEventListener('click', function () {
            popupOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });

        closePopup.addEventListener('click', function () {
            popupOverlay.classList.remove('show');
            document.body.style.overflow = 'auto';
        });
    }

    // Business permit popup
    if (openBusinessPopup && businessPopupOverlay && closeBusinessPopup) {
        openBusinessPopup.addEventListener('click', function () {
            businessPopupOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });

        closeBusinessPopup.addEventListener('click', function () {
            businessPopupOverlay.classList.remove('show');
            document.body.style.overflow = 'auto';
        });

        // Show/hide closure date based on selection
        const businessType = document.getElementById('businessType');
        const closureDateGroup = document.getElementById('closureDateGroup');

        if (businessType && closureDateGroup) {
            businessType.addEventListener('change', function () {
                if (this.value === 'closure') {
                    closureDateGroup.style.display = 'block';
                } else {
                    closureDateGroup.style.display = 'none';
                }
            });
        }
    }

    // Complaint popup
    if (openComplaintPopup && complaintPopupOverlay && closeComplaintPopup) {
        openComplaintPopup.addEventListener('click', function () {
            complaintPopupOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });

        closeComplaintPopup.addEventListener('click', function () {
            complaintPopupOverlay.classList.remove('show');
            document.body.style.overflow = 'auto';
        });

        // File upload display
        const evidenceImage = document.getElementById('evidence_image');
        const fileNameDisplay = document.getElementById('file-name-display');

        if (evidenceImage && fileNameDisplay) {
            evidenceImage.addEventListener('change', function () {
                if (this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                } else {
                    fileNameDisplay.textContent = 'No file selected';
                }
            });
        }
    }

    // Scholar application popup
    if (openScholarPopup && scholarPopupOverlay && closeScholarPopup) {
        openScholarPopup.addEventListener('click', function () {
            scholarPopupOverlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        });

        closeScholarPopup.addEventListener('click', function () {
            scholarPopupOverlay.classList.remove('show');
            document.body.style.overflow = 'auto';
        });
    }

    // Document type selector functionality
    const documentTypeSelector = document.querySelector('.document-type-selector');
    const selectBox = document.querySelector('.document-type-selector .select-box');
    const docTypeMenu = document.getElementById('docTypeMenu');
    const arrowIcon = document.querySelector('.document-type-selector .arrow i');
    const docCheckboxes = document.querySelectorAll('.options-container input[type="checkbox"]');
    const selectedDocumentsText = document.getElementById('selectedDocumentsText');

    // Toggle dropdown function
    function toggleDropdown() {
        if (docTypeMenu && arrowIcon) {
            docTypeMenu.classList.toggle('dropdown-open');
            selectBox.classList.toggle('active');
            arrowIcon.classList.toggle('fa-chevron-up');
            arrowIcon.classList.toggle('fa-chevron-down');
        }
    }

    // Make toggleDropdown globally accessible
    window.toggleDropdown = toggleDropdown;

    if (selectBox && docTypeMenu && arrowIcon) {
        // Toggle dropdown when clicking the selector
        selectBox.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleDropdown();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!documentTypeSelector.contains(e.target)) {
                docTypeMenu.classList.remove('dropdown-open');
                selectBox.classList.remove('active');
                arrowIcon.classList.remove('fa-chevron-up');
                arrowIcon.classList.add('fa-chevron-down');
            }
        });

        // Prevent dropdown from closing when clicking inside it
        docTypeMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // Update selected documents text
    if (docCheckboxes && selectedDocumentsText) {
        docCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const selected = Array.from(docCheckboxes)
                    .filter(c => c.checked)
                    .map(c => c.nextElementSibling.textContent);

                selectedDocumentsText.textContent = selected.length > 0
                    ? selected.join(', ')
                    : 'Select Document Type(s)';
            });
        });
    }

    // Close popup when clicking outside of it
    const popups = document.querySelectorAll('.popup-overlay');
    popups.forEach(popup => {
        popup.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Scholar application multi-step form
    function nextStep() {
        const currentStep = document.querySelector('.form-step.active');
        const nextStep = currentStep.nextElementSibling;

        if (nextStep && validateStep(currentStep.dataset.step)) {
            currentStep.classList.remove('active');
            nextStep.classList.add('active');

            // Update progress steps
            updateProgressSteps(nextStep.dataset.step);

            // Update review sections
            updateReviewSections();
        }
    }

    function prevStep() {
        const currentStep = document.querySelector('.form-step.active');
        const prevStep = currentStep.previousElementSibling;

        if (prevStep) {
            currentStep.classList.remove('active');
            prevStep.classList.add('active');

            // Update progress steps
            updateProgressSteps(prevStep.dataset.step);
        }
    }

    function updateProgressSteps(step) {
        const steps = document.querySelectorAll('.step');
        steps.forEach(s => {
            if (parseInt(s.dataset.step) <= parseInt(step)) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    }

    function validateStep(step) {
        let isValid = true;
        const currentStep = document.querySelector(`.form-step[data-step="${step}"]`);
        const inputs = currentStep.querySelectorAll('input[required], textarea[required], select[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }

            // Special validation for email
            if (input.type === 'email' && input.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value.trim())) {
                    input.classList.add('error');
                    isValid = false;
                }
            }

            // Special validation for reason textarea (minimum 50 words)
            if (input.name === 'reason' && input.value.trim()) {
                const wordCount = input.value.trim().split(/\s+/).length;
                if (wordCount < 50) {
                    input.classList.add('error');
                    isValid = false;
                    alert('Please provide at least 50 words for your reason.');
                }
            }
        });

        // Validate file uploads in step 2
        if (step === '2') {
            const fileInputs = currentStep.querySelectorAll('input[type="file"][required]');
            fileInputs.forEach(input => {
                if (!input.files || input.files.length === 0) {
                    const uploadItem = input.closest('.upload-item');
                    if (uploadItem) {
                        uploadItem.classList.add('error');
                        isValid = false;
                    }
                } else {
                    const uploadItem = input.closest('.upload-item');
                    if (uploadItem) {
                        uploadItem.classList.remove('error');
                    }
                }
            });
        }

        return isValid;
    }

    function updateReviewSections() {
        // Personal info
        const personalInfo = {
            'Name': `${document.querySelector('input[name="firstname"]').value} ${document.querySelector('input[name="lastname"]').value}`,
            'Email': document.querySelector('input[name="email"]').value,
            'Contact': document.querySelector('input[name="contact_no"]').value,
            'Address': document.querySelector('input[name="address"]').value
        };

        let personalHTML = '';
        for (const [key, value] of Object.entries(personalInfo)) {
            personalHTML += `<p><strong>${key}:</strong> ${value}</p>`;
        }
        document.getElementById('review-personal').innerHTML = personalHTML;

        // Reason
        document.getElementById('review-reason').innerHTML = `<p>${document.querySelector('textarea[name="reason"]').value}</p>`;

        // Documents
        const fileInputs = document.querySelectorAll('input[type="file"]');
        let documentsHTML = '';
        fileInputs.forEach(input => {
            if (input.files && input.files.length > 0) {
                documentsHTML += `<p><strong>${input.name.replace('_', ' ')}:</strong> ${input.files[0].name}</p>`;
            }
        });
        document.getElementById('review-documents').innerHTML = documentsHTML;
    }

    function handleFileUpload(input) {
        const previewElement = document.querySelector(`.file-preview[data-for="${input.name}"]`);
        if (previewElement) {
            if (input.files && input.files.length > 0) {
                previewElement.textContent = input.files[0].name;
                previewElement.classList.add('success');

                // Remove error class from parent upload item
                const uploadItem = input.closest('.upload-item');
                if (uploadItem) {
                    uploadItem.classList.remove('error');
                }
            } else {
                previewElement.textContent = '';
                previewElement.classList.remove('success');
            }
        }
    }

    // Global functions for scholar form
    window.nextStep = nextStep;
    window.prevStep = prevStep;
    window.handleFileUpload = handleFileUpload;

    // Check for success/error messages (only if the script element exists)
    const phpVarsScript = document.querySelector('script[data-php-vars]');
    if (phpVarsScript) {
        const phpVars = JSON.parse(phpVarsScript.textContent);

        if (phpVars.successMessage) {
            const notification = document.createElement('div');
            notification.className = 'popup-notification success';
            notification.textContent = phpVars.successMessage;

            // Insert at the beginning of the body
            document.body.insertBefore(notification, document.body.firstChild);

            // Remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        if (phpVars.errorMessage) {
            const notification = document.createElement('div');
            notification.className = 'popup-notification error';
            notification.textContent = phpVars.errorMessage;

            // Insert at the beginning of the body
            document.body.insertBefore(notification, document.body.firstChild);
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // If there's a reference number, open the appropriate popup
        if (phpVars.refNo) {
            // Determine which popup to open based on the current URL or other logic
            // For simplicity, we'll open the document request popup
            if (popupOverlay) {
                popupOverlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }
    }
});