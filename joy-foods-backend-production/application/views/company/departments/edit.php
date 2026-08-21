<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Department</h4>



                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Department Information</h4>

                        <form id="edit_department" action="<?php echo base_url('company/departments/update') ?>" method="post">
                            <input type="hidden" name="department_id" value="<?php echo $department->id; ?>">

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter department name" value="<?php echo $department->name; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Department Code</label>
                                        <input type="text" class="form-control" id="code" name="code" placeholder="e.g. HR, IT, FIN" style="text-transform: uppercase;" value="<?php echo $department->code; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter department description"><?php echo $department->description; ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <select class="form-control select2" id="is_active" name="is_active">
                                            <option value="1" <?php echo $department->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $department->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo base_url('company/departments/'); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Update Department</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">

                <!-- Usage Stats -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Statistics</h4>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-info rounded-circle text-info">
                                    <i class="uil uil-users-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $employee_count; ?></h5>
                                <small class="text-muted">Employees in this department</small>
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
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($department->created_at)); ?></p>
                        </div>
                        <?php if ($department->updated_at): ?>
                            <div class="mb-2">
                                <small class="text-muted">Last Updated</small>
                                <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($department->updated_at)); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>