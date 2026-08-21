<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Add User</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companyusers/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                            <li class="breadcrumb-item active">Add User</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">User Information</h4>

                        <form id="add_user" action="<?php echo base_url('client/companyusers/store') ?>" method="post">
                            <input type="hidden" name="company_id" value="<?php echo $company->id; ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" class="form-control" id="designation" name="designation" placeholder="e.g. Admin, Manager">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <select class="form-control select2" id="is_active" name="is_active">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Recharge Permissions -->
                                <div class="col-12">
                                    <hr>
                                    <h6 class="mb-3">Recharge Permissions</h6>
                                    <p class="text-muted small mb-3">Control which wallet-recharge actions this user can perform in the company portal.</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="can_manual_credit" name="can_manual_credit" value="1">
                                        <label class="form-check-label" for="can_manual_credit">
                                            <strong>Manual Credit</strong>
                                            <br><small class="text-muted">Add money to employee wallets</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="can_manual_debit" name="can_manual_debit" value="1">
                                        <label class="form-check-label" for="can_manual_debit">
                                            <strong>Manual Debit</strong>
                                            <br><small class="text-muted">Deduct money from employee wallets</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="can_razorpay_recharge" name="can_razorpay_recharge" value="1">
                                        <label class="form-check-label" for="can_razorpay_recharge">
                                            <strong>Razorpay Recharge</strong>
                                            <br><small class="text-muted">Recharge by paying via Razorpay</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo base_url('client/companyusers/' . $company->id); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Add User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <!-- Company Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Company</h4>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                    <i class="uil uil-building"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0"><?php echo $company->name; ?></h6>
                                <small class="text-muted"><?php echo $company->company_code; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Help</h4>
                        <div class="text-muted">
                            <p><i class="uil uil-check-circle text-success me-1"></i> Company users can login to manage company operations.</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Email must be unique within the company.</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Password must be at least 6 characters.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
