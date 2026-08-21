<div class="container-fluid">
    <div class="topnav">

        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">


                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo base_url('admin_root/') ?>">
                            <i class="uil-home-alt me-2"></i> Dashboard
                        </a>
                    </li>



                    <?php
                    if (get_admin_userdetails()->user_type == 1) {
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('admin_root/branches') ?>">
                                <i class="uil-store"></i> Branches
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo base_url('admin_root/manager') ?>">
                                <i class="uil-user-square"></i> Manager`s
                            </a>
                        </li>
                    <?php
                    }
                    ?>



                    <?php
                    if (get_admin_userdetails()->user_type == 1) {
                    ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                                <i class="uil-shopping-basket"></i>Catlog <div class="arrow-down"></div>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="topnav-pages">
                                <a class="dropdown-item" href="<?php echo base_url('admin_root/categorys') ?>">Categorys</a>
                                <a class="dropdown-item" href="<?php echo base_url('admin_root/products') ?>">Items</a>
                                <a class="dropdown-item" href="<?php echo base_url('admin_root/products/items_in_branch') ?>">Branch Items</a>
                            </div>
                        </li>
                    <?php
                    }
                    ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-pages" role="button">
                            <i class="uil-schedule"></i>Reports <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-pages">
                            <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/sales') ?>">Sale report</a>
                            <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/item_wise') ?>">Item Wise Sales</a>
                            <?php
                            if (get_admin_userdetails()->user_type == 1) {
                            ?>
                                <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/discount') ?>">Discount report</a>
                                <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/customers') ?>">Customers</a>
                                <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/suspicious_activities') ?>">Suspicious Activities</a>
                                <!-- <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/online_customers') ?>">Online Users</a> -->
                                <!-- <a class="dropdown-item" href="<?php echo base_url('admin_root/reports/feedbacks') ?>">Feedbacks</a> -->
                            <?php
                            }
                            ?>



                        </div>
                    </li>


                </ul>
            </div>
        </nav>
    </div>
</div>