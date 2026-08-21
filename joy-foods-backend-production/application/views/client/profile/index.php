<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">My Profile</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Profile Info Card -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img class="rounded-circle" src="https://ui-avatars.com/api/?bold=true&background=0f523b&name=<?php echo urlencode($user->first_name . ' ' . $user->last_name); ?>&rounded=true&color=fff&size=120" alt="Avatar" width="120" height="120">
                        </div>
                        <h5 class="mb-1"><?php echo $user->first_name . ' ' . $user->last_name; ?></h5>
                        <p class="text-muted mb-3"><?php echo $user->email; ?></p>
                        <span class="badge bg-success-subtle text-success p-2">
                            <i class="uil uil-building me-1"></i><?php echo $client->name; ?>
                        </span>

                        <hr>

                        <div class="text-start">
                            <div class="d-flex mb-2">
                                <div class="text-muted" style="min-width: 100px;">Email</div>
                                <div class="fw-medium"><?php echo $user->email; ?></div>
                            </div>
                            <div class="d-flex mb-2">
                                <div class="text-muted" style="min-width: 100px;">Phone</div>
                                <div class="fw-medium"><?php echo $user->phone ?: '-'; ?></div>
                            </div>
                            <div class="d-flex mb-2">
                                <div class="text-muted" style="min-width: 100px;">Status</div>
                                <div>
                                    <?php if ($user->is_active): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="text-muted" style="min-width: 100px;">Joined</div>
                                <div class="fw-medium"><?php echo date('d M Y', strtotime($user->created_at)); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Profile & Change Password -->
            <div class="col-xl-8">
                <!-- Edit Profile -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Edit Profile</h4>
                        <form id="profile_form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="first_name">First Name<code>*</code></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user->first_name; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user->last_name; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="email">Email</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo $user->email; ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user->phone; ?>">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="profile_submit_btn">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Change Password</h4>
                        <form id="password_form">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="current_password">Current Password<code>*</code></label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="new_password">New Password<code>*</code></label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="confirm_password">Confirm Password<code>*</code></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning" id="password_submit_btn">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
