<script>
$(document).ready(function() {

    // Initialize Select2
    $('.select2').select2({
        width: '100%'
    });

    // Auto uppercase department code
    $('#code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Form validation for add department
    if ($('#add_department').length > 0) {
        $('#add_department').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Department name is required'
                        },
                        stringLength: {
                            max: 100,
                            message: 'Department name must be less than 100 characters'
                        }
                    }
                },
                code: {
                    validators: {
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]*$/,
                            message: 'Code can only contain letters, numbers, hyphens and underscores'
                        },
                        stringLength: {
                            max: 20,
                            message: 'Code must be less than 20 characters'
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
                            window.location.href = document.referrer;
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Add Department');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Add Department');
                }
            });
        });
    }

    // Form validation for edit department
    if ($('#edit_department').length > 0) {
        $('#edit_department').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Department name is required'
                        },
                        stringLength: {
                            max: 100,
                            message: 'Department name must be less than 100 characters'
                        }
                    }
                },
                code: {
                    validators: {
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]*$/,
                            message: 'Code can only contain letters, numbers, hyphens and underscores'
                        },
                        stringLength: {
                            max: 20,
                            message: 'Code must be less than 20 characters'
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
                            window.location.href = document.referrer;
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Update Department');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Update Department');
                }
            });
        });
    }
});
</script>
