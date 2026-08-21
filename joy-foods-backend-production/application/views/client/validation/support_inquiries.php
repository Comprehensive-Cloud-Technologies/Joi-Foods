<script>
var base_url = '<?php echo base_url(); ?>';

$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2();

    // Initialize Flatpickr for date inputs
    
        $('.datepicker-init').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: "true"
        });

    // Load data on page load
    loadInquiriesData();

    // Form submission handler
    $('#inquiries_filter_form').on('submit', function(e) {
        e.preventDefault();
        loadInquiriesData();
    });

    // Clear filters
    $('#clear_filters').on('click', function() {
        $('#company_id').val('').trigger('change');
        $('#topic').val('').trigger('change');
        $('#date_from').val('<?php echo date('Y-m-01'); ?>');
        $('#date_to').val('<?php echo date('Y-m-d'); ?>');
        loadInquiriesData();
    });
});

function loadInquiriesData() {
    $('#submit_button').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    // Destroy existing DataTable
    if ($.fn.DataTable.isDataTable('#inquiries_data')) {
        $('#inquiries_data').DataTable().destroy();
    }

    // Reinitialize with AJAX
    $('#inquiries_data').DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],
        "processing": true,
        "ajax": {
            "url": base_url + 'client/supportinquiries/get_data',
            "type": 'POST',
            "data": function(d) {
                return $('#inquiries_filter_form').serialize();
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
            "emptyTable": "No inquiries found matching the selected filters",
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

function viewInquiry(id) {
    $.ajax({
        url: base_url + 'client/supportinquiries/get_by_id',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status == 200) {
                var data = response.data;

                $('#view_employee_name').text(data.employee_name || '-');
                $('#view_employee_code').text(data.employee_code || '-');
                $('#view_employee_email').text(data.employee_email || '-');
                $('#view_employee_phone').text(data.employee_phone || '-');
                $('#view_company_name').text(data.company_name || '-');
                $('#view_topic').text(data.topic || '-');
                $('#view_created_at').text(data.created_at || '-');
                $('#view_subject').text(data.subject || '-');
                $('#view_message').text(data.message || '-');

                var modal = new bootstrap.Modal(document.getElementById('viewInquiryModal'));
                modal.show();
            } else {
                toastr["error"](response.message || "Failed to load inquiry details");
            }
        },
        error: function() {
            toastr["error"]("Failed to load inquiry details");
        }
    });
}
</script>
