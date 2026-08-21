<script>
$(document).ready(function() {

    if ($('#change_password_form').length > 0) {
        var changePasswordSubmitting = false;
        $('#change_password_form').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                current_password: {
                    validators: {
                        notEmpty: {
                            message: 'Current password is required'
                        }
                    }
                },
                new_password: {
                    validators: {
                        notEmpty: {
                            message: 'New password is required'
                        },
                        stringLength: {
                            min: 6,
                            message: 'Password must be at least 6 characters'
                        },
                        different: {
                            field: 'current_password',
                            message: 'New password must be different from the current password'
                        }
                    }
                },
                confirm_password: {
                    validators: {
                        notEmpty: {
                            message: 'Please confirm your new password'
                        },
                        identical: {
                            field: 'new_password',
                            message: 'Passwords do not match'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            if (changePasswordSubmitting) return false;
            changePasswordSubmitting = true;

            var $form = $(e.target);
            $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: (function(f, fd) { f.find('select.select2').each(function() { fd.set(this.name, $(this).val() || ''); }); return fd; })($form, new FormData($form[0])),
                contentType: false,
                cache: false,
                processData: false,
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);

                        // Reset form and validation state
                        $form[0].reset();
                        if ($form.data('formValidation')) {
                            $form.data('formValidation').resetForm();
                        }
                    } else {
                        toastr["error"]("Error", obj.message);
                    }
                    changePasswordSubmitting = false;
                    $('#submit_button').prop('disabled', false).html('<i class="uil uil-lock-alt me-1"></i> Update Password');
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    changePasswordSubmitting = false;
                    $('#submit_button').prop('disabled', false).html('<i class="uil uil-lock-alt me-1"></i> Update Password');
                }
            });
            return false;
        });
    }
});
</script>
