<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="max-age=604800" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('assets/') ?>images/favicon.ico">

    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/') ?>libs/toastr/build/toastr.min.css">
    <!-- place here plugins start -->
    <?php
    if (isset($form_validation)) {
    ?>
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/validation/formValidation.css">
    <?php
    }

    if (isset($datatable)) {
    ?>
        <!-- DataTables -->
        <link href="<?php echo base_url(); ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo base_url(); ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <?php
    }
    if (isset($datatable_buttons)) {
    ?>
        <link href="<?php echo base_url(); ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <?php
    }
    if (isset($sweet_alert)) {
    ?>
        <!-- Sweet Alert-->
        <link href="<?php echo base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <?php
    }

    if (isset($select_2)) {
    ?>
        <!-- Sweet Alert-->
        <link href="<?php echo base_url(); ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <?php
    }
    if (isset($datepicker)) {
    ?>
        <link href="<?php echo base_url(); ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/libs/@chenfengyuan/datepicker/datepicker.min.css">
    <?php
    }

    if (isset($js_tree)) {
    ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <?php
    }

    if (isset($full_calendar)) {
    ?>
        <link href="<?php echo base_url(); ?>assets/libs/@fullcalendar/core/main.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/libs/@fullcalendar/daygrid/main.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/libs/@fullcalendar/bootstrap/main.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/libs/@fullcalendar/timegrid/main.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url(); ?>assets/libs/@fullcalendar/list/main.min.css" rel="stylesheet" type="text/css" />
    <?php
    }

    if (isset($jq_ui)) {
    ?>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <?php
    }

    ?>
    <!-- place here plugins end -->
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url('assets/') ?>css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo base_url('assets/') ?>css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('assets/') ?>css/crcticons.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url('assets/') ?>css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <script>
        var base_url = '<?php echo base_url() ?>';
    </script>
    <style>
        small.help-block {
            color: #f46a6a;
        }

        /* .gradient-1 {
            background: #E8CBC0;
            background: -webkit-linear-gradient(to right, #636FA4, #E8CBC0);
            background: linear-gradient(to right, #636FA4, #E8CBC0);
            border-color: #E8CBC0 !important;
        } */

        .datepicker {
            border: 1px solid #f5f6f8;
            padding: 8px;
            z-index: 1009 !important;
        }


        /* .gradient-1 {
            background: #E8CBC0;
            background: -webkit-linear-gradient(to right, #636FA4, #E8CBC0);
            background: linear-gradient(to right, #636FA4, #E8CBC0);
            border-color: #E8CBC0 !important;
        } */

        .thumbnail img {
            width: 150px;
            height: 150px;
            padding: .25rem;
            max-width: 100%;
            height: auto;
        }

        .btn-file {
            position: relative;
            overflow: hidden;
        }

        .btn-file input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }

        .img-zone {
            background-color: #92f2da66;
            border: 5px dashed #28ae8d;
            border-radius: 5px;
            padding: 20px;

        }

        .img-zone h2 {
            margin-top: 0;
        }

        .progress,
        #img-preview {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .col-file-manager {
            float: left;
            width: auto;
            padding: 5px;
        }

        .file-box {
            display: block;
            width: 100%;
            border: 1px solid #eee;
            cursor: pointer;
            text-align: center;
            position: relative;
            border-radius: 2px;
        }

        .file-box .image-container {
            display: block;
            width: 122px;
            height: 100px;
            overflow: hidden;
            text-align: center;
            border-radius: 2px;
        }

        .file-box .image-container img {
            margin: 0 auto;
            position: relative;
            width: auto;
            min-width: 100%;
            max-width: none;
            height: 100%;
            margin-left: 50%;
            transform: translateX(-50%);
            object-fit: cover;
        }

        .file-box .file-name {
            width: 100%;
            position: absolute;
            bottom: 0;
            left: 0;
            font-size: 12px;
            line-height: 14px;
            background-color: #f4f4f4;
            padding: 2px;
            display: block;
            text-align: center;
            word-break: break-all;
        }

        .file-manager-content .selected {
            box-shadow: 0 0 3px rgba(40, 174, 141, 1);
            border: 1px solid #28ae8d;
        }

        .pGalClose {
            /* position: absolute; */
            top: 0;
            padding: 0;
            margin-left: -11px;
            margin-top: -8px;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            line-height: 22px;
            text-align: center;
            background-color: #dc3545 !important;
            color: #fff !important;
            opacity: 1;
            font-size: 13px;
        }

        .btn-delete-additional-image {
            position: absolute;
            top: 0;
            padding: 0;
            margin-left: -11px;
            margin-top: -8px;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            line-height: 22px;
            text-align: center;
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .btn-delete-additional-image:hover {
            background-color: #bd2130 !important;
        }

        .additional-image-list {
            width: auto;
        }

        .additional-image-list .additional-item {
            display: inline-block;
            margin: 8px;
            position: relative;
            border: 1px solid #eee;
        }

        .img-additional {
            margin: 0 10px 10px 10px;
            width: 200px;
            height: 150px;
        }

        .hidden {
            display: none;
        }

        /* Minimalist Dashboard & List Styles */
        .dashboard-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .dashboard-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .stat-card-body {
            padding: 1.5rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0.5rem 0 0.25rem;
            color: #2d3748;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #718096;
            font-weight: 500;
            margin: 0;
        }

        .stat-footer {
            padding: 0.75rem 1.5rem;
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
        }

        .stat-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .chart-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .chart-card-body {
            padding: 1.5rem;
        }

        .chart-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .page-title-custom {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0;
        }

        /* Icon backgrounds */
        .icon-bg-primary {
            background: rgba(81, 86, 190, 0.1);
            color: #5156be;
        }

        .icon-bg-success {
            background: rgba(42, 181, 125, 0.1);
            color: #2ab57d;
        }

        .icon-bg-warning {
            background: rgba(255, 191, 83, 0.1);
            color: #ffbf53;
        }

        .icon-bg-danger {
            background: rgba(253, 98, 94, 0.1);
            color: #fd625e;
        }

        .icon-bg-info {
            background: rgba(75, 166, 239, 0.1);
            color: #4ba6ef;
        }

        /* List Card Styles */
        .list-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .list-card .card-body {
            padding: 1.5rem;
        }

        .list-card .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2d3748;
        }

        /* Equal height cards - apply only where needed */
        .row-eq-height>[class*="col-"]>.card {
            height: 100%;
        }

        .row-eq-height .chart-card .chart-card-body {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .row-eq-height .chart-card .chart-card-body>div[id] {
            flex: 1;
        }

        /* Action button styles */
        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .h-slider {
            height: 50px;
        }

        #block_ui_animation {
            animation-name: ckw;
            animation-duration: 3s;
            animation-iteration-count: infinite;
            transform-origin: 50% 50%;
            display: inline-block;
        }



        @keyframes ckw {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 1200px) {
            .additional-image-list .btn-delete-additional-image {
                left: 0 !important;
            }
        }
    </style>
