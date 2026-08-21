<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">Edit Staff</h4>
                        <p class="text-muted mb-0">
                            <i class="uil uil-store me-1"></i><?php echo $store->name; ?> (<?php echo $store->store_code; ?>)
                        </p>
                    </div>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/stores'); ?>">Stores</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/stores/staff/' . $store->id); ?>">Staff</a></li>
                            <li class="breadcrumb-item active">Edit Staff</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="edit_staff" name="edit_staff" action="<?php echo base_url('client/stores/update_staff'); ?>" method="post">
            <input type="hidden" name="staff_id" value="<?php echo $staff->id; ?>">
            <input type="hidden" name="store_id" value="<?php echo $store->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="staff_code">Staff Code</label>
                                        <input type="text" class="form-control" id="staff_code" name="staff_code" value="<?php echo $staff->staff_code; ?>" readonly>
                                        <small class="text-muted">Auto-generated (read-only)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="first_name">First Name<code>*</code></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $staff->first_name; ?>" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $staff->last_name; ?>" placeholder="Last Name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="id_number">ID Number</label>
                                        <input type="text" class="form-control" id="id_number" name="id_number" value="<?php echo $staff->id_number; ?>" placeholder="Aadhar / PAN / etc.">
                                        <small class="text-muted">Aadhar, PAN or any govt. ID</small>
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
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="email">Email<code>*</code></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $staff->email; ?>" placeholder="email@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $staff->phone; ?>" placeholder="Phone Number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Change Password (Optional)</h4>
                            <p class="text-muted">Leave blank to keep current password</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="password">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters">
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password">
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
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo ($staff->is_active == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active</strong>
                                            <br><small class="text-muted">Staff is active</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update Staff</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
