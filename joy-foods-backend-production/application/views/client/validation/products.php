<script>
var productsDataTable = null;

$(document).ready(function() {
    // Initialize DataTable
    if ($('#datatable').length > 0) {
        productsDataTable = $('#datatable').DataTable({
            "responsive": true,
            "pageLength": 25,
            "order": [[0, "asc"]]
        });

        // Custom filter — reads data-category / data-modules from each <tr>
        $.fn.dataTable.ext.search.push(function(settings, searchData, dataIndex, rowData, counter) {
            if (settings.nTable.id !== 'datatable') return true;

            var row = settings.aoData[dataIndex].nTr;
            var cat = (row.getAttribute('data-category') || '').toLowerCase();
            var mods = (row.getAttribute('data-modules') || '').split(',');

            var fCat = ($('#filter_category').val() || '').toLowerCase();
            var fMod = $('#filter_module').val() || '';

            if (fCat && cat !== fCat) return false;
            if (fMod && mods.indexOf(fMod) === -1) return false;

            return true;
        });

        $('#filter_category, #filter_module').on('change', function() {
            productsDataTable.draw();
        });

        $('#filter_reset_btn').on('click', function() {
            $('#filter_category').val('');
            $('#filter_module').val('');
            productsDataTable.draw();
        });
    }

    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Delete product function
function deleteProduct(id) {
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
            $.ajax({
                url: base_url + 'client/products/delete',
                type: 'POST',
                data: { id: id },
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

// Toggle product status
function toggleStatus(id, newStatus) {
    var statusText = newStatus == 1 ? 'activate' : 'deactivate';

    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to ' + statusText + ' this product?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, ' + statusText + ' it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: base_url + 'client/products/toggle_status',
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
