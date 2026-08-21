<script>
var premealTable;

$(document).ready(function() {

    // Initialize datepickers
    $('#date_from, #date_to').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        clearBtn: true
    });

    // Default: current month
    $('#date_from').datepicker('setDate', new Date(new Date().getFullYear(), new Date().getMonth(), 1));
    $('#date_to').datepicker('setDate', new Date());

    // Initialize DataTable
    premealTable = $('#premeal_table').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="uil uil-file-alt me-1"></i> Excel',
                className: 'btn btn-sm btn-soft-success',
                title: 'Premeal Report',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'print',
                text: '<i class="uil uil-print me-1"></i> Print',
                className: 'btn btn-sm btn-soft-primary',
                title: 'Premeal Report',
                exportOptions: { columns: ':visible' }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: 'No bookings found. Adjust your filters and try again.',
            zeroRecords: 'No matching bookings found.'
        }
    });

    // Load data on page load
    loadPremealData();

    // Filter button
    $('#btn_filter').on('click', function() {
        loadPremealData();
    });

    // Reset button
    $('#btn_reset').on('click', function() {
        $('#date_from').datepicker('setDate', new Date(new Date().getFullYear(), new Date().getMonth(), 1));
        $('#date_to').datepicker('setDate', new Date());
        $('#meal_type_filter').val('').trigger('change');
        $('#status_filter').val('').trigger('change');
        loadPremealData();
    });

    // Order detail click
    $(document).on('click', '.order-link', function(e) {
        e.preventDefault();
        showOrderDetail($(this).data('id'));
    });
});

function loadPremealData() {
    var btn = $('#btn_filter');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...');

    $.ajax({
        url: base_url + 'company/reports/premeal_data',
        type: 'POST',
        data: {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            meal_type: $('#meal_type_filter').val(),
            status: $('#status_filter').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                updateSummary(response.summary);
                updateTable(response.orders);
            } else {
                toastr['error']('Error', response.message || 'Failed to load data');
            }
        },
        error: function() {
            toastr['error']('Error', 'Failed to load report data');
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="uil uil-filter me-1"></i> Filter');
        }
    });
}

function updateSummary(summary) {
    $('#summary_orders').text(summary.total_orders);
    $('#summary_total').text('Rs. ' + formatNumber(summary.total_amount));
    $('#summary_company').text('Rs. ' + formatNumber(summary.company_contribution));
    $('#summary_employee').text('Rs. ' + formatNumber(summary.employee_contribution));
}

function updateTable(orders) {
    premealTable.clear();

    var mealColors = {
        'BREAKFAST': 'warning', 'LUNCH': 'success', 'DINNER': 'primary', 'SNACKS': 'info'
    };

    $.each(orders, function(index, order) {
        var statusColors = {
            'PENDING': 'warning', 'CONFIRMED': 'primary', 'PREPARING': 'info',
            'READY': 'success', 'DELIVERED': 'success', 'COMPLETED': 'success'
        };
        var sColor = statusColors[order.status] || 'secondary';
        var mColor = mealColors[order.meal_type] || 'secondary';
        var empName = (order.first_name || '') + ' ' + (order.last_name || '');

        premealTable.row.add([
            index + 1,
            '<a href="#" class="order-link text-primary fw-medium" data-id="' + order.id + '">' + order.order_number + '</a>',
            '<div><span class="fw-medium">' + empName.trim() + '</span><br><small class="text-muted">' + (order.employee_code || '') + '</small></div>',
            '<span class="badge bg-' + mColor + '-subtle text-' + mColor + '">' + (order.meal_type || '-') + '</span>',
            formatDate(order.scheduled_date),
            '<span class="text-end d-block">Rs. ' + formatNumber(order.total_amount) + '</span>',
            '<span class="text-end d-block text-danger">Rs. ' + formatNumber(order.company_contribution) + '</span>',
            '<span class="text-end d-block">Rs. ' + formatNumber(order.employee_contribution) + '</span>',
            '<span class="badge bg-' + sColor + '-subtle text-' + sColor + '">' + order.status + '</span>'
        ]);
    });

    premealTable.draw();
}

