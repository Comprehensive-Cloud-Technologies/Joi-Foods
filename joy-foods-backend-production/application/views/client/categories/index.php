<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Categories</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/dashboard'); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Categories</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Categories</p>
                                <h3 class="stat-value"><?php echo count($categories); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-layer-group"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-primary-subtle text-primary">
                            <i class="mdi mdi-folder"></i> All Categories
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Categories</p>
                                <?php $active_count = count(array_filter($categories, function($c) { return $c->is_active == 1; })); ?>
                                <h3 class="stat-value"><?php echo $active_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-success-subtle text-success">
                            <i class="mdi mdi-check-circle"></i> Active
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="stat-label">Primary Categories</p>
                                <?php $primary_count = count(array_filter($categories, function($c) { return $c->is_primary == 1; })); ?>
                                <h3 class="stat-value"><?php echo $primary_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-info-subtle text-info">
                            <i class="mdi mdi-star"></i> Featured
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="stat-label">Top Level</p>
                                <?php $top_level = count(array_filter($categories, function($c) { return $c->parent_id == null; })); ?>
                                <h3 class="stat-value"><?php echo $top_level; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-sitemap"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-warning-subtle text-warning">
                            <i class="mdi mdi-folder-outline"></i> Parent Categories
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Categories List</h5>
                            <a href="<?php echo base_url('client/categories/add'); ?>" class="btn btn-primary">
                                <i class="uil uil-plus me-1"></i> Add Category
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Category</th>
                                        <th>Parent Category</th>
                                        <th>Modules</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($categories)) { $i = 1; foreach ($categories as $category) { ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($category->thumbnail)): ?>
                                                        <div class="avatar-sm me-3">
                                                            <img src="<?php echo base_url($category->thumbnail); ?>" alt="<?php echo $category->name; ?>" class="rounded img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </div>
                                                    <?php elseif (!empty($category->icon)): ?>
                                                        <div class="avatar-sm me-3">
                                                            <img src="<?php echo base_url($category->icon); ?>" alt="<?php echo $category->name; ?>" class="rounded img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-soft-primary rounded text-primary">
                                                                <i class="uil uil-layer-group font-size-16"></i>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $category->name; ?></h6>
                                                        <?php if ($category->description): ?>
                                                            <small class="text-muted"><?php echo substr($category->description, 0, 50); ?><?php echo strlen($category->description) > 50 ? '...' : ''; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($category->parent_category_name): ?>
                                                    <span class="badge bg-soft-secondary text-secondary"><?php echo $category->parent_category_name; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($category->qsr_enabled): ?>
                                                    <span class="badge bg-soft-info text-info me-1">QSR</span>
                                                <?php endif; ?>
                                                <?php if ($category->kot_enabled): ?>
                                                    <span class="badge bg-soft-warning text-warning me-1">KOT</span>
                                                <?php endif; ?>
                                                <?php if ($category->premeal_enabled): ?>
                                                    <span class="badge bg-soft-success text-success me-1">PREMEAL</span>
                                                <?php endif; ?>
                                                <?php if (!$category->qsr_enabled && !$category->kot_enabled && !$category->premeal_enabled): ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($category->is_primary): ?>
                                                    <span class="badge bg-soft-primary text-primary">Primary</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <?php if ($category->is_active == 1): ?>
                                                        <button type="button" class="btn btn-soft-success action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Deactivate" onclick="toggleStatus(<?php echo $category->id; ?>, 0)">
                                                            <i class="uil uil-check-circle"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-soft-secondary action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Activate" onclick="toggleStatus(<?php echo $category->id; ?>, 1)">
                                                            <i class="uil uil-ban"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <a href="<?php echo base_url('client/categories/edit/' . $category->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteCategory(<?php echo $category->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No categories found</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
