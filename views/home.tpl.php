<?php include('layout/header.php') ?>

<body class="home-bg">
    <div class="container pt-6 pb-4">
        <div class="row justify-content-center pb-md-3">
            <div class="col-12 col-md-10 col-lg-8">
                <h1 class="mb-0 text-center">Розклад занять<br>у твоєму календарі</h1>
            </div>
        </div>
        <div class="row justify-content-center mt-3 pb-md-4">
            <div class="col-12 col-md-8 col-lg-6">
                <p class="mb-0 text-center">Введіть вашу групу та залишайтеся в курсі актуального розкладу занять, внесеного у ваш календар з <a href="http://rozklad.kpi.ua" target="_blank" rel="noopener noreferrer">http://rozklad.kpi.ua</a></p>
            </div>
        </div>
        <div class="row justify-content-center mt-3 pb-md-5">
            <div class="col-12 col-md-6 col-lg-4">
                <img class="w-100" src="static/img/importer_logo.png" alt="KPI ROZKLAD Importer">
            </div>
        </div>
        <form method="get">
            <div class="row justify-content-center mt-3 pb-md-3">
                <div class="col-12 col-md-8 col-lg-6">
                    <input class="w-100" id="groups" name="group" type="text" placeholder="Вкажіть вашу групу">
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                    <input class="w-100 btn btn-primary" type="submit" value="Імпортувати">
                </div>
            </div>
        </form>
    </div>

</body>

<?php include('layout/footer.php') ?>