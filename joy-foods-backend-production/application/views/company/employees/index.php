<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="page-title-custom">Employee Management</h4>
                    <button type="button" class="btn btn-primary" onclick="openAddModal()">
                        <i class="mdi mdi-plus me-1"></i> Add Employee
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
                                <p class="stat-label">Total Employees</p>
                                <h3 class="stat-value"><?php echo count($employees); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-primary">
                                <i class="uil uil-users-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Active Employees</p>
                                <?php $active_count = count(array_filter($employees, function ($e) {
                                    return $e->is_active == 1;
                                })); ?>
                                <h3 class="stat-value"><?php echo $active_count; ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-success">
                                <i class="uil uil-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Departments</p>
                                <h3 class="stat-value"><?php echo count($departments); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-info">
                                <i class="uil uil-sitemap"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="stat-card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <p class="stat-label">Policies Available</p>
                                <h3 class="stat-value"><?php echo count($policies); ?></h3>
                            </div>
                            <div class="stat-icon icon-bg-warning">
                                <i class="uil uil-file-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card list-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Employees List</h5>
                        <div class="table-responsive">
                            <table id="datatable" class="table table-hover" style="width: 100%;">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Employee</th>
                                        <th>Contact</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Access</th>
                                        <th>Wallet</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($employees)) {
                                        $i = 1;
                                        foreach ($employees as $emp) { ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-soft-primary rounded-circle text-primary">
                                                                <?php echo strtoupper(substr($emp->first_name, 0, 1)); ?>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo $emp->first_name . ' ' . $emp->last_name; ?></h6>
                                                            <small class="text-muted"><?php echo $emp->employee_code; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="uil uil-envelope text-muted me-1"></i>
                                                        <small><?php echo $emp->email; ?></small>
                                                        <?php if ($emp->email_verified_at): ?>
                                                            <i class="uil uil-check-circle text-success" title="Verified"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($emp->phone): ?>
                                                        <div>
                                                            <i class="uil uil-phone text-muted me-1"></i>
                                                            <small><?php echo $emp->phone; ?></small>
                                                            <?php if ($emp->phone_verified_at): ?>
                                                                <i class="uil uil-check-circle text-success" title="Verified"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $emp->department_name ?: '-'; ?></td>
                                                <td><?php echo $emp->designation ?: '-'; ?></td>
                                                <td>
                                                    <?php if ($emp->kot_permission): ?>
                                                        <span class="badge bg-primary-subtle text-primary">KOT</span>
                                                    <?php endif; ?>
                                                    <?php if ($emp->qsr_access): ?>
                                                        <span class="badge bg-success-subtle text-success">QSR</span>
                                                    <?php endif; ?>
                                                    <?php if ($emp->premeal_access): ?>
                                                        <span class="badge bg-info-subtle text-info">Premeal</span>
                                                    <?php endif; ?>
                                                    <?php if (!$emp->kot_permission && !$emp->qsr_access && !$emp->premeal_access): ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $wb = round((float)($emp->wallet_balance ?? 0), 2);
                                                    $wb_class = $wb > 0 ? 'text-success' : ($wb < 0 ? 'text-danger' : 'text-muted');
                                                    ?>
                                                    <span class="fw-medium <?php echo $wb_class; ?>">&#8377;<?php echo number_format($wb, 2); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($emp->is_active == 1): ?>
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    <?php endif; ?>
                                                    <?php if ($emp->is_registered): ?>
                                                        <span class="badge bg-info-subtle text-info">Registered</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <?php if (!empty($current_user_permissions->can_manual_credit) || !empty($current_user_permissions->can_manual_debit)): ?>
                                                        <button type="button" class="btn btn-soft-success action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Manual Wallet Adjustment" onclick="openCreditModal(<?php echo $emp->id; ?>, '<?php echo htmlspecialchars($emp->first_name . ' ' . $emp->last_name, ENT_QUOTES); ?>', <?php echo $wb; ?>)">
                                                            <i class="uil uil-wallet"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <?php if (!empty($current_user_permissions->can_razorpay_recharge)): ?>
                                                        <button type="button" class="btn btn-soft-info action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Recharge via Razorpay" onclick="openRazorpayModal(<?php echo $emp->id; ?>, '<?php echo htmlspecialchars($emp->first_name . ' ' . $emp->last_name, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($emp->email, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($emp->phone ?? '', ENT_QUOTES); ?>', <?php echo $wb; ?>)">
                                                            <i class="uil uil-bill"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-soft-warning action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" onclick="editEmployee(<?php echo $emp->id; ?>)">
                                                            <i class="uil uil-pen"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-soft-<?php echo $emp->is_active ? 'secondary' : 'success'; ?> action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $emp->is_active ? 'Deactivate' : 'Activate'; ?>" onclick="toggleStatus(<?php echo $emp->id; ?>)">
                                                            <i class="uil uil-<?php echo $emp->is_active ? 'ban' : 'check'; ?>"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-soft-danger action-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="deleteEmployee(<?php echo $emp->id; ?>)">
                                                            <i class="uil uil-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add/Edit Employee Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="employee_form" action="<?php echo base_url('company/employees/store'); ?>">
                <input type="hidden" name="id" id="employee_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employee Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="employee_code" id="employee_code" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateCode()" title="Generate Code">
                                        <i class="uil uil-sync"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select class="form-control select2-modal" name="department_id" id="department_id">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept->id; ?>"><?php echo $dept->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" id="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="last_name" id="last_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" id="phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Designation</label>
                                <input type="text" class="form-control" name="designation" id="designation">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Employment Type</label>
                                <select class="form-control" name="employment_type" id="employment_type">
                                    <option value="FULL_TIME">Full Time</option>
                                    <option value="PART_TIME">Part Time</option>
                                    <option value="CONTRACT">Contract</option>
                                    <option value="INTERN">Intern</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date of Joining</label>
                                <input type="text" class="form-control" name="date_of_joining" id="date_of_joining" readonly placeholder="Select date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-control" name="gender" id="gender">
                                    <option value="">Select Gender</option>
                                    <option value="MALE">Male</option>
                                    <option value="FEMALE">Female</option>
                                    <option value="OTHER">Other</option>
                                    <option value="PREFER_NOT_TO_SAY">Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <!-- RFID Card -->
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-3">RFID Card</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="rfid_card_number">RFID Card Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="uil uil-credit-card"></i></span>
                                    <input type="text" class="form-control" name="rfid_card_number" id="rfid_card_number" placeholder="Click here, then scan card on the HID reader" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary" id="rfid_clear_btn" title="Clear">
                                        <i class="uil uil-times"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="uil uil-info-circle me-1"></i>Click the input above and tap the card on the HID RFID reader. The card number will fill in automatically.
                                </small>
                                <div id="rfid_status" class="mt-1" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="rfid_card_issued_at">Card Issued On</label>
                                <input type="text" class="form-control" name="rfid_card_issued_at" id="rfid_card_issued_at" readonly placeholder="Auto-filled when card is scanned">
                                <small class="text-muted">Date the RFID card was issued. You can edit this if needed.</small>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-3">Login Credentials</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password <span class="text-danger password-required">*</span></label>
                                <input type="password" class="form-control" name="password" id="password">
                                <small class="text-muted edit-password-hint" style="display: none;">Leave blank to keep current password</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger password-required">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password">
                            </div>
                        </div>

                        <!-- Policy -->
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-3">Assign Policy</h6>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label">Policy</label>
                                <select class="form-control select2-modal" name="policy_id" id="policy_id">
                                    <option value="">Select Policy</option>
                                    <?php foreach ($policies as $policy): ?>
                                        <option value="<?php echo $policy->id; ?>" <?php echo $policy->is_default ? 'selected' : ''; ?>>
                                            <?php echo $policy->name; ?> (<?php echo $policy->policy_code; ?>) - <?php echo $policy->policy_type; ?>
                                            <?php if ($policy->is_default): ?> [Default]<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Select a policy to assign to this employee</small>
                            </div>
                        </div>

                        <!-- Access Permissions -->
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-3">Access Permissions</h6>
                            <?php
                                $any_module = $company->qsr_enabled || $company->premeal_enabled || $company->delivery_enabled;
                                if (!$any_module):
                            ?>
                                <div class="alert alert-warning mb-3">
                                    <i class="uil uil-exclamation-triangle me-1"></i>No modules are enabled for your company. Contact your administrator.
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($company->delivery_enabled): ?>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="kot_permission" id="kot_permission">
                                <label class="form-check-label" for="kot_permission">KOT Access</label>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($company->qsr_enabled): ?>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="qsr_access" id="qsr_access" checked>
                                <label class="form-check-label" for="qsr_access">QSR Access</label>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($company->premeal_enabled): ?>
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="premeal_access" id="premeal_access">
                                <label class="form-check-label" for="premeal_access">Premeal Access</label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Status -->
                        <div class="col-12">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submit_add_another">
                        <i class="uil uil-plus me-1"></i> Save &amp; Add Another
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_button">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Wallet Adjust Modal -->
<div class="modal fade" id="creditModal" tabindex="-1" aria-labelledby="creditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="creditModalLabel">Wallet Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="credit_form">
                <input type="hidden" name="employee_id" id="credit_employee_id">
                <input type="hidden" name="action" id="credit_action" value="credit">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="uil uil-user-circle font-size-20 me-2"></i>
                            <div>
                                <strong id="credit_employee_name"></strong>
                                <div class="small">Current Balance: <span id="credit_current_balance" class="fw-medium"></span></div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $can_credit = !empty($current_user_permissions->can_manual_credit);
                    $can_debit  = !empty($current_user_permissions->can_manual_debit);
                    $show_action_toggle = $can_credit && $can_debit;
                    $default_action = $can_credit ? 'credit' : 'debit';
                    ?>
                    <?php if ($show_action_toggle): ?>
                    <div class="mb-3">
                        <label class="form-label">Action <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="action_radio" id="action_credit" value="credit" checked>
                            <label class="btn btn-outline-success" for="action_credit"><i class="uil uil-plus me-1"></i>Add Money</label>
                            <input type="radio" class="btn-check" name="action_radio" id="action_debit" value="debit">
                            <label class="btn btn-outline-danger" for="action_debit"><i class="uil uil-minus me-1"></i>Deduct Money</label>
                        </div>
                    </div>
                    <?php endif; ?>
                    <input type="hidden" name="action_radio_default" value="<?php echo $default_action; ?>">
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" class="form-control" name="amount" id="credit_amount" min="1" max="50000" step="0.01" placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">Maximum: &#8377;50,000</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" id="credit_reason" rows="2" maxlength="255" placeholder="e.g., Monthly allowance, Bonus, Penalty, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="credit_submit_button">
                        <i class="uil uil-wallet me-1"></i> Add Money
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty($current_user_permissions->can_razorpay_recharge)): ?>
<!-- Razorpay Recharge Modal -->
<div class="modal fade" id="razorpayModal" tabindex="-1" aria-labelledby="razorpayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="razorpayModalLabel"><i class="uil uil-bill me-1"></i> Razorpay Recharge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="razorpay_form">
                <input type="hidden" name="employee_id" id="rzp_employee_id">
                <input type="hidden" id="rzp_employee_email">
                <input type="hidden" id="rzp_employee_phone">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-center">
                            <i class="uil uil-user-circle font-size-24 me-2"></i>
                            <div>
                                <div class="fw-medium" id="rzp_employee_name">-</div>
                                <small class="text-muted">Current balance: &#8377;<span id="rzp_current_balance">0.00</span></small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" class="form-control" name="amount" id="rzp_amount" min="1" max="50000" step="0.01" placeholder="Enter amount" required>
                        </div>
                        <small class="text-muted">You will be redirected to Razorpay to complete the payment. Maximum: &#8377;50,000</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="rzp_submit_button">
                        <i class="uil uil-bill me-1"></i> Pay &amp; Recharge
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php endif; ?>