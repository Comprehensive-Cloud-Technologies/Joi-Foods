<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Change Password</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('admin_root'); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Change Password</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="change_password_form" name="change_password_form" action="<?php echo base_url('admin_root/profile/update_password'); ?>" method="post">
            <div class="row">
                <div class="col-xl-6 col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Update Your Password</h4>
                            <p class="card-title-desc text-muted">Enter your current password and choose a new one.</p>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="current_password">Current Password <code>*</code></label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="new_password">New Password <code>*</code></label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
                                        <small class="text-muted">Minimum 6 characters.</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="confirm_password">Confirm New Password <code>*</code></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter new password" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-center">
                            <button class="btn btn-primary d-inline-block" type="submit" id="submit_button">
                                <i class="uil uil-lock-alt me-1"></i> Update Password
                            </button>
                            <a href="<?php echo base_url('admin_root'); ?>" class="btn btn-secondary d-inline-block ms-2">
                                <i class="uil uil-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
<!-- End Page-content -->
