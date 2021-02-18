<?php include('layout/header.php') ?>

<body class="error-bg">
    <div class="container pt-5 pt-lg-6 pb-4">
        <div class="row justify-content-center pb-md-4">
            <div class="col-12 col-md-6 col-lg-4">
                <img class="w-100" src="<?php echo $errObj['image'] ?>" alt="KPI ROZKLAD Error">
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-12 col-md-10 col-lg-8">
                <h1 class="mb-0 text-center">Упс! Не так сталося,<br> як гадалося...</h1>
            </div>
        </div>
        <div class="row justify-content-center mt-3 pb-md-3">
            <div class="col-12 col-md-8 col-lg-6">
                <p class="mb-0 text-center"><?php echo $errObj['message'] ?></p>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <a class="btn btn-primary w-100" href="<?php echo $_ENV['APP_ROOT_PATH'] ?>/">Спробувати ще раз</a>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            <?php if (isset($_COOKIE["group"])) { ?>
                gtag('event', '<?php echo $errObj['type']?>', {
                    'event_category': 'Schedule',
                    'event_label': '<?php echo $_COOKIE["group"] ?>',
                });
            <?php } ?>
        });
    </script>
</body>

<?php include('layout/footer.php') ?>