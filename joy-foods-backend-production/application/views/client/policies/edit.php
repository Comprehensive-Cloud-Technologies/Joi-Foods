<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Policy</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/policies'); ?>">Policies</a></li>
                            <li class="breadcrumb-item active">Edit Policy</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <?php if ($usage['companies'] > 0 || $usage['employees'] > 0): ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="uil uil-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This policy is currently assigned to
                        <strong><?php echo $usage['companies']; ?></strong> company(ies) and
                        <strong><?php echo $usage['employees']; ?></strong> employee(s).
                        Changes will affect all assignments.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="needs-validation" id="edit_policy" name="edit_policy" action="<?php echo base_url('client/policies/update'); ?>" method="post">
            <input type="hidden" name="policy_id" value="<?php echo $policy->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="policy_code">Policy Code<code>*</code></label>
                                        <input type="text" class="form-control" id="policy_code" name="policy_code" placeholder="e.g. POL-FREE-001" value="<?php echo $policy->policy_code; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Policy Name<code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Executive Free Lunch" value="<?php echo $policy->name; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="policy_type">Policy Type<code>*</code></label>
                                        <select id="policy_type" class="form-control" name="policy_type" required>
                                            <option value="">Select Type</option>
                                            <option value="FREE" <?php echo $policy->policy_type == 'FREE' ? 'selected' : ''; ?>>FREE - 100% Company Paid</option>
                                            <option value="PARTIAL" <?php echo $policy->policy_type == 'PARTIAL' ? 'selected' : ''; ?>>PARTIAL - Split Payment</option>
                                            <option value="PAID" <?php echo $policy->policy_type == 'PAID' ? 'selected' : ''; ?>>PAID - 100% Employee Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Policy description..."><?php echo $policy->description; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contribution Settings (for PARTIAL type) -->
                    <div class="card" id="contribution_card" style="<?php echo $policy->policy_type == 'PARTIAL' ? '' : 'display: none;'; ?>">
                        <div class="card-body">
                            <h4 class="card-title">Contribution Settings</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">Company Contribution</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="company_contribution_type">Type</label>
                                                <select id="company_contribution_type" class="form-control" name="company_contribution_type">
                                                    <option value="PERCENTAGE" <?php echo $policy->company_contribution_type == 'PERCENTAGE' ? 'selected' : ''; ?>>Percentage (%)</option>
                                                    <option value="FIXED_AMOUNT" <?php echo $policy->company_contribution_type == 'FIXED_AMOUNT' ? 'selected' : ''; ?>>Fixed Amount (INR)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="company_contribution_value">Value</label>
                                                <input type="number" class="form-control" id="company_contribution_value" name="company_contribution_value" placeholder="e.g. 50" min="0" step="0.01" value="<?php echo $policy->company_contribution_value; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-info mb-3">Employee Contribution</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="employee_contribution_type">Type</label>
                                                <select id="employee_contribution_type" class="form-control" name="employee_contribution_type">
                                                    <option value="PERCENTAGE" <?php echo $policy->employee_contribution_type == 'PERCENTAGE' ? 'selected' : ''; ?>>Percentage (%)</option>
                                                    <option value="FIXED_AMOUNT" <?php echo $policy->employee_contribution_type == 'FIXED_AMOUNT' ? 'selected' : ''; ?>>Fixed Amount (INR)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="employee_contribution_value">Value</label>
                                                <input type="number" class="form-control" id="employee_contribution_value" name="employee_contribution_value" placeholder="e.g. 50" min="0" step="0.01" value="<?php echo $policy->employee_contribution_value; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Limits -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Limits & Restrictions</h4>
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="daily_meal_limit">Daily Meal Limit</label>
                                        <input type="number" class="form-control" id="daily_meal_limit" name="daily_meal_limit" placeholder="e.g. 1" min="0" value="<?php echo $policy->daily_meal_limit; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="weekly_meal_limit">Weekly Meal Limit</label>
                                        <input type="number" class="form-control" id="weekly_meal_limit" name="weekly_meal_limit" placeholder="e.g. 5" min="0" value="<?php echo $policy->weekly_meal_limit; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="monthly_meal_limit">Monthly Meal Limit</label>
                                        <input type="number" class="form-control" id="monthly_meal_limit" name="monthly_meal_limit" placeholder="e.g. 22" min="0" value="<?php echo $policy->monthly_meal_limit; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Access & Meal Types -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h4 class="card-title">Module Access</h4>
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="applies_to_premeal" name="applies_to_premeal" <?php echo $policy->applies_to_premeal ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="applies_to_premeal">Premeal</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="applies_to_delivery" name="applies_to_delivery" <?php echo $policy->applies_to_delivery ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="applies_to_delivery">Delivery</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h4 class="card-title">Meal Types Covered</h4>
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="breakfast_enabled" name="breakfast_enabled" <?php echo $policy->breakfast_enabled ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="breakfast_enabled">Breakfast</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="lunch_enabled" name="lunch_enabled" <?php echo $policy->lunch_enabled ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="lunch_enabled">Lunch</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="dinner_enabled" name="dinner_enabled" <?php echo $policy->dinner_enabled ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="dinner_enabled">Dinner</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="snacks_enabled" name="snacks_enabled" <?php echo $policy->snacks_enabled ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="snacks_enabled">Snacks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Settings -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Booking Settings</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="advance_booking_days">Advance Booking Days</label>
                                        <input type="number" class="form-control" id="advance_booking_days" name="advance_booking_days" placeholder="e.g. 7" min="0" value="<?php echo $policy->advance_booking_days; ?>">
                                        <small class="text-muted">Days in advance for booking</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="booking_cutoff_hours">Booking Cutoff (Hours)</label>
                                        <input type="number" class="form-control" id="booking_cutoff_hours" name="booking_cutoff_hours" placeholder="e.g. 2" min="0" value="<?php echo $policy->booking_cutoff_hours; ?>">
                                        <small class="text-muted">Hours before meal time</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="cancellation_cutoff_hours">Cancellation Cutoff (Hours)</label>
                                        <input type="number" class="form-control" id="cancellation_cutoff_hours" name="cancellation_cutoff_hours" placeholder="e.g. 1" min="0" value="<?php echo $policy->cancellation_cutoff_hours; ?>">
                                        <small class="text-muted">Hours before meal for cancellation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Status</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="is_active">Status</label>
                                        <select id="is_active" class="form-control" name="is_active">
                                            <option value="1" <?php echo $policy->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $policy->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update Policy</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>