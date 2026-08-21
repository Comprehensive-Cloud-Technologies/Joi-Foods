<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Departments Management</h4>
                    </div>
                    <a href="<?php echo base_url('company/departments/add/') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Department
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Departments</p>
                                <h3 class="stat-value"><?php echo count($departments); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-sitemap"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All departments</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Departments</p>
                                <?php $active_count = count(array_filter($departments, function ($d) {
                                    return $d->is_active == 1;
                                })); ?>
                                <h3 class="stat-value"><?php echo $active_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-success-subtle text-success">
                            <i class="mdi mdi-check"></i> Active
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Employees</p>
                                <?php $total_employees = array_sum(array_column($departments, 'employee_count')); ?>
                                <h3 class="stat-value"><?php echo $total_employees; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Across all departments</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Departments List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Department</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Employees</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($departments)) {
                                        $i = 1;
                                        foreach ($departments as $department) { ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                                <i class="uil uil-sitemap font-size-16"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo $department->name; ?></h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($department->code): ?>
                                                        <code><?php echo $department->code; ?></code>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $department->description ? substr($department->description, 0, 50) . (strlen($department->description) > 50 ? '...' : '') : '-'; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info">
                                                        <i class="uil uil-users-alt me-1"></i><?php echo $department->employee_count; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($department->is_active == 1): ?>
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="<?php echo base_url('company/departments/edit/' . $department->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                            <i class="uil uil-pen"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteDepartment(<?php echo $department->id; ?>, <?php echo $department->employee_count; ?>)">
                                                            <i class="uil uil-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="uil uil-sitemap font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No departments found</p>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>