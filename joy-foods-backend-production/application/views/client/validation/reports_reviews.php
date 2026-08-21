<script>
var base_url = '<?php echo base_url(); ?>';

$(document).ready(function() {
    $('.select2').select2();
    $('.datepicker-init').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: "true"
    });

    // Set default dates - current month
    $('#date_from').val('<?php echo date('Y-m-01'); ?>');
    $('#date_to').val('<?php echo date('Y-m-d'); ?>');

    loadReviewsData();

    $('#reviews_filter_form').on('submit', function(e) {
        e.preventDefault();
        loadReviewsData();
    });

    // Order detail click
    $(document).on('click', '.order-link', function(e) {
        e.preventDefault();
        var orderId = $(this).data('id');
        showOrderDetail(orderId);
    });

    $('#clear_filters').on('click', function() {
        $('#company_id').val('').trigger('change');
        $('#store_id').val('').trigger('change');
        $('#module').val('').trigger('change');
        $('#date_from').val('<?php echo date('Y-m-01'); ?>');
        $('#date_to').val('<?php echo date('Y-m-d'); ?>');
        loadReviewsData();
    });
});

function truncateText(text, maxLen) {
    if (!text) return '<span class="text-muted">-</span>';
    var escaped = $('<span>').text(text).html();
    if (text.length > maxLen) {
        return '<span data-bs-toggle="tooltip" title="' + escaped.replace(/"/g, '&quot;') + '">' + escaped.substring(0, maxLen) + '...</span>';
    }
    return escaped;
}

function getModuleBadge(module) {
    var colors = { 'QSR': 'primary', 'KOT': 'success', 'PREMEAL': 'warning' };
    var color = colors[module] || 'secondary';
    return '<span class="badge bg-soft-' + color + ' text-' + color + '">' + module + '</span>';
}

function loadReviewsData() {
    $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    // Load summary + table data
    $.ajax({
        url: base_url + 'client/reports/reviews_data',
        type: 'POST',
        data: $('#reviews_filter_form').serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Update summary cards
                $('#summary_total_reviews').text(response.summary.total_reviews);
                $('#summary_stores_reviewed').text(response.summary.stores_reviewed);
                $('#summary_companies_reviewed').text(response.summary.companies_reviewed);

                // Build table data
                var tableData = [];
                if (response.reviews && response.reviews.length > 0) {
                    $.each(response.reviews, function(i, r) {
                        var empName = $.trim((r.first_name || '') + ' ' + (r.last_name || ''));
                        tableData.push([
                            r.id,
                            r.created_at ? new Date(r.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-',
                            '<a href="#" class="order-link text-primary fw-medium" data-id="' + r.order_id + '">' + (r.order_number || '-') + '</a>',
                            '<span class="badge bg-soft-primary text-primary">' + (r.company_name || 'N/A') + '</span>',
                            (r.store_name || '-') + (r.store_code ? ' <small class="text-muted">(' + r.store_code + ')</small>' : ''),
                            empName || '-',
                            getModuleBadge(r.module),
                            truncateText(r.food_review, 40),
                            truncateText(r.service_review, 40),
                            '<button type="button" class="btn btn-soft-primary btn-sm" onclick="viewReview(' + r.id + ')" data-bs-toggle="tooltip" title="View Details"><i class="uil uil-eye"></i></button>'
                        ]);
                    });
                }

                // Reinitialize DataTable
                if ($.fn.DataTable.isDataTable('#reviews_data')) {
                    $('#reviews_data').DataTable().destroy();
                }

                $('#reviews_data').DataTable({
                    "data": tableData,
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "order": [[0, "desc"]],
                    "dom": 'Bfrtip',
                    "buttons": [
                        {
                            extend: 'excel',
                            text: '<i class="uil uil-file-alt me-1"></i> Excel',
                            className: 'btn btn-sm btn-outline-success',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                        },
                        {
                            extend: 'print',
                            text: '<i class="uil uil-print me-1"></i> Print',
                            className: 'btn btn-sm btn-outline-primary',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
                        }
                    ],
                    "language": {
                        "emptyTable": "No reviews found matching the selected filters",
                        "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div>"
                    },
                    "columnDefs": [
                        { "targets": [9], "orderable": false }
                    ],
                    "drawCallback": function() {
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
                    }
                });
            }
            $('#submit_button').prop('disabled', false).html('<i class="uil-search-alt"></i>');
        },
        error: function() {
            $('#submit_button').prop('disabled', false).html('<i class="uil-search-alt"></i>');
            toastr["error"]("Failed to load reviews data");
        }
    });
}

// --- Order Detail (same as Sales Report) ---

