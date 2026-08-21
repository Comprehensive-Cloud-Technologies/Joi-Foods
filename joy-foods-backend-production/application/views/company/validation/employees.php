<script>
    var employeeModal;
    var isEditMode = false;
    var saveAndAddAnother = false;

    $(document).ready(function() {

        // Initialize DataTable
        $('#datatable').DataTable({
            responsive: true,
            order: [
                [0, 'asc']
            ]
        });

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize modal
        employeeModal = new bootstrap.Modal(document.getElementById('employeeModal'));

        // Track which submit button was used (defaults to normal save, e.g. on Enter)
        $('#submit_button').on('click', function() { saveAndAddAnother = false; });
        $('#submit_add_another').on('click', function() { saveAndAddAnother = true; });

        // Initialize Select2 in modal
        $('.select2-modal').each(function() {
            var $this = $(this);
            $this.select2({
                dropdownParent: $('#employeeModal .modal-body'),
                width: '100%'
            });
        });

        // Initialize datepicker inside modal when shown
        $('#employeeModal').on('shown.bs.modal', function() {
            $('#date_of_joining').datepicker('destroy').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                container: '#employeeModal'
            });
        });

        // Form validation
        $('#employee_form').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                employee_code: {
                    validators: {
                        notEmpty: {
                            message: 'Employee code is required'
                        }
                    }
                },
                first_name: {
                    validators: {
                        notEmpty: {
                            message: 'First name is required'
                        },
                        stringLength: {
                            max: 100,
                            message: 'First name must be less than 100 characters'
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'Email is required'
                        },
                        emailAddress: {
                            message: 'Please enter a valid email address'
                        }
                    }
                },
                phone: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9+\-\s()]*$/,
                            message: 'Please enter a valid phone number'
                        }
                    }
                },
                password: {
                    validators: {
                        callback: {
                            message: 'Password is required',
                            callback: function(value, validator, $field) {
                                if (!isEditMode && value === '') {
                                    return false;
                                }
                                return true;
                            }
                        },
                        stringLength: {
                            min: 6,
                            message: 'Password must be at least 6 characters'
                        }
                    }
                },
                confirm_password: {
                    validators: {
                        callback: {
                            message: 'Confirm password is required',
                            callback: function(value, validator, $field) {
                                var password = $('#password').val();
                                if (!isEditMode && value === '') {
                                    return false;
                                }
                                if (password !== '' && value !== password) {
                                    return {
                                        valid: false,
                                        message: 'Passwords do not match'
                                    };
                                }
                                return true;
                            }
                        }
                    }
                },
                policy_id: {
                    validators: {
                        callback: {
                            message: 'Please select a policy',
                            callback: function(value, validator, $field) {
                                console.log(value);
                                var isPremealAccessChecked = $('#premeal_access').is(':checked');
                                var isKotPermissionChecked = $('#kot_permission').is(':checked');

                                if (isPremealAccessChecked || isKotPermissionChecked) {
                                    if (value === '' || value === null) {
                                        return false;
                                    }
                                }

                                return true;
                            }
                        }
                    }
                },
                qsr_access: {

                },
                kot_permission: {

                },
                premeal_access: {

                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            var url = isEditMode ? base_url + 'company/employees/update' : base_url + 'company/employees/store';

            // "Save & Add Another" only applies when adding
            var addAnother = saveAndAddAnother && !isEditMode;

            var $clicked = addAnother ? $('#submit_add_another') : $('#submit_button');
            var originalHtml = $clicked.html();

            $('#submit_button, #submit_add_another').prop('disabled', true);
            $clicked.html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: url,
                type: "POST",
                data: $form.serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);

                        if (addAnother) {
                            // Keep the modal open and reset for the next employee
                            openAddModal();
                            $('#submit_button, #submit_add_another').prop('disabled', false);
                            $clicked.html(originalHtml);
                        } else {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button, #submit_add_another').prop('disabled', false);
                        $clicked.html(originalHtml);
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button, #submit_add_another').prop('disabled', false);
                    $clicked.html(originalHtml);
                }
            });
        });
    });

    // Open Add Modal
    function openAddModal() {
        isEditMode = false;
        saveAndAddAnother = false;
        $('#employee_form')[0].reset();
        $('#employee_id').val('');
        $('#employeeModalLabel').text('Add Employee');
        $('#submit_button').text('Add Employee');
        // Show "Save & Add Another" only when adding
        $('#submit_add_another').prop('disabled', false).html('<i class="uil uil-plus me-1"></i> Save & Add Another').show();
        $('#employee_form').attr('action', base_url + 'company/employees/store');

        // Reset Select2
        $('#department_id').val('').trigger('change');

        // Set default policy
        var defaultPolicy = '';
        <?php foreach ($policies as $policy): ?>
            <?php if ($policy->is_default): ?>
                defaultPolicy = '<?php echo $policy->id; ?>';
            <?php endif; ?>
        <?php endforeach; ?>
        $('#policy_id').val(defaultPolicy).trigger('change');

        // Reset checkboxes
        $('#is_active').prop('checked', true);
        $('#kot_permission').prop('checked', false);
        $('#qsr_access').prop('checked', true);
        $('#premeal_access').prop('checked', false);

        // Show password required indicator
        $('.password-required').show();
        $('.edit-password-hint').hide();

        // Reset form validation
        $('#employee_form').data('formValidation').resetForm();

        // Clear RFID
        $('#rfid_card_number').val('');
        $('#rfid_card_issued_at').val('');
        $('#rfid_status').hide().empty();

        employeeModal.show();
        setTimeout(function() { $('#rfid_card_number').trigger('blur'); }, 200);
    }

    // Edit Employee
    function editEmployee(id) {
        isEditMode = true;
        saveAndAddAnother = false;
        $('#employeeModalLabel').text('Edit Employee');
        $('#submit_button').text('Update Employee');
        // "Save & Add Another" is not applicable while editing
        $('#submit_add_another').hide();
        $('#employee_form').attr('action', base_url + 'company/employees/update');

        // Hide password required indicator for edit mode
        $('.password-required').hide();
        $('.edit-password-hint').show();

        // Clear password fields
        $('#password').val('');
        $('#confirm_password').val('');

        // Reset form validation
        $('#employee_form').data('formValidation').resetForm();

        // Fetch employee data
        $.ajax({
            url: base_url + 'company/employees/get/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var emp = response.data;
                    $('#employee_id').val(emp.id);
                    $('#employee_code').val(emp.employee_code);
                    $('#first_name').val(emp.first_name);
                    $('#last_name').val(emp.last_name);
                    $('#email').val(emp.email);
                    $('#phone').val(emp.phone);
                    $('#designation').val(emp.designation);
                    $('#employment_type').val(emp.employment_type);
                    $('#date_of_joining').val(emp.date_of_joining);
                    $('#gender').val(emp.gender);
                    $('#is_active').prop('checked', emp.is_active == 1);
                    $('#kot_permission').prop('checked', emp.kot_permission == 1);
                    $('#qsr_access').prop('checked', emp.qsr_access == 1);
                    $('#premeal_access').prop('checked', emp.premeal_access == 1);

                    // RFID
                    $('#rfid_card_number').val(emp.rfid_card_number || '');
                    $('#rfid_card_issued_at').val(emp.rfid_card_issued_at || '');
                    $('#rfid_status').hide().empty();
                    if (emp.rfid_card_number) {
                        $('#rfid_status').show().html('<span class="text-success small"><i class="uil uil-check-circle me-1"></i>Card on file. Scan a new card to replace it.</span>');
                    }

                    // Set department
                    $('#department_id').val(emp.department_id).trigger('change');

                    // Set policy
                    $('#policy_id').val(emp.policy_id).trigger('change');

                    employeeModal.show();
                } else {
                    toastr["error"]("Error", response.message);
                }
            },
            error: function() {
                toastr["error"]("Error", "Failed to load employee data");
            }
        });
    }

    // Generate Employee Code
    function generateCode() {
        $.ajax({
            url: base_url + 'company/employees/generate_code',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#employee_code').val(response.code);
                }
            }
        });
    }

    // Delete Employee
    function deleteEmployee(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this employee?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#556ee6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'company/employees/delete',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    success: function(response) {
                        var obj = JSON.parse(response);
                        if (obj.status === 'success') {
                            Swal.fire({
                                title: 'Deleted!',
                                text: obj.message,
                                icon: 'success',
                                confirmButtonColor: '#556ee6'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: obj.message,
                                icon: 'error',
                                confirmButtonColor: '#556ee6'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#556ee6'
                        });
                    }
                });
            }
        });
    }

    // Wallet Adjust Modal
    var creditModal;
    var razorpayModal;
    $(document).ready(function() {
        creditModal = new bootstrap.Modal(document.getElementById('creditModal'));
        if (document.getElementById('razorpayModal')) {
            razorpayModal = new bootstrap.Modal(document.getElementById('razorpayModal'));
        }

        // Toggle action button style
        $('input[name="action_radio"]').on('change', function() {
            var action = $(this).val();
            $('#credit_action').val(action);
            if (action === 'debit') {
                $('#credit_submit_button').removeClass('btn-success').addClass('btn-danger').html('<i class="uil uil-minus-circle me-1"></i> Deduct Money');
            } else {
                $('#credit_submit_button').removeClass('btn-danger').addClass('btn-success').html('<i class="uil uil-wallet me-1"></i> Add Money');
            }
        });
    });

    function openCreditModal(id, name, balance) {
        $('#credit_employee_id').val(id);
        $('#credit_employee_name').text(name);
        var balClass = balance > 0 ? 'text-success' : (balance < 0 ? 'text-danger' : 'text-muted');
        $('#credit_current_balance').html('&#8377;' + parseFloat(balance).toFixed(2)).attr('class', 'fw-medium ' + balClass);
        $('#credit_amount').val('');
        $('#credit_reason').val('');

        // Determine starting action: if both perms, default to credit + radio is shown.
        // If only one perm, use that one (radio is hidden).
        var defaultAction = $('input[name="action_radio_default"]').val() || 'credit';
        var actionRadio = document.getElementById('action_credit');
        if (actionRadio) {
            // Both-perm path: reset radio to credit
            $('#action_credit').prop('checked', true).trigger('change');
            $('#credit_action').val('credit');
            $('#credit_submit_button').prop('disabled', false)
                .removeClass('btn-danger').addClass('btn-success')
                .html('<i class="uil uil-wallet me-1"></i> Add Money');
        } else {
            // Single-perm path: action is locked
            $('#credit_action').val(defaultAction);
            if (defaultAction === 'debit') {
                $('#credit_submit_button').prop('disabled', false)
                    .removeClass('btn-success').addClass('btn-danger')
                    .html('<i class="uil uil-minus me-1"></i> Deduct Money');
            } else {
                $('#credit_submit_button').prop('disabled', false)
                    .removeClass('btn-danger').addClass('btn-success')
                    .html('<i class="uil uil-plus me-1"></i> Add Money');
            }
        }
        creditModal.show();
    }

    $(document).on('submit', '#credit_form', function(e) {
        e.preventDefault();
        var amount = parseFloat($('#credit_amount').val());
        if (!amount || amount <= 0) {
            toastr["error"]("Error", "Please enter a valid amount");
            return;
        }

        var action = $('#credit_action').val();
        var isDebit = action === 'debit';
        var actionWord = isDebit ? 'Deduct' : 'Add';
        var confirmColor = isDebit ? '#f46a6a' : '#34c38f';
        var confirmText = isDebit ? 'Yes, deduct' : 'Yes, add money';
        var confirmHtml = (isDebit ? 'Deduct' : 'Add') + ' <strong>&#8377;' + amount.toFixed(2) + '</strong> ' + (isDebit ? 'from' : 'to') + ' <strong>' + $('#credit_employee_name').text() + '</strong>\'s wallet?';

        Swal.fire({
            title: 'Confirm ' + actionWord,
            html: confirmHtml,
            icon: isDebit ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#556ee6',
            confirmButtonText: confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                var btnHtml = isDebit ? '<i class="uil uil-minus-circle me-1"></i> Deduct Money' : '<i class="uil uil-wallet me-1"></i> Add Money';
                $('#credit_submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $.ajax({
                    url: base_url + 'company/employees/credit_wallet',
                    type: 'POST',
                    data: $('#credit_form').serialize(),
                    success: function(response) {
                        var obj = JSON.parse(response);
                        if (obj.status === 'success') {
                            creditModal.hide();
                            Swal.fire({
                                title: 'Success!',
                                text: obj.message,
                                icon: 'success',
                                confirmButtonColor: '#556ee6'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            toastr["error"]("Error", obj.message);
                            $('#credit_submit_button').prop('disabled', false).html(btnHtml);
                        }
                    },
                    error: function() {
                        toastr["error"]("Error", "Something went wrong. Please try again.");
                        $('#credit_submit_button').prop('disabled', false).html(btnHtml);
                    }
                });
            }
        });
    });

    // Toggle Status
    function toggleStatus(id) {
        $.ajax({
            url: base_url + 'company/employees/toggle_status',
            type: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.status === 'success') {
                    toastr["success"]("Success", obj.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr["error"]("Error", obj.message);
                }
            },
            error: function() {
                toastr["error"]("Error", "Something went wrong. Please try again.");
            }
        });
    }

    // ---------------- RFID HID Reader Handling ----------------
    // HID readers act as keyboards: they type the card number rapidly and
    // usually finish with an Enter keypress. We:
    //  - swallow the Enter so the form doesn't submit on scan
    //  - timestamp the scan and stamp the issue date if empty
    //  - show a green status badge when a card is captured
    (function() {
        var rfidInput = document.getElementById('rfid_card_number');
        var rfidStatus = $('#rfid_status');
        var rfidIssued = $('#rfid_card_issued_at');
        var lastKeyTime = 0;
        var keystrokeBuffer = '';

        if (!rfidInput) return;

        // Visual cue when input is focused — ready to scan
        $(rfidInput).on('focus', function() {
            $(this).addClass('border-primary');
            if (!$(this).val()) {
                rfidStatus.show().html('<span class="text-primary small"><i class="uil uil-spinner-alt me-1"></i>Ready — please scan the RFID card now</span>');
            }
            keystrokeBuffer = '';
        });

        $(rfidInput).on('blur', function() {
            $(this).removeClass('border-primary');
        });

        // Track keystroke speed — HID readers fire keys very fast (<30ms apart)
        $(rfidInput).on('keydown', function(e) {
            var now = Date.now();
            if (now - lastKeyTime < 50) {
                // Likely scanner input
                keystrokeBuffer += (e.key && e.key.length === 1) ? e.key : '';
            } else {
                keystrokeBuffer = (e.key && e.key.length === 1) ? e.key : '';
            }
            lastKeyTime = now;

            // Block Enter from submitting the form on scan completion
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();
                onCardScanned($(rfidInput).val());
            }
        });

        // Also detect "settled" state — if input value didn't change for 300ms after rapid typing
        var settleTimer = null;
        $(rfidInput).on('input', function() {
            clearTimeout(settleTimer);
            var val = $(this).val();
            settleTimer = setTimeout(function() {
                if (val && val.length >= 4) {
                    onCardScanned(val);
                }
            }, 300);
        });

        $('#rfid_clear_btn').on('click', function() {
            $(rfidInput).val('').focus();
            rfidIssued.val('');
            rfidStatus.hide().empty();
        });

        function onCardScanned(value) {
            value = (value || '').trim();
            if (!value) return;

            $(rfidInput).val(value);
            rfidStatus.show().html('<span class="text-success small"><i class="uil uil-check-circle me-1"></i>Card captured: <strong>' + $('<div>').text(value).html() + '</strong></span>');

            // Auto-fill issue date if not already set
            if (!rfidIssued.val()) {
                var d = new Date();
                var iso = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                rfidIssued.val(iso);
            }
        }
    })();

    // ---------------- Razorpay Recharge Flow ----------------
    function openRazorpayModal(id, name, email, phone, balance) {
        if (!razorpayModal) return;
        $('#rzp_employee_id').val(id);
        $('#rzp_employee_name').text(name);
        $('#rzp_employee_email').val(email || '');
        $('#rzp_employee_phone').val(phone || '');
        $('#rzp_current_balance').text(parseFloat(balance || 0).toFixed(2));
        $('#rzp_amount').val('');
        $('#rzp_submit_button').prop('disabled', false).html('<i class="uil uil-bill me-1"></i> Pay & Recharge');
        razorpayModal.show();
    }

    $(document).on('submit', '#razorpay_form', function(e) {
        e.preventDefault();
        var employeeId = $('#rzp_employee_id').val();
        var amount = parseFloat($('#rzp_amount').val());

        if (!amount || amount <= 0) {
            toastr["error"]("Error", "Please enter a valid amount");
            return;
        }
        if (amount > 50000) {
            toastr["error"]("Error", "Maximum amount is ₹50,000");
            return;
        }

        var btnHtml = $('#rzp_submit_button').html();
        $('#rzp_submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Initiating...');

        // Step 1 — initiate Razorpay order on the server
        $.ajax({
            url: base_url + 'company/employees/razorpay_recharge_initiate',
            type: 'POST',
            data: { employee_id: employeeId, amount: amount },
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.status !== 'success') {
                    toastr["error"]("Error", obj.message);
                    $('#rzp_submit_button').prop('disabled', false).html(btnHtml);
                    return;
                }

                var data = obj.data;

                // Step 2 — open Razorpay checkout
                var options = {
                    key:        data.razorpay_key,
                    amount:     data.amount * 100, // paise
                    currency:   data.currency || 'INR',
                    name:       'Wallet Recharge',
                    description: 'Recharge for ' + data.employee_name,
                    order_id:   data.razorpay_order_id,
                    prefill: {
                        name:    data.employee_name,
                        email:   data.employee_email || '',
                        contact: data.employee_phone || ''
                    },
                    theme: { color: '#556ee6' },
                    handler: function(rzp_response) {
                        // Step 3 — verify + credit wallet on the server
                        $('#rzp_submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verifying...');
                        $.ajax({
                            url: base_url + 'company/employees/razorpay_recharge_complete',
                            type: 'POST',
                            data: {
                                employee_id:         employeeId,
                                amount:              data.amount,
                                razorpay_order_id:   rzp_response.razorpay_order_id,
                                razorpay_payment_id: rzp_response.razorpay_payment_id,
                                razorpay_signature:  rzp_response.razorpay_signature
                            },
                            success: function(verify_response) {
                                var v = JSON.parse(verify_response);
                                if (v.status === 'success') {
                                    razorpayModal.hide();
                                    Swal.fire({
                                        title: 'Recharge Successful!',
                                        text: v.message,
                                        icon: 'success',
                                        confirmButtonColor: '#556ee6'
                                    }).then(() => { location.reload(); });
                                } else {
                                    toastr["error"]("Error", v.message);
                                    $('#rzp_submit_button').prop('disabled', false).html(btnHtml);
                                }
                            },
                            error: function() {
                                toastr["error"]("Error", "Payment captured but wallet credit failed. Please contact support.");
                                $('#rzp_submit_button').prop('disabled', false).html(btnHtml);
                            }
                        });
                    },
                    modal: {
                        ondismiss: function() {
                            $('#rzp_submit_button').prop('disabled', false).html(btnHtml);
                            toastr["info"]("Cancelled", "Payment was cancelled");
                        }
                    }
                };

                var rzp = new Razorpay(options);
                rzp.on('payment.failed', function(resp) {
                    toastr["error"]("Payment Failed", resp.error.description || 'Razorpay payment failed');
                    $('#rzp_submit_button').prop('disabled', false).html(btnHtml);
                });
                rzp.open();
            },
            error: function() {
                toastr["error"]("Error", "Failed to initiate payment");
                $('#rzp_submit_button').prop('disabled', false).html(btnHtml);
            }
        });
    });
</script>