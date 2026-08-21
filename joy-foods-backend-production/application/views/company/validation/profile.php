<script>
    $(document).ready(function() {

        // Profile form validation & submit
        $('#profile_form').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                first_name: {
                    validators: {
                        notEmpty: { message: 'First name is required' },
                        stringLength: { max: 100, message: 'Max 100 characters' }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();

            $('#profile_submit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            $.ajax({
                url: base_url + 'company/profile/update',
                type: 'POST',
                data: $('#profile_form').serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#profile_submit_btn').prop('disabled', false).html('Update Profile');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong");
                    $('#profile_submit_btn').prop('disabled', false).html('Update Profile');
                }
            });
        });

        // Password form validation & submit
        $('#password_form').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                current_password: {
                    validators: {
                        notEmpty: { message: 'Current password is required' }
                    }
                },
                new_password: {
                    validators: {
                        notEmpty: { message: 'New password is required' },
                        stringLength: { min: 6, message: 'Minimum 6 characters' }
                    }
                },
                confirm_password: {
                    validators: {
                        notEmpty: { message: 'Confirm password is required' },
                        identical: { field: 'new_password', message: 'Passwords do not match' }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();

            $('#password_submit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Changing...');

            $.ajax({
                url: base_url + 'company/profile/change_password',
                type: 'POST',
                data: $('#password_form').serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);
                        $('#password_form')[0].reset();
                        $('#password_form').data('formValidation').resetForm();
                    } else {
                        toastr["error"]("Error", obj.message);
                    }
                    $('#password_submit_btn').prop('disabled', false).html('Change Password');
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong");
                    $('#password_submit_btn').prop('disabled', false).html('Change Password');
                }
            });
        });
    });
</script>
