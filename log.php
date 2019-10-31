<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Parsing Log</title>

    <link rel="stylesheet" href="assets/lib/bootstrap4/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">


    <script defer src="assets/lib/jquery/jquery-3.3.1.min.js"></script>
    <script defer src="assets/lib/bootstrap4/bootstrap.bundle.min.js"></script>
    <script defer src="assets/lib/color-hash/color-hash.js"></script>

    <script defer src="assets/js/log.js"></script>

</head>
<body>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="d-inline-block">
                <strong class="display-4">Log </strong>
                <span
                        class="badge badge-pill badge-success status_updating">
                    Обновление...
                </span>

                <span
                        class="badge badge-pill badge-primary status_stop d-none">
                   Приоставновлено
                </span>
            </div>


            <div class="shadow stop_updating_hover_handler p-5 my-3 rounded">
                <ul class="timeline">
                    <li class="timeline__shablon shablon d-none">
                        <a class="shablon__title font-weight-bold" href="#"></a>
                        <a href="#" class="float-right shablon__date">
                        </a>
                        <a class="shablon__proccess badge badge-pill badge-success" href="#"></a>
                        <p class="shablon__content">

                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

</body>
</html>