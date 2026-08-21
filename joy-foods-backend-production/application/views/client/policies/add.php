<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Add Policy</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/policies'); ?>">Policies</a></li>
                            <li class="breadcrumb-item active">Add Policy</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="add_policy" name="add_policy" action="<?php echo base_url('client/policies/store'); ?>" method="post">
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
                                        <input type="text" class="form-control" id="policy_code" name="policy_code" placeholder="e.g. POL-FREE-001" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Policy Name<code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Executive Free Lunch" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="policy_type">Policy Type<code>*</code></label>
                                        <select id="policy_type" class="form-control" name="policy_type" required>
                                            <option value="">Select Type</option>
                                            <option value="FREE">FREE - 100% Company Paid</option>
                                            <option value="PARTIAL">PARTIAL - Split Payment</option>
                                            <option value="PAID">PAID - 100% Employee Paid</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Policy description..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contribution Settings (for PARTIAL type) -->
                    <div class="card" id="contribution_card" style="display: none;">
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
                                                    <option value="PERCENTAGE">Percentage (%)</option>
                                                    <option value="FIXED_AMOUNT">Fixed Amount (INR)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="company_contribution_value">Value</label>
                                                <input type="number" class="form-control" id="company_contribution_value" name="company_contribution_value" placeholder="e.g. 50" min="0" step="0.01">
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
                                                    <option value="PERCENTAGE">Percentage (%)</option>
                                                    <option value="FIXED_AMOUNT">Fixed Amount (INR)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="employee_contribution_value">Value</label>
                                                <input type="number" class="form-control" id="employee_contribution_value" name="employee_contribution_value" placeholder="e.g. 50" min="0" step="0.01">
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
                                        <input type="number" class="form-control" id="daily_meal_limit" name="daily_meal_limit" placeholder="e.g. 1" min="0" value="1">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="weekly_meal_limit">Weekly Meal Limit</label>
                                        <input type="number" class="form-control" id="weekly_meal_limit" name="weekly_meal_limit" placeholder="e.g. 5" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="monthly_meal_limit">Monthly Meal Limit</label>
                                        <input type="number" class="form-control" id="monthly_meal_limit" name="monthly_meal_limit" placeholder="e.g. 22" min="0">
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
                                            <input class="form-check-input" type="checkbox" id="applies_to_premeal" name="applies_to_premeal" checked>
                                            <label class="form-check-label" for="applies_to_premeal">Premeal</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="applies_to_delivery" name="applies_to_delivery">
                                            <label class="form-check-label" for="applies_to_delivery">Delivery</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <h4 class="card-title">Meal Types Covered</h4>
                                    <div class="d-flex gap-4">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="breakfast_enabled" name="breakfast_enabled">
                                            <label class="form-check-label" for="breakfast_enabled">Breakfast</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="lunch_enabled" name="lunch_enabled" checked>
                                            <label class="form-check-label" for="lunch_enabled">Lunch</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="dinner_enabled" name="dinner_enabled">
                                            <label class="form-check-label" for="dinner_enabled">Dinner</label>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="snacks_enabled" name="snacks_enabled">
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
                                        <input type="number" class="form-control" id="advance_booking_days" name="advance_booking_days" placeholder="e.g. 7" min="0" value="7">
                                        <small class="text-muted">Days in advance for booking</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="booking_cutoff_hours">Booking Cutoff (Hours)</label>
                                        <input type="number" class="form-control" id="booking_cutoff_hours" name="booking_cutoff_hours" placeholder="e.g. 2" min="0" value="2">
                                        <small class="text-muted">Hours before meal time</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="cancellation_cutoff_hours">Cancellation Cutoff (Hours)</label>
                                        <input type="number" class="form-control" id="cancellation_cutoff_hours" name="cancellation_cutoff_hours" placeholder="e.g. 1" min="0" value="1">
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
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Add Policy</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>