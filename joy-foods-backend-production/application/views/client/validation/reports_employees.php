<script>
var employeesDataTable = null;

$(document).ready(function() {
    loadEmployeesData();
});

function loadEmployeesData() {
    var filters = {
        company_id: $('#filter_company').val(),
        status: $('#filter_status').val()
    };

    $.ajax({
        url: base_url + 'client/reports/employees_data',
        type: 'POST',
        data: filters,
        beforeSend: function() {
            $('#employees_tbody').html('<tr><td colspan="8" class="text-center py-4"><i class="fa fa-spinner fa-spin me-1"></i> Loading...</td></tr>');
        },
        success: function(response) {
            var obj = JSON.parse(response);
            if (obj.status === 'success') {
                updateSummary(obj.summary);
                renderTable(obj.employees);
            }
        },
        error: function() {
            $('#employees_tbody').html('<tr><td colspan="8" class="text-center text-danger py-4">Failed to load data</td></tr>');
        }
    });
}

function updateSummary(summary) {
    $('#total_employees').text(summary.total_employees);
    $('#active_employees').text(summary.active_employees);
    $('#inactive_employees').text(summary.inactive_employees);
    $('#total_wallet_balance').html('&#8377;' + formatNumber(summary.total_wallet_balance));
}

function renderTable(employees) {
    // Destroy existing DataTable
    if (employeesDataTable) {
        employeesDataTable.destroy();
        employeesDataTable = null;
    }

    var html = '';
    if (employees.length === 0) {
        html = '<tr><td colspan="8" class="text-center py-4"><i class="uil uil-users-alt font-size-24 text-muted"></i><p class="text-muted mb-0 mt-2">No employees found</p></td></tr>';
        $('#employees_tbody').html(html);
        return;
    }

    $.each(employees, function(i, emp) {
        var fullName = emp.first_name + ' ' + (emp.last_name || '');
        var balance = parseFloat(emp.wallet_balance) || 0;
        var balanceClass = balance > 0 ? 'text-success' : (balance < 0 ? 'text-danger' : 'text-muted');

        var modules = [];
        if (emp.qsr_access == 1) modules.push('<span class="badge bg-primary-subtle text-primary">QSR</span>');
        if (emp.premeal_access == 1) modules.push('<span class="badge bg-info-subtle text-info">Premeal</span>');
        if (emp.kot_permission == 1) modules.push('<span class="badge bg-warning-subtle text-warning">KOT</span>');

        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td>';
        html += '<div><strong>' + fullName + '</strong></div>';
        html += '<small class="text-muted">' + (emp.employee_code || '-') + '</small>';
        html += '</td>';
        html += '<td>';
        html += '<div>' + emp.company_name + '</div>';
        html += '<small class="text-muted">' + emp.company_code + '</small>';
        html += '</td>';
        html += '<td>' + (emp.department_name || '-') + '</td>';
        html += '<td>';
        html += '<div>' + (emp.email || '-') + '</div>';
        html += '<small class="text-muted">' + (emp.phone || '-') + '</small>';
        html += '</td>';
        html += '<td>' + (modules.length > 0 ? modules.join(' ') : '<span class="text-muted">-</span>') + '</td>';
        html += '<td class="' + balanceClass + ' fw-medium">&#8377;' + formatNumber(balance) + '</td>';
        html += '<td>';
        if (emp.is_active == 1) {
            html += '<span class="badge bg-success-subtle text-success">Active</span>';
        } else {
            html += '<span class="badge bg-danger-subtle text-danger">Inactive</span>';
        }
        html += '</td>';
        html += '</tr>';
    });

    $('#employees_tbody').html(html);

    // Reinitialize DataTable
    employeesDataTable = $('#employees_table').DataTable({
        responsive: true,
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', className: 'btn btn-sm btn-soft-success', text: '<i class="uil uil-file-alt me-1"></i> Excel' },
            { extend: 'csv', className: 'btn btn-sm btn-soft-info', text: '<i class="uil uil-file me-1"></i> CSV' },
            { extend: 'print', className: 'btn btn-sm btn-soft-primary', text: '<i class="uil uil-print me-1"></i> Print' }
        ],
        pageLength: 25
    });
}

function resetFilters() {
    $('#filter_company').val('').trigger('change');
    $('#filter_status').val('').trigger('change');
    loadEmployeesData();
}

function formatNumber(num) {
    return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
