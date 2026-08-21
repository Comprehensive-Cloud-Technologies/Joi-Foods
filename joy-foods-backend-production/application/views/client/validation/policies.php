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

// Delete policy function with usage check
function deletePolicy(id) {
    // First check if policy is in use
    $.ajax({
        url: base_url + 'client/policies/check_usage',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var usage = response.usage;

                if (usage.companies > 0 || usage.employees > 0) {
                    // Policy is in use, show detailed warning
                    var companiesList = '';
                    var employeesList = '';

                    if (response.companies && response.companies.length > 0) {
                        companiesList = '<br><br><strong>Companies:</strong><ul>';
                        response.companies.forEach(function(c) {
                            companiesList += '<li>' + c.name + ' (' + c.company_code + ')</li>';
                        });
                        companiesList += '</ul>';
                    }

                    if (response.employees && response.employees.length > 0) {
                        employeesList = '<strong>Employees:</strong><ul>';
                        var displayCount = Math.min(response.employees.length, 5);
                        for (var i = 0; i < displayCount; i++) {
                            var emp = response.employees[i];
                            employeesList += '<li>' + emp.first_name + ' ' + (emp.last_name || '') + ' (' + emp.employee_code + ')</li>';
                        }
                        if (response.employees.length > 5) {
                            employeesList += '<li>...and ' + (response.employees.length - 5) + ' more</li>';
                        }
                        employeesList += '</ul>';
                    }

                    Swal.fire({
                        title: 'Policy In Use!',
                        html: 'This policy is assigned to <strong>' + usage.companies + '</strong> company(ies) and <strong>' + usage.employees + '</strong> employee(s).' +
                              companiesList + employeesList +
                              '<br><strong>Deleting will remove all these assignments.</strong>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete anyway!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performDelete(id, true);
                        }
                    });
                } else {
                    // Policy not in use, simple confirmation
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
                            performDelete(id, false);
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

function performDelete(id, forceDelete) {
    $.ajax({
        url: base_url + 'client/policies/delete',
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
                Swal.fire({
                    title: 'Policy In Use!',
                    text: 'This policy is assigned to ' + response.usage.companies + ' company(ies) and ' + response.usage.employees + ' employee(s). Delete anyway?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete anyway!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDelete(id, true);
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
