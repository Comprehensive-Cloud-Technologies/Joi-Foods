<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Coupon</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/coupons'); ?>">Coupons</a></li>
                            <li class="breadcrumb-item active">Edit Coupon</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <form class="needs-validation" id="coupon_form" name="coupon_form" action="<?php echo base_url('client/coupons/update'); ?>" method="post">
            <input type="hidden" name="coupon_id" value="<?php echo $coupon->id; ?>">
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <!-- Basic Information -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Basic Information</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="code">Coupon Code<code>*</code></label>
                                        <input type="text" class="form-control text-uppercase" id="code" name="code" placeholder="e.g. SAVE20" required value="<?php echo $coupon->code; ?>">
                                        <small class="text-muted">Unique code for customers to use</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Coupon Name<code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g. Summer Sale 20% Off" required value="<?php echo $coupon->name; ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="company_id">Applicable Company</label>
                                        <select id="company_id" class="form-control select2" name="company_id">
                                            <option value="">All Companies</option>
                                            <?php if (!empty($companies)) {
                                                foreach ($companies as $company) {
                                                    $selected = ($coupon->company_id == $company->id) ? 'selected' : '';
                                                    echo '<option value="' . $company->id . '" ' . $selected . '>' . $company->name . ' (' . $company->company_code . ')</option>';
                                                }
                                            } ?>
                                        </select>
                                        <small class="text-muted">Leave empty to apply to all companies</small>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2" placeholder="Coupon description..."><?php echo $coupon->description; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Settings -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Discount Settings</h4>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="discount_type">Discount Type<code>*</code></label>
                                        <select id="discount_type" class="form-control" name="discount_type" required>
                                            <option value="">Select Type</option>
                                            <option value="PERCENTAGE" <?php echo $coupon->discount_type == 'PERCENTAGE' ? 'selected' : ''; ?>>Percentage (%)</option>
                                            <option value="FIXED" <?php echo $coupon->discount_type == 'FIXED' ? 'selected' : ''; ?>>Fixed Amount (₹)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="discount_value">Discount Value<code>*</code></label>
                                        <input type="number" class="form-control" id="discount_value" name="discount_value" placeholder="e.g. 20" min="0" step="0.01" required value="<?php echo $coupon->discount_value; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3" id="max_discount_div" style="<?php echo $coupon->discount_type == 'FIXED' ? 'display:none;' : ''; ?>">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="max_discount_amount">Max Discount (₹)</label>
                                        <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" placeholder="e.g. 100" min="0" step="0.01" value="<?php echo $coupon->max_discount_amount; ?>">
                                        <small class="text-muted">For percentage discounts</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="min_order_amount">Min Order Amount (₹)</label>
                                        <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" placeholder="e.g. 200" min="0" step="0.01" value="<?php echo $coupon->min_order_amount; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Limits -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Usage Limits</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="usage_limit">Total Usage Limit</label>
                                        <input type="number" class="form-control" id="usage_limit" name="usage_limit" placeholder="Leave empty for unlimited" min="1" value="<?php echo $coupon->usage_limit; ?>">
                                        <small class="text-muted">Max times this coupon can be used. Current usage: <?php echo $coupon->usage_count; ?></small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="per_user_limit">Per User Limit</label>
                                        <input type="number" class="form-control" id="per_user_limit" name="per_user_limit" placeholder="e.g. 1" min="1" value="<?php echo $coupon->per_user_limit; ?>">
                                        <small class="text-muted">Max times per employee</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Validity Period</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="valid_from">Valid From<code>*</code></label>
                                        <input type="text" class="form-control datepicker" readonly id="valid_from" name="valid_from" placeholder="Start Date" required value="<?php echo date('Y-m-d', strtotime($coupon->valid_from)); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="valid_until">Valid Until</label>
                                        <input type="text" class="form-control datepicker" readonly id="valid_until" name="valid_until" placeholder="End Date (optional)" value="<?php echo $coupon->valid_until ? date('Y-m-d', strtotime($coupon->valid_until)) : ''; ?>">
                                        <small class="text-muted">Leave empty for no expiry</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Access -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Applicable Modules</h4>
                            <div class="d-flex gap-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="applies_to_qsr" name="applies_to_qsr" <?php echo $coupon->applies_to_qsr ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="applies_to_qsr">QSR</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="applies_to_kot" name="applies_to_kot" <?php echo $coupon->applies_to_kot ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="applies_to_kot">KOT</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="applies_to_premeal" name="applies_to_premeal" <?php echo $coupon->applies_to_premeal ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="applies_to_premeal">Premeal</label>
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
                                        <select id="is_active" class="form-control" name="is_active">
                                            <option value="1" <?php echo $coupon->is_active == 1 ? 'selected' : ''; ?>>Active</option>
                                            <option value="0" <?php echo $coupon->is_active == 0 ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-primary mx-auto d-block" type="submit" id="submit_button">Update Coupon</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
