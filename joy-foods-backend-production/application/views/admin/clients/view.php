<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">View Client</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('admin_root/clients'); ?>">Clients</a></li>
                            <li class="breadcrumb-item active">View Client</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0"><?php echo $client->name; ?></h4>
                            <div>
                                <?php if ($client->is_active == 1): ?>
                                    <span class="badge bg-success font-size-14 me-2">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger font-size-14 me-2">Inactive</span>
                                <?php endif; ?>
                                <?php if ($client->is_verified == 1): ?>
                                    <span class="badge bg-info font-size-14 me-2">Verified</span>
                                <?php endif; ?>
                                <a href="<?php echo base_url('admin_root/clients/edit/' . $client->id); ?>" class="btn btn-warning">
                                    <i class="mdi mdi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn btn-danger" onclick="deleteClient(<?php echo $client->id; ?>)">
                                    <i class="mdi mdi-delete"></i> Delete
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary"><i class="mdi mdi-store me-2"></i>Basic Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Client Code:</strong></td>
                                        <td><span class="badge bg-info font-size-12"><?php echo $client->client_code; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Client Name:</strong></td>
                                        <td><?php echo $client->name; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Legal Name:</strong></td>
                                        <td><?php echo $client->legal_name ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><a href="mailto:<?php echo $client->email; ?>"><?php echo $client->email; ?></a></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo $client->phone ? '<a href="tel:' . $client->phone . '">' . $client->phone . '</a>' : '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Alternate Phone:</strong></td>
                                        <td><?php echo $client->alternate_phone ?: '-'; ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary"><i class="mdi mdi-map-marker me-2"></i>Address Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Address Line 1:</strong></td>
                                        <td><?php echo $client->address_line1 ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Address Line 2:</strong></td>
                                        <td><?php echo $client->address_line2 ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>City:</strong></td>
                                        <td><?php echo $client->city ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>State:</strong></td>
                                        <td><?php echo $client->state ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Country:</strong></td>
                                        <td><?php echo $client->country ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pincode:</strong></td>
                                        <td><?php echo $client->pincode ?: '-'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary"><i class="mdi mdi-file-document me-2"></i>Business Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>GST Number:</strong></td>
                                        <td><?php echo $client->gst_number ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>PAN Number:</strong></td>
                                        <td><?php echo $client->pan_number ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>FSSAI License:</strong></td>
                                        <td><?php echo $client->fssai_license ?: '-'; ?></td>
                                    </tr>
                                </table>

                                <h5 class="mb-3 text-primary"><i class="mdi mdi-key me-2"></i>Razorpay Keys</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Razorpay Key ID:</strong></td>
                                        <td><?php echo $client->razorpay_key_id ?: '-'; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Razorpay Key Secret:</strong></td>
                                        <td><?php echo $client->razorpay_key_secret ?: '-'; ?></td>
                                    </tr>
                                </table>

                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3 text-primary"><i class="mdi mdi-clock-outline me-2"></i>Timestamps</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Created At:</strong></td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($client->created_at)); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Updated At:</strong></td>
                                        <td><?php echo $client->updated_at ? date('d M Y, h:i A', strtotime($client->updated_at)) : '-'; ?></td>
                                    </tr>
                                    <?php if ($client->is_verified && $client->verified_at): ?>
                                    <tr>
                                        <td><strong>Verified At:</strong></td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($client->verified_at)); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <i class="mdi mdi-store font-size-40 mb-2"></i>
                                <h3 class="text-white mb-1"><?php echo $client->client_code; ?></h3>
                                <p class="text-white-50 mb-0">Client Code</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-<?php echo $client->is_active ? 'success' : 'danger'; ?> text-white">
                            <div class="card-body text-center">
                                <i class="mdi mdi-<?php echo $client->is_active ? 'check-circle' : 'close-circle'; ?> font-size-40 mb-2"></i>
                                <h3 class="text-white mb-1"><?php echo $client->is_active ? 'Active' : 'Inactive'; ?></h3>
                                <p class="text-white-50 mb-0">Account Status</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-<?php echo $client->is_verified ? 'info' : 'warning'; ?> text-white">
                            <div class="card-body text-center">
                                <i class="mdi mdi-<?php echo $client->is_verified ? 'shield-check' : 'shield-alert'; ?> font-size-40 mb-2"></i>
                                <h3 class="text-white mb-1"><?php echo $client->is_verified ? 'Verified' : 'Unverified'; ?></h3>
                                <p class="text-white-50 mb-0">Verification Status</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end card -->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->
