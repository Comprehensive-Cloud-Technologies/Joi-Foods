<script>
$(document).ready(function() {

    // Form validation for add category
    if ($('#add_category').length > 0) {

        // Track which submit button triggered the form
        var addAnother = false;
        $('#submit_button').on('click', function() { addAnother = false; });
        $('#submit_add_another').on('click', function() { addAnother = true; });

        // Reset the form so the user can immediately enter another category
        function resetAddCategoryForm($form) {
            $form[0].reset();

            // Clear formValidation icons/messages
            if ($form.data('formValidation')) {
                $form.data('formValidation').resetForm();
            }

            // Focus category name for quick entry
            $('#name').focus();
            $('html, body').animate({ scrollTop: 0 }, 300);
        }

        $('#add_category').formValidation({
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
                            message: 'Category name is required'
                        }
                    }
                },
                display_order: {
                    validators: {
                        integer: {
                            message: 'Display order must be a number'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();
            var $form = $(e.target);
            var $clicked = addAnother ? $('#submit_add_another') : $('#submit_button');
            var originalHtml = $clicked.html();

            $('#submit_button, #submit_add_another').prop('disabled', true);
            $clicked.html('<i class="fa fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: $form.serialize(),
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 200) {
                        toastr["success"]("Success", obj.message);

                        if (addAnother) {
                            // Stay on the page and reset for the next category
                            resetAddCategoryForm($form);
                            $('#submit_button, #submit_add_another').prop('disabled', false);
                            $clicked.html(originalHtml);
                        } else {
                            setTimeout(function() {
                                window.location.href = base_url + 'client/categories';
                            }, 2000);
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
    }

    // Form validation for edit category
    if ($('#edit_category').length > 0) {
        $('#edit_category').formValidation({
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
                            message: 'Category name is required'
                        }
                    }
                },
                display_order: {
                    validators: {
                        integer: {
                            message: 'Display order must be a number'
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
                            window.location.href = base_url + 'client/categories';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-1"></i> Update Category');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-1"></i> Update Category');
                }
            });
        });
    }
});
</script>
