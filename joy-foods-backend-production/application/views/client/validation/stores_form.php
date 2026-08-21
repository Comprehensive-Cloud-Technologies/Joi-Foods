<script>
// Toggle meal timings card based on store type
function toggleMealTimings() {
    var storeType = $('#store_type').val();
    if (storeType === 'PREMEAL') {
        $('#meal_timings_card').show();
    } else {
        $('#meal_timings_card').hide();
    }
}

$(document).ready(function() {

    // Initialize Select2
    $('.select2').select2({
        width: '100%'
    });

    // Toggle meal timings on store type change
    $('#store_type').on('change', function() {
        toggleMealTimings();
    });

    // Initial toggle on page load (for edit page)
    toggleMealTimings();

    // Auto uppercase store code
    $('#store_code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Form validation for add store
    if ($('#add_store').length > 0) {
        $('#add_store').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                company_id: {
                    validators: {
                        notEmpty: {
                            message: 'Company is required'
                        }
                    }
                },
                store_code: {
                    validators: {
                        notEmpty: {
                            message: 'Store code is required'
                        },
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]+$/,
                            message: 'Store code can only contain letters, numbers, hyphens and underscores'
                        },
                        stringLength: {
                            min: 3,
                            max: 20,
                            message: 'Store code must be between 3 and 20 characters'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Store name is required'
                        }
                    }
                },
                store_type: {
                    validators: {
                        notEmpty: {
                            message: 'Store type is required'
                        }
                    }
                },
                primary_email: {
                    validators: {
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
                contact_person_phone: {
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
                latitude: {
                    validators: {
                        regexp: {
                            regexp: /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/,
                            message: 'Please enter a valid latitude (-90 to 90)'
                        }
                    }
                },
                longitude: {
                    validators: {
                        regexp: {
                            regexp: /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/,
                            message: 'Please enter a valid longitude (-180 to 180)'
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
                            window.location.href = base_url + 'client/stores';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Add Store');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Add Store');
                }
            });
        });
    }

    // Form validation for edit store
    if ($('#edit_store').length > 0) {
        $('#edit_store').formValidation({
            framework: 'bootstrap',
            icon: {
                valid: 'glyphicon glyphicon-ok',
                invalid: 'glyphicon glyphicon-remove',
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                company_id: {
                    validators: {
                        notEmpty: {
                            message: 'Company is required'
                        }
                    }
                },
                store_code: {
                    validators: {
                        notEmpty: {
                            message: 'Store code is required'
                        },
                        regexp: {
                            regexp: /^[A-Za-z0-9\-_]+$/,
                            message: 'Store code can only contain letters, numbers, hyphens and underscores'
                        },
                        stringLength: {
                            min: 3,
                            max: 20,
                            message: 'Store code must be between 3 and 20 characters'
                        }
                    }
                },
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Store name is required'
                        }
                    }
                },
                store_type: {
                    validators: {
                        notEmpty: {
                            message: 'Store type is required'
                        }
                    }
                },
                primary_email: {
                    validators: {
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
                contact_person_phone: {
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
                latitude: {
                    validators: {
                        regexp: {
                            regexp: /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)$/,
                            message: 'Please enter a valid latitude (-90 to 90)'
                        }
                    }
                },
                longitude: {
                    validators: {
                        regexp: {
                            regexp: /^[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/,
                            message: 'Please enter a valid longitude (-180 to 180)'
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
                            window.location.href = base_url + 'client/stores';
                        }, 2000);
                    } else {
                        toastr["error"]("Error", obj.message);
                        $('#submit_button').prop('disabled', false).html('Update Store');
                    }
                },
                error: function() {
                    toastr["error"]("Error", "Something went wrong. Please try again.");
                    $('#submit_button').prop('disabled', false).html('Update Store');
                }
            });
        });
    }
});
</script>
