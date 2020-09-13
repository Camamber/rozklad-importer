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
                    <input class="w-100" id="group" name="group" autocomplete="off" type="text" placeholder="Вкажіть вашу групу">
                    <div style="position:relative;">
                        <ul class="w-100 hint-list">
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-3">
                <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                    <input class="w-100 btn btn-primary" type="submit" value="Імпортувати">
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const body = document.querySelector('body');
            const ul = document.querySelector('.hint-list')

            const input = document.getElementById('group');
            const debounced = debounce(searchGroup, 300);
            input.addEventListener('input', debounced);

            body.addEventListener('click', (e) => {
                if (e.target != input) hideHint(input, ul);
            })
            input.addEventListener('click', () => {
                if (ul.children.length) showHint(input, ul)
            })
        });

        function searchGroup(e) {
            if (!e.target.value.length) return;

            fetch('api/groups?query=' + e.target.value, )
                .then((response) => response.json())
                .then((data) => {
                    if (!data || !data.length) return;

                    const ul = document.querySelector('.hint-list')
                    ul.innerHTML = '';
                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.classList.add('hint-list-item');
                        li.innerText = item;
                        li.addEventListener('click', (eLi) => {
                            e.target.value = eLi.target.innerText;
                            hideHint(e.target, ul);
                        })
                        ul.appendChild(li);
                    });

                    showHint(e.target, ul);
                });
        }

        function showHint(input, ul) {
            input.style['border-bottom-left-radius'] = '0';
            input.style['border-bottom-right-radius'] = '0';
            ul.classList.add('visible');
        }

        function hideHint(input, ul) {
            input.style['border-bottom-left-radius'] = '7px';
            input.style['border-bottom-right-radius'] = '7px';
            ul.classList.remove('visible')
        }

        function debounce(func, wait = 100) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(this, args);
                }, wait);
            };
        }
    </script>

</body>

<?php include('layout/footer.php') ?>