</head>


<body>

    <!-- Begin page -->
    <div id="layout-wrapper">


        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="<?php echo base_url('client') ?>" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?php echo base_url('assets/') ?>images/logo-sm.png" alt="" height="50">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo base_url('assets/') ?>images/logo-dark.png" alt="" height="20">
                            </span>
                        </a>

                        <a href="<?php echo base_url('client') ?>" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="<?php echo base_url('assets/') ?>images/logo-sm.png" alt="" height="50">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo base_url('assets/') ?>images/logo-light.png" alt="" height="20">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button id="back_button" type="button" class="btn btn-dark waves-effect">
                            <i class="uil-angle-left"></i> Back
                        </button>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                            <i class="uil-minus-path"></i>
                        </button>
                    </div>

                    <?php
                    $client_details = get_client_user_details();
                    $client_details->full_name = $client_details->first_name . ' ' . $client_details->last_name;
                    ?>
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="https://ui-avatars.com/api/?bold=true&background=0f523b&name=<?php echo $client_details->full_name ?>&rounded=true&color=fff" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1 fw-medium font-size-15"><?php echo $client_details->full_name ?></span>
                            <i class="uil-angle-down d-none d-xl-inline-block font-size-15"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="<?php echo base_url('client/profile') ?>"><i class="uil uil-user-circle font-size-18 align-middle text-muted me-1"></i> <span class="align-middle">My Profile</span></a>
                            <a class="dropdown-item" href="<?php echo base_url('client/profile') ?>#change-password"><i class="uil uil-lock font-size-18 align-middle text-muted me-1"></i> <span class="align-middle">Change Password</span></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo base_url('client/common/logout') ?>"><i class="uil uil-sign-out-alt font-size-18 align-middle me-1 text-muted"></i> <span class="align-middle">Sign out</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </header>