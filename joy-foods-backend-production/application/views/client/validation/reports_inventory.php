<script>
var inventoryDataTable = null;

$(document).ready(function() {
    loadInventory();
});

function loadInventory() {
    var filters = {
        company_id: $('#filter_company').val(),
        store_id: $('#filter_store').val(),
        category_id: $('#filter_category').val(),
        stock_status: $('#filter_stock_status').val()
    };

    $.ajax({
        url: base_url + 'client/reports/inventory_data',
        type: 'POST',
        data: filters,
        beforeSend: function() {
            $('#inventory_tbody').html('<tr><td colspan="9" class="text-center py-4"><i class="fa fa-spinner fa-spin me-1"></i> Loading...</td></tr>');
        },
        success: function(response) {
            var obj = JSON.parse(response);
            if (obj.status === 'success') {
                updateSummary(obj.summary);
                renderTable(obj.rows);
            }
        },
        error: function() {
            $('#inventory_tbody').html('<tr><td colspan="9" class="text-center text-danger py-4">Failed to load data</td></tr>');
        }
    });
}

function updateSummary(summary) {
    $('#total_products').text(summary.total_products);
    $('#in_stock').text(summary.in_stock);
    $('#out_of_stock').text(summary.out_of_stock);
    $('#total_units').text(formatInt(summary.total_units));
}

function renderTable(rows) {
    if (inventoryDataTable) {
        inventoryDataTable.destroy();
        inventoryDataTable = null;
    }

    var html = '';
    if (rows.length === 0) {
        html = '<tr><td colspan="9" class="text-center py-4"><i class="uil uil-box font-size-24 text-muted"></i><p class="text-muted mb-0 mt-2">No products found</p></td></tr>';
        $('#inventory_tbody').html(html);
        return;
    }

    $.each(rows, function(i, row) {
        var isUnlimited = (row.available_stock === null);
        var stock = isUnlimited ? 0 : parseInt(row.available_stock);

        var stockDisplay, stockStatusBadge;
        if (isUnlimited) {
            stockDisplay = '<span class="text-muted">&#8734; Unlimited</span>';
            stockStatusBadge = '<span class="badge bg-secondary-subtle text-secondary">Unlimited</span>';
        } else if (stock === 0) {
            stockDisplay = '<span class="text-danger fw-bold">0</span>';
            stockStatusBadge = '<span class="badge bg-danger-subtle text-danger">Out of Stock</span>';
        } else {
            stockDisplay = '<span class="fw-medium">' + stock + '</span>';
            stockStatusBadge = '<span class="badge bg-success-subtle text-success">In Stock</span>';
        }

        var moduleBadge = getModuleBadge(row.store_type);

        var productStatus = (row.store_is_active == 1)
            ? '<span class="badge bg-success-subtle text-success">Active</span>'
            : '<span class="badge bg-danger-subtle text-danger">Inactive</span>';

        var productName = row.product_name;
        if (row.is_vegetarian == 1) {
            productName = '<span class="text-success me-1" title="Vegetarian">&#9679;</span>' + productName;
        }

        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td><div><strong>' + (row.store_name || '-') + '</strong></div><small class="text-muted">' + (row.store_code || '') + (row.company_name ? ' &middot; ' + row.company_name : '') + '</small></td>';
        html += '<td>' + productName + '</td>';
        html += '<td>' + (row.category_name || '-') + '</td>';
        html += '<td>' + moduleBadge + '</td>';
        html += '<td>' + stockDisplay + '</td>';
        html += '<td>' + stockStatusBadge + '</td>';
        html += '<td>' + productStatus + '</td>';
        html += '<td><small class="text-muted">' + formatDate(row.stock_updated_at) + '</small></td>';
        html += '</tr>';
    });

    $('#inventory_tbody').html(html);

    inventoryDataTable = $('#inventory_table').DataTable({
        responsive: true,
        order: [[1, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-sm btn-soft-success', text: '<i class="uil uil-file-alt me-1"></i> Excel' },
            { extend: 'csv', className: 'btn btn-sm btn-soft-info', text: '<i class="uil uil-file me-1"></i> CSV' },
            { extend: 'print', className: 'btn btn-sm btn-soft-primary', text: '<i class="uil uil-print me-1"></i> Print' }
        ],
        pageLength: 25
    });
}

function getModuleBadge(type) {
    var badges = {
        'QSR': '<span class="badge bg-info-subtle text-info">QSR</span>',
        'KOT': '<span class="badge bg-warning-subtle text-warning">KOT</span>',
        'PREMEAL': '<span class="badge bg-success-subtle text-success">PREMEAL</span>'
    };
    return badges[type] || '<span class="badge bg-light text-dark">' + (type || '-') + '</span>';
}

function resetFilters() {
    $('#filter_company').val('').trigger('change');
    $('#filter_store').val('').trigger('change');
    $('#filter_category').val('').trigger('change');
    $('#filter_stock_status').val('').trigger('change');
    loadInventory();
}

function formatInt(num) {
    return parseInt(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatDate(datetime) {
    if (!datetime) return '-';
    return datetime;
}
</script>
