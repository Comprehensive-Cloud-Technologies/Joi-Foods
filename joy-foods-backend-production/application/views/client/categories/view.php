<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Category Details</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/categories'); ?>">Categories</a></li>
                            <li class="breadcrumb-item active">View Category</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-12">
                <!-- Category Header -->
                <div class="card" style="border: 1px solid #e9ecef; margin-bottom: 24px;">
                    <div class="card-body" style="padding: 24px;">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <?php if (!empty($category->thumbnail)): ?>
                                    <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef; flex-shrink: 0;">
                                        <img src="<?php echo base_url($category->thumbnail); ?>" alt="<?php echo $category->name; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php elseif (!empty($category->icon)): ?>
                                    <div style="width: 80px; height: 80px; border-radius: 8px; background: #f8f9fa; border: 1px solid #e9ecef; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="<?php echo $category->icon; ?>" style="font-size: 32px; color: #6c757d;"></i>
                                    </div>
                                <?php else: ?>
                                    <div style="width: 80px; height: 80px; border-radius: 8px; background: #f8f9fa; border: 1px solid #e9ecef; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="uil uil-layer-group" style="font-size: 32px; color: #6c757d;"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h4 class="mb-2" style="color: #212529; font-weight: 600; font-size: 22px;"><?php echo $category->name; ?></h4>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <?php if ($category->is_primary): ?>
                                            <span class="badge bg-primary" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                                Primary
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($category->qsr_enabled): ?>
                                            <span class="badge bg-info" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">QSR</span>
                                        <?php endif; ?>
                                        <?php if ($category->kot_enabled): ?>
                                            <span class="badge bg-warning" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">KOT</span>
                                        <?php endif; ?>
                                        <?php if ($category->premeal_enabled): ?>
                                            <span class="badge bg-success" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">PREMEAL</span>
                                        <?php endif; ?>
                                        <?php if ($category->is_active == 1): ?>
                                            <span class="badge bg-success" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger" style="padding: 5px 10px; font-size: 12px; font-weight: 500;">
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($category->parent_category_name): ?>
                                        <p class="text-muted mb-0" style="font-size: 14px;">
                                            <i class="uil uil-folder me-1"></i>Parent: <?php echo $category->parent_category_name; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <a href="<?php echo base_url('client/categories/edit/' . $category->id); ?>" class="btn btn-primary" style="padding: 8px 20px;">
                                    <i class="uil uil-pen me-1"></i> Edit
                                </a>
                                <a href="<?php echo base_url('client/categories'); ?>" class="btn btn-outline-secondary" style="padding: 8px 20px;">
                                    <i class="uil uil-arrow-left me-1"></i> Back
                                </a>
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
                                    <label class="text-muted mb-1">Category Name</label>
                                    <p class="mb-0 fw-medium"><?php echo $category->name; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Display Order</label>
                                    <p class="mb-0 fw-medium"><?php echo $category->display_order; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Parent Category</label>
                                    <p class="mb-0 fw-medium">
                                        <?php if ($category->parent_category_name): ?>
                                            <?php echo $category->parent_category_name; ?>
                                        <?php else: ?>
                                            <span class="text-muted">None (Top Level)</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted mb-1">Icon Class</label>
                                    <p class="mb-0 fw-medium">
                                        <?php if ($category->icon): ?>
                                            <i class="<?php echo $category->icon; ?> me-2"></i><?php echo $category->icon; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($category->description): ?>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="text-muted mb-1">Description</label>
                                        <p class="mb-0"><?php echo $category->description; ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
