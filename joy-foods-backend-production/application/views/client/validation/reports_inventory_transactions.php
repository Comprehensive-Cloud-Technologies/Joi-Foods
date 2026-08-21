<script>
var transactionsDataTable = null;

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

    loadTransactions();
});

function loadTransactions() {
    var filters = {
        company_id: $('#filter_company').val(),
        store_id: $('#filter_store').val(),
        transaction_type: $('#filter_type').val(),
        source: $('#filter_source').val(),
        date_from: $('#filter_date_from').val(),
        date_to: $('#filter_date_to').val()
    };

    $.ajax({
        url: base_url + 'client/reports/inventory_transactions_data',
        type: 'POST',
        data: filters,
        beforeSend: function() {
            $('#transactions_tbody').html('<tr><td colspan="11" class="text-center py-4"><i class="fa fa-spinner fa-spin me-1"></i> Loading...</td></tr>');
        },
        success: function(response) {
            var obj = JSON.parse(response);
            if (obj.status === 'success') {
                updateSummary(obj.summary);
                renderTable(obj.transactions);
            }
        },
        error: function() {
            $('#transactions_tbody').html('<tr><td colspan="11" class="text-center text-danger py-4">Failed to load data</td></tr>');
        }
    });
}

function updateSummary(summary) {
    $('#total_transactions').text(summary.total_transactions);
    $('#total_in').text(formatInt(summary.total_in));
    $('#total_out').text(formatInt(summary.total_out));
    $('#total_adjustments').text(summary.total_adjustments);
}

function renderTable(transactions) {
    if (transactionsDataTable) {
        transactionsDataTable.destroy();
        transactionsDataTable = null;
    }

    var html = '';
    if (transactions.length === 0) {
        html = '<tr><td colspan="11" class="text-center py-4"><i class="uil uil-exchange font-size-24 text-muted"></i><p class="text-muted mb-0 mt-2">No transactions found</p></td></tr>';
        $('#transactions_tbody').html(html);
        return;
    }

    $.each(transactions, function(i, txn) {
        var typeBadge = getTypeBadge(txn.transaction_type);
        var sourceBadge = getSourceBadge(txn.source);

        var qtyClass = 'text-muted';
        var qtyPrefix = '';
        if (txn.transaction_type == 'IN') { qtyClass = 'text-success fw-medium'; qtyPrefix = '+'; }
        else if (txn.transaction_type == 'OUT') { qtyClass = 'text-danger fw-medium'; qtyPrefix = '-'; }

        var stockBefore = (txn.stock_before === null) ? '<span class="text-muted">&#8734;</span>' : txn.stock_before;
        var stockAfter = (txn.stock_after === null) ? '<span class="text-muted">&#8734;</span>' : txn.stock_after;

        var reference = '-';
        if (txn.order_number) {
            reference = '<span class="badge bg-light text-dark">' + txn.order_number + '</span>';
        } else if (txn.reference_type == 'MANUAL') {
            reference = '<span class="text-muted">Manual</span>';
        }

        var performedBy = getPerformedBy(txn.performed_by_type);

        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td><small>' + txn.created_at + '</small></td>';
        html += '<td><div>' + (txn.store_name || '-') + '</div><small class="text-muted">' + (txn.store_code || '') + '</small></td>';
        html += '<td>' + (txn.product_name || '-') + '</td>';
        html += '<td>' + typeBadge + '</td>';
        html += '<td class="' + qtyClass + '">' + qtyPrefix + txn.quantity + '</td>';
        html += '<td>' + stockBefore + '</td>';
        html += '<td>' + stockAfter + '</td>';
        html += '<td>' + sourceBadge + '</td>';
        html += '<td>' + reference + '</td>';
        html += '<td>' + performedBy + '</td>';
        html += '</tr>';
    });

    $('#transactions_tbody').html(html);

    transactionsDataTable = $('#transactions_table').DataTable({
        responsive: true,
        order: [[1, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-sm btn-soft-success', text: '<i class="uil uil-file-alt me-1"></i> Excel' },
            { extend: 'csv', className: 'btn btn-sm btn-soft-info', text: '<i class="uil uil-file me-1"></i> CSV' },
            { extend: 'print', className: 'btn btn-sm btn-soft-primary', text: '<i class="uil uil-print me-1"></i> Print' }
        ],
        pageLength: 25
    });
}

function getTypeBadge(type) {
    var badges = {
        'IN': '<span class="badge bg-success-subtle text-success"><i class="uil uil-arrow-down-left"></i> In</span>',
        'OUT': '<span class="badge bg-danger-subtle text-danger"><i class="uil uil-arrow-up-right"></i> Out</span>',
        'SET': '<span class="badge bg-info-subtle text-info"><i class="uil uil-edit"></i> Set</span>'
    };
    return badges[type] || '<span class="badge bg-light text-dark">' + (type || '-') + '</span>';
}

function getSourceBadge(source) {
    var badges = {
        'ORDER_PLACED': '<span class="badge bg-primary-subtle text-primary">Order Placed</span>',
        'ORDER_REJECTED': '<span class="badge bg-warning-subtle text-warning">Order Rejected</span>',
        'ORDER_CANCELLED': '<span class="badge bg-warning-subtle text-warning">Order Cancelled</span>',
        'MANUAL_UPDATE': '<span class="badge bg-info-subtle text-info">Manual Update</span>',
        'INITIAL_STOCK': '<span class="badge bg-secondary-subtle text-secondary">Initial Stock</span>'
    };
    return badges[source] || '<span class="badge bg-light text-dark">' + (source || '-') + '</span>';
}

function getPerformedBy(type) {
    var labels = {
        'STORE_STAFF': '<span class="badge bg-soft-primary text-primary">Store Staff</span>',
        'EMPLOYEE': '<span class="badge bg-soft-info text-info">Employee</span>',
        'GUEST': '<span class="badge bg-soft-warning text-warning">Guest</span>',
        'SYSTEM': '<span class="badge bg-soft-secondary text-secondary">System</span>'
    };
    return labels[type] || '<span class="text-muted">-</span>';
}

function resetFilters() {
    $('#filter_company').val('').trigger('change');
    $('#filter_store').val('').trigger('change');
    $('#filter_type').val('').trigger('change');
    $('#filter_source').val('').trigger('change');
    var now = new Date();
    $('#filter_date_from').datepicker('setDate', new Date(now.getFullYear(), now.getMonth(), 1));
    $('#filter_date_to').datepicker('setDate', new Date(now.getFullYear(), now.getMonth() + 1, 0));
    loadTransactions();
}

function formatInt(num) {
    return parseInt(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
