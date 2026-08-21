<script>
var transactionsDataTable = null;

$(document).ready(function() {
    // Set default dates to current month
    var now = new Date();
    var firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    var lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    function pad(n) { return n < 10 ? '0' + n : n; }
    var defaultFrom = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-01';
    var defaultTo = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(lastDay.getDate());

    // Initialize datepickers with defaults
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
        employee_id: $('#filter_employee').val(),
        type: $('#filter_type').val(),
        source: $('#filter_source').val(),
        date_from: $('#filter_date_from').val(),
        date_to: $('#filter_date_to').val()
    };

    $.ajax({
        url: base_url + 'client/reports/wallet_transactions_data',
        type: 'POST',
        data: filters,
        beforeSend: function() {
            $('#transactions_tbody').html('<tr><td colspan="8" class="text-center py-4"><i class="fa fa-spinner fa-spin me-1"></i> Loading...</td></tr>');
        },
        success: function(response) {
            var obj = JSON.parse(response);
            if (obj.status === 'success') {
                updateSummary(obj.summary);
                renderTable(obj.transactions);
            }
        },
        error: function() {
            $('#transactions_tbody').html('<tr><td colspan="8" class="text-center text-danger py-4">Failed to load data</td></tr>');
        }
    });
}

function updateSummary(summary) {
    $('#total_transactions').text(summary.total_transactions);
    $('#total_credits').html('&#8377;' + formatNumber(summary.total_credits));
    $('#total_debits').html('&#8377;' + formatNumber(summary.total_debits));
    $('#company_credits').html('&#8377;' + formatNumber(summary.company_credits));
}

function renderTable(transactions) {
    if (transactionsDataTable) {
        transactionsDataTable.destroy();
        transactionsDataTable = null;
    }

    var html = '';
    if (transactions.length === 0) {
        html = '<tr><td colspan="8" class="text-center py-4"><i class="uil uil-exchange font-size-24 text-muted"></i><p class="text-muted mb-0 mt-2">No transactions found</p></td></tr>';
        $('#transactions_tbody').html(html);
        return;
    }

    $.each(transactions, function(i, txn) {
        var isCredit = txn.transaction_type == 1;
        var typeClass = isCredit ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
        var typeLabel = isCredit ? 'Credit' : 'Debit';
        var amountClass = isCredit ? 'text-success' : 'text-danger';
        var amountPrefix = isCredit ? '+' : '-';

        var sourceLabel = getSourceLabel(txn.source);
        var sourceBadge = getSourceBadge(txn.source);

        var description = txn.transaction_label || '-';
        if (txn.credited_by_name) {
            description += '<br><small class="text-muted">By: ' + txn.credited_by_name + '</small>';
        }
        if (txn.credit_reason) {
            description += '<br><small class="text-info">Reason: ' + txn.credit_reason + '</small>';
        }

        var empName = txn.first_name + ' ' + (txn.last_name || '');

        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td><div>' + txn.transaction_date + '</div><small class="text-muted">' + formatTime(txn.transaction_time) + '</small></td>';
        html += '<td><div><strong>' + empName + '</strong></div><small class="text-muted">' + (txn.employee_code || '') + '</small></td>';
        html += '<td><div>' + txn.company_name + '</div><small class="text-muted">' + txn.company_code + '</small></td>';
        html += '<td><span class="badge ' + typeClass + '">' + typeLabel + '</span></td>';
        html += '<td class="' + amountClass + ' fw-medium">' + amountPrefix + '&#8377;' + formatNumber(txn.amount) + '</td>';
        html += '<td>' + sourceBadge + '</td>';
        html += '<td>' + description + '</td>';
        html += '</tr>';
    });

    $('#transactions_tbody').html(html);

    transactionsDataTable = $('#transactions_table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-sm btn-soft-success', text: '<i class="uil uil-file-alt me-1"></i> Excel' },
            { extend: 'csv', className: 'btn btn-sm btn-soft-info', text: '<i class="uil uil-file me-1"></i> CSV' },
            { extend: 'print', className: 'btn btn-sm btn-soft-primary', text: '<i class="uil uil-print me-1"></i> Print' }
        ],
        pageLength: 25
    });
}

function getSourceLabel(source) {
    var labels = {
        'RAZORPAY':                  'Razorpay (Employee)',
        'COMPANY_RAZORPAY_RECHARGE': 'Razorpay (Company)',
        'COMPANY_CREDIT':            'Company Credit',
        'COMPANY_DEBIT':             'Company Debit',
        'ORDER_REFUND':              'Refund',
        'SYSTEM':                    'System'
    };
    return labels[source] || source || 'N/A';
}

function getSourceBadge(source) {
    var badges = {
        'RAZORPAY':                  '<span class="badge bg-primary-subtle text-primary">Razorpay (Employee)</span>',
        'COMPANY_RAZORPAY_RECHARGE': '<span class="badge bg-primary-subtle text-primary">Razorpay (Company)</span>',
        'COMPANY_CREDIT':            '<span class="badge bg-info-subtle text-info">Company Credit</span>',
        'COMPANY_DEBIT':             '<span class="badge bg-danger-subtle text-danger">Company Debit</span>',
        'ORDER_REFUND':              '<span class="badge bg-warning-subtle text-warning">Refund</span>',
        'SYSTEM':                    '<span class="badge bg-secondary-subtle text-secondary">System</span>'
    };
    return badges[source] || '<span class="badge bg-light text-dark">' + (source || 'N/A') + '</span>';
}

function resetFilters() {
    $('#filter_company').val('').trigger('change');
    $('#filter_employee').val('').trigger('change');
    $('#filter_type').val('').trigger('change');
    $('#filter_source').val('').trigger('change');
    var now = new Date();
    $('#filter_date_from').datepicker('setDate', new Date(now.getFullYear(), now.getMonth(), 1));
    $('#filter_date_to').datepicker('setDate', new Date(now.getFullYear(), now.getMonth() + 1, 0));
    loadTransactions();
}

function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function formatTime(datetime) {
    if (!datetime) return '';
    var parts = datetime.split(' ');
    return parts.length > 1 ? parts[1] : '';
}
</script>
