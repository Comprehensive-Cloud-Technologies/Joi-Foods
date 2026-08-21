<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Products</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('client/dashboard'); ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Products</li>
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
                                <p class="stat-label">Total Products</p>
                                <h3 class="stat-value"><?php echo count($products); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-shopping-basket"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-primary-subtle text-primary">
                            <i class="mdi mdi-cart"></i> All Products
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Products</p>
                                <?php $active_count = count(array_filter($products, function($p) { return $p->is_active == 1; })); ?>
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
                                <p class="stat-label">Featured Products</p>
                                <?php $featured_count = count(array_filter($products, function($p) { return $p->is_featured == 1; })); ?>
                                <h3 class="stat-value"><?php echo $featured_count; ?></h3>
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
                                <p class="stat-label">PREMEAL Items</p>
                                <?php $premeal_count = count(array_filter($products, function($p) { return $p->premeal_enabled == 1; })); ?>
                                <h3 class="stat-value"><?php echo $premeal_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-restaurant"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-badge bg-warning-subtle text-warning">
                            <i class="mdi mdi-food"></i> PREMEAL
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <?php
            // Build unique categories list for the filter dropdown
            $filter_categories = [];
            foreach ($products as $_p) {
                if (!empty($_p->category_name) && !in_array($_p->category_name, $filter_categories, true)) {
                    $filter_categories[] = $_p->category_name;
                }
            }
            sort($filter_categories);
        ?>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body py-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Category</label>
                                <select class="form-control" id="filter_category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($filter_categories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-1">Module</label>
                                <select class="form-control" id="filter_module">
                                    <option value="">All Modules</option>
                                    <option value="QSR">QSR</option>
                                    <option value="KOT">KOT</option>
                                    <option value="PREMEAL">PREMEAL</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-light" id="filter_reset_btn">
                                    <i class="uil uil-redo me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card list-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Products List</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="<?php echo base_url('client/products/bulk_import'); ?>" class="btn btn-outline-primary">
                                    <i class="uil uil-import me-1"></i> Bulk Import
                                </a>
                                <a href="<?php echo base_url('client/products/add'); ?>" class="btn btn-primary">
                                    <i class="uil uil-plus me-1"></i> Add Product
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Modules</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($products)) { $i = 1; foreach ($products as $product) {
                                        $row_modules = [];
                                        if (!empty($product->qsr_enabled))     $row_modules[] = 'QSR';
                                        if (!empty($product->kot_enabled))     $row_modules[] = 'KOT';
                                        if (!empty($product->premeal_enabled)) $row_modules[] = 'PREMEAL';
                                    ?>
                                        <tr data-category="<?php echo htmlspecialchars($product->category_name ?? ''); ?>" data-modules="<?php echo implode(',', $row_modules); ?>">
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($product->thumbnail)): ?>
                                                        <div class="avatar-sm me-3">
                                                            <img src="<?php echo base_url($product->thumbnail); ?>" alt="<?php echo $product->name; ?>" class="rounded img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-soft-primary rounded text-primary">
                                                                <i class="uil uil-shopping-basket font-size-16"></i>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo $product->name; ?></h6>
                                                        <?php if ($product->description): ?>
                                                            <small class="text-muted"><?php echo substr($product->description, 0, 50); ?><?php echo strlen($product->description) > 50 ? '...' : ''; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($product->category_name): ?>
                                                    <span class="badge bg-soft-secondary text-secondary"><?php echo $product->category_name; ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong>₹<?php echo number_format($product->base_price, 2); ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($product->qsr_enabled): ?>
                                                    <span class="badge bg-soft-info text-info me-1">QSR</span>
                                                <?php endif; ?>
                                                <?php if ($product->kot_enabled): ?>
                                                    <span class="badge bg-soft-warning text-warning me-1">KOT</span>
                                                <?php endif; ?>
                                                <?php if ($product->premeal_enabled): ?>
                                                    <span class="badge bg-soft-success text-success me-1">PREMEAL</span>
                                                    <div class="mt-1">
                                                        <?php if ($product->breakfast): ?><small class="badge bg-soft-primary text-primary">Breakfast</small><?php endif; ?>
                                                        <?php if ($product->lunch): ?><small class="badge bg-soft-primary text-primary">Lunch</small><?php endif; ?>
                                                        <?php if ($product->dinner): ?><small class="badge bg-soft-primary text-primary">Dinner</small><?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!$product->qsr_enabled && !$product->kot_enabled && !$product->premeal_enabled): ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product->is_featured): ?>
                                                    <span class="badge bg-soft-primary text-primary">Featured</span>
                                                <?php else: ?>
                                                    <span class="text-muted">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <?php if ($product->is_active == 1): ?>
                                                        <button type="button" class="btn btn-soft-success action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Deactivate" onclick="toggleStatus(<?php echo $product->id; ?>, 0)">
                                                            <i class="uil uil-check-circle"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-soft-secondary action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Activate" onclick="toggleStatus(<?php echo $product->id; ?>, 1)">
                                                            <i class="uil uil-ban"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <a href="<?php echo base_url('client/products/edit/' . $product->id); ?>" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="uil uil-pen"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteProduct(<?php echo $product->id; ?>)">
                                                        <i class="uil uil-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No products found</td>
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
