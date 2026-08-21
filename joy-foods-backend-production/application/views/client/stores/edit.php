<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Store</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/stores'); ?>">Stores</a></li>
                            <li class="breadcrumb-item active">Edit Store</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="edit_store" name="edit_store" action="<?php echo base_url('client/stores/update'); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="store_id" value="<?php echo $store->id; ?>">
            <input type="hidden" name="existing_thumbnail" value="<?php echo $store->thumbnail; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="thumbnail">Store Thumbnail</label>
                                        <?php if (!empty($store->thumbnail)): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo base_url($store->thumbnail); ?>" alt="Current Thumbnail" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                                <p class="text-muted mb-0"><small>Current thumbnail</small></p>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                        <small class="text-muted">Upload new image to replace (JPG, PNG - Max 2MB)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="store_code">Store Code<code>*</code></label>
                                        <input type="text" class="form-control text-uppercase" id="store_code" name="store_code" value="<?php echo $store->store_code; ?>" placeholder="e.g. STR-001" required>
                                        <small class="text-muted">Unique code for store</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Store Name<code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $store->name; ?>" placeholder="e.g. Main Cafeteria" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="short_name">Short Name</label>
                                        <input type="text" class="form-control" id="short_name" name="short_name" value="<?php echo $store->short_name; ?>" placeholder="e.g. Main Cafe">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="company_id">Company<code>*</code></label>
                                        <select id="company_id" class="form-control select2" name="company_id" required>
                                            <option value="">-- Select Company --</option>
                                            <?php if (!empty($companies)) { foreach ($companies as $company) { ?>
                                                <option value="<?php echo $company->id; ?>" <?php echo ($store->company_id == $company->id) ? 'selected' : ''; ?>><?php echo $company->name; ?> (<?php echo $company->company_code; ?>)</option>
                                            <?php }} ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description about the store"><?php echo $store->description; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Store Type -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Store Type</h4>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="store_type">Type<code>*</code></label>
                                        <select id="store_type" class="form-control select2" name="store_type" required>
                                            <option value="">-- Select Type --</option>
                                            <option value="QSR" <?php echo ($store->store_type == 'QSR') ? 'selected' : ''; ?>>QSR (Quick Service Restaurant)</option>
                                            <option value="KOT" <?php echo ($store->store_type == 'KOT') ? 'selected' : ''; ?>>KOT (Kitchen Order Ticket)</option>
                                            <option value="PREMEAL" <?php echo ($store->store_type == 'PREMEAL') ? 'selected' : ''; ?>>PREMEAL (Pre-ordered Meals)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PREMEAL Meal Timings -->
                    <div class="card" id="meal_timings_card" style="display: <?php echo ($store->store_type == 'PREMEAL') ? 'block' : 'none'; ?>;">
                        <div class="card-body">
                            <h4 class="card-title">PREMEAL Meal Timings</h4>
                            <p class="text-muted mb-3">Set the serving times for each meal. Orders must be placed before the cutoff time (defined in policy).</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="breakfast_time">Breakfast Time</label>
                                        <input type="time" class="form-control" id="breakfast_time" name="breakfast_time" value="<?php echo $store->breakfast_time ? substr($store->breakfast_time, 0, 5) : '08:00'; ?>">
                                        <small class="text-muted">e.g., 08:00 AM</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="lunch_time">Lunch Time</label>
                                        <input type="time" class="form-control" id="lunch_time" name="lunch_time" value="<?php echo $store->lunch_time ? substr($store->lunch_time, 0, 5) : '12:30'; ?>">
                                        <small class="text-muted">e.g., 12:30 PM</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="dinner_time">Dinner Time</label>
                                        <input type="time" class="form-control" id="dinner_time" name="dinner_time" value="<?php echo $store->dinner_time ? substr($store->dinner_time, 0, 5) : '19:00'; ?>">
                                        <small class="text-muted">e.g., 07:00 PM</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Contact Information</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="primary_email">Primary Email</label>
                                        <input type="email" class="form-control" id="primary_email" name="primary_email" value="<?php echo $store->primary_email; ?>" placeholder="store@company.com">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="secondary_email">Secondary Email</label>
                                        <input type="email" class="form-control" id="secondary_email" name="secondary_email" value="<?php echo $store->secondary_email; ?>" placeholder="manager@company.com">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="primary_phone">Primary Phone</label>
                                        <input type="text" class="form-control" id="primary_phone" name="primary_phone" value="<?php echo $store->primary_phone; ?>" placeholder="Phone Number">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="secondary_phone">Secondary Phone</label>
                                        <input type="text" class="form-control" id="secondary_phone" name="secondary_phone" value="<?php echo $store->secondary_phone; ?>" placeholder="Alternate Phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="contact_person_name">Contact Person Name</label>
                                        <input type="text" class="form-control" id="contact_person_name" name="contact_person_name" value="<?php echo $store->contact_person_name; ?>" placeholder="Manager Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="contact_person_phone">Contact Person Phone</label>
                                        <input type="text" class="form-control" id="contact_person_phone" name="contact_person_phone" value="<?php echo $store->contact_person_phone; ?>" placeholder="Manager Phone">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Address Information</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="address_line1">Address Line 1</label>
                                        <input type="text" class="form-control" id="address_line1" name="address_line1" value="<?php echo $store->address_line1; ?>" placeholder="Street Address">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="address_line2">Address Line 2</label>
                                        <input type="text" class="form-control" id="address_line2" name="address_line2" value="<?php echo $store->address_line2; ?>" placeholder="Building, Floor, etc.">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="city">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $store->city; ?>" placeholder="City">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="state">State</label>
                                        <input type="text" class="form-control" id="state" name="state" value="<?php echo $store->state; ?>" placeholder="State">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="country">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" value="<?php echo $store->country; ?>" placeholder="Country">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="pincode">Pincode</label>
                                        <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo $store->pincode; ?>" placeholder="Pincode">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="landmark">Landmark</label>
                                        <input type="text" class="form-control" id="landmark" name="landmark" value="<?php echo $store->landmark; ?>" placeholder="Nearby landmark">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Coordinates -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Location Coordinates</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="latitude">Latitude</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" value="<?php echo $store->latitude; ?>" placeholder="e.g. 12.9716">
                                        <small class="text-muted">Decimal degrees format</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="longitude">Longitude</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" value="<?php echo $store->longitude; ?>" placeholder="e.g. 77.5946">
                                        <small class="text-muted">Decimal degrees format</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legal & Compliance -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Legal & Compliance</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="gst_number">GST Number</label>
                                        <input type="text" class="form-control" id="gst_number" name="gst_number" value="<?php echo $store->gst_number; ?>" placeholder="e.g. 22AAAAA0000A1Z5">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="fssai_license">FSSAI License</label>
                                        <input type="text" class="form-control" id="fssai_license" name="fssai_license" value="<?php echo $store->fssai_license; ?>" placeholder="FSSAI License Number">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="trade_license_number">Trade License Number</label>
                                        <input type="text" class="form-control" id="trade_license_number" name="trade_license_number" value="<?php echo $store->trade_license_number; ?>" placeholder="Trade License Number">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Status</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="is_active">Status</label>
                                        <select id="is_active" class="form-control select2" name="is_active">
                                            <option value="1" <?php echo ($store->is_active == 1) ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo ($store->is_active == 0) ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3 mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_operational" name="is_operational" <?php echo ($store->is_operational == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_operational">
                                            <strong>Operational</strong>
                                            <br><small class="text-muted">Store is currently operational</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update Store</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
