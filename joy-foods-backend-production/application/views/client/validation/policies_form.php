<script>
$(document).ready(function() {

    // Toggle contribution card based on policy type
    $('#policy_type').on('change', function() {
        if ($(this).val() === 'PARTIAL') {
            $('#contribution_card').slideDown();
        } else {
            $('#contribution_card').slideUp();
        }
    });

    // Trigger on page load for edit form
    if ($('#policy_type').val() === 'PARTIAL') {
        $('#contribution_card').show();
    }

    // Form validation for add policy
    if ($('#add_policy').length > 0) {
        $('#add_policy').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                policy_code: {
                    validators: {
                        notEmpty: {
                            message: 'Policy code is required'
                        },
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]+$/,
                            message: 'Policy code can only contain letters, numbers, hyphens and underscores'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Policy name is required'
                        }
                    }
                },
                policy_type: {
                    validators: {
                        notEmpty: {
                            message: 'Policy type is required'
                        }
                    }
                },
                company_contribution_value: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                employee_contribution_value: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                max_meal_value: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                daily_meal_limit: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                weekly_meal_limit: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                monthly_meal_limit: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                monthly_budget_limit: {
                    validators: {
                        numeric: {
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
                            window.location.href = base_url + 'client/policies';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Add Policy');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Add Policy');
                }
            });
        });
    }

    // Form validation for edit policy
    if ($('#edit_policy').length > 0) {
        $('#edit_policy').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                policy_code: {
                    validators: {
                        notEmpty: {
                            message: 'Policy code is required'
                        },
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]+$/,
                            message: 'Policy code can only contain letters, numbers, hyphens and underscores'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Policy name is required'
                        }
                    }
                },
                policy_type: {
                    validators: {
                        notEmpty: {
                            message: 'Policy type is required'
                        }
                    }
                },
                company_contribution_value: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                employee_contribution_value: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                max_meal_value: {
                    validators: {
                        numeric: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                daily_meal_limit: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                weekly_meal_limit: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                monthly_meal_limit: {
                    validators: {
                        integer: {
                            message: 'Please enter a valid number'
                        }
                    }
                },
                monthly_budget_limit: {
                    validators: {
                        numeric: {
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
                            window.location.href = base_url + 'client/policies';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Update Policy');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Update Policy');
                }
            });
        });
    }
});
</script>
