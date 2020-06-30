<?php


$resriction_types = [
    'travel_ban' => 'Полный запрет',
    'travel_ban_eu' => 'Полный запрет (Европа)',
    'non-global_restriction' => 'Не глобальные ограничения',
    'quarantine_measures' => 'Меры при карантине',
    '' => 'Игнорирование (или нет данных)'
]

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        Визуализация графовых методов
    </title>

    <link rel="stylesheet" href="assets/lib/bootstrap4/bootstrap.min.css">
    <link rel="stylesheet" href="assets/lib/jquery-nice-select-1.1.0/css/nice-select.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="stylesheet" href="assets/lib/font/css/all.min.css">
    <link rel="stylesheet" href="assets/lib/bootstrap_multiselect/BsMultiSelect.min.css">
    <script src="assets/lib/font/js/all.js"></script>

    <link rel="stylesheet" href="assets/css/helper.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style id="link_dynamic_style">
        .link {
            stroke-opacity: 0.1;
        }
    </style>
</head>
<body>

<script>
    let resriction_types = JSON.parse('<?=json_encode($resriction_types, JSON_UNESCAPED_UNICODE)?>');
</script>


<div class="full_preloader">
</div>

<div class="container-fluid h-100">


    <div class="row h-100 ">
        <div class="col-2 bg-secondary p-0">
            <h4 class="border-bottom pb-2 mt-0 text-center text-white pb-1">
                <a class="text-white text-decoration-none" href="/">
                    Визуализация
                </a>
            </h4>


            <div class="d-flex align-items-center flex-column bg-white mx-2 mb-4 p-2 rounded">
                <label class="text-center m-0 font-weight-light">
                    Вид графа
                </label>

                <div class="flex_centered m-2 w-100 px-4">
                    <select class="nice-select w-100" id="graph_type_handler">
                        <option value="hierarchy">
                            Круговой
                        </option>
                        <option value="stratify">
                            Пузырьчатый
                        </option>
                    </select>
                </div>
            </div>

            <div class="d-flex align-items-center flex-column bg-white mx-2 my-4 p-2 rounded">
                <label class="text-center m-0 font-weight-light">
                    Источник данных
                </label>

                <div class="flex_centered m-2 w-100 px-4">
                    <select class="nice-select w-100" id="data_source_handler">
                        <option value="flare">
                            Зависимости библиотеки
                        </option>
                        <option value="generate">
                            Случайные данные
                        </option>
                        <option value="covid">
                            Авиамаршруты
                        </option>

                        <option value="elibrary">
                            Цитирование
                        </option>

                    </select>
                </div>


            </div>


            <div id="restriction_filter_input_container">
                <div class="-flex align-items-center flex-column bg-white mx-2 my-4 p-2 rounded">

                    <div class="flex_centered flex-column">
                        <label class="text-center m-0 font-weight-bold text-danger">
                            Ограничения из-за COVID-19
                        </label>

                        <div class="flex_centered m-2 w-100 px-4">

                            <select name="restriction" data-filter="restriction_type" id="restriction_filter_input"
                                    class="form-control" multiple="multiple"
                                    style="display: none;">

                                <?php foreach ($resriction_types as $key => $value) {
                                    ?>
                                    <option <?= $key == 'travel_ban' ? 'selected' : '' ?> value="<?= $key ?>">
                                        <?= $value ?>
                                    </option>
                                    <?php
                                } ?>

                            </select>

                        </div>


                        <!--                        <div class="flex_centered m-2 w-100 px-4">-->
                        <!---->
                        <!--                            <a class="btn-link" target="_blank" href="/ajax.php?source=covid&type=update">-->
                        <!--                                Обновить данные-->
                        <!--                            </a>-->
                        <!--                        </div>-->
                    </div>
                </div>
            </div>


            <div id="node_count_container">
                <div class="d-flex align-items-center flex-column bg-white mx-2 my-4 p-2 rounded">
                    <div class="flex_centered flex-column">
                        <label class="text-center m-0 font-weight-light">
                            Количество вершин
                        </label>

                        <div class="flex_centered m-2 w-100 px-4">

                            <input id="node_count_input" type="number" class="form-control" placeholder="0" min="5"
                                   max="1000">
                        </div>
                    </div>
                </div>
            </div>


            <div class="d-flex align-items-center flex-column bg-white mx-2 my-4 p-2 rounded">

                <div class="border-bottom d-flex align-items-center flex-column w-100">
                    <label class="text-center m-0 font-weight-light">
                        Степень жгутирования = <span id="value-simple" class="font-weight-bold">1</span>
                    </label>
                    <div class="col-12 m-0 ">
                        <div id="slider-simple"></div>
                    </div>
                </div>

                <div class=" d-flex align-items-center flex-column w-100">
                    <label class="text-center mt-3 mb-0 font-weight-light">
                        Прозрачность = <span id="link_opacity_value" class="font-weight-bold">1</span>
                    </label>
                    <div class="col-12 m-0 ">
                        <div id="link_opacity_slider"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center flex-column  mt-5 p-2 rounded">
                <button class="btn btn-primary reload_ajax_data_handler">
                    <i class="fa fa-sync not_loading d-none"></i>
                    <i class="fa fa-sync fa-spin loading"></i>
                    Перезагрузить
                </button>

                <button class="btn btn-danger reset_params_handler mt-2" style="z-index: 9999">
                    <i class="fa fa-window-close"></i>
                    Сбросить
                </button>
            </div>


        </div>


        <div class="col-10 h-100 position-relative">

            <div class="transition-loader">
                <div class="transition-loader-inner">
                    <label></label>
                    <label></label>
                    <label></label>
                    <label></label>
                    <label></label>
                    <label></label>
                </div>
            </div>

            <svg id="graph_svg"></svg>


            <div class="svg_info_container">
                <div class="svg_info__item">
                    <div class="color_block bg-dark"></div>
                    <div class="mx-1"> -</div>
                    <div class="">
                        Текущая вершина
                    </div>
                </div>
                <!--<div class="svg_info__item">-->
                <!--<div class="color_block bg-primary"></div>-->
                <!--<div class="mx-1"> -</div>-->
                <!--<div class=""> Связь</div>-->
                <!--</div>-->
                <div class="svg_info__item">
                    <div class="color_block bg-success"></div>
                    <div class="mx-1"> -</div>
                    <div class="">
                        Входящие связи
                    </div>
                </div>
                <div class="svg_info__item">
                    <div class="color_block bg-danger"></div>
                    <div class="mx-1"> -</div>
                    <div class="">
                        Исходящие связи
                    </div>
                </div>

                <div class="svg_info__item">
                    <div class="color_block bg-primary"></div>
                    <div class="mx-1"> -</div>
                    <div class="">
                        Взаимные связи
                    </div>
                </div>
            </div>


            <div class="node_relations_outcoming node_relations d-none">
            </div>

            <div class="node_relations_incoming node_relations d-none">
            </div>
        </div>

    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modal_info__airport" role="dialog" aria-hidden="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">


                <table class="table-bordered table table-hover">
                    <thead class="bg-primary text-white">
                    <tr>
                        <td colspan="2" class="text-center">
                            <i class="fa fa-mosque"></i>
                            Город: <span class="city_name font-weight-bold"></span>
                        </td>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            CPI
                        </td>
                        <td class="city_cpi">

                        </td>
                    </tr>

                    <tr>
                        <td>
                            Уровень безопасности
                        </td>
                        <td class="city_safety">

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Часовой пояс
                        </td>
                        <td class="city_timezone">

                        </td>
                    </tr>

                    <tr>
                        <td>
                            Численность населения
                        </td>
                        <td class="city_population">

                        </td>
                    </tr>
                    </tbody>
                </table>

                <table class="table-bordered table table-hover">

                    <thead class="bg-success text-white ">
                    <tr>
                        <td colspan="2" class="text-center ">
                            <i class="fa fa-flag-usa"></i>
                            Страна: <span class="country_name font-weight-bold"></span>
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            Другие названия
                        </td>

                        <td class="country_alter_name">
                            <span class="country_alter_name"></span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Континент
                        </td>
                        <td class="country_continent">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Телефонный код
                        </td>
                        <td class="phone">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Столица
                        </td>
                        <td class="capital">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Язык
                        </td>
                        <td>
                            <span class="language">
                            </span>
                            <span class="language1">
                            </span>
                            <span class="language2">
                            </span>
                        </td>
                    </tr>


                    <tr>
                        <td>
                            Численность населения
                        </td>
                        <td class="country_population">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Национальная валюта
                        </td>
                        <td>
                            <span class="country_currency_name">
                            </span>
                            <span class="country_currency">
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Площадь территории
                        </td>
                        <td class="country_area">
                        </td>
                    </tr>

                    </tbody>
                </table>


                <table class="table-bordered table table-hover">

                    <thead class="bg-danger text-white ">
                    <tr>
                        <td colspan="2" class="text-center ">
                            <i class="fa fa-biohazard"></i>
                            Информация о COVID-19 на <span class="covid_date font-weight-bold"></span>
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            Ограничения на въезд
                        </td>
                        <td class="restriction_type">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Описание ограничения
                        </td>
                        <td class="restriction_text">
                        </td>
                    </tr>


                    <tr>
                        <td>
                            Общее количество заражённых
                        </td>

                        <td class="covid_confirmed">

                        </td>
                    </tr>

                    <tr>
                        <td>
                            Общее количество выздоровевших
                        </td>

                        <td class="covid_recovered">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Общее количество погибших
                        </td>

                        <td class="covid_deaths">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Количество новых заражённых (за сутки)
                        </td>

                        <td class="covid_confirmed_new">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Количество выздоровевших (за сутки)
                        </td>

                        <td class="covid_recovered_new">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Количество смертельных исходов (за сутки)
                        </td>

                        <td class="covid_deaths_new">
                        </td>
                    </tr>

                    </tbody>
                </table>


                <table class="table-bordered table table-hover">

                    <thead class="">
                    <tr>
                        <td colspan="2" class="text-center bg-info text-white font-weight-bold">
                            <i class="fa fa-plane-arrival"></i>
                            Входящие авианаправления
                        </td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td class="text-primary">
                            Аэропорт
                        </td>
                        <td class=" text-primary">
                            Город
                        </td>
                    </tr>
                    </thead>
                    <tbody class="airport_destinations_list">
                    </tbody>

                </table>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modal_info__publication" role="dialog" aria-hidden="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">


                <table class="table-bordered table table-hover">
                    <thead class="bg-primary text-white">
                    <tr>
                        <td colspan="2" class="text-center">
                            <i class="fa fa-newspaper"></i>
                            Публикация
                        </td>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>
                            Тип
                        </td>
                        <td data-type="type">

                        </td>
                    </tr>

                    <tr>
                        <td>
                            Дата публикации
                        </td>
                        <td data-type="year">

                        </td>
                    </tr>
                    <tr>
                        <td>
                            Язык
                        </td>
                        <td data-type="language">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Издание
                        </td>
                        <td data-type="publisher">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Тематический рубрикатор
                        </td>
                        <td data-type="rubric">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Входит в РИНЦ
                        </td>
                        <td data-type="in_rinc">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Входит в ядро РИНЦ
                        </td>
                        <td data-type="in_rinc_ker">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Цитирований в РИНЦ
                        </td>
                        <td data-type="cit_in_rinc">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Цитирований из ядра РИНЦ
                        </td>
                        <td data-type="cit_in_rinc_ker">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Импакт-фактор
                        </td>
                        <td data-type="impact_factor">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Норм. цитируемость по направлению
                        </td>
                        <td data-type="norm_cit">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Ключевые слова
                        </td>
                        <td data-type="keywords">
                        </td>
                    </tr>


                    </tbody>
                </table>

                <table class="table-bordered table table-hover">

                    <thead class="">
                    <tr>
                        <td colspan="5" class="text-center bg-info text-white font-weight-bold">
                            <i class="fa fa-address-card"></i>
                            Авторы
                        </td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td class="text-primary">
                            ФИО
                        </td>
                        <td class=" text-primary">
                            Должность
                        </td>
                        <td class=" text-primary">
                            Число публикаций
                        </td>
                        <td class=" text-primary">
                            Количество цитирований автора
                        </td>
                        <td class=" text-primary">
                            Индекс Хирша
                        </td>

                    </tr>
                    </thead>

                    <tbody class="publication_authors_list"></tbody>
                </table>


                <table class="table-bordered table table-hover">

                    <thead class="">
                    <tr>
                        <td colspan="54" class="text-center bg-success text-white font-weight-bold">
                            <i class="fa fa-random"></i>
                            Публикации, цитирующие эту публикацию
                        </td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td class="text-primary">
                            Название
                        </td>

                        <td class="text-primary">
                            Тип
                        </td>

                        <td class="text-primary">
                            Год
                        </td>

                        <td class="text-primary">
                            Рубрика
                        </td>
                    </tr>
                    </thead>
                    <tbody class="publication_relations_list">
                    </tbody>

                </table>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Закрыть
                </button>
            </div>
        </div>
    </div>
</div>

<script defer src="assets/lib/jquery/jquery-3.3.1.min.js"></script>
<script defer src="assets/lib/popper.js/dist/umd/popper.min.js"></script>
<script defer src="assets/lib/bootstrap4/bootstrap.bundle.min.js"></script>
<script defer src="assets/lib/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>
<script defer src="assets/lib/d3/d3.v4.js"></script>
<script defer src="assets/lib/d3/d3-selection-multi.v0.4.js"></script>
<script defer src="assets/lib/d3/d3-simple-slider.min.js"></script>
<script defer src="assets/lib/bootstrap_multiselect/BsMultiSelect.min.js"></script>

<script defer src="assets/js/index.js"></script>

<script>


</script>
</body>
</html>