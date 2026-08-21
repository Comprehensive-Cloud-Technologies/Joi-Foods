<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Coupon Management</h4>
                    <a href="<?php echo base_url('client/coupons/add') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add Coupon
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
                                <p class="stat-label">Total Coupons</p>
                                <h3 class="stat-value"><?php echo $stats['total']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-ticket"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All created coupons</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Coupons</p>
                                <h3 class="stat-value"><?php echo $stats['active']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-success-subtle text-success">
                            <i class="mdi mdi-check"></i> Currently Active
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Percentage Discount</p>
                                <h3 class="stat-value"><?php echo $stats['percentage']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-percentage"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-warning-subtle text-warning">
                            <i class="mdi mdi-percent"></i> Percentage Based
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Fixed Discount</p>
                                <h3 class="stat-value"><?php echo $stats['fixed']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-rupee-sign"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-info-subtle text-info">
                            <i class="mdi mdi-currency-inr"></i> Fixed Amount
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
                        <h5 class="card-title mb-4">Coupons List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Coupon</th>
                                        <th>Discount</th>
                                        <th>Validity</th>
                                        <th>Limits</th>
                                        <th>Modules</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($coupons)) { $i = 1; foreach ($coupons as $coupon) { ?>
                                        <?php
                                        $now = date('Y-m-d H:i:s');
                                        $is_expired = ($coupon->valid_until !== null && $coupon->valid_until < $now);
                                        ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                            <i class="uil uil-ticket font-size-16"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $coupon->name; ?></h6>
                                                        <small class="text-muted"><code><?php echo $coupon->code; ?></code></small>
                                                        <?php if ($coupon->company_name): ?>
                                                            <br><small class="text-info"><?php echo $coupon->company_name; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($coupon->discount_type == 'PERCENTAGE'): ?>
                                                    <span class="badge bg-warning-subtle text-warning"><?php echo $coupon->discount_value; ?>%</span>
                                                    <?php if ($coupon->max_discount_amount): ?>
                                                        <br><small class="text-muted">Max: ₹<?php echo $coupon->max_discount_amount; ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success">₹<?php echo $coupon->discount_value; ?></span>
                                                <?php endif; ?>
                                                <?php if ($coupon->min_order_amount > 0): ?>
                                                    <br><small class="text-muted">Min: ₹<?php echo $coupon->min_order_amount; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><small><strong>From:</strong> <?php echo date('d M Y', strtotime($coupon->valid_from)); ?></small></div>
                                                <?php if ($coupon->valid_until): ?>
                                                    <div><small><strong>Until:</strong> <?php echo date('d M Y', strtotime($coupon->valid_until)); ?></small></div>
                                                <?php else: ?>
                                                    <div><small class="text-success">No Expiry</small></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><small><strong>Total:</strong> <?php echo $coupon->usage_limit ?: 'Unlimited'; ?></small></div>
                                                <div><small><strong>Per User:</strong> <?php echo $coupon->per_user_limit; ?></small></div>
                                                <div><small><strong>Used:</strong> <?php echo $coupon->usage_count; ?></small></div>
                                            </td>
                                            <td>
                                                <?php if ($coupon->applies_to_qsr): ?>
                                                    <span class="badge bg-soft-secondary text-secondary me-1">QSR</span>
                                                <?php endif; ?>
                                                <?php if ($coupon->applies_to_kot): ?>
                                                    <span class="badge bg-soft-secondary text-secondary me-1">KOT</span>
                                                <?php endif; ?>
                                                <?php if ($coupon->applies_to_premeal): ?>
                                                    <span class="badge bg-soft-secondary text-secondary">Premeal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($is_expired): ?>
                                                    <span class="badge bg-danger-subtle text-danger">Expired</span>
                                                <?php elseif ($coupon->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('client/coupons/edit/' . $coupon->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <?php if ($coupon->is_active == 1): ?>
                                                        <button type="button" class="btn btn-soft-secondary action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Deactivate" onclick="toggleStatus(<?php echo $coupon->id; ?>, 0)">
                                                            <i class="uil uil-toggle-off"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-soft-success action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Activate" onclick="toggleStatus(<?php echo $coupon->id; ?>, 1)">
                                                            <i class="uil uil-toggle-on"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteCoupon(<?php echo $coupon->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="uil uil-ticket font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No coupons found</p>
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
