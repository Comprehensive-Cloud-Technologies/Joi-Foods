<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit User</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companyusers/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                            <li class="breadcrumb-item active">Edit User</li>
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

                        <form id="edit_user" action="<?php echo base_url('client/companyusers/update') ?>" method="post">
                            <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" value="<?php echo $user->first_name; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" value="<?php echo $user->last_name; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" value="<?php echo $user->email; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" value="<?php echo $user->phone; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave empty to keep current">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="designation" class="form-label">Designation</label>
                                        <input type="text" class="form-control" id="designation" name="designation" placeholder="e.g. Admin, Manager" value="<?php echo $user->designation; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <select class="form-control select2" id="is_active" name="is_active">
                                            <option value="1" <?php echo $user->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $user->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
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
                                        <input class="form-check-input" type="checkbox" id="can_manual_credit" name="can_manual_credit" value="1" <?php echo !empty($user->can_manual_credit) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_manual_credit">
                                            <strong>Manual Credit</strong>
                                            <br><small class="text-muted">Add money to employee wallets</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="can_manual_debit" name="can_manual_debit" value="1" <?php echo !empty($user->can_manual_debit) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_manual_debit">
                                            <strong>Manual Debit</strong>
                                            <br><small class="text-muted">Deduct money from employee wallets</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="can_razorpay_recharge" name="can_razorpay_recharge" value="1" <?php echo !empty($user->can_razorpay_recharge) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_razorpay_recharge">
                                            <strong>Razorpay Recharge</strong>
                                            <br><small class="text-muted">Recharge by paying via Razorpay</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo base_url('client/companyusers/' . $company->id); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Update User</button>
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

                <!-- Meta Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Meta Information</h4>
                        <div class="mb-2">
                            <small class="text-muted">Created At</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($user->created_at)); ?></p>
                        </div>
                        <?php if ($user->updated_at): ?>
                        <div class="mb-2">
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($user->updated_at)); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Help</h4>
                        <div class="text-muted">
                            <p><i class="uil uil-check-circle text-success me-1"></i> Leave password empty to keep the current password.</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Email must be unique within the company.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
