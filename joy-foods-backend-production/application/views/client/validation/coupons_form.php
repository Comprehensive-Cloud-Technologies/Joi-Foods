<script>
    $(document).ready(function() {
        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        // Initialize select2
        $('.select2').select2();

        // Toggle max discount field based on discount type
        $('#discount_type').on('change', function() {
            if ($(this).val() === 'FIXED') {
                $('#max_discount_div').hide();
            } else {
                $('#max_discount_div').show();
            }
        });
    });

    $(function() {
        $('#coupon_form').formValidation({
            message: 'This value is not valid',
            icon: {
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                code: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter coupon code'
                        },
                        stringLength: {
                            min: 3,
                            max: 50,
                            message: 'Coupon code must be between 3 and 50 characters'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter coupon name'
                        }
                    }
                },
                discount_type: {
                    validators: {
                        notEmpty: {
                            message: 'Please select discount type'
                        }
                    }
                },
                discount_value: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter discount value'
                        },
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                valid_from: {
                    validators: {
                        notEmpty: {
                            message: 'Please select start date'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            // Prevent form submission
            e.preventDefault();

            // Get the form instance
            var $form = $(e.target);

            // Disable submit button
            $('#submit_button').prop('disabled', true).text('Please wait...');

            // Use Ajax to submit form data
            $.ajax({
                url: $form.attr('action'),
                type: "POST",
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.href = base_url + 'client/coupons';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error'
                        });
                        $('#submit_button').prop('disabled', false).text('Submit');
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error'
                    });
                    $('#submit_button').prop('disabled', false).text('Submit');
                }
            });
        });
    });
</script>
