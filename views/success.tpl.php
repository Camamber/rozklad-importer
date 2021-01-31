<?php include('layout/header.php') ?>

<body class="success-bg">
    <div class="container pt-6 pb-4">
        <div class="row justify-content-center pb-md-4">
            <div class="col-12 col-md-6 col-lg-4">
                <img class="w-100" src="static/img/pixeltrue-plan.svg" alt="KPI ROZKLAD Success">
            </div>
        </div>
        <div class="row justify-content-center mt-3 pb-md-4">
            <div class="col-12 col-md-10 col-lg-8">
                <h1 class="mb-0 text-center">Розклад успішно імпортовано!</h1>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <a class="btn btn-primary w-100" href="https://calendar.google.com" target="_blank" rel="noopener noreferrer">Переглянути календар</a>
            </div>
            <div class="col-10 col-md-4 col-lg-3 col-xl-2 mt-3 mt-sm-0">
                <a class="btn btn-secondary w-100" href="<?php echo $_ENV['APP_ROOT_PATH'] ?>/">На головну</a>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            <?php if (isset($_COOKIE["group"]) && isset($_COOKIE["group_id"])) { ?>
                ga('send', 'event', 'Schedule', 'imported', '<?php echo $_COOKIE["group"] ?>', '<?php echo $_COOKIE["group_id"] ?>');
            <?php } ?>
        });
    </script>
</body>

<?php include('layout/footer.php') ?>