function showOrderDetail(orderId) {
    $('#order_detail_body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#orderDetailModal').modal('show');

    $.ajax({
        url: base_url + 'company/reports/order_detail',
        type: 'POST',
        data: { order_id: orderId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                renderOrderDetail(response.order, response.items, response.payments);
            } else {
                $('#order_detail_body').html('<div class="text-center py-4"><i class="uil uil-exclamation-triangle font-size-24 text-danger"></i><p class="text-muted mt-2">' + (response.message || 'Failed to load order') + '</p></div>');
            }
        },
        error: function() {
            $('#order_detail_body').html('<div class="text-center py-4"><i class="uil uil-exclamation-triangle font-size-24 text-danger"></i><p class="text-muted mt-2">Failed to load order details</p></div>');
        }
    });
}

function renderOrderDetail(order, items, payments) {
    var statusColors = {
        'PENDING': 'warning', 'CONFIRMED': 'primary', 'PREPARING': 'info',
        'READY': 'success', 'DELIVERED': 'success', 'COMPLETED': 'success',
        'CANCELLED': 'danger', 'REJECTED': 'danger'
    };
    var sColor = statusColors[order.status] || 'secondary';
    var empName = ((order.first_name || '') + ' ' + (order.last_name || '')).trim();

    var html = '';

    // Order header
    html += '<div class="d-flex justify-content-between align-items-center mb-3">';
    html += '<div>';
    html += '<h5 class="mb-1">' + order.order_number + '</h5>';
    html += '<span class="badge bg-info-subtle text-info me-1">PREMEAL</span>';
    html += '<span class="badge bg-' + sColor + '-subtle text-' + sColor + '">' + order.status + '</span>';
    html += '</div>';
    html += '<div class="text-end">';
    html += '<h4 class="mb-0 text-primary">Rs. ' + formatNumber(order.total_amount) + '</h4>';
    html += '<small class="text-muted">' + formatDateTime(order.created_at) + '</small>';
    html += '</div>';
    html += '</div>';

    html += '<hr class="my-3">';

    // Info grid
    html += '<div class="row g-3 mb-3">';

    html += '<div class="col-md-4">';
    html += '<p class="text-muted small mb-1">Employee</p>';
    html += '<p class="mb-0 fw-medium">' + (empName || '-') + '</p>';
    html += '<small class="text-muted">' + (order.employee_code || '') + '</small>';
    html += '</div>';

    html += '<div class="col-md-4">';
    html += '<p class="text-muted small mb-1">Store</p>';
    html += '<p class="mb-0 fw-medium">' + (order.store_name || '-') + '</p>';
    html += '<small class="text-muted">' + (order.store_code || '') + '</small>';
    html += '</div>';

    html += '<div class="col-md-4">';
    html += '<p class="text-muted small mb-1">Meal Type</p>';
    var mealColors = { 'BREAKFAST': 'warning', 'LUNCH': 'success', 'DINNER': 'primary', 'SNACKS': 'info' };
    var mColor = mealColors[order.meal_type] || 'secondary';
    html += '<span class="badge bg-' + mColor + '-subtle text-' + mColor + '">' + (order.meal_type || '-') + '</span>';
    html += '</div>';

    html += '<div class="col-md-4">';
    html += '<p class="text-muted small mb-1">Scheduled Date</p>';
    html += '<p class="mb-0 fw-medium">' + (order.scheduled_date ? formatDate(order.scheduled_date) : '-') + '</p>';
    html += '</div>';

    if (order.policy_name) {
        html += '<div class="col-md-4">';
        html += '<p class="text-muted small mb-1">Policy</p>';
        html += '<p class="mb-0 fw-medium">' + order.policy_name + '</p>';
        html += '</div>';
    }

    html += '</div>';

    // Order items
    html += '<h6 class="mb-2 mt-3">Order Items</h6>';
    html += '<div class="table-responsive">';
    html += '<table class="table table-sm table-bordered mb-0">';
    html += '<thead class="table-light"><tr>';
    html += '<th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Tax</th><th class="text-end">Total</th>';
    html += '</tr></thead><tbody>';

    $.each(items, function(i, item) {
        html += '<tr>';
        html += '<td>' + item.product_name;
        if (item.note) html += '<br><small class="text-muted fst-italic">' + item.note + '</small>';
        html += '</td>';
        html += '<td class="text-center">' + item.quantity + '</td>';
        html += '<td class="text-end">Rs. ' + formatNumber(item.unit_price) + '</td>';
        html += '<td class="text-end">Rs. ' + formatNumber(item.tax_amount) + '</td>';
        html += '<td class="text-end fw-medium">Rs. ' + formatNumber(item.total_amount) + '</td>';
        html += '</tr>';
    });

    html += '</tbody></table></div>';

    // Pricing summary
    html += '<div class="row mt-3">';
    html += '<div class="col-md-6">';

    if (payments && payments.length > 0) {
        html += '<h6 class="mb-2">Payment Breakdown</h6>';
        html += '<table class="table table-sm mb-0">';
        var paymentLabels = { 'WALLET_DEBIT': 'Wallet', 'ONLINE_PAYMENT': 'Online', 'COMPANY_SUBSIDY': 'Company Subsidy', 'REFUND_CREDIT': 'Refund' };
        var paymentIcons = { 'WALLET_DEBIT': 'uil-wallet', 'ONLINE_PAYMENT': 'uil-globe', 'COMPANY_SUBSIDY': 'uil-building', 'REFUND_CREDIT': 'uil-redo' };
        $.each(payments, function(i, p) {
            var pColor = p.status === 'SUCCESS' ? 'success' : (p.status === 'FAILED' ? 'danger' : 'warning');
            html += '<tr>';
            html += '<td><i class="uil ' + (paymentIcons[p.payment_type] || 'uil-money-bill') + ' me-1"></i>' + (paymentLabels[p.payment_type] || p.payment_type) + '</td>';
            html += '<td class="text-end">Rs. ' + formatNumber(p.amount) + '</td>';
            html += '<td><span class="badge bg-' + pColor + '-subtle text-' + pColor + '">' + p.status + '</span></td>';
            html += '</tr>';
        });
        html += '</table>';
    }

    html += '</div>';

    html += '<div class="col-md-6">';
    html += '<div class="bg-light rounded p-3">';
    html += '<table class="table table-sm table-borderless mb-0">';
    html += '<tr><td class="text-muted">Subtotal</td><td class="text-end">Rs. ' + formatNumber(order.subtotal) + '</td></tr>';
    html += '<tr><td class="text-muted">Tax</td><td class="text-end">Rs. ' + formatNumber(order.tax_amount) + '</td></tr>';

    if (parseFloat(order.discount_amount) > 0) {
        html += '<tr><td class="text-muted">Discount';
        if (order.coupon_code) html += ' <small>(' + order.coupon_code + ')</small>';
        html += '</td><td class="text-end text-success">- Rs. ' + formatNumber(order.discount_amount) + '</td></tr>';
    }

    html += '<tr class="border-top"><td class="fw-bold">Total</td><td class="text-end fw-bold">Rs. ' + formatNumber(order.total_amount) + '</td></tr>';

    if (parseFloat(order.company_contribution) > 0) {
        html += '<tr><td class="text-danger">Company Paid</td><td class="text-end text-danger">Rs. ' + formatNumber(order.company_contribution) + '</td></tr>';
    }

    html += '<tr><td class="text-muted">Employee Paid</td><td class="text-end">Rs. ' + formatNumber(order.employee_contribution) + '</td></tr>';

    if (parseFloat(order.wallet_deducted) > 0) {
        html += '<tr><td class="text-muted small">via Wallet</td><td class="text-end small">Rs. ' + formatNumber(order.wallet_deducted) + '</td></tr>';
    }

    var onlinePaid = parseFloat(order.employee_contribution) - parseFloat(order.wallet_deducted || 0);
    if (onlinePaid > 0.01) {
        html += '<tr><td class="text-muted small">via Online</td><td class="text-end small">Rs. ' + formatNumber(onlinePaid) + '</td></tr>';
    }

    html += '</table></div></div></div>';

    if (order.customer_note) {
        html += '<div class="mt-3 p-2 bg-light rounded">';
        html += '<small class="text-muted"><i class="uil uil-comment-alt-message me-1"></i>Customer Note:</small> ';
        html += '<span class="small">' + order.customer_note + '</span>';
        html += '</div>';
    }

    $('#orderDetailModalLabel').text('Order: ' + order.order_number);
    $('#order_detail_body').html(html);
}

function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var hours = d.getHours();
    var mins = d.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    if (hours === 0) hours = 12;
    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear() + ', ' + hours + ':' + (mins < 10 ? '0' : '') + mins + ' ' + ampm;
}
</script>
