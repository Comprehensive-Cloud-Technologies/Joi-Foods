<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#datatable').length > 0) {
        $('#datatable').DataTable({
            "responsive": true,
            "pageLength": 25,
            "order": [[0, "asc"]]
        });
    }

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Delete company function with usage check
function deleteCompany(id) {
    // First check if company has related data
    $.ajax({
        url: base_url + 'client/companies/check_usage',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var usage = response.usage;
                var totalUsage = usage.departments + usage.employees + usage.users + usage.policies + usage.stores + usage.orders;

                if (totalUsage > 0) {
                    // Company has related data, show detailed warning
                    var usageList = '<ul class="text-start mt-2">';
                    if (usage.orders > 0) usageList += '<li><strong>' + usage.orders + ' Order(s)</strong></li>';
                    if (usage.stores > 0) usageList += '<li>' + usage.stores + ' Store(s)</li>';
                    if (usage.employees > 0) usageList += '<li>' + usage.employees + ' Employee(s)</li>';
                    if (usage.departments > 0) usageList += '<li>' + usage.departments + ' Department(s)</li>';
                    if (usage.policies > 0) usageList += '<li>' + usage.policies + ' Policy(ies) attached</li>';
                    if (usage.users > 0) usageList += '<li>' + usage.users + ' Company Admin(s)</li>';
                    usageList += '</ul>';

                    Swal.fire({
                        title: 'Company Has Data!',
                        html: 'This company has the following related data:' + usageList +
                              '<br><strong class="text-danger">All related data will be deleted!</strong>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete everything!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performDeleteCompany(id, true);
                        }
                    });
                } else {
                    // Company has no related data, simple confirmation
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
                            performDeleteCompany(id, false);
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

function performDeleteCompany(id, forceDelete) {
    $.ajax({
        url: base_url + 'client/companies/delete',
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
                    title: 'Company Has Data!',
                    text: 'This company has related data. Delete anyway?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete everything!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDeleteCompany(id, true);
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
