<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Store Details</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/stores'); ?>">Stores</a></li>
                            <li class="breadcrumb-item active">View Store</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-12">
                <!-- Store Header -->
                <div class="card" style="border: 1px solid #e9ecef; margin-bottom: 24px;">
                    <div class="card-body" style="padding: 24px;">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <?php if (!empty($store->thumbnail)): ?>
                                    <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef; flex-shrink: 0;">
                                        <img src="<?php echo base_url($store->thumbnail); ?>" alt="<?php echo $store->name; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php else: ?>
                                    <div style="width: 80px; height: 80px; border-radius: 8px; background: #f8f9fa; border: 1px solid #e9ecef; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="uil uil-store" style="font-size: 32px; color: #6c757d;"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h4 class="mb-2" style="color: #212529; font-weight: 600; font-size: 22px;"><?php echo $store->name; ?></h4>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="badge bg-light text-dark" style="padding: 5px 10px; font-size: 12px; font-weight: 500; border: 1px solid #dee2e6;">
                                            <?php echo $store->store_code; ?>
                                        </span>
                                        <?php
                                        $type_badges = [
                                            'QSR' => 'bg-info',
                                            'KOT' => 'bg-warning',
                                            'PREMEAL' => 'bg-success'
                                        ];
                                        $badge_class = $type_badges[$store->store_type] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                            <?php echo $store->store_type; ?>
                                        </span>
                                        <?php if ($store->is_active == 1): ?>
                                            <span class="badge bg-success" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($store->is_operational == 1): ?>
                                            <span class="badge bg-primary" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                                Operational
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($store->company_name): ?>
                                        <p class="text-muted mb-0" style="font-size: 14px;">
                                            <i class="uil uil-building me-1"></i><?php echo $store->company_name; ?> (<?php echo $store->company_code; ?>)
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <a href="<?php echo base_url('client/stores/edit/' . $store->id); ?>" class="btn btn-primary" style="padding: 8px 20px;">
                                    <i class="uil uil-pen me-1"></i> Edit
                                </a>
                                <a href="<?php echo base_url('client/stores'); ?>" class="btn btn-outline-secondary" style="padding: 8px 20px;">
                                    <i class="uil uil-arrow-left me-1"></i> Back
                                </a>
                                <?php if (in_array($store->store_type, ['QSR', 'KOT'])): ?>
                                <button id="generate_qr_btn" class="btn btn-outline-info" style="padding: 8px 20px;">
                                    <i class="uil uil-qrcode-scan me-1"></i> View QR
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Store Name</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->name; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Short Name</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->short_name ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Store Code</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->store_code; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Company</label>
                                    <p class="mb-0 fw-medium">
                                        <?php if ($store->company_name): ?>
                                            <?php echo $store->company_name; ?> (<?php echo $store->company_code; ?>)
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($store->description): ?>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="text-muted mb-1">Description</label>
                                        <p class="mb-0"><?php echo $store->description; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Contact Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Primary Email</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->primary_email ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Secondary Email</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->secondary_email ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Primary Phone</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->primary_phone ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Secondary Phone</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->secondary_phone ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Contact Person</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->contact_person_name ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Contact Person Phone</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->contact_person_phone ?: '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address & Location -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Address & Location</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Address</label>
                                    <p class="mb-0">
                                        <?php
                                        $address_parts = array_filter([
                                            $store->address_line1,
                                            $store->address_line2,
                                            $store->city,
                                            $store->state,
                                            $store->country,
                                            $store->pincode
                                        ]);
                                        echo !empty($address_parts) ? implode(', ', $address_parts) : '-';
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($store->landmark): ?>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="text-muted mb-1">Landmark</label>
                                        <p class="mb-0"><?php echo $store->landmark; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($store->latitude && $store->longitude): ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted mb-1">Latitude</label>
                                        <p class="mb-0 fw-medium"><?php echo $store->latitude; ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="text-muted mb-1">Longitude</label>
                                        <p class="mb-0 fw-medium"><?php echo $store->longitude; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Legal & Compliance -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Legal & Compliance</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">GST Number</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->gst_number ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">FSSAI License</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->fssai_license ?: '-'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Trade License</label>
                                    <p class="mb-0 fw-medium"><?php echo $store->trade_license_number ?: '-'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Staff -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Store Staff (<?php echo count($staff); ?>)</h5>
                        <?php if (!empty($staff)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Staff Code</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>ID Number</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staff as $member): ?>
                                            <tr>
                                                <td><?php echo $member->staff_code; ?></td>
                                                <td><?php echo $member->first_name . ' ' . $member->last_name; ?></td>
                                                <td><?php echo $member->email; ?></td>
                                                <td><?php echo $member->phone ?: '-'; ?></td>
                                                <td><?php echo $member->id_number ?: '-'; ?></td>
                                                <td>
                                                    <?php if ($member->is_active == 1): ?>
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="uil uil-users-alt font-size-24 text-muted"></i>
                                <p class="text-muted mb-0 mt-2">No staff assigned</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Documents -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Documents</h4>
                            <a href="<?php echo base_url('client/stores/documents/' . $store->id); ?>" class="btn btn-sm btn-primary">
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
                            <a href="<?php echo base_url('client/stores/documents/' . $store->id); ?>" class="text-primary">
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

                <!-- Status -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Status</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Active Status</label>
                                    <p class="mb-0">
                                        <?php if ($store->is_active == 1): ?>
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Operational Status</label>
                                    <p class="mb-0">
                                        <?php if ($store->is_operational == 1): ?>
                                            <span class="badge bg-warning-subtle text-warning">Operational</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">Not Operational</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
