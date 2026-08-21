<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Company Management</h4>
                    <a href="<?php echo base_url('client/companies/add') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Company
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Companies</p>
                                <h3 class="stat-value"><?php echo count($companies); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-building"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All registered companies</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Companies</p>
                                <?php $active_count = count(array_filter($companies, function($c) { return $c->is_active == 1; })); ?>
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
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Employees</p>
                                <?php $total_employees = array_sum(array_column($companies, 'employee_count')); ?>
                                <h3 class="stat-value"><?php echo $total_employees; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Across all companies</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Inactive Companies</p>
                                <?php $inactive_count = count(array_filter($companies, function($c) { return $c->is_active == 0; })); ?>
                                <h3 class="stat-value"><?php echo $inactive_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-warning-subtle text-warning">
                            <i class="mdi mdi-pause"></i> Inactive
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Companies List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Company</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Modules</th>
                                        <th>Stats</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($companies)) { $i = 1; foreach ($companies as $company) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                            <i class="uil uil-building font-size-16"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $company->name; ?></h6>
                                                        <small class="text-muted"><?php echo $company->company_code; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="uil uil-envelope text-muted me-1"></i>
                                                    <small><?php echo $company->primary_email; ?></small>
                                                </div>
                                                <div>
                                                    <i class="uil uil-phone text-muted me-1"></i>
                                                    <small><?php echo $company->primary_phone ?: '-'; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="uil uil-map-marker text-muted me-1"></i>
                                                <?php echo ($company->city ? $company->city . ', ' : '') . ($company->state ?: '-'); ?>
                                            </td>
                                            <td>
                                                <?php if ($company->qsr_enabled): ?>
                                                    <span class="badge bg-soft-secondary text-secondary me-1">QSR</span>
                                                <?php endif; ?>
                                                <?php if ($company->premeal_enabled): ?>
                                                    <span class="badge bg-soft-secondary text-secondary me-1">Premeal</span>
                                                <?php endif; ?>
                                                <?php if ($company->delivery_enabled): ?>
                                                    <span class="badge bg-soft-secondary text-secondary">Delivery</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><small><i class="uil uil-users-alt text-muted me-1"></i> <?php echo $company->employee_count; ?> Employees</small></div>
                                                <div><small><i class="uil uil-sitemap text-muted me-1"></i> <?php echo $company->department_count; ?> Departments</small></div>
                                            </td>
                                            <td>
                                                <?php if ($company->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('client/companies/view/' . $company->id); ?>" class="btn btn-soft-info action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                        <i class="uil uil-eye"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/departments/' . $company->id); ?>" class="btn btn-soft-primary action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Departments">
                                                        <i class="uil uil-sitemap"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/companypolicies/' . $company->id); ?>" class="btn btn-soft-success action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Policies">
                                                        <i class="uil uil-file-alt"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/companyusers/' . $company->id); ?>" class="btn btn-soft-secondary action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Users">
                                                        <i class="uil uil-user"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/companies/edit/' . $company->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteCompany(<?php echo $company->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="uil uil-building font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No companies found</p>
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
