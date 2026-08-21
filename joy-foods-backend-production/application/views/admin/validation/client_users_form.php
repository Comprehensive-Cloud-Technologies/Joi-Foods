<script>
$(document).ready(function() {

    // Initialize Select2
    if ($('.select2').length > 0) {
        $('.select2').select2();
    }

    // Form validation for add client user
    // Guard: skip if formValidation already initialized on this form (double jQuery load causes double ready())
    if ($('#add_client_user').length > 0 && !$('#add_client_user').data('formValidation')) {
        $('#add_client_user').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                client_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a client'
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
                first_name: {
                    validators: {
                        notEmpty: {
                            message: 'First name is required'
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
                        notEmpty: {
                            message: 'Password is required'
                        },
                        stringLength: {
                            min: 6,
                            message: 'Password must be at least 6 characters'
                        }
                    }
                },
                confirm_password: {
                    validators: {
                        notEmpty: {
                            message: 'Please confirm your password'
                        },
                        identical: {
                            field: 'password',
                            message: 'Passwords do not match'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            // window flag is shared across all jQuery instances/closures
            if (window._addClientUserSubmitting) return false;
            window._addClientUserSubmitting = true;

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
                            window.location.href = base_url + 'admin_root/clients/users';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        window._addClientUserSubmitting = false;
                        $('#submit_button').prop('disabled', false).html('Add User');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    window._addClientUserSubmitting = false;
                    $('#submit_button').prop('disabled', false).html('Add User');
                }
            });
        });
    }

    // Form validation for edit client user
    if ($('#edit_client_user').length > 0 && !$('#edit_client_user').data('formValidation')) {
        $('#edit_client_user').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                client_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a client'
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
                first_name: {
                    validators: {
                        notEmpty: {
                            message: 'First name is required'
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
                        stringLength: {
                            min: 6,
                            message: 'Password must be at least 6 characters'
                        }
                    }
                },
                confirm_password: {
                    validators: {
                        identical: {
                            field: 'password',
                            message: 'Passwords do not match'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            if (window._editClientUserSubmitting) return false;
            window._editClientUserSubmitting = true;

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
                            window.location.href = base_url + 'admin_root/clients/users';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        window._editClientUserSubmitting = false;
                        $('#submit_button').prop('disabled', false).html('Update User');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    window._editClientUserSubmitting = false;
                    $('#submit_button').prop('disabled', false).html('Update User');
                }
            });
        });
    }
});
</script>
