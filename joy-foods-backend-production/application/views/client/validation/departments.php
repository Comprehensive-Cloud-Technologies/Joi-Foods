<script>
$(document).ready(function() {

    // Initialize DataTable
    $('#datatable').DataTable({
        responsive: true,
        order: [[0, 'asc']]
    });

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

});

function deleteDepartment(id, employeeCount) {
    if (employeeCount > 0) {
        Swal.fire({
            title: 'Cannot Delete',
            html: 'This department has <strong>' + employeeCount + ' employee(s)</strong> assigned.<br><br>Please reassign or remove these employees before deleting the department.',
            icon: 'error',
            confirmButtonColor: '#556ee6'
        });
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this department?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#556ee6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: base_url + 'client/departments/delete',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj.status === 'success') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: obj.message,
                            icon: 'success',
                            confirmButtonColor: '#556ee6'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: obj.message,
                            icon: 'error',
                            confirmButtonColor: '#556ee6'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#556ee6'
                    });
                }
            });
        }
    });
}
</script>
