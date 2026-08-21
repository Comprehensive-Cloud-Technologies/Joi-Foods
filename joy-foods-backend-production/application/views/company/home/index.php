<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="page-title-custom mb-1">Dashboard</h4>
                        <p class="text-muted mb-0">Welcome back, <?php echo get_company_sessiondata('first_name'); ?>!</p>
                    </div>
                    <div>
                        <span class="badge bg-success-subtle text-success fs-6 p-2">
                            <i class="uil uil-building me-1"></i>
                            <?php echo !empty($company) ? $company->name : ''; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Employees</p>
                                <h3 class="stat-value"><?php echo $stats['employees']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="<?php echo base_url('company/employees'); ?>" class="text-primary">
                            View all <i class="uil uil-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Departments</p>
                                <h3 class="stat-value"><?php echo $stats['departments']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-sitemap"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <a href="<?php echo base_url('company/departments'); ?>" class="text-success">
                            Manage <i class="uil uil-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Today's Orders</p>
                                <h3 class="stat-value"><?php echo $stats['today_orders']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo date('d M Y'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">This Month Orders</p>
                                <h3 class="stat-value"><?php echo $stats['month_orders']; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
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

        <!-- Contribution Cards - This Month -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Company Contribution</p>
                                <h3 class="stat-value">₹ <?php echo number_format($contributions['this_month']['company_contribution'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-danger">
                                <i class="uil uil-bill"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <?php
                        $last = $contributions['last_month']['company_contribution'];
                        $current = $contributions['this_month']['company_contribution'];
                        if ($last > 0) {
                            $change = round((($current - $last) / $last) * 100, 1);
                            $arrow = $change >= 0 ? 'uil-arrow-up' : 'uil-arrow-down';
                            $color = $change >= 0 ? 'danger' : 'success';
                        ?>
                            <span class="text-<?php echo $color; ?>">
                                <i class="uil <?php echo $arrow; ?>"></i> <?php echo abs($change); ?>%
                            </span>
                            <span class="text-muted ms-1">vs last month</span>
                        <?php } else { ?>
                            <span class="text-muted"><?php echo $contributions['this_month']['label']; ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Employee Contribution</p>
                                <h3 class="stat-value">₹ <?php echo number_format($contributions['this_month']['employee_contribution'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-wallet"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo $contributions['this_month']['label']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Total Order Value</p>
                                <h3 class="stat-value">₹ <?php echo number_format($contributions['this_month']['total'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-receipt"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo $contributions['this_month']['label']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Last Month Total</p>
                                <h3 class="stat-value">₹ <?php echo number_format($contributions['last_month']['total'], 2); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-history"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-footer">
                        <span class="text-muted"><?php echo $contributions['last_month']['label']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
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
                        <h5 class="card-title mb-3">Contribution Trend <small class="text-muted fw-normal">(Last 6 Months)</small></h5>
                        <div id="contribution_trend_chart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department-wise KOT Billing -->
        <?php if ($company->delivery_enabled): ?>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card list-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="card-title mb-0">Department-wise KOT Billing <small class="text-muted fw-normal">(<?php echo date('M Y'); ?>)</small></h5>
                                <span class="badge bg-warning-subtle text-warning p-2">
                                    <i class="uil uil-restaurant me-1"></i> Pantry / Delivery Orders
                                </span>
                            </div>
                            <?php if (!empty($dept_billing)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Department</th>
                                                <th class="text-center">Orders</th>
                                                <th class="text-end">Total Amount</th>
                                                <th class="text-end">Company Paid</th>
                                                <th class="text-end">Employee Paid</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sl = 1;
                                            $grand_total = 0;
                                            $grand_company = 0;
                                            $grand_employee = 0;
                                            $grand_orders = 0;
                                            foreach ($dept_billing as $dept):
                                                $grand_total += $dept->total;
                                                $grand_company += $dept->company_share;
                                                $grand_employee += $dept->employee_share;
                                                $grand_orders += $dept->order_count;
                                            ?>
                                                <tr>
                                                    <td><?php echo $sl++; ?></td>
                                                    <td>
                                                        <span class="fw-medium"><?php echo $dept->department_name ?? 'Unassigned'; ?></span>
                                                        <?php if (!empty($dept->department_code)): ?>
                                                            <small class="text-muted ms-1">(<?php echo $dept->department_code; ?>)</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center"><?php echo $dept->order_count; ?></td>
                                                    <td class="text-end">₹ <?php echo number_format($dept->total, 2); ?></td>
                                                    <td class="text-end text-danger fw-medium">₹ <?php echo number_format($dept->company_share, 2); ?></td>
                                                    <td class="text-end">₹ <?php echo number_format($dept->employee_share, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr class="fw-bold">
                                                <td colspan="2">Total</td>
                                                <td class="text-center"><?php echo $grand_orders; ?></td>
                                                <td class="text-end">₹ <?php echo number_format($grand_total, 2); ?></td>
                                                <td class="text-end text-danger">₹ <?php echo number_format($grand_company, 2); ?></td>
                                                <td class="text-end">₹ <?php echo number_format($grand_employee, 2); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="uil uil-restaurant font-size-24 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">No KOT orders this month</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Company Info -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Company Info</h5>
                        <div class="d-flex flex-wrap gap-4 align-items-center">
                            <div>
                                <label class="text-muted small">Company Code</label>
                                <p class="mb-0 fw-medium"><code><?php echo $company->company_code; ?></code></p>
                            </div>
                            <div>
                                <label class="text-muted small">Modules Enabled</label>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <?php if ($company->qsr_enabled): ?>
                                        <span class="badge bg-primary p-2">QSR</span>
                                    <?php endif; ?>
                                    <?php if ($company->premeal_enabled): ?>
                                        <span class="badge bg-info p-2">Premeal</span>
                                    <?php endif; ?>
                                    <?php if ($company->delivery_enabled): ?>
                                        <span class="badge bg-warning p-2">KOT / Delivery</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
