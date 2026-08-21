<script>
var base_url = '<?php echo base_url(); ?>';
var isEditMode = false;

$(document).ready(function() {
    // Initialize DataTable
    if ($('#datatable').length > 0) {
        $('#datatable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            "order": [[0, "asc"]],
            "columnDefs": [{
                "targets": [7],
                "orderable": false
            }]
        });
    }

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Select2 for modal when modal is shown
    $('#bannerModal').on('shown.bs.modal', function() {
        initModalSelect2();
    });

    // Image preview
    $('#image').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image_preview').show().find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Form validation and submission
    $('#bannerForm').formValidation({
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
            title: {
                validators: {
                    notEmpty: {
                        message: 'Title is required'
                    }
                }
            },
            action_type: {
                validators: {
                    notEmpty: {
                        message: 'Action type is required'
                    }
                }
            }
        }
    }).on('success.form.fv', function(e) {
        e.preventDefault();

        var $form = $(e.target);
        var formData = new FormData($form[0]);
        var url = isEditMode ? base_url + 'client/banners/update' : base_url + 'client/banners/store';

        // Validate image for new banners
        if (!isEditMode && $('#image')[0].files.length === 0) {
            toastr["error"]("Banner image is required");
            return false;
        }

        $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(result) {
                var obj = JSON.parse(result);
                if (obj.status == 200) {
                    toastr["success"](obj.message);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    toastr["error"](obj.message);
                    $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-1"></i> Save Banner');
                }
            },
            error: function() {
                toastr["error"]("Something went wrong. Please try again.");
                $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-1"></i> Save Banner');
            }
        });
    });

    // Reset modal on close
    $('#bannerModal').on('hidden.bs.modal', function() {
        resetForm();
    });
});

// Initialize Select2 for modal
function initModalSelect2() {
    // Destroy existing Select2 instances first
    if ($('#company_id').hasClass('select2-hidden-accessible')) {
        $('#company_id').select2('destroy');
    }
    if ($('#action_type').hasClass('select2-hidden-accessible')) {
        $('#action_type').select2('destroy');
    }
    if ($('#product_id').hasClass('select2-hidden-accessible')) {
        $('#product_id').select2('destroy');
    }
    if ($('#category_id').hasClass('select2-hidden-accessible')) {
        $('#category_id').select2('destroy');
    }

    // Reinitialize Select2 with modal as parent
    $('#company_id').select2({
        dropdownParent: $('#bannerModal .modal-content'),
        width: '100%',
        placeholder: '-- Select Company --'
    });

    $('#action_type').select2({
        dropdownParent: $('#bannerModal .modal-content'),
        width: '100%',
        minimumResultsForSearch: -1
    }).on('select2:select', function(e) {
        // Handle action type change when Select2 selection changes
        handleActionTypeChange($(this).val());
    });

    $('#product_id').select2({
        dropdownParent: $('#bannerModal .modal-content'),
        width: '100%',
        placeholder: '-- Select Product --',
        allowClear: true
    });

    $('#category_id').select2({
        dropdownParent: $('#bannerModal .modal-content'),
        width: '100%',
        placeholder: '-- Select Category --',
        allowClear: true
    });
}

// Handle action type change
function handleActionTypeChange(actionType) {
    // Hide all action fields first
    $('#product_field').hide();
    $('#category_field').hide();
    $('#url_field').hide();

    // Show relevant field based on selection
    if (actionType == 'PRODUCT') {
        $('#product_field').show();
    } else if (actionType == 'CATEGORY') {
        $('#category_field').show();
    } else if (actionType == 'URL') {
        $('#url_field').show();
    }
}

// Open Add Modal
function openAddModal() {
    isEditMode = false;
    resetForm();
    $('#bannerModalLabel').text('Add Banner');
    $('#submit_button').html('<i class="uil uil-check me-1"></i> Save Banner');
}

