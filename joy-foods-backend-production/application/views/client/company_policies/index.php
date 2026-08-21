<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Policies - <?php echo $company->name; ?></h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies/view/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                                <li class="breadcrumb-item active">Attached Policies</li>
                            </ol>
                        </nav>
                    </div>
                    <?php if (!empty($available_policies)): ?>
                    <a href="<?php echo base_url('client/companypolicies/attach/' . $company->id) ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Attach Policy
                    </a>
                    <?php endif; ?>
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
                                <p class="stat-label">Attached Policies</p>
                                <h3 class="stat-value"><?php echo count($attached_policies); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-file-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Total attached</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Available to Attach</p>
                                <h3 class="stat-value"><?php echo count($available_policies); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-plus-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Policies not yet attached</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Attached Policies</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Policy</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($attached_policies)) { $i = 1; foreach ($attached_policies as $cp) { ?>
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
                                                        <h6 class="mb-0">
                                                            <?php echo $cp->policy_name; ?>
                                                        </h6>
                                                        <small class="text-muted"><?php echo $cp->policy_code; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($cp->policy_type == 'FREE'): ?>
                                                    <span class="badge bg-success-subtle text-success">FREE</span>
                                                <?php elseif ($cp->policy_type == 'PARTIAL'): ?>
                                                    <span class="badge bg-warning-subtle text-warning">PARTIAL</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info-subtle text-info">PAID</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($cp->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('client/companypolicies/edit/' . $cp->id); ?>" class="btn btn-soft-info action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Detach" onclick="detachPolicy(<?php echo $cp->id; ?>)">
                                                        <i class="uil uil-times-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="uil uil-file-alt font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No policies attached to this company</p>
                                                <?php if (!empty($available_policies)): ?>
                                                <a href="<?php echo base_url('client/companypolicies/attach/' . $company->id); ?>" class="btn btn-primary btn-sm mt-2">
                                                    <i class="uil uil-plus me-1"></i> Attach Policy
                                                </a>
                                                <?php endif; ?>
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
