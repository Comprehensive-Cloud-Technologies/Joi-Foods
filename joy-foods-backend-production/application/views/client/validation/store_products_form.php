<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>
<script>
    var base_url = '<?php echo base_url(); ?>';
    var productMap = {};
    var rowCount = 0;
    var fetchedProducts = {}; // product_id -> product data, from the last category fetch

    $(document).ready(function() {
        // Initialize Select2
        if ($('.select2').length > 0) {
            $('.select2').select2();
        }

        // Typeahead for product search
        $('#auto_complete_product_name').typeahead({
            source: function(query, result) {
                productMap = {};
                $.ajax({
                    url: base_url + "client/store_products/get_products_autocomplete",
                    data: {
                        query: query
                    },
                    dataType: "json",
                    type: "POST",
                    success: function(data) {
                        var newData = [];
                        $.each(data, function() {
                            productMap[this.productname] = this;
                            newData.push(this.productname);
                        });
                        return result(newData);
                    }
                });
            },
            afterSelect: function(args) {
                addProductToTable(productMap[args].id);
                $('#auto_complete_product_name').val('');
            }
        });

        // Store selection change - lock after selection
        $('#store_id').on('change', function() {
            if ($(this).val() != '') {
                // Add hidden field to preserve value when disabled
                if ($('#store_id_hidden').length === 0) {
                    $('#add_items_to_store').append('<input type="hidden" id="store_id_hidden" name="store_id" value="' + $(this).val() + '">');
                } else {
                    $('#store_id_hidden').val($(this).val());
                }
                // Remove name from dropdown to avoid conflicts and lock it
                $(this).removeAttr('name');
                $(this).prop('disabled', true);
                // Show info message
                toastr["info"]("Store locked. Reload page to change store.");
            }
        });

        // ---- Bulk selection in the main table ----

        // Master "select all" checkbox in table header
        $('#select_all_rows').on('change', function() {
            $('#prodtable .row-check').prop('checked', $(this).prop('checked'));
            updateSelectedCount();
        });

        // Individual row checkbox (delegated)
        $('#prodtable').on('change', '.row-check', function() {
            updateMasterCheckbox();
            updateSelectedCount();
        });

        // Remove selected rows
        $('#remove_selected_button').on('click', function() {
            var $checked = $('#prodtable .row-check:checked');
            if ($checked.length === 0) {
                return;
            }

            Swal.fire({
                title: "Remove selected items?",
                text: "This will remove " + $checked.length + " item(s) from the list.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#34c38f",
                cancelButtonColor: "#f46a6a",
                confirmButtonText: "Yes, remove"
            }).then(function(result) {
                if (result.value) {
                    $checked.closest('tr').remove();
                    $('#select_all_rows').prop('checked', false);
                    renumberRows();
                    refreshTableState();
                }
            });
        });

        // ---- Selection modal (after category fetch) ----

        // Modal "select all"
        $('#modal_select_all').on('change', function() {
            $('#modal_product_list .modal-check:not(:disabled)').prop('checked', $(this).prop('checked'));
            updateModalSelectedCount();
        });

        // Modal individual checkbox
        $('#modal_product_list').on('change', '.modal-check', function() {
            updateModalMasterCheckbox();
            updateModalSelectedCount();
        });

        // Modal search filter
        $('#modal_search').on('keyup', function() {
            var term = $(this).val().toLowerCase();
            $('#modal_product_list tr').each(function() {
                var name = $(this).data('name') || '';
                $(this).toggle(name.indexOf(term) > -1);
            });
        });

        // Add selected products from modal to the table
        $('#modal_add_selected').on('click', function() {
            var addedCount = 0;

            $('#modal_product_list .modal-check:checked:not(:disabled)').each(function() {
                var pid = $(this).val();
                var item = fetchedProducts[pid];
                if (item && !$('#p-' + item.product_id).length) {
                    rowCount++;
                    $('#prodtable').append(buildProductRow(item));
                    addedCount++;
                }
            });

            // Hide modal
            var modalEl = document.getElementById('productSelectModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }

            if (addedCount > 0) {
                renumberRows();
                refreshTableState();
                registerValidationFields();
                toastr["success"](addedCount + " product(s) added to the list");
            } else {
                toastr["info"]("No new products were added");
            }
        });
    });

    // Build a single product <tr> for the main table
    function buildProductRow(item) {
        var tr_element = 'p-' + item.product_id;
        var existingBadge = item.is_existing ?
            '<span class="badge bg-info existing-badge ms-2">Already in Store</span>' : '';

        var html = '<tr id="' + tr_element + '">';
        html += '<td class="text-center"><input type="checkbox" class="form-check-input row-check"></td>';
        html += '<td class="sno"></td>';
        html += '<td>' + item.product_name + existingBadge + '</td>';
        html += '<td>' + parseFloat(item.base_price).toFixed(2) + '</td>';
        html += '<td class="inputtd form-group">';
        html += '<input type="number" class="form-control store-price" ';
        html += 'id="store_price_' + item.product_id + '" name="store_price[]" ';
        html += 'placeholder="Store Price" required step="0.01" min="0" ';
        html += 'value="' + parseFloat(item.store_price).toFixed(2) + '">';
        html += '<input type="hidden" name="product_id[]" value="' + item.product_id + '">';
        html += '</td>';
        html += '<td>' + (item.category_name || 'N/A') + '</td>';
        html += '<td>';
        html += '<button type="button" class="btn btn-danger btn-sm" onclick="removeItem(' + item.product_id + ')">';
        html += '<i class="uil uil-trash"></i>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';

        return html;
    }

    // Add a single product to the table (used by typeahead search)
    function addProductToTable(pid) {
        var store_id = $('#store_id').val();

        if (store_id == '') {
            toastr["error"]("Please select a store first");
            return;
        }

        $.ajax({
            url: base_url + 'client/store_products/get_product_by_id',
            type: "POST",
            data: {
                store_id: store_id,
                pid: pid
            },
            success: function(data) {
                var obj = JSON.parse(data);

                if (obj.status == 200) {
                    if ($('#p-' + obj.data.product_id).length) {
                        toastr["error"]("Item already added to the list");
                    } else {
                        rowCount++;
                        $('#prodtable').append(buildProductRow(obj.data));
                        renumberRows();
                        refreshTableState();
                        registerValidationFields();
                    }
                } else {
                    toastr["error"](obj.message);
                }
            },
            error: function() {
                toastr["error"]("An error occurred. Please try again.");
            }
        });
    }

    // Remove a single item from the table
    function removeItem(product_id) {
        $('#p-' + product_id).remove();
        renumberRows();
        refreshTableState();
    }

    // Renumber the S.No. column
    function renumberRows() {
        rowCount = 0;
        $('#prodtable tr').each(function() {
            rowCount++;
            $(this).find('.sno').text(rowCount);
        });
    }

    // Toggle footer / empty message / toolbar based on row count
    function refreshTableState() {
        var count = $('#prodtable tr').length;

        if (count === 0) {
            $('#card_footer').addClass('d-none');
            $('#empty_message').show();
            $('#table_toolbar').addClass('d-none');
            $('#select_all_rows').prop('checked', false);
            rowCount = 0;
        } else {
            $('#card_footer').removeClass('d-none');
            $('#empty_message').hide();
            $('#table_toolbar').removeClass('d-none');
        }

        updateMasterCheckbox();
        updateSelectedCount();
    }

    // Re-register dynamic inputs with formValidation
    function registerValidationFields() {
        var inputs = $("#prodtable").find("input.store-price");
        if (inputs.length) {
            $('#add_items_to_store').formValidation('addField', inputs);
        }
    }

    // Sync the header master checkbox with row checkboxes
    function updateMasterCheckbox() {
        var total = $('#prodtable .row-check').length;
        var checked = $('#prodtable .row-check:checked').length;
        var $master = $('#select_all_rows');

        $master.prop('checked', total > 0 && checked === total);
        $master.prop('indeterminate', checked > 0 && checked < total);
    }

    // Update selected count + enable/disable Remove Selected
    function updateSelectedCount() {
        var checked = $('#prodtable .row-check:checked').length;
        $('#selected_count').text(checked);
        $('#remove_selected_button').prop('disabled', checked === 0);
    }

    // ---- Modal helpers ----

    function updateModalMasterCheckbox() {
        var total = $('#modal_product_list .modal-check:not(:disabled)').length;
        var checked = $('#modal_product_list .modal-check:checked:not(:disabled)').length;
        var $master = $('#modal_select_all');

        $master.prop('checked', total > 0 && checked === total);
        $master.prop('indeterminate', checked > 0 && checked < total);
    }

    function updateModalSelectedCount() {
        var checked = $('#modal_product_list .modal-check:checked:not(:disabled)').length;
        $('#modal_selected_count').text(checked);
    }

    // Cancel items - clear the whole table
    function cancel_items() {
        Swal.fire({
            title: "Are you sure?",
            text: "This will clear all added items.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#34c38f",
            cancelButtonColor: "#f46a6a",
            confirmButtonText: "Yes, clear all"
        }).then(function(result) {
            if (result.value) {
                $('#prodtable').html('');
                renumberRows();
                refreshTableState();
            }
        });
    }

    // Product filter form -> fetch products and open selection modal
    $('#products_filter_form').on('submit', function(e) {
        e.preventDefault();

        var store_id = $('#store_id').val();
        if (store_id == '') {
            toastr["error"]("Please select a store first");
            return false;
        }

        var category_id = $('#filter_category').val();

        $('#filter_products_button').prop('disabled', true).html('<i class="uil-spinner-alt fa-spin me-1"></i> Loading...');

        $.ajax({
            url: base_url + 'client/store_products/get_products_by_filter',
            type: "POST",
            data: {
                store_id: store_id,
                category_id: category_id
            },
            success: function(result) {
                $('#filter_products_button').prop('disabled', false).html('<i class="uil-search-alt me-1"></i> Get Products');

                var obj = JSON.parse(result);

                if (obj.status == 200) {
                    openProductModal(obj.data);
                } else {
                    toastr["error"](obj.message);
                }
            },
            error: function() {
                $('#filter_products_button').prop('disabled', false).html('<i class="uil-search-alt me-1"></i> Get Products');
                toastr["error"]("An error occurred. Please try again.");
            }
        });
    });

    // Populate and show the selection modal
    function openProductModal(products) {
        fetchedProducts = {};
        $('#modal_search').val('');

        var rows = '';
        $.each(products, function(index, item) {
            fetchedProducts[item.product_id] = item;

            var alreadyAdded = $('#p-' + item.product_id).length > 0;

            var statusBadges = '';
            if (item.is_existing) {
                statusBadges += '<span class="badge bg-info existing-badge">Already in Store</span> ';
            }
            if (alreadyAdded) {
                statusBadges += '<span class="badge bg-secondary existing-badge">In List</span>';
            }

            rows += '<tr data-name="' + (item.product_name || '').toLowerCase() + '">';
            rows += '<td class="text-center">';
            rows += '<input type="checkbox" class="form-check-input modal-check" value="' + item.product_id + '"' +
                (alreadyAdded ? ' disabled' : ' checked') + '>';
            rows += '</td>';
            rows += '<td>' + item.product_name + '</td>';
            rows += '<td>' + parseFloat(item.base_price).toFixed(2) + '</td>';
            rows += '<td>' + (item.category_name || 'N/A') + '</td>';
            rows += '<td>' + (statusBadges || '<span class="text-muted">New</span>') + '</td>';
            rows += '</tr>';
        });

        $('#modal_product_list').html(rows);

        // Master checkbox reflects only the selectable (non-disabled) rows
        $('#modal_select_all').prop('checked', $('#modal_product_list .modal-check:not(:disabled)').length > 0);
        updateModalMasterCheckbox();
        updateModalSelectedCount();

        var modal = new bootstrap.Modal(document.getElementById('productSelectModal'));
        modal.show();
    }

    // Main form validation and submission
    $(function() {
        $('#add_items_to_store').formValidation({
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
                },
                'store_price[]': {
                    selector: '.store-price',
                    row: '.inputtd',
                    validators: {
                        notEmpty: {
                            message: 'Please enter store price'
                        },
                        numeric: {
                            message: 'Please enter a valid price'
                        }
                    }
                }
            }
        }).on('success.form.fv', function(e) {
            e.preventDefault();

            var $form = $(e.target);

            // Check if at least one product is added
            if ($('#prodtable tr').length === 0) {
                toastr["error"]("Please add at least one product");
                return false;
            }

            const form_data = new FormData(this);

            Swal.fire({
                title: "Are you sure?",
                text: "Please verify the store and prices before submitting.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#34c38f",
                cancelButtonColor: "#f46a6a",
                confirmButtonText: "Yes, Submit"
            }).then(function(result) {
                if (result.value) {
                    $('#submit_button').prop('disabled', true).html('<i class="uil-spinner-alt fa-spin me-2"></i> Saving...');

                    $.ajax({
                        url: $form.attr('action'),
                        type: "POST",
                        data: form_data,
                        contentType: false,
                        cache: false,
                        processData: false,
                        success: function(result) {
                            var obj = JSON.parse(result);

                            if (obj.status == 200) {
                                toastr["success"](obj.message);

                                setTimeout(function() {
                                    window.location.href = base_url + 'client/store_products';
                                }, 2000);
                            } else {
                                $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-2"></i> Save Store Items');
                                toastr["error"](obj.message);
                            }
                        },
                        error: function() {
                            $('#submit_button').prop('disabled', false).html('<i class="uil uil-check me-2"></i> Save Store Items');
                            toastr["error"]("An error occurred. Please try again.");
                        }
                    });
                }
            });
        });
    });
</script>
