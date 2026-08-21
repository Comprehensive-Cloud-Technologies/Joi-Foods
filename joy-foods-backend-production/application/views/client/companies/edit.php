<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Company</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item active">Edit Company</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <?php if ($usage['employees'] > 0 || $usage['departments'] > 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="uil uil-info-circle me-2"></i>
                    <strong>Info:</strong> This company has
                    <strong><?php echo $usage['employees']; ?></strong> employee(s),
                    <strong><?php echo $usage['departments']; ?></strong> department(s),
                    <strong><?php echo $usage['policies']; ?></strong> policy(ies) attached.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form class="needs-validation" id="edit_company" name="edit_company" action="<?php echo base_url('client/companies/update'); ?>" method="post">
            <input type="hidden" name="company_id" value="<?php echo $company->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="company_code">Company Code<code>*</code></label>
                                        <input type="text" class="form-control text-uppercase" id="company_code" name="company_code" placeholder="e.g. TCS-BLR" value="<?php echo $company->company_code; ?>" required>
                                        <small class="text-muted">Unique code for employee login</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Company Name<code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. TCS - Bangalore" value="<?php echo $company->name; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="legal_name">Legal Name</label>
                                        <input type="text" class="form-control" id="legal_name" name="legal_name" placeholder="Registered Legal Name" value="<?php echo $company->legal_name; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="short_name">Short Name</label>
                                        <input type="text" class="form-control" id="short_name" name="short_name" placeholder="e.g. TCS BLR" value="<?php echo $company->short_name; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Contact Information</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="primary_email">Primary Email<code>*</code></label>
                                        <input type="email" class="form-control" id="primary_email" name="primary_email" placeholder="hr@company.com" value="<?php echo $company->primary_email; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="secondary_email">Secondary Email</label>
                                        <input type="email" class="form-control" id="secondary_email" name="secondary_email" placeholder="admin@company.com" value="<?php echo $company->secondary_email; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="primary_phone">Primary Phone</label>
                                        <input type="text" class="form-control" id="primary_phone" name="primary_phone" placeholder="Phone Number" value="<?php echo $company->primary_phone; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="secondary_phone">Secondary Phone</label>
                                        <input type="text" class="form-control" id="secondary_phone" name="secondary_phone" placeholder="Alternate Phone" value="<?php echo $company->secondary_phone; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Address Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="address_line1">Address Line 1</label>
                                        <input type="text" class="form-control" id="address_line1" name="address_line1" placeholder="Street Address" value="<?php echo $company->address_line1; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="address_line2">Address Line 2</label>
                                        <input type="text" class="form-control" id="address_line2" name="address_line2" placeholder="Building, Floor, etc." value="<?php echo $company->address_line2; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city" placeholder="City" value="<?php echo $company->city; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" placeholder="State" value="<?php echo $company->state; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" placeholder="Country" value="<?php echo $company->country; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="pincode">Pincode</label>
                                        <input type="text" class="form-control" id="pincode" name="pincode" placeholder="Pincode" value="<?php echo $company->pincode; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Details -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Business Details</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="gst_number">GST Number</label>
                                        <input type="text" class="form-control" id="gst_number" name="gst_number" placeholder="e.g. 22AAAAA0000A1Z5" value="<?php echo $company->gst_number; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="pan_number">PAN Number</label>
                                        <input type="text" class="form-control" id="pan_number" name="pan_number" placeholder="e.g. AAAAA0000A" value="<?php echo $company->pan_number; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="cin_number">CIN Number</label>
                                        <input type="text" class="form-control" id="cin_number" name="cin_number" placeholder="Corporate Identity Number" value="<?php echo $company->cin_number; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Details -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Contract Details</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="contract_start_date">Contract Start Date</label>
                                        <input type="text" class="form-control datepicker-init" id="contract_start_date" name="contract_start_date" placeholder="Select date" value="<?php echo $company->contract_start_date; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="contract_end_date">Contract End Date</label>
                                        <input type="text" class="form-control datepicker-init" id="contract_end_date" name="contract_end_date" placeholder="Select date" value="<?php echo $company->contract_end_date; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="billing_cycle">Billing Cycle</label>
                                        <select id="billing_cycle" class="form-control select2" name="billing_cycle">
                                            <option value="WEEKLY" <?php echo $company->billing_cycle == 'WEEKLY' ? 'selected' : ''; ?>>Weekly</option>
                                            <option value="BIWEEKLY" <?php echo $company->billing_cycle == 'BIWEEKLY' ? 'selected' : ''; ?>>Bi-Weekly</option>
                                            <option value="MONTHLY" <?php echo $company->billing_cycle == 'MONTHLY' ? 'selected' : ''; ?>>Monthly</option>
                                            <option value="QUARTERLY" <?php echo $company->billing_cycle == 'QUARTERLY' ? 'selected' : ''; ?>>Quarterly</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="payment_terms_days">Payment Terms (Days)</label>
                                        <input type="number" class="form-control" id="payment_terms_days" name="payment_terms_days" placeholder="e.g. 30" value="<?php echo $company->payment_terms_days; ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="max_employees">Max Employees</label>
                                        <input type="number" class="form-control" id="max_employees" name="max_employees" placeholder="Leave empty for unlimited" value="<?php echo $company->max_employees; ?>" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Access -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Module Access</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="qsr_enabled" name="qsr_enabled" <?php echo $company->qsr_enabled ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="qsr_enabled">
                                            <strong>QSR Module</strong>
                                            <br><small class="text-muted">Quick Service Restaurant ordering</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="premeal_enabled" name="premeal_enabled" <?php echo $company->premeal_enabled ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="premeal_enabled">
                                            <strong>Premeal Module</strong>
                                            <br><small class="text-muted">Advance meal booking</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="delivery_enabled" name="delivery_enabled" <?php echo $company->delivery_enabled ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="delivery_enabled">
                                            <strong>KOT Module</strong>
                                            <br><small class="text-muted">Pantry delivery</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Booking Settings -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Guest Booking Settings</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="allow_guest_booking" name="allow_guest_booking" <?php echo $company->allow_guest_booking ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="allow_guest_booking">
                                            <strong>Allow Guest Booking</strong>
                                            <br><small class="text-muted">Enable guest meal bookings</small>
                                        </label>
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
                                        <select id="is_active" class="form-control select2" name="is_active">
                                            <option value="1" <?php echo $company->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $company->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update Company</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
