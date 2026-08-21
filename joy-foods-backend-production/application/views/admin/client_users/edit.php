<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Client User</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('admin_root/clients/users'); ?>">Client Users</a></li>
                            <li class="breadcrumb-item active">Edit User</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="edit_client_user" name="edit_client_user" action="<?php echo base_url('admin_root/clients/update_user'); ?>" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">User Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="client_id">Select Client<code>*</code></label>
                                        <select class="form-control select2" id="client_id" name="client_id" required>
                                            <option value="">Select Client</option>
                                            <?php if (!empty($clients)) { foreach ($clients as $client) { ?>
                                                <option value="<?php echo $client->id; ?>" <?php echo $user->client_id == $client->id ? 'selected' : ''; ?>><?php echo $client->name; ?> (<?php echo $client->client_code; ?>)</option>
                                            <?php } } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="email">Email<code>*</code></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user->email; ?>" required>
                                    </div>
                                </div>
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
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user->phone; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="is_active">Status</label>
                                        <select id="is_active" class="form-control" name="is_active">
                                            <option value="1" <?php echo $user->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $user->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Change Password <small class="text-muted">(Leave blank to keep current password)</small></h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="password">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="New Password">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update User</button>
                        </div>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </form>
    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