// Edit Banner
function editBanner(id) {
    isEditMode = true;
    resetForm();

    $.ajax({
        url: base_url + 'client/banners/get_by_id',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status == 200) {
                var banner = response.data;

                $('#banner_id').val(banner.id);
                $('#title').val(banner.title);
                $('#description').val(banner.description);
                $('#display_order').val(banner.display_order);
                $('#existing_image').val(banner.image_path);

                // Show existing image
                if (banner.image_path) {
                    $('#image_preview').show().find('img').attr('src', base_url + banner.image_path);
                }

                $('#bannerModalLabel').text('Edit Banner');
                $('#submit_button').html('<i class="uil uil-check me-1"></i> Update Banner');

                // Show modal first
                var modal = new bootstrap.Modal(document.getElementById('bannerModal'));
                modal.show();

                // Set company and action type after modal is shown (Select2 initialized)
                setTimeout(function() {
                    $('#company_id').val(banner.company_id).trigger('change');
                    $('#action_type').val(banner.action_type).trigger('change');
                    handleActionTypeChange(banner.action_type);

                    // Set action payload based on type
                    setTimeout(function() {
                        if (banner.action_type == 'PRODUCT') {
                            $('#product_id').val(banner.action_payload).trigger('change');
                        } else if (banner.action_type == 'CATEGORY') {
                            $('#category_id').val(banner.action_payload).trigger('change');
                        } else if (banner.action_type == 'URL') {
                            $('#url').val(banner.action_payload);
                        }
                    }, 100);
                }, 300);
            } else {
                toastr["error"](response.message);
            }
        },
        error: function() {
            toastr["error"]("Failed to load banner data");
        }
    });
}

// Reset Form
function resetForm() {
    $('#bannerForm')[0].reset();
    $('#banner_id').val('');
    $('#existing_image').val('');
    $('#image_preview').hide().find('img').attr('src', '');

    // Hide all action fields
    $('#product_field').hide();
    $('#category_field').hide();
    $('#url_field').hide();

    // Reset form validation
    var fv = $('#bannerForm').data('formValidation');
    if (fv) {
        fv.resetForm();
    }

    // Reset select2 values
    if ($('#company_id').hasClass('select2-hidden-accessible')) {
        $('#company_id').val('').trigger('change');
    }
    if ($('#action_type').hasClass('select2-hidden-accessible')) {
        $('#action_type').val('NONE').trigger('change');
    }
    if ($('#product_id').hasClass('select2-hidden-accessible')) {
        $('#product_id').val('').trigger('change');
    }
    if ($('#category_id').hasClass('select2-hidden-accessible')) {
        $('#category_id').val('').trigger('change');
    }
}

// Toggle Status
$(document).on('change', '.status-toggle', function() {
    var id = $(this).data('id');
    var status = $(this).is(':checked') ? 1 : 0;

    $.ajax({
        url: base_url + 'client/banners/toggle_status',
        type: "POST",
        data: {
            id: id,
            status: status
        },
        success: function(result) {
            var obj = JSON.parse(result);
            if (obj.status == 'success') {
                toastr["success"](obj.message);
            } else {
                toastr["error"](obj.message);
            }
        },
        error: function() {
            toastr["error"]("An error occurred. Please try again.");
        }
    });
});

// Delete Banner
function deleteBanner(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "This banner will be deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#34c38f",
        cancelButtonColor: "#f46a6a",
        confirmButtonText: "Yes, delete it!"
    }).then(function(result) {
        if (result.value) {
            $.ajax({
                url: base_url + 'client/banners/delete',
                type: "POST",
                data: { id: id },
                success: function(result) {
                    var obj = JSON.parse(result);
                    if (obj.status == 'success') {
                        toastr["success"](obj.message);
                        $('#banner_row_' + id).fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        toastr["error"](obj.message);
                    }
                },
                error: function() {
                    toastr["error"]("An error occurred. Please try again.");
                }
            });
        }
    });
}
</script>
