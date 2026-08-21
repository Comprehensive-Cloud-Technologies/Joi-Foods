<script>
var slabDataTable = null;
var taxOrdersDataTable = null;

$(document).ready(function() {
    var now = new Date();
    var firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    var lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    $('#filter_date_from').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    }).datepicker('setDate', firstDay);

    $('#filter_date_to').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    }).datepicker('setDate', lastDay);

    loadTax();
});

function loadTax() {
    var filters = {
        company_id: $('#filter_company').val(),
        store_id: $('#filter_store').val(),
        module: $('#filter_module').val(),
        date_from: $('#filter_date_from').val(),
        date_to: $('#filter_date_to').val()
    };

    $.ajax({
        url: base_url + 'client/reports/tax_data',
        type: 'POST',
        data: filters,
        beforeSend: function() {
            $('#slab_tbody').html('<tr><td colspan="7" class="text-center py-3"><i class="fa fa-spinner fa-spin me-1"></i> Loading...</td></tr>');
            $('#tax_orders_tbody').html('<tr><td colspan="11" class="text-center py-4"><i class="fa fa-spinner fa-spin me-1"></i> Loading...</td></tr>');
        },
        success: function(response) {
            var obj = JSON.parse(response);
            if (obj.status === 'success') {
                updateSummary(obj.summary);
                renderSlabs(obj.slabs);
                renderOrders(obj.orders);
            }
        },
        error: function() {
            $('#tax_orders_tbody').html('<tr><td colspan="11" class="text-center text-danger py-4">Failed to load data</td></tr>');
        }
    });
}

function updateSummary(summary) {
    $('#total_orders').text(summary.total_orders);
    $('#taxable_value').html('&#8377;' + formatNumber(summary.taxable_value));
    $('#total_tax').html('&#8377;' + formatNumber(summary.total_tax));
    $('#gross_total').html('&#8377;' + formatNumber(summary.gross_total));
}

function renderSlabs(slabs) {
    if (slabDataTable) {
        slabDataTable.destroy();
        slabDataTable = null;
    }

    var html = '';
    if (slabs.length === 0) {
        $('#slab_tbody').html('<tr><td colspan="7" class="text-center py-3 text-muted">No tax data found</td></tr>');
        return;
    }

    $.each(slabs, function(i, slab) {
        var tax = parseFloat(slab.tax_amount);
        var half = tax / 2;
        var rate = parseFloat(slab.tax_percentage);

        html += '<tr>';
        html += '<td><span class="badge bg-primary-subtle text-primary">' + rate.toFixed(2) + '%</span></td>';
        html += '<td>' + slab.order_count + '</td>';
        html += '<td>&#8377;' + formatNumber(slab.taxable_value) + '</td>';
        html += '<td>&#8377;' + formatNumber(half) + '</td>';
        html += '<td>&#8377;' + formatNumber(half) + '</td>';
        html += '<td class="fw-medium text-success">&#8377;' + formatNumber(tax) + '</td>';
        html += '<td>&#8377;' + formatNumber(slab.gross_total) + '</td>';
        html += '</tr>';
    });

    $('#slab_tbody').html(html);

    slabDataTable = $('#slab_table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        paging: false,
        searching: false,
        info: false,
        dom: 'Brt',
        buttons: [
            { extend: 'excel', className: 'btn btn-sm btn-soft-success', text: '<i class="uil uil-file-alt me-1"></i> Excel', title: 'Tax_by_Slab' },
            { extend: 'print', className: 'btn btn-sm btn-soft-primary', text: '<i class="uil uil-print me-1"></i> Print' }
        ]
    });
}

function renderOrders(orders) {
    if (taxOrdersDataTable) {
        taxOrdersDataTable.destroy();
        taxOrdersDataTable = null;
    }

    var html = '';
    if (orders.length === 0) {
        $('#tax_orders_tbody').html('<tr><td colspan="11" class="text-center py-4"><i class="uil uil-receipt font-size-24 text-muted"></i><p class="text-muted mb-0 mt-2">No orders found</p></td></tr>');
        return;
    }

    $.each(orders, function(i, o) {
        var moduleBadge = getModuleBadge(o.module);
        var statusBadge = getStatusBadge(o.status);

        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td><strong>' + o.order_number + '</strong></td>';
        html += '<td><small>' + o.created_at + '</small></td>';
        html += '<td><div>' + (o.company_name || '-') + '</div><small class="text-muted">' + (o.company_code || '') + '</small></td>';
        html += '<td>' + (o.store_name || '-') + '</td>';
        html += '<td>' + moduleBadge + '</td>';
        html += '<td>&#8377;' + formatNumber(o.subtotal) + '</td>';
        html += '<td class="text-success">&#8377;' + formatNumber(o.tax_amount) + '</td>';
        html += '<td>&#8377;' + formatNumber(o.discount_amount || 0) + '</td>';
        html += '<td class="fw-medium">&#8377;' + formatNumber(o.total_amount) + '</td>';
        html += '<td>' + statusBadge + '</td>';
        html += '</tr>';
    });

    $('#tax_orders_tbody').html(html);

    taxOrdersDataTable = $('#tax_orders_table').DataTable({
        responsive: true,
        order: [[2, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-sm btn-soft-success', text: '<i class="uil uil-file-alt me-1"></i> Excel', title: 'Tax_Report' },
            { extend: 'csv', className: 'btn btn-sm btn-soft-info', text: '<i class="uil uil-file me-1"></i> CSV', title: 'Tax_Report' },
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

function getStatusBadge(status) {
    var badges = {
        'PENDING': '<span class="badge bg-warning-subtle text-warning">Pending</span>',
        'CONFIRMED': '<span class="badge bg-info-subtle text-info">Confirmed</span>',
        'PREPARING': '<span class="badge bg-info-subtle text-info">Preparing</span>',
        'READY': '<span class="badge bg-primary-subtle text-primary">Ready</span>',
        'COMPLETED': '<span class="badge bg-success-subtle text-success">Completed</span>',
        'DELIVERED': '<span class="badge bg-success-subtle text-success">Delivered</span>',
        'CANCELLED': '<span class="badge bg-danger-subtle text-danger">Cancelled</span>',
        'REJECTED': '<span class="badge bg-danger-subtle text-danger">Rejected</span>'
    };
    return badges[status] || '<span class="badge bg-light text-dark">' + (status || '-') + '</span>';
}

function resetFilters() {
    $('#filter_company').val('').trigger('change');
    $('#filter_store').val('').trigger('change');
    $('#filter_module').val('').trigger('change');
    var now = new Date();
    $('#filter_date_from').datepicker('setDate', new Date(now.getFullYear(), now.getMonth(), 1));
    $('#filter_date_to').datepicker('setDate', new Date(now.getFullYear(), now.getMonth() + 1, 0));
    loadTax();
}

function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
