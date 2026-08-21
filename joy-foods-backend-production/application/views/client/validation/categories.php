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

// Delete category function with usage check
function deleteCategory(id) {
    // First check if category has related data
    $.ajax({
        url: base_url + 'client/categories/check_usage',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                var usage = response.usage;
                var totalUsage = usage.products + usage.subcategories;

                if (totalUsage > 0) {
                    // Category has related data, show detailed warning
                    var usageList = '<ul class="text-start mt-2">';
                    if (usage.products > 0) usageList += '<li>' + usage.products + ' Product(s)</li>';
                    if (usage.subcategories > 0) usageList += '<li>' + usage.subcategories + ' Subcategor(ies)</li>';
                    usageList += '</ul>';

                    Swal.fire({
                        title: 'Category Has Data!',
                        html: 'This category has the following related data:' + usageList +
                              '<br><strong class="text-danger">All related data will be deleted!</strong>',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete everything!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performDeleteCategory(id, true);
                        }
                    });
                } else {
                    // Category has no related data, simple confirmation
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
                            performDeleteCategory(id, false);
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

function performDeleteCategory(id, forceDelete) {
    $.ajax({
        url: base_url + 'client/categories/delete',
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
                    title: 'Category Has Data!',
                    text: 'This category has related data. Delete anyway?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete everything!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDeleteCategory(id, true);
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

// Toggle category status
function toggleStatus(id, newStatus) {
    var statusText = newStatus == 1 ? 'activate' : 'deactivate';

    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to ' + statusText + ' this category?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, ' + statusText + ' it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: base_url + 'client/categories/toggle_status',
                type: 'POST',
                data: {
                    id: id,
                    status: newStatus
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire(
                            'Success!',
                            response.message,
                            'success'
                        ).then(() => {
                            location.reload();
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
    });
}
</script>
