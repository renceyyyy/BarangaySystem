<?php
// terms_conditions.php - Terms and Conditions Module

function displayTermsAndConditions($formId = 'mainForm') {
    ob_start();
    ?>
    <div class="terms-section">
        <h3>Terms and Conditions</h3>
        <div class="terms-content">
            <div class="terms-scrollable">
                <h4>1. Acceptance of Terms</h4>
                <p>By submitting this application, you agree to be bound by these terms and conditions. The Barangay Sampaguita reserves the right to modify these terms at any time.</p>

                <h4>2. Eligibility Requirements</h4>
                <p>2.1. Applicants must be at least 18 years of age.<br>
                2.2. Applicants must be residents of Barangay Sampaguita.<br>
                2.3. All information provided must be true and accurate.</p>

                <h4>3. Document Requirements</h4>
                <p>3.1. All submitted documents must be clear and legible.<br>
                3.2. Documents must be valid and not expired.<br>
                3.3. Falsified documents will result in immediate rejection and possible legal action.</p>

                <h4>4. Processing Time</h4>
                <p>4.1. Regular processing time is 3-5 working days.<br>
                4.2. Incomplete applications will not be processed.<br>
                4.3. The Barangay reserves the right to request additional documents.</p>

                <h4>5. Data Privacy</h4>
                <p>5.1. All personal information will be protected under the Data Privacy Act.<br>
                5.2. Information will only be used for the intended purpose.<br>
                5.3. You have the right to access and correct your personal data.</p>

                <h4>6. Approval and Rejection</h4>
                <p>6.1. Approval is subject to verification and validation.<br>
                6.2. The Barangay's decision is final and conclusive.<br>
                6.3. Reasons for rejection will be communicated to the applicant.</p>

                <h4>7. Fees and Charges</h4>
                <p>7.1. Some services may require processing fees.<br>
                7.2. Fees are non-refundable once processing has begun.<br>
                7.3. Official receipts will be provided for all payments.</p>

                <h4>8. Prohibited Activities</h4>
                <p>8.1. Providing false information.<br>
                8.2. Submitting forged documents.<br>
                8.3. Attempting to bribe barangay officials.</p>

                <h4>9. Liability</h4>
                <p>The Barangay Sampaguita shall not be liable for any loss or damage arising from the use of its services, except where such liability cannot be excluded by law.</p>

                <h4>10. Governing Law</h4>
                <p>These terms and conditions shall be governed by and construed in accordance with the laws of the Republic of the Philippines.</p>

                <div class="terms-footer">
                    <p><strong>Contact Information:</strong><br>
                    Barangay Sampaguita Office<br>
                    Email: barangay@sampaguita.gov.ph<br>
                    Phone: (02) 1234-5678</p>
                </div>
            </div>
        </div>

        <div class="terms-agreement">
            <div class="checkbox-group">
                <input type="checkbox" id="agreeTerms" name="agreeTerms" value="1" required>
                <label for="agreeTerms">
                    <strong>I have read, understood, and agree to the Terms and Conditions stated above.</strong>
                    <span class="required">*</span>
                </label>
            </div>
            <div class="terms-validation" id="termsValidation" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span>You must agree to the terms and conditions to proceed.</span>
            </div>
        </div>
    </div>

    <style>
        .terms-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8faf9;
            border-radius: 12px;
            border: 1px solid #e8ede8;
        }

        .terms-section h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            border-bottom: 2px solid #5CB25D;
            padding-bottom: 0.5rem;
        }

        .terms-content {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #e8ede8;
        }

        .terms-scrollable {
            max-height: 300px;
            overflow-y: auto;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 6px;
        }

        .terms-scrollable h4 {
            color: #5CB25D;
            margin: 1.5rem 0 0.5rem 0;
            font-size: 1rem;
        }

        .terms-scrollable h4:first-child {
            margin-top: 0;
        }

        .terms-scrollable p {
            margin-bottom: 1rem;
            line-height: 1.5;
            color: #555;
        }

        .terms-footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e8ede8;
            background: #e8f5e9;
            padding: 1rem;
            border-radius: 6px;
        }

        .terms-footer p {
            margin: 0;
            color: #2e7d32;
        }

        .terms-agreement {
            margin-top: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e8ede8;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .checkbox-group input[type="checkbox"] {
            margin-top: 0.2rem;
            transform: scale(1.2);
        }

        .checkbox-group label {
            font-weight: normal;
            cursor: pointer;
            line-height: 1.4;
        }

        .terms-validation {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #d32f2f;
            background: #ffebee;
            padding: 0.75rem;
            border-radius: 6px;
            margin-top: 0.75rem;
            border-left: 4px solid #d32f2f;
        }

        .terms-validation i {
            flex-shrink: 0;
        }

        /* Scrollbar styling */
        .terms-scrollable::-webkit-scrollbar {
            width: 8px;
        }

        .terms-scrollable::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .terms-scrollable::-webkit-scrollbar-thumb {
            background: #5CB25D;
            border-radius: 4px;
        }

        .terms-scrollable::-webkit-scrollbar-thumb:hover {
            background: #4A9A47;
        }

        @media (max-width: 768px) {
            .terms-section {
                padding: 1rem;
                margin: 1.5rem 0;
            }

            .terms-scrollable {
                max-height: 250px;
                padding: 0.75rem;
            }

            .checkbox-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .checkbox-group input[type="checkbox"] {
                align-self: flex-start;
            }
        }
    </style>

    <script>
        function validateTermsAndConditions() {
            const agreeCheckbox = document.getElementById('agreeTerms');
            const validationMessage = document.getElementById('termsValidation');
            
            if (!agreeCheckbox.checked) {
                validationMessage.style.display = 'flex';
                agreeCheckbox.focus();
                return false;
            }
            
            validationMessage.style.display = 'none';
            return true;
        }

        function setupTermsValidation(formId) {
            const form = document.getElementById(formId);
            const agreeCheckbox = document.getElementById('agreeTerms');
            const validationMessage = document.getElementById('termsValidation');
            
            if (form && agreeCheckbox) {
                form.addEventListener('submit', function(e) {
                    if (!agreeCheckbox.checked) {
                        e.preventDefault();
                        validationMessage.style.display = 'flex';
                        agreeCheckbox.focus();
                        
                        // Scroll to terms section
                        document.querySelector('.terms-section').scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                });
                
                // Hide validation message when user checks the box
                agreeCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        validationMessage.style.display = 'none';
                    }
                });
            }
        }

        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            setupTermsValidation('<?php echo $formId; ?>');
        });
    </script>
    <?php
    return ob_get_clean();
}

function validateTermsAgreement() {
    if (!isset($_POST['agreeTerms']) || $_POST['agreeTerms'] !== '1') {
        return "You must agree to the terms and conditions.";
    }
    return true;
}
?>