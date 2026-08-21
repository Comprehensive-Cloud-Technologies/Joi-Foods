<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Company Policy</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companypolicies/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                            <li class="breadcrumb-item active">Edit Policy</li>
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
                        <h4 class="card-title mb-4">Edit Policy Attachment</h4>

                        <form id="edit_company_policy" action="<?php echo base_url('client/companypolicies/update') ?>" method="post">
                            <input type="hidden" name="company_policy_id" value="<?php echo $company_policy->id; ?>">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Policy</label>
                                        <div class="d-flex align-items-center p-3 bg-light rounded">
                                            <div class="avatar-sm me-3">
                                                <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                    <i class="uil uil-file-alt"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo $company_policy->policy_name; ?></h6>
                                                <small class="text-muted"><?php echo $company_policy->policy_code; ?> - <?php echo $company_policy->policy_type; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <select class="form-control select2" id="is_active" name="is_active">
                                            <option value="1" <?php echo $company_policy->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $company_policy->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo base_url('client/companypolicies/' . $company->id); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Update Policy</button>
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
                            <small class="text-muted">Attached At</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($company_policy->created_at)); ?></p>
                        </div>
                        <?php if ($company_policy->updated_at): ?>
                        <div class="mb-2">
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($company_policy->updated_at)); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Help</h4>
                        <div class="text-muted">
                            <p><i class="uil uil-check-circle text-success me-1"></i> Changes to status will affect policy availability for this company.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
