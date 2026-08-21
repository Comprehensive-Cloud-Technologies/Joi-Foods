<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Client</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('admin_root/clients'); ?>">Clients</a></li>
                            <li class="breadcrumb-item active">Edit Client</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="edit_client" name="edit_client" action="<?php echo base_url('admin_root/clients/update'); ?>" method="post">
            <input type="hidden" name="client_id" value="<?php echo $client->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="client_code">Client Code<code>*</code></label>
                                        <input type="text" class="form-control" id="client_code" name="client_code" value="<?php echo $client->client_code; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Client Name<code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $client->name; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="legal_name">Legal Name</label>
                                        <input type="text" class="form-control" id="legal_name" name="legal_name" value="<?php echo $client->legal_name; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="email">Email<code>*</code></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $client->email; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="phone">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $client->phone; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="alternate_phone">Alternate Phone</label>
                                        <input type="text" class="form-control" id="alternate_phone" name="alternate_phone" value="<?php echo $client->alternate_phone; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Address Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="address_line1">Address Line 1</label>
                                        <input type="text" class="form-control" id="address_line1" name="address_line1" value="<?php echo $client->address_line1; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="address_line2">Address Line 2</label>
                                        <input type="text" class="form-control" id="address_line2" name="address_line2" value="<?php echo $client->address_line2; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $client->city; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" value="<?php echo $client->state; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" value="<?php echo $client->country; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="pincode">Pincode</label>
                                        <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo $client->pincode; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Business Details</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="gst_number">GST Number</label>
                                        <input type="text" class="form-control" id="gst_number" name="gst_number" value="<?php echo $client->gst_number; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="pan_number">PAN Number</label>
                                        <input type="text" class="form-control" id="pan_number" name="pan_number" value="<?php echo $client->pan_number; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="fssai_license">FSSAI License</label>
                                        <input type="text" class="form-control" id="fssai_license" name="fssai_license" value="<?php echo $client->fssai_license; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="is_active">Status</label>
                                        <select id="is_active" class="form-control select2 search-disabled" name="is_active">
                                            <option value="1" <?php echo $client->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $client->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Payment Gateway Details</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="razorpay_key_id">Razorpay Key ID</label>
                                        <input type="text" class="form-control" required id="razorpay_key_id" name="razorpay_key_id" placeholder="e.g. rzp_test_1234567890" value="<?php echo $client->razorpay_key_id; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="razorpay_key_secret">Razorpay Key Secret</label>
                                        <input type="text" class="form-control" required id="razorpay_key_secret" name="razorpay_key_secret" placeholder="e.g. 1234567890abcdef1234567890abcdef" value="<?php echo $client->razorpay_key_secret; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update Client</button>
                        </div>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </form>
    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->