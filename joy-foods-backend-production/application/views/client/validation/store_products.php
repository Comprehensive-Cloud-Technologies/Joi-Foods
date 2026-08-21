<script>
    var base_url = '<?php echo base_url(); ?>';

    $(document).ready(function() {
        // Initialize Select2
        if ($('.select2').length > 0) {
            $('.select2').select2();
        }

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Filter form validation
    $(function() {
        $('#filter_form').formValidation({
            message: 'This value is not valid',
            icon: {
                validating: 'glyphicon glyphicon-refresh'
            },
            fields: {
                store_id: {
                    validators: {
                        notEmpty: {
                            message: 'Please select a store'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();

            var $form = $(e.target);
            var store_id = $('#store_id').val();
            var category_id = $('#category_id').val();

            $('#filter_button').prop('disabled', true).html('<i class="uil-spinner-alt fa-spin me-1"></i> Loading...');

            $.ajax({
                url: base_url + 'client/store_products/get_store_products',
                type: "POST",
                data: {
                    store_id: store_id,
                    category_id: category_id
                },
                success: function(result) {
                    $('#filter_button').prop('disabled', false).html('<i class="uil-search-alt me-1"></i> Get Store Items');

                    var obj = JSON.parse(result);

                    if (obj.status == 200) {
                        renderStoreProducts(obj.data);
                        $('#results_info').text('Showing ' + obj.data.length + ' store items');
                    } else {
                        toastr["error"](obj.message);
                    }
                },
                error: function() {
                    $('#filter_button').prop('disabled', false).html('<i class="uil-search-alt me-1"></i> Get Store Items');
                    toastr["error"]("An error occurred. Please try again.");
                }
            });
        });
    });

    // Reset filter
    $('#reset_button').on('click', function() {
        $('#store_id').val('').trigger('change');
        $('#category_id').val('all').trigger('change');
        $('#store_products_tbody').html('<tr><td colspan="9" class="text-center text-muted">No data available. Please filter to view store items.</td></tr>');
        $('#results_info').text('Select a store and click "Get Store Items" to view items.');
        $('#select_all_items').prop('checked', false).prop('indeterminate', false);
        refreshToolbar();
        // Reset form validation
        $('#filter_form').data('formValidation').resetForm();
    });

    // Render store products table
    function renderStoreProducts(data) {
        var html = '';

        if (data.length > 0) {
            $.each(data, function(index, item) {
                var statusBadge = item.is_active == 1 ?
                    '<span class="badge bg-success">Active</span>' :
                    '<span class="badge bg-danger">Inactive</span>';

                var statusChecked = item.is_active == 1 ? 'checked' : '';

                html += '<tr id="sp_row_' + item.id + '">';
                html += '<td class="text-center"><input type="checkbox" class="form-check-input item-check" value="' + item.id + '"></td>';
                html += '<td>' + (index + 1) + '</td>';
                html += '<td>' + item.product_name + '</td>';
                html += '<td>' + (item.category_name || 'N/A') + '</td>';
                html += '<td>' + parseFloat(item.base_price).toFixed(2) + '</td>';
                html += '<td>' + parseFloat(item.price).toFixed(2) + '</td>';
                html += '<td>' + item.store_name + ' (' + item.store_code + ')</td>';
                html += '<td>';
                html += '<div class="form-check form-switch">';
                html += '<input class="form-check-input status-toggle" type="checkbox" data-id="' + item.id + '" ' + statusChecked + '>';
                html += '</div>';
                html += '</td>';
                html += '<td>';
                html += '<button type="button" class="btn btn-sm btn-danger delete-btn" data-id="' + item.id + '" data-bs-toggle="tooltip" title="Delete">';
                html += '<i class="uil uil-trash"></i>';
                html += '</button>';
                html += '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="9" class="text-center text-muted">No store items found for the selected filter.</td></tr>';
        }

        $('#store_products_tbody').html(html);

        // Reset selection state and toggle the bulk toolbar
        $('#select_all_items').prop('checked', false).prop('indeterminate', false);
        refreshToolbar();

        // Reinitialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Show/hide the bulk toolbar based on whether real rows exist
    function refreshToolbar() {
        var hasRows = $('#store_products_tbody .item-check').length > 0;
        $('#items_toolbar').toggleClass('d-none', !hasRows);
        updateSelectedCount();
    }

    // Sync header master checkbox with row checkboxes
    function updateMasterCheckbox() {
        var total = $('#store_products_tbody .item-check').length;
        var checked = $('#store_products_tbody .item-check:checked').length;
        var $master = $('#select_all_items');

        $master.prop('checked', total > 0 && checked === total);
        $master.prop('indeterminate', checked > 0 && checked < total);
    }

    // Update selected count + enable/disable Delete Selected
    function updateSelectedCount() {
        var checked = $('#store_products_tbody .item-check:checked').length;
        $('#selected_count').text(checked);
        $('#delete_selected_button').prop('disabled', checked === 0);
    }

    // Master "select all" checkbox
    $(document).on('change', '#select_all_items', function() {
        $('#store_products_tbody .item-check').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });

    // Individual row checkbox
    $(document).on('change', '.item-check', function() {
        updateMasterCheckbox();
        updateSelectedCount();
    });

    // Delete selected (bulk)
    $('#delete_selected_button').on('click', function() {
        var ids = $('#store_products_tbody .item-check:checked').map(function() {
            return $(this).val();
        }).get();

        if (ids.length === 0) {
            return;
        }

        Swal.fire({
            title: "Are you sure?",
            text: "This will remove " + ids.length + " item(s) from the store.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#34c38f",
            cancelButtonColor: "#f46a6a",
            confirmButtonText: "Yes, delete them!"
        }).then(function(result) {
            if (result.value) {
                $('#delete_selected_button').prop('disabled', true).html('<i class="uil-spinner-alt fa-spin me-1"></i> Deleting...');

                $.ajax({
                    url: base_url + 'client/store_products/bulk_delete',
                    type: "POST",
                    data: {
                        ids: ids
                    },
                    success: function(result) {
                        var obj = JSON.parse(result);
                        if (obj.status == 'success') {
                            toastr["success"](obj.message);

                            $.each(ids, function(i, id) {
                                $('#sp_row_' + id).remove();
                            });

                            if ($('#store_products_tbody tr').length === 0) {
                                $('#store_products_tbody').html('<tr><td colspan="9" class="text-center text-muted">No store items found.</td></tr>');
                            }

                            $('#select_all_items').prop('checked', false).prop('indeterminate', false);
                            refreshToolbar();
                        } else {
                            toastr["error"](obj.message);
                        }
                        $('#delete_selected_button').html('<i class="uil uil-trash me-1"></i> Delete Selected');
                        updateSelectedCount();
                    },
                    error: function() {
                        toastr["error"]("An error occurred. Please try again.");
                        $('#delete_selected_button').prop('disabled', false).html('<i class="uil uil-trash me-1"></i> Delete Selected');
                    }
                });
            }
        });
    });

    // Toggle status
    $(document).on('change', '.status-toggle', function() {
        var id = $(this).data('id');
        var status = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: base_url + 'client/store_products/toggle_status',
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

    // Delete store product
    $(document).on('click', '.delete-btn', function() {
        var id = $(this).data('id');
        var row = $('#sp_row_' + id);

        Swal.fire({
            title: "Are you sure?",
            text: "This will remove the item from the store.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#34c38f",
            cancelButtonColor: "#f46a6a",
            confirmButtonText: "Yes, delete it!"
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    url: base_url + 'client/store_products/delete',
                    type: "POST",
                    data: {
                        id: id
                    },
                    success: function(result) {
                        var obj = JSON.parse(result);
                        if (obj.status == 'success') {
                            toastr["success"](obj.message);
                            row.fadeOut(300, function() {
                                $(this).remove();
                                // Check if table is empty
                                if ($('#store_products_tbody tr').length === 0) {
                                    $('#store_products_tbody').html('<tr><td colspan="9" class="text-center text-muted">No store items found.</td></tr>');
                                }
                                updateMasterCheckbox();
                                refreshToolbar();
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
    });
</script>
