<script>
var base_url = '<?php echo base_url(); ?>';

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Initialize datepicker for date inputs
    $('.datepicker-init').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: "true"
    });

    // Load data on page load
    loadRequestsData();

    // Form submission handler
    $('#requests_filter_form').on('submit', function(e) {
        e.preventDefault();
        loadRequestsData();
    });

    // Clear filters
    $('#clear_filters').on('click', function() {
        $('#company_id').val('').trigger('change');
        $('status_1').val('').trigger('change');
        $('#date_from').val('<?php echo date('Y-m-01'); ?>');
        $('#date_to').val('<?php echo date('Y-m-d'); ?>');
        loadRequestsData();
    });

    // Process / Reject handlers
    $('#btn_process').on('click', function() {
        updateRequestStatus('PROCESSED');
    });
    $('#btn_reject').on('click', function() {
        updateRequestStatus('REJECTED');
    });
});

function loadRequestsData() {
    $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    // Destroy existing DataTable
    if ($.fn.DataTable.isDataTable('#requests_data')) {
        $('#requests_data').DataTable().destroy();
    }

    // Reinitialize with AJAX
    $('#requests_data').DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        "processing": true,
        "ajax": {
            "url": base_url + 'client/accountdeletionrequests/get_data',
            "type": 'POST',
            "data": function(d) {
                return $('#requests_filter_form').serialize();
            },
            "dataSrc": function(json) {
                if (json.status == 200) {
                    return json.data;
                } else {
                    return [];
                }
            }
        },
        "order": [[0, "desc"]],
        "language": {
            "emptyTable": "No account deletion requests found matching the selected filters",
            "processing": "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>Loading...</span></div>"
        },
        "columnDefs": [
            { "targets": [7], "orderable": false }
        ],
        "drawCallback": function(settings) {
            $('#submit_button').prop('disabled', false).html('<i class="uil-search-alt"></i>');
            // Reinitialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el);
            });
        }
    });
}

function viewRequest(id) {
    $.ajax({
        url: base_url + 'client/accountdeletionrequests/get_by_id',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status == 200) {
                var data = response.data;

                $('#process_request_id').val(data.id);
                $('#process_note').val(data.note || '');

                $('#view_company_name').text(data.company_name || '-');
                $('#view_company_code').text(data.company_code || '-');
                $('#view_email').text(data.email || '-');
                $('#view_status').text(data.status || '-');
                $('#view_created_at').text(data.created_at || '-');
                $('#view_ip_address').text(data.ip_address || '-');

                $('#view_employee_name').text(data.employee_name || 'No match');
                $('#view_employee_code').text(data.employee_code || '-');
                $('#view_employee_email').text(data.employee_email || '-');
                $('#view_employee_phone').text(data.employee_phone || '-');

                var modal = new bootstrap.Modal(document.getElementById('viewRequestModal'));
                modal.show();
            } else {
                toastr["error"](response.message || "Failed to load request details");
            }
        },
        error: function() {
            toastr["error"]("Failed to load request details");
        }
    });
}

function updateRequestStatus(status) {
    var id = $('#process_request_id').val();
    var note = $('#process_note').val();

    if (!id) {
        return;
    }

    $.ajax({
        url: base_url + 'client/accountdeletionrequests/update_status',
        type: 'POST',
        data: { id: id, status: status, note: note },
        dataType: 'json',
        success: function(response) {
            if (response.status == 200) {
                toastr["success"](response.message || "Request updated successfully");
                var modalEl = document.getElementById('viewRequestModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                loadRequestsData();
            } else {
                toastr["error"](response.message || "Failed to update request");
            }
        },
        error: function() {
            toastr["error"]("Failed to update request");
        }
    });
}
</script>
