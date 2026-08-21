<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Banner Management</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bannerModal" onclick="openAddModal()">
                        <i class="mdi mdi-plus me-1"></i> Add Banner
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Banners</p>
                                <h3 class="stat-value"><?php echo count($banners); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-image"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">All banners</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Banners</p>
                                <?php $active_count = count(array_filter($banners, function($b) { return $b->is_active == 1; })); ?>
                                <h3 class="stat-value"><?php echo $active_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-success-subtle text-success">
                            <i class="mdi mdi-check"></i> Active
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Inactive Banners</p>
                                <?php $inactive_count = count(array_filter($banners, function($b) { return $b->is_active == 0; })); ?>
                                <h3 class="stat-value"><?php echo $inactive_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-times-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-danger-subtle text-danger">
                            <i class="mdi mdi-close"></i> Inactive
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">With Actions</p>
                                <?php $action_count = count(array_filter($banners, function($b) { return $b->action_type != 'NONE'; })); ?>
                                <h3 class="stat-value"><?php echo $action_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-link"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Clickable banners</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Banners List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">Order</th>
                                        <th>Banner</th>
                                        <th>Title</th>
                                        <th>Company</th>
                                        <th>Action Type</th>
                                        <th>Action Target</th>
                                        <th>Status</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($banners)) { foreach ($banners as $banner) { ?>
                                        <tr id="banner_row_<?php echo $banner->id; ?>">
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $banner->display_order; ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($banner->image_path)): ?>
                                                    <img src="<?php echo base_url($banner->image_path); ?>" alt="<?php echo $banner->title; ?>" class="img-thumbnail" style="max-width: 120px; max-height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="avatar-sm">
                                                        <div class="avatar-title bg-soft-primary rounded text-primary">
                                                            <i class="uil uil-image font-size-16"></i>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <h6 class="mb-0"><?php echo $banner->title; ?></h6>
                                                <?php if (!empty($banner->description)): ?>
                                                    <small class="text-muted"><?php echo substr($banner->description, 0, 50); ?><?php echo strlen($banner->description) > 50 ? '...' : ''; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-primary text-primary"><?php echo $banner->company_name ?: 'N/A'; ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $type_badges = [
                                                    'PRODUCT' => 'bg-soft-info text-info',
                                                    'CATEGORY' => 'bg-soft-warning text-warning',
                                                    'URL' => 'bg-soft-primary text-primary',
                                                    'NONE' => 'bg-soft-secondary text-secondary'
                                                ];
                                                $badge_class = $type_badges[$banner->action_type] ?? 'bg-soft-secondary text-secondary';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $banner->action_type; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($banner->action_type == 'NONE'): ?>
                                                    <span class="text-muted">-</span>
                                                <?php elseif ($banner->action_type == 'URL'): ?>
                                                    <a href="<?php echo $banner->action_payload; ?>" target="_blank" class="text-primary">
                                                        <i class="uil uil-external-link-alt me-1"></i>
                                                        <?php echo strlen($banner->action_payload) > 30 ? substr($banner->action_payload, 0, 30) . '...' : $banner->action_payload; ?>
                                                    </a>
                                                <?php elseif ($banner->action_type == 'PRODUCT'): ?>
                                                    <span><?php echo get_product_name($banner->action_payload) ?: 'N/A'; ?></span>
                                                <?php elseif ($banner->action_type == 'CATEGORY'): ?>
                                                    <span><?php echo get_category_name($banner->action_payload) ?: 'N/A'; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox" data-id="<?php echo $banner->id; ?>" <?php echo $banner->is_active == 1 ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" onclick="editBanner(<?php echo $banner->id; ?>)">
                                                        <i class="uil uil-pen"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteBanner(<?php echo $banner->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Banner Modal -->
<div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bannerModalLabel">Add Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bannerForm" name="bannerForm" enctype="multipart/form-data">
                <input type="hidden" name="banner_id" id="banner_id" value="">
                <input type="hidden" name="existing_image" id="existing_image" value="">
                <div class="modal-body">
                    <div class="row">
                        <!-- Company Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="company_id">Company<code>*</code></label>
                            <select class="form-control" id="company_id" name="company_id" required>
                                <option value="">-- Select Company --</option>
                                <?php if (!empty($companies)) { foreach ($companies as $company) { ?>
                                    <option value="<?php echo $company->id; ?>"><?php echo $company->name; ?> (<?php echo $company->company_code; ?>)</option>
                                <?php }} ?>
                            </select>
                        </div>

                        <!-- Image Upload -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="image">Banner Image<code>*</code></label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Upload banner image (JPG, PNG, WEBP - Max 2MB)</small>
                            <div id="image_preview" class="mt-2" style="display: none;">
                                <img src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 150px;">
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="title">Title<code>*</code></label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="Banner title" required>
                        </div>

                        <!-- Display Order -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" placeholder="0" value="0" min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Optional description"></textarea>
                        </div>

                        <!-- Action Type -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_type">Action Type<code>*</code></label>
                            <select class="form-control" id="action_type" name="action_type" required>
                                <option value="NONE">None (No Action)</option>
                                <option value="PRODUCT">Product</option>
                                <option value="CATEGORY">Category</option>
                                <option value="URL">External URL</option>
                            </select>
                        </div>

                        <!-- Product Selection (shown when action_type is PRODUCT) -->
                        <div class="col-md-6 mb-3" id="product_field" style="display: none;">
                            <label class="form-label" for="product_id">Select Product<code>*</code></label>
                            <select class="form-control" id="product_id" name="product_id">
                                <option value="">-- Select Product --</option>
                                <?php if (!empty($products)) { foreach ($products as $product) { ?>
                                    <option value="<?php echo $product->id; ?>"><?php echo $product->name; ?></option>
                                <?php }} ?>
                            </select>
                        </div>

                        <!-- Category Selection (shown when action_type is CATEGORY) -->
                        <div class="col-md-6 mb-3" id="category_field" style="display: none;">
                            <label class="form-label" for="category_id">Select Category<code>*</code></label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">-- Select Category --</option>
                                <?php if (!empty($categories)) { foreach ($categories as $category) { ?>
                                    <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
                                <?php }} ?>
                            </select>
                        </div>

                        <!-- URL Input (shown when action_type is URL) -->
                        <div class="col-md-6 mb-3" id="url_field" style="display: none;">
                            <label class="form-label" for="url">External URL<code>*</code></label>
                            <input type="url" class="form-control" id="url" name="url" placeholder="https://example.com">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submit_button">
                        <i class="uil uil-check me-1"></i> Save Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
