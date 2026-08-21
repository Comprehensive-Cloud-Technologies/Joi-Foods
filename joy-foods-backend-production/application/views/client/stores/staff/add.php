<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">Add Staff</h4>
                        <p class="text-muted mb-0">
                            <i class="uil uil-store me-1"></i><?php echo $store->name; ?> (<?php echo $store->store_code; ?>)
                        </p>
                    </div>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/stores'); ?>">Stores</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/stores/staff/' . $store->id); ?>">Staff</a></li>
                            <li class="breadcrumb-item active">Add Staff</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="add_staff" name="add_staff" action="<?php echo base_url('client/stores/store_staff'); ?>" method="post">
            <input type="hidden" name="store_id" value="<?php echo $store->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="alert alert-info">
                                <i class="uil uil-info-circle me-1"></i> Staff code will be auto-generated (e.g., <?php echo $store->store_code; ?>-STF-001)
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="first_name">First Name<code>*</code></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="last_name">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="id_number">ID Number</label>
                                        <input type="text" class="form-control" id="id_number" name="id_number" placeholder="Aadhar / PAN / etc.">
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
                                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone Number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Password</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="password">Password<code>*</code></label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters" required>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="confirm_password">Confirm Password<code>*</code></label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
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
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
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
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Add Staff</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
