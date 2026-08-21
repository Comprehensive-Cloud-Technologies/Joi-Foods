<script>
var billingTable;

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
    billingTable = $('#billing_table').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="uil uil-file-alt me-1"></i> Excel',
                className: 'btn btn-sm btn-soft-success',
                title: 'Company Billing Report',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'print',
                text: '<i class="uil uil-print me-1"></i> Print',
                className: 'btn btn-sm btn-soft-primary',
                title: 'Company Billing Report',
                exportOptions: { columns: ':visible' }
            }
        ],
        order: [[4, 'desc']],
        language: {
            emptyTable: 'No billing data found. Adjust your filters and try again.',
            zeroRecords: 'No matching records found.'
        }
    });

    // Load data on page load
    loadBillingData();

    // Filter button
    $('#btn_filter').on('click', function() {
        loadBillingData();
    });

    // Reset button
    $('#btn_reset').on('click', function() {
        $('#date_from').datepicker('setDate', new Date(new Date().getFullYear(), new Date().getMonth(), 1));
        $('#date_to').datepicker('setDate', new Date());
        $('#company_filter').val('').trigger('change');
        $('#module_filter').val('').trigger('change');
        loadBillingData();
    });
});

function loadBillingData() {
    var btn = $('#btn_filter');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...');

    $.ajax({
        url: base_url + 'client/reports/company_billing_data',
        type: 'POST',
        data: {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            company_id: $('#company_filter').val(),
            module: $('#module_filter').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                updateSummary(response.summary);
                updateTable(response.rows);
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
    $('#summary_companies').text(summary.total_companies);
    $('#summary_total').text('Rs. ' + formatNumber(summary.total_amount));
    $('#summary_company').text('Rs. ' + formatNumber(summary.company_contribution));
    $('#summary_employee').text('Rs. ' + formatNumber(summary.employee_contribution));
}

function updateTable(rows) {
    billingTable.clear();

    $.each(rows, function(index, row) {
        var avg = row.order_count > 0 ? (parseFloat(row.total) / parseInt(row.order_count)) : 0;

        billingTable.row.add([
            index + 1,
            '<span class="fw-medium">' + (row.company_name || '-') + '</span>',
            '<span class="text-muted">' + (row.company_code || '-') + '</span>',
            '<span class="text-center d-block">' + row.order_count + '</span>',
            '<span class="text-end d-block text-danger">Rs. ' + formatNumber(row.company_share) + '</span>',
            '<span class="text-end d-block">Rs. ' + formatNumber(row.employee_share) + '</span>',
            '<span class="text-end d-block fw-medium">Rs. ' + formatNumber(row.total) + '</span>',
            '<span class="text-end d-block text-muted">Rs. ' + formatNumber(avg) + '</span>'
        ]);
    });

    billingTable.draw();
}

function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
