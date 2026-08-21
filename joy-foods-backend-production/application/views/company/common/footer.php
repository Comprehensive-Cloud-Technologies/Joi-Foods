</div>
<!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- JAVASCRIPT -->
<script src="<?php echo base_url('assets/') ?>libs/jquery/jquery.min.js"></script>
<script src="<?php echo base_url('assets/') ?>libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('assets/') ?>libs/metismenu/metisMenu.min.js"></script>
<script src="<?php echo base_url('assets/') ?>libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo base_url('assets/') ?>libs/node-waves/waves.min.js"></script>
<script src="<?php echo base_url('assets/') ?>libs/waypoints/lib/jquery.waypoints.min.js"></script>
<script src="<?php echo base_url('assets/') ?>libs/jquery.counterup/jquery.counterup.min.js"></script>
<script src="<?php echo base_url('assets/') ?>/libs/toastr/build/toastr.min.js"></script>
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
<?php
if (isset($form_validation)) {
?>

    <script src="<?php echo base_url('assets/') ?>libs/metismenu/metisMenu.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/validation/formValidation.js"></script>
    <script src="<?php echo base_url(); ?>assets/validation/bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/inputmask/min/jquery.inputmask.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".input-mask").inputmask()
        });
    </script>
<?php
}

if (isset($datatable)) {
?>
    <!-- Required datatable js -->
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<?php
}
if (isset($datatable_buttons)) {
?>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<?php
}
if (isset($sweet_alert)) {
?>
    <!-- Sweet Alerts js -->
    <script src="<?php echo base_url(); ?>assets/libs/sweetalert2/sweetalert2.min.js"></script>
<?php
}

if (isset($apex_chart)) {
?>
    <!-- Apex Charts js -->
    <script src="<?php echo base_url(); ?>assets/libs/apexcharts/apexcharts.min.js"></script>
<?php
}

if (isset($select_2)) {
?>
    <!-- Select2 js -->
    <script src="<?php echo base_url(); ?>assets/libs/select2/js/select2.min.js"></script>
    <script>
        $(".select2").select2({
            width: '100%',
            dropdownAutoWidth: true,
        });

        $(".select2-modal").each(function() {
            var $this = $(this);
            $this.select2({
                dropdownParent: $this.closest('.modal'),
                width: '100%'
            });
        });

        $(".select2-modal-disabled").each(function() {
            var $this = $(this);
            $this.select2({
                dropdownParent: $this.closest('.modal'),
                width: '100%',
                minimumResultsForSearch: -1
            });
        });
    </script>
<?php
}

if (isset($datepicker)) {
?>
    <script src="<?php echo base_url(); ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/spectrum-colorpicker2/spectrum.min.js"></script>
<?php
}

if (isset($js_tree)) {
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<?php
}

if (isset($jq_ui)) {
?>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php
}

if (isset($full_calendar)) {
?>
    <!-- full_calendar js -->
    <script src="<?php echo base_url(); ?>assets/libs/moment/min/moment.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/jquery-ui-dist/jquery-ui.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/@fullcalendar/core/main.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/@fullcalendar/bootstrap/main.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/@fullcalendar/daygrid/main.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/@fullcalendar/timegrid/main.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/@fullcalendar/list/main.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/libs/@fullcalendar/interaction/main.min.js"></script>
<?php
}
?>

<!-- App js -->
<script src="<?php echo base_url('assets/') ?>js/app.js"></script>
<script>
    <?php
    if (config_item('console')) {
    ?>
        $('body').keydown(function(e) {
            if (e.which == 123) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 73) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 75) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 67) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 74) {
                e.preventDefault();
            }
        });
        ! function() {
            function detectDevTool(allow) {
                if (isNaN(+allow)) allow = 100;
                var start = +new Date();
                debugger;
                var end = +new Date();
                if (isNaN(start) || isNaN(end) || end - start > allow) {
                    console.log('DEVTOOLS detected ' + allow);
                }
            }
            if (window.attachEvent) {
                if (document.readyState === "complete" || document.readyState === "interactive") {
                    detectDevTool();
                    window.attachEvent('onresize', detectDevTool);
                    window.attachEvent('onmousemove', detectDevTool);
                    window.attachEvent('onfocus', detectDevTool);
                    window.attachEvent('onblur', detectDevTool);
                } else {
                    setTimeout(argument.callee, 0);
                }
            } else {
                window.addEventListener('load', detectDevTool);
                window.addEventListener('resize', detectDevTool);
                window.addEventListener('mousemove', detectDevTool);
                window.addEventListener('focus', detectDevTool);
                window.addEventListener('blur', detectDevTool);
            }
        }();
    <?php
    }
    ?>

    $('#back_button').on('click', function() {
        event.preventDefault();
        history.back(1);
    });
</script>
<script>
    const cssStyles = `
        font-family: monospace;
        color: #FF2685;
    `;
    console.log(`%c
  ____           ____ ____   ____ _____ \r\n | __ ) _   _   \/ ___|  _ \\ \/ ___|_   _|\r\n |  _ \\| | | | | |   | |_) | |     | |  \r\n | |_) | |_| | | |___|  _ <| |___  | |  \r\n |____\/ \\__, |  \\____|_| \\_\\\\____| |_|  \r\n        |___\/
`, cssStyles);
</script>
<?php if ($this->session->flashdata('success')) : ?>
    <script>
        toastr["success"]("message", '<?php echo $this->session->flashdata('success'); ?>');
    </script>
<?php endif; ?>

<?php if ($this->session->flashdata('error')) : ?>
    <script>
        toastr["error"]("message", '<?php echo $this->session->flashdata('error'); ?>');
    </script>
<?php endif; ?>
</body>

</html>
