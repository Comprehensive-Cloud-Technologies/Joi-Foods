<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Client Management</h4>
                    <a href="<?php echo base_url('admin_root/clients/add') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Client
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
                                <p class="stat-label">Total Clients</p>
                                <h3 class="stat-value"><?php echo count($clients); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All registered clients</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Clients</p>
                                <?php $active_count = count(array_filter($clients, function($c) { return $c->is_active == 1; })); ?>
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
                                <p class="stat-label">Verified Clients</p>
                                <?php $verified_count = count(array_filter($clients, function($c) { return $c->is_verified == 1; })); ?>
                                <h3 class="stat-value"><?php echo $verified_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-shield-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Verified accounts</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Inactive Clients</p>
                                <?php $inactive_count = count(array_filter($clients, function($c) { return $c->is_active == 0; })); ?>
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
                        <h5 class="card-title mb-4">Clients List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Client</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th>GST/PAN</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($clients)) { $i = 1; foreach ($clients as $client) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                            <i class="uil uil-store font-size-16"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $client->name; ?></h6>
                                                        <small class="text-muted"><?php echo $client->client_code; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <i class="uil uil-envelope text-muted me-1"></i>
                                                    <small><?php echo $client->email; ?></small>
                                                </div>
                                                <div>
                                                    <i class="uil uil-phone text-muted me-1"></i>
                                                    <small><?php echo $client->phone ?: '-'; ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="uil uil-map-marker text-muted me-1"></i>
                                                <?php echo ($client->city ? $client->city . ', ' : '') . ($client->state ?: '-'); ?>
                                            </td>
                                            <td>
                                                <div><small><strong>GST:</strong> <?php echo $client->gst_number ?: '-'; ?></small></div>
                                                <div><small><strong>PAN:</strong> <?php echo $client->pan_number ?: '-'; ?></small></div>
                                            </td>
                                            <td>
                                                <?php if ($client->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                                <?php if ($client->is_verified == 1): ?>
                                                    <span class="badge bg-info-subtle text-info">Verified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('admin_root/clients/view/' . $client->id); ?>" class="btn btn-soft-info action-btn" title="View">
                                                        <i class="uil uil-eye"></i>
                                                    </a>
                                                    <a href="<?php echo base_url('admin_root/clients/edit/' . $client->id); ?>" class="btn btn-soft-warning action-btn" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" onclick="deleteClient(<?php echo $client->id; ?>)" title="Delete">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="uil uil-users-alt font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No clients found</p>
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
