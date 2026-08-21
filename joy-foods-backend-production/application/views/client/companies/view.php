<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">View Company</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/companies'); ?>">Companies</a></li>
                            <li class="breadcrumb-item active">View Company</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-8">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Company Details</h4>
                            <div>
                                <?php if ($company->is_active): ?>
                                    <span class="badge bg-success-subtle text-success fs-6">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger fs-6">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Company Code</label>
                                    <p class="mb-0 fw-medium"><code><?php echo $company->company_code; ?></code></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Company Name</label>
                                    <p class="mb-0 fw-medium"><?php echo $company->name; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">Short Name</label>
                                    <p class="mb-0 fw-medium"><?php echo $company->short_name ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Legal Name</label>
                                    <p class="mb-0"><?php echo $company->legal_name ?: '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Contact Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small"><i class="uil uil-envelope me-1"></i> Primary Email</label>
                                    <p class="mb-0"><?php echo $company->primary_email; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small"><i class="uil uil-envelope me-1"></i> Secondary Email</label>
                                    <p class="mb-0"><?php echo $company->secondary_email ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small"><i class="uil uil-phone me-1"></i> Primary Phone</label>
                                    <p class="mb-0"><?php echo $company->primary_phone ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small"><i class="uil uil-phone me-1"></i> Secondary Phone</label>
                                    <p class="mb-0"><?php echo $company->secondary_phone ?: '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Address</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="mb-2">
                                    <?php
                                    $address_parts = array_filter([
                                        $company->address_line1,
                                        $company->address_line2,
                                        $company->city,
                                        $company->state,
                                        $company->pincode,
                                        $company->country
                                    ]);
                                    echo !empty($address_parts) ? implode(', ', $address_parts) : '-';
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Details -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Business Details</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">GST Number</label>
                                    <p class="mb-0 fw-medium"><?php echo $company->gst_number ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">PAN Number</label>
                                    <p class="mb-0 fw-medium"><?php echo $company->pan_number ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted small">CIN Number</label>
                                    <p class="mb-0 fw-medium"><?php echo $company->cin_number ?: '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract & Modules -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Contract & Module Access</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Contract Details</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Start Date</label>
                                            <p class="mb-0"><?php echo $company->contract_start_date ? date('d M Y', strtotime($company->contract_start_date)) : '-'; ?></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">End Date</label>
                                            <p class="mb-0"><?php echo $company->contract_end_date ? date('d M Y', strtotime($company->contract_end_date)) : '-'; ?></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Billing Cycle</label>
                                            <p class="mb-0"><?php echo $company->billing_cycle; ?></p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="text-muted small">Payment Terms</label>
                                            <p class="mb-0"><?php echo $company->payment_terms_days; ?> days</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Modules Enabled</h6>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge <?php echo $company->qsr_enabled ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-store me-1"></i> QSR
                                    </span>
                                    <span class="badge <?php echo $company->premeal_enabled ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-calendar-alt me-1"></i> Premeal
                                    </span>
                                    <span class="badge <?php echo $company->delivery_enabled ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <i class="uil uil-truck me-1"></i> KOT
                                    </span>
                                </div>
                                <h6 class="text-muted mb-3">Guest Booking</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge <?php echo $company->allow_guest_booking ? 'bg-success' : 'bg-secondary'; ?> p-2">
                                        <?php echo $company->allow_guest_booking ? 'Allowed' : 'Not Allowed'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attached Policies -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Attached Policies</h4>
                            <a href="<?php echo base_url('client/companypolicies/' . $company->id); ?>" class="btn btn-sm btn-primary">
                                <i class="uil uil-cog me-1"></i> Manage Policies
                            </a>
                        </div>
                        <?php if (!empty($attached_policies)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Policy</th>
                                        <th>Type</th>
                                        <th>Default</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attached_policies as $policy): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $policy->name; ?></strong>
                                            <br><small class="text-muted"><?php echo $policy->policy_code; ?></small>
                                        </td>
                                        <td>
                                            <?php if ($policy->policy_type == 'FREE'): ?>
                                                <span class="badge bg-success-subtle text-success">FREE</span>
                                            <?php elseif ($policy->policy_type == 'PARTIAL'): ?>
                                                <span class="badge bg-warning-subtle text-warning">PARTIAL</span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info">PAID</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($policy->is_default): ?>
                                                <span class="badge bg-primary-subtle text-primary">Default</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <i class="uil uil-file-alt font-size-24 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No policies attached</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Departments -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Departments</h4>
                            <a href="<?php echo base_url('client/departments/' . $company->id); ?>" class="btn btn-sm btn-primary">
                                <i class="uil uil-cog me-1"></i> Manage Departments
                            </a>
                        </div>
                        <?php if (!empty($departments)): ?>
                        <div class="row">
                            <?php foreach ($departments as $dept): ?>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="border rounded p-2">
                                    <i class="uil uil-sitemap text-muted me-1"></i>
                                    <?php echo $dept->name; ?>
                                    <?php if ($dept->code): ?>
                                        <small class="text-muted">(<?php echo $dept->code; ?>)</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <i class="uil uil-sitemap font-size-24 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No departments created</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Documents -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Documents</h4>
                            <a href="<?php echo base_url('client/companies/documents/' . $company->id); ?>" class="btn btn-sm btn-primary">
                                <i class="uil uil-cog me-1"></i> Manage Documents
                            </a>
                        </div>
                        <?php if (!empty($documents)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Label</th>
                                        <th>File</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $doc_count = 0; foreach ($documents as $doc): if ($doc_count >= 5) break; $doc_count++; ?>
                                    <tr>
                                        <td>
                                            <i class="<?php echo get_mime_type_icon($doc->mime_type); ?> text-muted me-1"></i>
                                            <?php echo $doc->label; ?>
                                        </td>
                                        <td>
                                            <small><?php echo $doc->original_filename; ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo get_file_size_formatted($doc->file_size); ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($documents) > 5): ?>
                        <div class="text-center mt-2">
                            <a href="<?php echo base_url('client/companies/documents/' . $company->id); ?>" class="text-primary">
                                View all <?php echo count($documents); ?> documents
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <i class="uil uil-file-alt font-size-24 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No documents uploaded</p>
                        </div>
                        <?php endif; ?>
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
                                <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                    <i class="uil uil-users-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['employees']; ?></h5>
                                <small class="text-muted">Employees</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-success rounded-circle text-success">
                                    <i class="uil uil-sitemap"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['departments']; ?></h5>
                                <small class="text-muted">Departments</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-warning rounded-circle text-warning">
                                    <i class="uil uil-file-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['policies']; ?></h5>
                                <small class="text-muted">Policies Attached</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-info rounded-circle text-info">
                                    <i class="uil uil-user-check"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['users']; ?></h5>
                                <small class="text-muted">Company Admins</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title bg-soft-secondary rounded-circle text-secondary">
                                    <i class="uil uil-file-alt"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $usage['documents']; ?></h5>
                                <small class="text-muted">Documents</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Users -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Company Admins</h4>
                            <a href="<?php echo base_url('client/companyusers/' . $company->id); ?>" class="btn btn-sm btn-primary">
                                <i class="uil uil-cog me-1"></i> Manage Users
                            </a>
                        </div>
                        <?php if (!empty($company_users)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($company_users as $user): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                            <?php echo strtoupper(substr($user->first_name, 0, 1)); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo $user->first_name . ' ' . $user->last_name; ?></h6>
                                        <small class="text-muted"><?php echo $user->email; ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-3">
                            <i class="uil uil-user font-size-24 text-muted"></i>
                            <p class="text-muted mb-0 mt-2">No users created</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Actions</h4>
                        <div class="d-flex gap-2">
                            <a href="<?php echo base_url('client/companies/edit/' . $company->id); ?>" class="btn btn-primary">
                                <i class="uil uil-pen me-1"></i> Edit Company
                            </a>
                            <a href="<?php echo base_url('client/companies'); ?>" class="btn btn-outline-secondary">
                                <i class="uil uil-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Meta Information</h4>
                        <div class="mb-2">
                            <small class="text-muted">Created At</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($company->created_at)); ?></p>
                        </div>
                        <?php if ($company->updated_at): ?>
                        <div class="mb-2">
                            <small class="text-muted">Last Updated</small>
                            <p class="mb-0"><?php echo date('d M Y, h:i A', strtotime($company->updated_at)); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($company->max_employees): ?>
                        <div class="mb-0">
                            <small class="text-muted">Max Employees Limit</small>
                            <p class="mb-0"><?php echo $company->max_employees; ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
