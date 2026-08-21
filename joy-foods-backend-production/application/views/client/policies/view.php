<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">View Policy</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/policies'); ?>">Policies</a></li>
                            <li class="breadcrumb-item active">View Policy</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-8">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Policy Details</h4>
                            <div>
                                <?php if ($policy->policy_type == 'FREE'): ?>
                                    <span class="badge bg-success fs-6">FREE</span>
                                <?php elseif ($policy->policy_type == 'PARTIAL'): ?>
                                    <span class="badge bg-warning fs-6">PARTIAL</span>
                                <?php else: ?>
                                    <span class="badge bg-info fs-6">PAID</span>
                                <?php endif; ?>
                                <?php if ($policy->is_active): ?>
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                <?php endif; ?>
                                <?php if ($policy->is_default): ?>
                                    <span class="badge bg-primary-subtle text-primary">Default</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Policy Code</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->policy_code; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Policy Name</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->name; ?></p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="text-muted small">Description</label>
                                    <p class="mb-0"><?php echo $policy->description ?: '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contribution Details -->
                <?php if ($policy->policy_type == 'PARTIAL'): ?>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Contribution Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="text-primary"><i class="uil uil-building me-1"></i> Company Contribution</h6>
                                    <h3 class="mb-0">
                                        <?php echo $policy->company_contribution_value; ?>
                                        <?php echo $policy->company_contribution_type == 'PERCENTAGE' ? '%' : ' INR'; ?>
                                    </h3>
                                    <small class="text-muted"><?php echo $policy->company_contribution_type == 'PERCENTAGE' ? 'Percentage' : 'Fixed Amount'; ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="text-info"><i class="uil uil-user me-1"></i> Employee Contribution</h6>
                                    <h3 class="mb-0">
                                        <?php echo $policy->employee_contribution_value; ?>
                                        <?php echo $policy->employee_contribution_type == 'PERCENTAGE' ? '%' : ' INR'; ?>
                                    </h3>
                                    <small class="text-muted"><?php echo $policy->employee_contribution_type == 'PERCENTAGE' ? 'Percentage' : 'Fixed Amount'; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Limits & Restrictions -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Limits & Restrictions</h4>
                        <div class="row">
                           
                            <div class="col-md-4 col-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Daily Limit</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->daily_meal_limit ?: '-'; ?> meals</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Weekly Limit</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->weekly_meal_limit ?: '-'; ?> meals</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Monthly Limit</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->monthly_meal_limit ?: '-'; ?> meals</p>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Module & Meal Access -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Module & Meal Access</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Modules</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge <?php echo $policy->applies_to_premeal ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-calendar-alt me-1"></i> Premeal
                                    </span>
                                    <span class="badge <?php echo $policy->applies_to_delivery ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-truck me-1"></i> Delivery
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Meal Types</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge <?php echo $policy->breakfast_enabled ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-coffee me-1"></i> Breakfast
                                    </span>
                                    <span class="badge <?php echo $policy->lunch_enabled ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-restaurant me-1"></i> Lunch
                                    </span>
                                    <span class="badge <?php echo $policy->dinner_enabled ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-moon me-1"></i> Dinner
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Settings -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Booking Settings</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Advance Booking Days</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->advance_booking_days; ?> days</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Booking Cutoff</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->booking_cutoff_hours; ?> hours</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Cancellation Cutoff</label>
                                    <p class="mb-0 fw-medium"><?php echo $policy->cancellation_cutoff_hours; ?> hours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <!-- Usage Stats -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Usage Statistics</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                    <i class="uil uil-building"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['companies']; ?></h5>
                                <small class="text-muted">Companies Assigned</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-success rounded-circle text-success">
                                    <i class="uil uil-users-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['employees']; ?></h5>
                                <small class="text-muted">Employees Assigned</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attached Companies -->
                <?php if (!empty($attached_companies)): ?>
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Attached Companies</h4>
                        <div class="list-group list-group-flush">
                            <?php foreach ($attached_companies as $company): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                            <i class="uil uil-building font-size-12"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo $company->name; ?></h6>
                                        <small class="text-muted"><?php echo $company->company_code; ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Actions</h4>
                        <div class="d-grid gap-2">
                            <a href="<?php echo base_url('client/policies/edit/' . $policy->id); ?>" class="btn btn-primary">
                                <i class="uil uil-pen me-1"></i> Edit Policy
                            </a>
                            <a href="<?php echo base_url('client/policies'); ?>" class="btn btn-outline-secondary">
                                <i class="uil uil-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Meta Information</h4>
                        <div class="mb-2">
                            <small class="text-muted">Created At</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($policy->created_at)); ?></p>
                        </div>
                        <?php if ($policy->updated_at): ?>
                        <div class="mb-0">
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($policy->updated_at)); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
