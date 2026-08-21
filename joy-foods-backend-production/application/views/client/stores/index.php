<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Store Management</h4>
                    <a href="<?php echo base_url('client/stores/add') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Store
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
                                <p class="stat-label">Total Stores</p>
                                <h3 class="stat-value"><?php echo count($stores); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-store"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All registered stores</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Stores</p>
                                <?php $active_count = count(array_filter($stores, function($s) { return $s->is_active == 1; })); ?>
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
                                <p class="stat-label">Total Staff</p>
                                <?php $total_staff = array_sum(array_column($stores, 'staff_count')); ?>
                                <h3 class="stat-value"><?php echo $total_staff; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Across all stores</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Operational</p>
                                <?php $operational_count = count(array_filter($stores, function($s) { return $s->is_operational == 1; })); ?>
                                <h3 class="stat-value"><?php echo $operational_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-power"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-warning-subtle text-warning">
                            <i class="mdi mdi-power"></i> Operational
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
                        <h5 class="card-title mb-4">Stores List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Store</th>
                                        <th class="filters">Company</th>
                                        <th>Type</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>Staff</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($stores)) { $i = 1; foreach ($stores as $store) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($store->thumbnail)): ?>
                                                        <div class="avatar-sm me-3">
                                                            <img src="<?php echo base_url($store->thumbnail); ?>" alt="<?php echo $store->name; ?>" class="rounded-circle img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                                <i class="uil uil-store font-size-16"></i>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $store->name; ?></h6>
                                                        <small class="text-muted"><?php echo $store->store_code; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($store->company_name): ?>
                                                    <div>
                                                        <?php echo $store->company_name; ?>
                                                        <br><small class="text-muted"><?php echo $store->company_code; ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $type_badges = [
                                                    'QSR' => 'bg-soft-info text-info',
                                                    'KOT' => 'bg-soft-warning text-warning',
                                                    'PREMEAL' => 'bg-soft-success text-success'
                                                ];
                                                $badge_class = $type_badges[$store->store_type] ?? 'bg-soft-secondary text-secondary';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $store->store_type; ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="uil uil-envelope text-muted me-1"></i>
                                                    <small><?php echo $store->primary_email ?: '-'; ?></small>
                                                </div>
                                                <div>
                                                    <i class="uil uil-phone text-muted me-1"></i>
                                                    <small><?php echo $store->primary_phone ?: '-'; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="uil uil-map-marker text-muted me-1"></i>
                                                <?php echo ($store->city ? $store->city . ', ' : '') . ($store->state ?: '-'); ?>
                                            </td>
                                            <td>
                                                <div><small><i class="uil uil-users-alt text-muted me-1"></i> <?php echo $store->staff_count; ?> Staff</small></div>
                                            </td>
                                            <td>
                                                <?php if ($store->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                                <?php if ($store->is_operational == 1): ?>
                                                    <br><span class="badge bg-warning-subtle text-warning mt-1">Operational</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('client/stores/view/' . $store->id); ?>" class="btn btn-soft-info action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="View">
                                                        <i class="uil uil-eye"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/stores/staff/' . $store->id); ?>" class="btn btn-soft-success action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Manage Staff">
                                                        <i class="uil uil-users-alt"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('client/stores/edit/' . $store->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteStore(<?php echo $store->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="uil uil-store font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No stores found</p>
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
