<script>
$(document).ready(function() {
    // Initialize DataTable
    var table;
    if ($('#datatable').length > 0) {
        table = $('#datatable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            "order": [[0, "asc"]],
            "columnDefs": [{
                "targets": [8], // Action column
                "orderable": false
            }],
            initComplete: function() {
                var api = this.api();

                // Add dropdowns only to columns with 'filters' class
                api.columns('.filters').every(function() {
                    var column = this;
                    var columnIndex = column.index();
                    var header = $(column.header());
                    var title = header.text().trim();

                    // Clear the header text
                    header.html('');

                    // Create styled dropdown container
                    var selectContainer = $('<div></div>')
                        .on('click', function(e) {
                            e.stopPropagation(); // Prevent sorting when clicking dropdown
                        });

                    var select = $('<select class="form-select form-select-sm">' +
                            '<option value="">All</option>' +
                            '</select>')
                        .appendTo(selectContainer)
                        .on('change', function() {
                            var searchValue = $(this).val();

                            // Simple text search - search for the company name in the column
                            column.search(searchValue, false, false).draw();
                        });

                    // Add the dropdown to the header
                    header.append(selectContainer);

                    // Populate dropdown with unique values from the column
                    var uniqueValues = [];
                    column.data().unique().sort().each(function(d, j) {
                        // Parse HTML and extract only company name (remove small tag with code)
                        var tempDiv = $('<div>').html(d);
                        // Remove the small tag (company code)
                        tempDiv.find('small').remove();
                        // Get the text and remove extra whitespace/line breaks
                        var cleanData = tempDiv.text().replace(/\s+/g, ' ').trim();

                        if (cleanData && cleanData !== '' && cleanData !== '-' && uniqueValues.indexOf(cleanData) === -1) {
                            uniqueValues.push(cleanData);
                        }
                    });

                    // Sort and add options
                    uniqueValues.sort().forEach(function(value) {
                        select.append('<option value="' + value + '">' + value + '</option>');
                    });
                });
            }
        });
    }

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Delete store function with usage check
function deleteStore(id) {
    // First check if store has related data
    $.ajax({
        url: base_url + 'client/stores/check_usage',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var usage = response.usage;
                var totalUsage = usage.staff;

                if (totalUsage > 0) {
                    // Store has related data, show detailed warning
                    var usageList = '<ul class="text-start mt-2">';
                    if (usage.staff > 0) usageList += '<li>' + usage.staff + ' Staff Member(s)</li>';
                    usageList += '</ul>';

                    Swal.fire({
                        title: 'Store Has Data!',
                        html: 'This store has the following related data:' + usageList +
                              '<br><strong class="text-danger">All related data will be deleted!</strong>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete everything!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performDeleteStore(id, true);
                        }
                    });
                } else {
                    // Store has no related data, simple confirmation
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performDeleteStore(id, false);
                        }
                    });
                }
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        }
    });
}

function performDeleteStore(id, forceDelete) {
    $.ajax({
        url: base_url + 'client/stores/delete',
        type: 'POST',
        data: {
            id: id,
            force_delete: forceDelete ? 1 : 0
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire(
                    'Deleted!',
                    response.message,
                    'success'
                ).then(() => {
                    location.reload();
                });
            } else if (response.status === 'warning' && response.requires_confirmation) {
                // Handle case where force_delete wasn't sent properly
                var usage = response.usage;
                Swal.fire({
                    title: 'Store Has Data!',
                    text: 'This store has related data. Delete anyway?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete everything!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDeleteStore(id, true);
                    }
                });
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
        }
    });
}
</script>
