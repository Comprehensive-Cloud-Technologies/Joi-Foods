<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Edit Category</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/categories'); ?>">Categories</a></li>
                            <li class="breadcrumb-item active">Edit Category</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Category Information</h5>

                        <form class="needs-validation" id="edit_category" name="edit_category" action="<?php echo base_url('client/categories/update'); ?>" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="category_id" value="<?php echo $category->id; ?>">
                            <input type="hidden" name="existing_icon" value="<?php echo $category->icon; ?>">
                            <input type="hidden" name="existing_thumbnail" value="<?php echo $category->thumbnail; ?>">

                            <div class="row">
                                <!-- Category Name -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="name">Category Name <code>*</code></label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $category->name; ?>" required>
                                    </div>
                                </div>

                                <!-- Display Order -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="display_order">Display Order</label>
                                        <input type="number" class="form-control" id="display_order" name="display_order" value="<?php echo $category->display_order; ?>">
                                    </div>
                                </div>

                                <!-- Icon -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="icon">Icon Image</label>
                                        <?php if (!empty($category->icon)): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo base_url($category->icon); ?>" alt="Current Icon" class="img-thumbnail" style="max-width: 100px;">
                                                <p class="text-muted mb-0"><small>Current icon</small></p>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                                        <small class="text-muted">Upload icon (JPG, PNG - Max 2MB)</small>
                                    </div>
                                </div>

                                <!-- Thumbnail -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="thumbnail">Category Thumbnail</label>
                                        <?php if (!empty($category->thumbnail)): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo base_url($category->thumbnail); ?>" alt="Current Thumbnail" class="img-thumbnail" style="max-width: 100px;">
                                                <p class="text-muted mb-0"><small>Current thumbnail</small></p>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                                        <small class="text-muted">Upload new image (JPG, PNG - Max 2MB)</small>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $category->description; ?></textarea>
                                    </div>
                                </div>

                                <!-- Module Access -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Module Access</label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="qsr_enabled" name="qsr_enabled" value="1" <?php echo $category->qsr_enabled ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="qsr_enabled">QSR Module</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="kot_enabled" name="kot_enabled" value="1" <?php echo $category->kot_enabled ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="kot_enabled">KOT Module</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="premeal_enabled" name="premeal_enabled" value="1" <?php echo $category->premeal_enabled ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="premeal_enabled">PREMEAL Module</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Category Type -->
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary" value="1" <?php echo $category->is_primary ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_primary">Primary/Featured Category</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" id="submit_button" class="btn btn-primary">
                                    <i class="uil uil-check me-1"></i> Update Category
                                </button>
                                <a href="<?php echo base_url('client/categories'); ?>" class="btn btn-secondary">
                                    <i class="uil uil-times me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
