<script>
$(document).ready(function() {

    // Initialize Bootstrap Datepicker
    $('.datepicker-init').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        orientation: 'bottom auto'
    });

    // Initialize Select2
    $('.select2').select2({
        width: '100%'
    });

    // Auto uppercase company code
    $('#company_code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Form validation for add company
    if ($('#add_company').length > 0) {
        $('#add_company').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                company_code: {
                    validators: {
                        notEmpty: {
                            message: 'Company code is required'
                        },
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]+$/,
                            message: 'Company code can only contain letters, numbers, hyphens and underscores'
                        },
                        stringLength: {
                            min: 3,
                            max: 20,
                            message: 'Company code must be between 3 and 20 characters'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Company name is required'
                        }
                    }
                },
                primary_email: {
                    validators: {
                        notEmpty: {
                            message: 'Primary email is required'
                        },
                        emailAddress: {
                            message: 'Please enter a valid email address'
                        }
                    }
                },
                secondary_email: {
                    validators: {
                        emailAddress: {
                            message: 'Please enter a valid email address'
                        }
                    }
                },
                primary_phone: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9+\-\s()]*$/,
                            message: 'Please enter a valid phone number'
                        }
                    }
                },
                secondary_phone: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9+\-\s()]*$/,
                            message: 'Please enter a valid phone number'
                        }
                    }
                },
                pincode: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9]*$/,
                            message: 'Please enter a valid pincode'
                        }
                    }
                },
                gst_number: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9A-Z]*$/,
                            message: 'Please enter a valid GST number'
                        }
                    }
                },
                pan_number: {
                    validators: {
                        regexp: {
                            regexp: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/,
                            message: 'Please enter a valid PAN number (e.g., AAAAA0000A)'
                        }
                    }
                },
                payment_terms_days: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                max_employees: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: $form.serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        setTimeout(function() {
                            window.location.href = base_url + 'client/companies';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Add Company');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Add Company');
                }
            });
        });
    }

    // Form validation for edit company
    if ($('#edit_company').length > 0) {
        $('#edit_company').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                company_code: {
                    validators: {
                        notEmpty: {
                            message: 'Company code is required'
                        },
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]+$/,
                            message: 'Company code can only contain letters, numbers, hyphens and underscores'
                        },
                        stringLength: {
                            min: 3,
                            max: 20,
                            message: 'Company code must be between 3 and 20 characters'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Company name is required'
                        }
                    }
                },
                primary_email: {
                    validators: {
                        notEmpty: {
                            message: 'Primary email is required'
                        },
                        emailAddress: {
                            message: 'Please enter a valid email address'
                        }
                    }
                },
                secondary_email: {
                    validators: {
                        emailAddress: {
                            message: 'Please enter a valid email address'
                        }
                    }
                },
                primary_phone: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9+\-\s()]*$/,
                            message: 'Please enter a valid phone number'
                        }
                    }
                },
                secondary_phone: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9+\-\s()]*$/,
                            message: 'Please enter a valid phone number'
                        }
                    }
                },
                pincode: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9]*$/,
                            message: 'Please enter a valid pincode'
                        }
                    }
                },
                gst_number: {
                    validators: {
                        regexp: {
                            regexp: /^[0-9A-Z]*$/,
                            message: 'Please enter a valid GST number'
                        }
                    }
                },
                pan_number: {
                    validators: {
                        regexp: {
                            regexp: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/,
                            message: 'Please enter a valid PAN number (e.g., AAAAA0000A)'
                        }
                    }
                },
                payment_terms_days: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                max_employees: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: $form.serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        setTimeout(function() {
                            window.location.href = base_url + 'client/companies';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Update Company');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Update Company');
                }
            });
        });
    }
});
</script>
