<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Client Users</h4>
                    <a href="<?php echo base_url('admin_root/clients/add_user') ?>" class="btn btn-primary">
                        <i class="mdi mdi-plus me-1"></i> Add User
                    </a>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Client Users List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>User</th>
                                        <th>Client</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)) { $i = 1; foreach ($users as $user) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                            <i class="uil uil-user font-size-16"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $user->first_name . ' ' . $user->last_name; ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="badge bg-info-subtle text-info"><?php echo $user->client_code; ?></span>
                                                </div>
                                                <small class="text-muted"><?php echo $user->client_name; ?></small>
                                            </td>
                                            <td>
                                                <i class="uil uil-envelope text-muted me-1"></i>
                                                <?php echo $user->email; ?>
                                            </td>
                                            <td>
                                                <i class="uil uil-phone text-muted me-1"></i>
                                                <?php echo $user->phone ?: '-'; ?>
                                            </td>
                                            <td>
                                                <?php if ($user->is_active == 1): ?>
                                                    <span class="badge bg-success-subtle text-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?php echo base_url('admin_root/clients/edit_user/' . $user->id); ?>" class="btn btn-soft-warning action-btn" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" onclick="deleteUser(<?php echo $user->id; ?>)" title="Delete">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="uil uil-users-alt font-size-24 text-muted"></i>
                                                <p class="text-muted mb-0 mt-2">No client users found</p>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
