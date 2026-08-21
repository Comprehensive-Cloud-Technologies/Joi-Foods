<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Add Department</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/departments/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                            <li class="breadcrumb-item active">Add Department</li>
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
                        <h4 class="card-title mb-4">Department Information</h4>

                        <form id="add_department" action="<?php echo base_url('client/departments/store') ?>" method="post">
                            <input type="hidden" name="company_id" value="<?php echo $company->id; ?>">

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
                                <a href="<?php echo base_url('client/departments/' . $company->id); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Add Department</button>
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
