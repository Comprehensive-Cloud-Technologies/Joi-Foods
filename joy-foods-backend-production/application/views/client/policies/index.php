<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Policy Management</h4>
                    <a href="<?php echo base_url('client/policies/add') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Policy
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
                                <p class="stat-label">Total Policies</p>
                                <h3 class="stat-value"><?php echo count($policies); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All created policies</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Free Policies</p>
                                <?php $free_count = count(array_filter($policies, function($p) { return $p->policy_type == 'FREE'; })); ?>
                                <h3 class="stat-value"><?php echo $free_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-gift"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-success-subtle text-success">
                            <i class="mdi mdi-check"></i> 100% Company Paid
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Partial Policies</p>
                                <?php $partial_count = count(array_filter($policies, function($p) { return $p->policy_type == 'PARTIAL'; })); ?>
                                <h3 class="stat-value"><?php echo $partial_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-percentage"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-warning-subtle text-warning">
                            <i class="mdi mdi-swap-horizontal"></i> Split Payment
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Paid Policies</p>
                                <?php $paid_count = count(array_filter($policies, function($p) { return $p->policy_type == 'PAID'; })); ?>
                                <h3 class="stat-value"><?php echo $paid_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-wallet"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-info-subtle text-info">
                            <i class="mdi mdi-account-cash"></i> 100% Employee Paid
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
                        <h5 class="card-title mb-4">Policies List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Policy</th>
                                        <th>Type</th>
                                        <th>Contribution</th>
                                        <th>Limits</th>
                                        <th>Modules</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($policies)) { $i = 1; foreach ($policies as $policy) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                            <i class="uil uil-file-alt font-size-16"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $policy->name; ?></h6>
                                                        <small class="text-muted"><?php echo $policy->policy_code; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($policy->policy_type == 'FREE'): ?>
                                                    <span class="badge bg-success-subtle text-success">FREE</span>
                                                <?php elseif ($policy->policy_type == 'PARTIAL'): ?>
                                                    <span class="badge bg-warning-subtle text-warning">PARTIAL</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info-subtle text-info">PAID</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($policy->policy_type == 'PARTIAL'): ?>
                                                    <div><small><strong>Company:</strong> <?php echo $policy->company_contribution_value . ($policy->company_contribution_type == 'PERCENTAGE' ? '%' : ' INR'); ?></small></div>
                                                    <div><small><strong>Employee:</strong> <?php echo $policy->employee_contribution_value . ($policy->employee_contribution_type == 'PERCENTAGE' ? '%' : ' INR'); ?></small></div>
                                                <?php elseif ($policy->policy_type == 'FREE'): ?>
                                                    <small class="text-success">100% Company</small>
                                                <?php else: ?>
                                                    <small class="text-info">100% Employee</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><small><strong>Daily:</strong> <?php echo $policy->daily_meal_limit ?: '-'; ?></small></div>
                                                <div><small><strong>Monthly:</strong> <?php echo $policy->monthly_meal_limit ?: '-'; ?></small></div>
                                            </td>
                                            <td>
                                                <?php if ($policy->applies_to_qsr): ?>
                                                    <span class="badge bg-soft-secondary text-secondary me-1">QSR</span>
                                                <?php endif; ?>
                                                <?php if ($policy->applies_to_premeal): ?>
                                                    <span class="badge bg-soft-secondary text-secondary me-1">Premeal</span>
                                                <?php endif; ?>
                                                <?php if ($policy->applies_to_delivery): ?>
                                                    <span class="badge bg-soft-secondary text-secondary">Delivery</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($policy->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                                <?php if ($policy->is_default == 1): ?>
                                                    <span class="badge bg-primary-subtle text-primary">Default</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('client/policies/view/' . $policy->id); ?>" class="btn btn-soft-info action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                        <i class="uil uil-eye"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/policies/edit/' . $policy->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deletePolicy(<?php echo $policy->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="uil uil-file-alt font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No policies found</p>
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
