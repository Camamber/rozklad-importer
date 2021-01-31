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
        <form method="get" id="form">
            <div class="row justify-content-center mt-3 pb-md-3">
                <div class="col-12 col-md-8 col-lg-6">
                    <input class="w-100 " id="group" name="group" autocomplete="off" type="text" placeholder="Вкажіть вашу групу (мінімум 2 символа)">
                    <span class="input-error-hint" style="display: none;">Такої групи не існує, котику. Спробуй ще раз.</span>
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
        <!--<div class="row justify-content-center pt-6">
            <div class="col-12 col-md-10 col-lg-8">
                <h1 class="mb-0 text-center">About Service</h1>
            </div>
        </div>
         <div class="row justify-content-center mt-3 pb-md-4">
            <div class="col-12 col-md-8 col-lg-8">
                <p class="mb-0 text-center">This application is designed to easily and quickly import lesson timetables into your google calendar. The app uses google login to manage your calendars and events.</p>
                <p>
                    <ul style="color: var(--dark);">
                        <strong>The service uses the following scopes:</strong>
                        <li>
                            the app will request data from the calendar via https://www.googleapis.com/auth/calendar.readonly so that users can manage the schedule in the app.
                        </li>
                        <li>
                            the app will create calendars via https://www.googleapis.com/auth/calendar so that users can manage the schedule in the app.
                        </li>
                        <li>
                            the app will create events via https://www.googleapis.com/auth/calendar.events so that users can manage the schedule in the app.
                        </li>
                        <li>
                            the app will request a list of claendar events via https://www.googleapis.com/auth/calendar.events.readonly so that users can manage the schedule in the app. 
                        </li>
                    </ul>
                </p>
                <p>Please read our privacy policy before use
                <a href="privacy" >Privacy Policy</a>
                </p>
            </div>
        </div> -->


        <!-- Modal -->
        <div class="modal fade <?php echo isset($groupIds) ? 'show pt-6 pt-md-5' : ''  ?>" id="exampleModal" tabindex="-1" role="dialog" style="<?php echo isset($groupIds) ? 'display: block;' : 'display: none;'  ?>" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Воу, існує декілька однакових груп.</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul>
                            <?php foreach ($groupIds as $value) { ?>
                                <li><a href="?group_id=<?php echo $value['id'] ?>"><?php echo $value['name'] ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($groupIds)) { ?>
        <div class="modal-backdrop fade show"></div>
    <?php } ?>

    <script>
        let lastGroupResponse = null;
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


            const form = document.getElementById('form');
            form.addEventListener('submit', (e) => {
                if (lastGroupResponse && lastGroupResponse.length) {
                    const group = lastGroupResponse.find(g => g.toLocaleLowerCase() == input.value.toLocaleLowerCase())
                    if (group) {
                        input.value = group;
                        return;
                    }
                }

                e.preventDefault()
                input.classList.add('input-error');
                document.querySelector('form .input-error-hint').style['display'] = 'inline';
            });

            const modalClose = document.querySelector('.modal .close');
            modalClose.addEventListener('click', () => {
                window.location.href = window.location.href.split('?')[0];
            })

            const modal = document.querySelector('.modal');
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target == modal) window.location.href = window.location.href.split('?')[0];
                })
            }
        });


        function searchGroup(e) {
            e.target.classList.remove('input-error');
            document.querySelector('form .input-error-hint').style['display'] = 'none';
            if (!e.target.value.length || e.target.value.length < 2) return;

            gtag('event', 'search', { search_term: e.target.value});

            fetch('api/groups?query=' + e.target.value, )
                .then((response) => response.json())
                .then((data) => {
                    lastGroupResponse = [...data];

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