function showOrderDetail(orderId) {
    $('#order_detail_body').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    $('#orderDetailModal').modal('show');

    $.ajax({
        url: base_url + 'client/reports/order_detail',
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
    var moduleColors = { 'QSR': 'primary', 'KOT': 'success', 'PREMEAL': 'warning' };
    var statusColors = {
        'PENDING': 'warning', 'CONFIRMED': 'primary', 'PREPARING': 'info',
        'READY': 'success', 'DELIVERED': 'success', 'COMPLETED': 'success',
        'CANCELLED': 'danger', 'REJECTED': 'danger'
    };
    var mColor = moduleColors[order.module] || 'secondary';
    var sColor = statusColors[order.status] || 'secondary';
    var empName = ((order.first_name || '') + ' ' + (order.last_name || '')).trim();

    var html = '';

    // Order header
    html += '<div class="d-flex justify-content-between align-items-center mb-3">';
    html += '<div>';
    html += '<h5 class="mb-1">' + order.order_number + '</h5>';
    html += '<span class="badge bg-' + mColor + '-subtle text-' + mColor + ' me-1">' + order.module + '</span>';
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
    html += '<div class="col-md-4"><p class="text-muted small mb-1">Company</p><p class="mb-0 fw-medium">' + (order.company_name || '-') + '</p><small class="text-muted">' + (order.company_code || '') + '</small></div>';
    html += '<div class="col-md-4"><p class="text-muted small mb-1">Store</p><p class="mb-0 fw-medium">' + (order.store_name || '-') + '</p><small class="text-muted">' + (order.store_code || '') + '</small></div>';
    html += '<div class="col-md-4"><p class="text-muted small mb-1">Employee</p><p class="mb-0 fw-medium">' + (empName || '-') + '</p><small class="text-muted">' + (order.employee_code || '') + '</small></div>';

    if (order.module === 'KOT') {
        html += '<div class="col-md-4"><p class="text-muted small mb-1">Department</p><p class="mb-0 fw-medium">' + (order.department_name || '-') + '</p></div>';
        html += '<div class="col-md-4"><p class="text-muted small mb-1">Delivery Location</p><p class="mb-0 fw-medium">' + (order.location_name || '-') + '</p></div>';
    }
    if (order.module === 'PREMEAL') {
        html += '<div class="col-md-4"><p class="text-muted small mb-1">Meal Type</p><p class="mb-0 fw-medium">' + (order.meal_type || '-') + '</p></div>';
        html += '<div class="col-md-4"><p class="text-muted small mb-1">Scheduled Date</p><p class="mb-0 fw-medium">' + (order.scheduled_date ? formatDate(order.scheduled_date) : '-') + '</p></div>';
    }
    if (order.policy_name) {
        html += '<div class="col-md-4"><p class="text-muted small mb-1">Policy</p><p class="mb-0 fw-medium">' + order.policy_name + '</p></div>';
    }
    html += '</div>';

    // Order items
    html += '<h6 class="mb-2 mt-3">Order Items</h6>';
    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
    html += '<thead class="table-light"><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Tax</th><th class="text-end">Total</th></tr></thead><tbody>';
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
    html += '<div class="row mt-3"><div class="col-md-6">';
    if (payments && payments.length > 0) {
        html += '<h6 class="mb-2">Payment Breakdown</h6><table class="table table-sm mb-0">';
        var paymentLabels = { 'WALLET_DEBIT': 'Wallet', 'ONLINE_PAYMENT': 'Online', 'COMPANY_SUBSIDY': 'Company Subsidy', 'REFUND_CREDIT': 'Refund' };
        var paymentIcons = { 'WALLET_DEBIT': 'uil-wallet', 'ONLINE_PAYMENT': 'uil-globe', 'COMPANY_SUBSIDY': 'uil-building', 'REFUND_CREDIT': 'uil-redo' };
        $.each(payments, function(i, p) {
            var pStatusColor = p.status === 'SUCCESS' ? 'success' : (p.status === 'FAILED' ? 'danger' : 'warning');
            html += '<tr><td><i class="uil ' + (paymentIcons[p.payment_type] || 'uil-money-bill') + ' me-1"></i>' + (paymentLabels[p.payment_type] || p.payment_type) + '</td>';
            html += '<td class="text-end">Rs. ' + formatNumber(p.amount) + '</td>';
            html += '<td><span class="badge bg-' + pStatusColor + '-subtle text-' + pStatusColor + '">' + p.status + '</span></td></tr>';
        });
        html += '</table>';
    }
    html += '</div><div class="col-md-6"><div class="bg-light rounded p-3"><table class="table table-sm table-borderless mb-0">';
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
        html += '<div class="mt-3 p-2 bg-light rounded"><small class="text-muted"><i class="uil uil-comment-alt-message me-1"></i>Customer Note:</small> <span class="small">' + order.customer_note + '</span></div>';
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

// --- Review Detail ---

function viewReview(id) {
    $('#reviewModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    var modal = new bootstrap.Modal(document.getElementById('viewReviewModal'));
    modal.show();

    $.ajax({
        url: base_url + 'client/reports/review_detail',
        type: 'POST',
        data: { review_id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var r = response.review;
                var empName = $.trim((r.first_name || '') + ' ' + (r.last_name || ''));
                var orderDate = r.order_date ? new Date(r.order_date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
                var reviewDate = r.created_at ? new Date(r.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

                var html = '';
                html += '<div class="row">';

                // Order & Store Info
                html += '<div class="col-md-6 mb-3">';
                html += '<div class="card bg-light mb-0"><div class="card-body">';
                html += '<h6 class="card-title text-muted mb-3"><i class="uil uil-receipt me-1"></i> Order Info</h6>';
                html += '<p class="mb-1"><strong>Order #:</strong> ' + (r.order_number || '-') + '</p>';
                html += '<p class="mb-1"><strong>Module:</strong> ' + getModuleBadge(r.module) + '</p>';
                html += '<p class="mb-1"><strong>Order Amount:</strong> <span class="fw-medium">₹' + parseFloat(r.total_amount || 0).toFixed(2) + '</span></p>';
                html += '<p class="mb-0"><strong>Order Date:</strong> ' + orderDate + '</p>';
                html += '</div></div></div>';

                // Employee Info
                html += '<div class="col-md-6 mb-3">';
                html += '<div class="card bg-light mb-0"><div class="card-body">';
                html += '<h6 class="card-title text-muted mb-3"><i class="uil uil-user me-1"></i> Employee Details</h6>';
                html += '<p class="mb-1"><strong>Name:</strong> ' + (empName || '-') + '</p>';
                html += '<p class="mb-1"><strong>Code:</strong> ' + (r.employee_code || '-') + '</p>';
                html += '<p class="mb-1"><strong>Email:</strong> ' + (r.employee_email || '-') + '</p>';
                html += '<p class="mb-0"><strong>Phone:</strong> ' + (r.employee_phone || '-') + '</p>';
                html += '</div></div></div>';

                html += '</div>';

                // Store & Company row
                html += '<div class="row">';
                html += '<div class="col-md-6 mb-3">';
                html += '<div class="card bg-light mb-0"><div class="card-body">';
                html += '<h6 class="card-title text-muted mb-3"><i class="uil uil-store me-1"></i> Store</h6>';
                html += '<p class="mb-1"><strong>Name:</strong> ' + (r.store_name || '-') + '</p>';
                html += '<p class="mb-1"><strong>Code:</strong> ' + (r.store_code || '-') + '</p>';
                html += '<p class="mb-0"><strong>Type:</strong> ' + (r.store_type || '-') + '</p>';
                html += '</div></div></div>';

                html += '<div class="col-md-6 mb-3">';
                html += '<div class="card bg-light mb-0"><div class="card-body">';
                html += '<h6 class="card-title text-muted mb-3"><i class="uil uil-building me-1"></i> Company</h6>';
                html += '<p class="mb-1"><strong>Name:</strong> ' + (r.company_name || '-') + '</p>';
                html += '<p class="mb-0"><strong>Code:</strong> ' + (r.company_code || '-') + '</p>';
                html += '</div></div></div>';
                html += '</div>';

                // Reviews
                html += '<div class="row">';
                html += '<div class="col-12 mb-3">';
                html += '<label class="form-label fw-bold"><i class="uil uil-utensils me-1"></i> Food Review</label>';
                html += '<div class="p-3 bg-light rounded" style="white-space: pre-wrap;">' + $('<span>').text(r.food_review || 'No food review provided').html() + '</div>';
                html += '</div>';

                html += '<div class="col-12 mb-3">';
                html += '<label class="form-label fw-bold"><i class="uil uil-thumbs-up me-1"></i> Service Review</label>';
                html += '<div class="p-3 bg-light rounded" style="white-space: pre-wrap;">' + $('<span>').text(r.service_review || 'No service review provided').html() + '</div>';
                html += '</div>';

                if (r.extra_comments) {
                    html += '<div class="col-12 mb-3">';
                    html += '<label class="form-label fw-bold"><i class="uil uil-comment-alt-message me-1"></i> Additional Comments</label>';
                    html += '<div class="p-3 bg-light rounded" style="white-space: pre-wrap;">' + $('<span>').text(r.extra_comments).html() + '</div>';
                    html += '</div>';
                }
                html += '</div>';

                // Review date footer
                html += '<div class="text-end text-muted"><small>Reviewed on: ' + reviewDate + '</small></div>';

                $('#reviewModalBody').html(html);
            } else {
                $('#reviewModalBody').html('<div class="text-center text-danger py-4">Review not found</div>');
            }
        },
        error: function() {
            $('#reviewModalBody').html('<div class="text-center text-danger py-4">Failed to load review details</div>');
        }
    });
}
</script>
