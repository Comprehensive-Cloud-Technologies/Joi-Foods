<script>
$(document).ready(function() {

    // Form validation for add client
    if ($('#add_client').length > 0) {
        var addClientSubmitting = false;
        $('#add_client').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                client_code: {
                    validators: {
                        notEmpty: {
                            message: 'Client code is required'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Client name is required'
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
                alternate_phone: {
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
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var btn = document.getElementById('submit_button');
            if (!btn || btn.disabled) return false;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

            var $form = $(e.target);
            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: $form.serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        setTimeout(function() {
                            window.location.href = base_url + 'admin_root/clients';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        btn.disabled = false;
                        btn.innerHTML = 'Add Client';
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    btn.disabled = false;
                    btn.innerHTML = 'Add Client';
                }
            });
            return false;
        });
    }

    // Form validation for edit client
    if ($('#edit_client').length > 0) {
        $('#edit_client').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                client_code: {
                    validators: {
                        notEmpty: {
                            message: 'Client code is required'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Client name is required'
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
                alternate_phone: {
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
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var btn = document.getElementById('submit_button');
            if (!btn || btn.disabled) return false;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

            var $form = $(e.target);
            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: $form.serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        setTimeout(function() {
                            window.location.href = base_url + 'admin_root/clients';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        btn.disabled = false;
                        btn.innerHTML = 'Update Client';
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    btn.disabled = false;
                    btn.innerHTML = 'Update Client';
                }
            });
            return false;
        });
    }
});
</script>
