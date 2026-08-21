<script>
var storeTable;

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
    storeTable = $('#store_table').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="uil uil-file-alt me-1"></i> Excel',
                className: 'btn btn-sm btn-soft-success',
                title: 'Store Performance Report',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'print',
                text: '<i class="uil uil-print me-1"></i> Print',
                className: 'btn btn-sm btn-soft-primary',
                title: 'Store Performance Report',
                exportOptions: { columns: ':visible' }
            }
        ],
        order: [[7, 'desc']],
        language: {
            emptyTable: 'No store data found. Adjust your filters and try again.',
            zeroRecords: 'No matching records found.'
        }
    });

    // Load data on page load
    loadStoreData();

    // Filter button
    $('#btn_filter').on('click', function() {
        loadStoreData();
    });

    // Reset button
    $('#btn_reset').on('click', function() {
        $('#date_from').datepicker('setDate', new Date(new Date().getFullYear(), new Date().getMonth(), 1));
        $('#date_to').datepicker('setDate', new Date());
        $('#store_filter').val('').trigger('change');
        $('#module_filter').val('').trigger('change');
        loadStoreData();
    });
});

function loadStoreData() {
    var btn = $('#btn_filter');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Loading...');

    $.ajax({
        url: base_url + 'client/reports/store_performance_data',
        type: 'POST',
        data: {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            store_id: $('#store_filter').val(),
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
    $('#summary_stores').text(summary.total_stores);
    $('#summary_orders').text(summary.total_orders);
    $('#summary_total').text('Rs. ' + formatNumber(summary.total_amount));
    $('#summary_company').text('Rs. ' + formatNumber(summary.company_contribution));
}

function updateTable(rows) {
    storeTable.clear();

    var typeColors = { 'QSR': 'primary', 'KOT': 'warning', 'PREMEAL': 'info' };

    $.each(rows, function(index, row) {
        var tColor = typeColors[row.store_type] || 'secondary';
        var avg = row.order_count > 0 ? (parseFloat(row.total) / parseInt(row.order_count)) : 0;

        storeTable.row.add([
            index + 1,
            '<span class="fw-medium">' + (row.store_name || '-') + '</span>',
            '<span class="text-muted">' + (row.store_code || '-') + '</span>',
            '<span class="badge bg-' + tColor + '-subtle text-' + tColor + '">' + (row.store_type || '-') + '</span>',
            '<span class="text-center d-block">' + row.order_count + '</span>',
            '<span class="text-end d-block text-danger">Rs. ' + formatNumber(row.company_share) + '</span>',
            '<span class="text-end d-block">Rs. ' + formatNumber(row.employee_share) + '</span>',
            '<span class="text-end d-block fw-medium">Rs. ' + formatNumber(row.total) + '</span>',
            '<span class="text-end d-block text-muted">Rs. ' + formatNumber(avg) + '</span>'
        ]);
    });

    storeTable.draw();
}

function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
