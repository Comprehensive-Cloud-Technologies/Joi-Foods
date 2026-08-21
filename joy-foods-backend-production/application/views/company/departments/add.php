<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Add Department</h4>


                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Department Information</h4>

                        <form id="add_department" action="<?php echo base_url('company/departments/store') ?>" method="post">

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter department name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Department Code</label>
                                        <input type="text" class="form-control" id="code" name="code" placeholder="e.g. HR, IT, FIN" style="text-transform: uppercase;">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter department description"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <select class="form-control select2" id="is_active" name="is_active">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo base_url('company/departments/' ); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Add Department</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">

                <!-- Help Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Help</h4>
                        <div class="text-muted">
                            <p><i class="uil uil-check-circle text-success me-1"></i> Department name is required and must be unique within the company.</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Department code is optional but useful for quick reference.</p>
                            <p><i class="uil uil-check-circle text-success me-1"></i> Inactive departments won't appear in employee assignment dropdowns.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>