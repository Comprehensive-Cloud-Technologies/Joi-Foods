<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Creative Central" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('assets/') ?>images/favicon.ico">

    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/') ?>libs/toastr/build/toastr.min.css">
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url('assets/') ?>css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo base_url('assets/') ?>css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url('assets/') ?>css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/validation/formValidation.css">

    <style>
        small.help-block {
            color: #f46a6a;
        }

        /* Enhanced UI Styles */
        body.authentication-bg {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: #556ee6;
            border-radius: 50%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            top: 20%;
            right: 10%;
            width: 60px;
            height: 60px;
            background: #34c38f;
            border-radius: 10px;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            bottom: 10%;
            left: 20%;
            width: 100px;
            height: 100px;
            background: #f1b44c;
            border-radius: 50%;
            animation-delay: 4s;
        }

        .floating-shape:nth-child(4) {
            bottom: 30%;
            right: 15%;
            width: 70px;
            height: 70px;
            background: #f46a6a;
            border-radius: 15px;
            animation-delay: 1s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        /* Enhanced card design */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transform: translateY(0);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .login-card:hover {
            /* transform: translateY(-5px); */
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #556ee6, #34c38f, #f1b44c);
        }

        /* Logo animation */
        .auth-logo {
            /* animation: slideDown 0.8s ease-out; */
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced form inputs */
        .form-floating {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control-enhanced {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem 1rem 1rem 3rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            height: 56px;
        }

        .form-control-enhanced:focus {
            border-color: #556ee6;
            box-shadow: 0 0 0 0.2rem rgba(85, 110, 230, 0.25);
            transform: translateY(-2px);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            transition: all 0.3s ease;
            z-index: 10;
            font-size: 1.1rem;
        }

        .input-group-custom:focus-within .input-icon {
            color: #556ee6;
        }

        .form-label-custom {
            position: absolute;
            top: 50%;
            left: 3rem;
            transform: translateY(-50%);
            background: transparent;
            padding: 0 0.5rem;
            color: #6c757d;
            transition: all 0.3s ease;
            pointer-events: none;
            font-size: 0.95rem;
        }

        .input-group-custom:focus-within .form-label-custom,
        .form-control-enhanced:not(:placeholder-shown)+.form-label-custom {
            top: 0;
            left: 2.5rem;
            font-size: 0.75rem;
            color: #556ee6;
            background: rgba(255, 255, 255, 0.9);
        }

        /* Enhanced button */
        .btn-login {
            background: linear-gradient(135deg, #556ee6, #34c38f);
            border: none;
            border-radius: 12px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(85, 110, 230, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        /* Welcome text animation */
        .welcome-text {
            /* animation: fadeInUp 0.8s ease-out 0.3s both; */
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form animation */
        .login-form {
            /* animation: fadeInUp 0.8s ease-out 0.6s both; */
        }

        /* Loading state */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Footer enhancement */
        .footer-text {
            /* animation: fadeInUp 0.8s ease-out 0.9s both; */
            color: #6c757d;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .floating-shape {
                display: none;
            }

            .login-card {
                margin: 1rem;
                border-radius: 15px;
            }
        }
    </style>
</head>

<body class="authentication-bg">
    <!-- Animated background -->
    <div class="bg-animation">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <div class="account-pages w-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <a href="<?php echo base_url('client'); ?>" class="mb-5 d-block auth-logo">
                            <img src="<?php echo base_url('assets/') ?>images/logo-dark.png" alt="" height="75" class="logo logo-dark">
                            <img src="<?php echo base_url('assets/') ?>images/logo-light.png" alt="" height="75" class="logo logo-light">
                        </a>
                    </div>
                </div>
            </div>
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card login-card">
                        <div class="card-body p-4">
                            <div class="text-center mt-2 welcome-text">
                                <h5 class="text-primary mb-3" style="font-weight: 700; font-size: 1.5rem;">Welcome Back!</h5>
                                <p class="text-muted mb-0">Sign in to continue to <?php echo config_item('application_name') ?>.</p>
                            </div>

                            <div class="p-2 mt-4 login-form">
                                <form action="<?php echo base_url('client/common/check_login') ?>" id="login_form" name="login_form">
                                    <div class="input-group-custom form-group">
                                        <i class="mdi mdi-account input-icon"></i>
                                        <input type="text" class="form-control form-control-enhanced" id="username" name="username" placeholder=" ">
                                        <label class="form-label-custom" for="username">Username</label>
                                    </div>

                                    <div class="input-group-custom form-group">
                                        <i class="mdi mdi-lock input-icon"></i>
                                        <input type="password" class="form-control form-control-enhanced" name="userpassword" id="userpassword" placeholder=" ">
                                        <label class="form-label-custom" for="userpassword">Password</label>
                                    </div>

                                    <div class="mt-4 text-center">
                                        <button class="btn btn-primary btn-login w-100 waves-effect waves-light" type="submit" id="submit_button">
                                            <span class="btn-text">Sign In</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center footer-text">
                        <p class="mb-0">© <script>
                                document.write(new Date().getFullYear())
                            </script> <?php echo config_item('application_name') ?>. Developed by <?php echo config_item('author') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="<?php echo base_url('assets/') ?>libs/jquery/jquery.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>libs/metismenu/metisMenu.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>libs/simplebar/simplebar.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>libs/node-waves/waves.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>libs/waypoints/lib/jquery.waypoints.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>libs/jquery.counterup/jquery.counterup.min.js"></script>
    <script src="<?php echo base_url('assets/') ?>/libs/toastr/build/toastr.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/validation/formValidation.js"></script>
    <script src="<?php echo base_url(); ?>assets/validation/bootstrap.js"></script>
    <script src="<?php echo base_url('assets/') ?>js/app.js"></script>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": 300,
            "hideDuration": 1000,
            "timeOut": 5000,
            "extendedTimeOut": 1000,
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>

    <script>
        $(function() {
            // Enhanced loading state
            function setLoadingState(button, loading) {
                if (loading) {
                    button.addClass('btn-loading').prop('disabled', true);
                } else {
                    button.removeClass('btn-loading').prop('disabled', false);
                }
            }

            // Login form validation (keeping original functionality)
            $('#login_form').formValidation({
                message: 'This value is not valid',
                icon: {
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    username: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter Username or email'
                            }
                        }
                    },
                    userpassword: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter password'
                            }
                        }
                    }
                }
            }).on('success.form.fv', function(e) {
                e.preventDefault();

                var $form = $(e.target);
                var $submitButton = $('#submit_button');

                // Show loading state
                setLoadingState($submitButton, true);

                $.ajax({
                    url: $form.attr('action'),
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        console.log(result);
                        var obj = JSON.parse(result);

                        // Remove loading state
                        setLoadingState($submitButton, false);

                        if (obj.status == 200) {
                            toastr["success"](obj.message);
                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        } else {
                            toastr["error"](obj.message);
                        }
                    },
                    error: function() {
                        setLoadingState($submitButton, false);
                        toastr["error"]("Something went wrong. Please try again.");
                    }
                });
            });

            // Enhanced input interactions
            $('.form-control-enhanced').on('focus blur input', function() {
                const $this = $(this);
                const $group = $this.closest('.input-group-custom');

                if ($this.is(':focus') || $this.val().length > 0) {
                    $group.addClass('focused');
                } else {
                    $group.removeClass('focused');
                }
            });

            // Add smooth scrolling for better UX
            $('html, body').animate({
                scrollTop: $('.login-card').offset().top - 50
            }, 1000);
        });
    </script>
</body>

</html>