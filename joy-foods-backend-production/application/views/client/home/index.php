<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="page-title-custom mb-1">Dashboard</h4>
                <p class="text-muted mb-0">Welcome back, <?php echo get_client_sessiondata('first_name'); ?>!</p>
            </div>
        </div>

        <!-- Quick Stats Row 1: Infrastructure -->
        <div class="row g-3 mb-4">
            <div class="col-xxl-2 col-xl-4 col-md-4 col-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Companies</p>
                                <h3 class="stat-value"><?php echo $stats['companies']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-building"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="<?php echo base_url('client/companies'); ?>" class="text-primary">View all <i class="uil uil-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-4 col-md-4 col-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Stores</p>
                                <h3 class="stat-value"><?php echo $stats['stores']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-store"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="<?php echo base_url('client/stores'); ?>" class="text-success">Manage <i class="uil uil-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-4 col-md-4 col-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Employees</p>
                                <h3 class="stat-value"><?php echo $stats['employees']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted">Across all companies</span>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-4 col-md-4 col-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Products</p>
                                <h3 class="stat-value"><?php echo $stats['products']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-shopping-basket"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="<?php echo base_url('client/products'); ?>" class="text-warning">View all <i class="uil uil-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-4 col-md-4 col-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Today's Orders</p>
                                <h3 class="stat-value"><?php echo $stats['today_orders']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo date('d M Y'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-4 col-md-4 col-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Month Orders</p>
                                <h3 class="stat-value"><?php echo $stats['month_orders']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo date('M Y'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">This Month Revenue</p>
                                <h3 class="stat-value">₹ <?php echo number_format($revenue['this_month']['total'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-rupee-sign"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <?php
                        $last_total = $revenue['last_month']['total'];
                        $curr_total = $revenue['this_month']['total'];
                        if ($last_total > 0) {
                            $change = round((($curr_total - $last_total) / $last_total) * 100, 1);
                            $arrow = $change >= 0 ? 'uil-arrow-up' : 'uil-arrow-down';
                            $color = $change >= 0 ? 'success' : 'danger';
                        ?>
                            <span class="text-<?php echo $color; ?>"><i class="uil <?php echo $arrow; ?>"></i> <?php echo abs($change); ?>%</span>
                            <span class="text-muted ms-1">vs last month</span>
                        <?php } else { ?>
                            <span class="text-muted"><?php echo $revenue['this_month']['label']; ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Company Contributions</p>
                                <h3 class="stat-value">₹ <?php echo number_format($revenue['this_month']['company_contribution'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-bill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo $revenue['this_month']['label']; ?> - Billed to companies</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Employee Payments</p>
                                <h3 class="stat-value">₹ <?php echo number_format($revenue['this_month']['employee_contribution'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-wallet"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo $revenue['this_month']['label']; ?> - Wallet + Online</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Last Month Revenue</p>
                                <h3 class="stat-value">₹ <?php echo number_format($revenue['last_month']['total'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-history"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo $revenue['last_month']['label']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row: Module Donut + Revenue Trend -->
        <div class="row g-3 mb-4">
            <div class="col-xl-4">
                <div class="card list-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Module-wise Orders <small class="text-muted fw-normal">(<?php echo date('M Y'); ?>)</small></h5>
                        <div id="module_orders_chart"></div>
                        <div class="mt-3">
                            <?php foreach ($module_stats as $module => $ms): ?>
                                <div class="d-flex justify-content-between align-items-center <?php echo $module != 'PREMEAL' ? 'mb-2 pb-2 border-bottom' : ''; ?>">
                                    <div>
                                        <span class="badge bg-<?php echo $module == 'QSR' ? 'primary' : ($module == 'KOT' ? 'warning' : 'info'); ?>-subtle text-<?php echo $module == 'QSR' ? 'primary' : ($module == 'KOT' ? 'warning' : 'info'); ?> me-2"><?php echo $module; ?></span>
                                        <span class="text-muted small"><?php echo $ms['order_count']; ?> orders</span>
                                    </div>
                                    <span class="fw-medium">₹ <?php echo number_format($ms['total'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card list-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Revenue Trend <small class="text-muted fw-normal">(Last 6 Months)</small></h5>
                        <div id="revenue_trend_chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company-wise Billing + Store-wise Orders -->
        <div class="row g-3 mb-4">
            <div class="col-xl-7">
                <div class="card list-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Company-wise Billing <small class="text-muted fw-normal">(<?php echo date('M Y'); ?>)</small></h5>
                        <?php if (!empty($company_billing)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Company</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-end">Company Paid</th>
                                            <th class="text-end">Employee Paid</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $g_orders = 0; $g_company = 0; $g_employee = 0; $g_total = 0;
                                        foreach ($company_billing as $cb):
                                            $g_orders += $cb->order_count;
                                            $g_company += $cb->company_share;
                                            $g_employee += $cb->employee_share;
                                            $g_total += $cb->total;
                                        ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-medium"><?php echo $cb->company_name ?? 'Unknown'; ?></span>
                                                    <small class="text-muted d-block"><?php echo $cb->company_code ?? ''; ?></small>
                                                </td>
                                                <td class="text-center"><?php echo $cb->order_count; ?></td>
                                                <td class="text-end text-danger">₹ <?php echo number_format($cb->company_share, 2); ?></td>
                                                <td class="text-end">₹ <?php echo number_format($cb->employee_share, 2); ?></td>
                                                <td class="text-end fw-medium">₹ <?php echo number_format($cb->total, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr class="fw-bold">
                                            <td>Total</td>
                                            <td class="text-center"><?php echo $g_orders; ?></td>
                                            <td class="text-end text-danger">₹ <?php echo number_format($g_company, 2); ?></td>
                                            <td class="text-end">₹ <?php echo number_format($g_employee, 2); ?></td>
                                            <td class="text-end">₹ <?php echo number_format($g_total, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="uil uil-building font-size-24 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">No orders this month</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card list-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Store-wise Orders <small class="text-muted fw-normal">(<?php echo date('M Y'); ?>)</small></h5>
                        <?php if (!empty($store_orders)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Store</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-end">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($store_orders as $so): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-medium"><?php echo $so->store_name ?? 'Unknown'; ?></span>
                                                    <?php if (!empty($so->store_type)): ?>
                                                        <span class="badge bg-<?php echo $so->store_type == 'QSR' ? 'primary' : ($so->store_type == 'KOT' ? 'warning' : 'info'); ?>-subtle text-<?php echo $so->store_type == 'QSR' ? 'primary' : ($so->store_type == 'KOT' ? 'warning' : 'info'); ?> ms-1"><?php echo $so->store_type; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?php echo $so->order_count; ?></td>
                                                <td class="text-end fw-medium">₹ <?php echo number_format($so->total, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="uil uil-store font-size-24 text-muted"></i>
                                <p class="text-muted mt-2 mb-0">No store orders this month</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
