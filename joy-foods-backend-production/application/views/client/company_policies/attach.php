<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Attach Policy</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companypolicies/' . $company->id); ?>"><?php echo $company->name; ?></a></li>
                            <li class="breadcrumb-item active">Attach Policy</li>
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
                        <h4 class="card-title mb-4">Policy Attachment</h4>

                        <form id="attach_policy" action="<?php echo base_url('client/companypolicies/store') ?>" method="post">
                            <input type="hidden" name="company_id" value="<?php echo $company->id; ?>">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="policy_id" class="form-label">Select Policy <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="policy_id" name="policy_id">
                                            <option value="">Select a policy</option>
                                            <?php foreach ($available_policies as $policy): ?>
                                                <option value="<?php echo $policy->id; ?>">
                                                    <?php echo $policy->name; ?> (<?php echo $policy->policy_code; ?>) - <?php echo $policy->policy_type; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
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
                                <a href="<?php echo base_url('client/companypolicies/' . $company->id); ?>" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="submit_button">Attach Policy</button>
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
                            <p><i class="uil uil-check-circle text-success me-1"></i> Select a policy from your policy library to attach to this company.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
