<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" href="assets/lib/bootstrap4/bootstrap.min.css">
    <link rel="stylesheet" href="assets/lib/jquery-nice-select-1.1.0/css/nice-select.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">

    <link rel="stylesheet" href="assets/css/helper.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>

    </style>
</head>
<body>

<div class="full_preloader">
</div>

<div class="container-fluid h-100">


    <div class="row h-100 ">
        <div class="col-2 bg-secondary p-0">
            <h1 class="border-bottom p-0 m-0 text-center text-white pb-1">
                Визуализация
            </h1>

            <div class="d-flex align-items-center flex-column bg-white mx-2 my-4 p-2 rounded">
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
                    <select class="nice-select w-100"  id="data_source_handler">
                        <option value="flare">
                            Зависимости библиотеки
                        </option>
                        <option value="generator" disabled>
                            Случайные данные
                        </option>
                        <option value="elibrary">
                            Цитирование (elibrary.ru)
                        </option>
                    </select>
                </div>


                <div class="flex_centered flex-column" id="node_count_container">
                    <label class="text-center m-0 font-weight-light">
                        Количество вершин
                    </label>

                    <div class="flex_centered m-2 w-100 px-4">

                        <input id="node_count_input" type="number" class="form-control" placeholder="0" min="5"
                               max="1000">
                    </div>
                </div>
            </div>


            <div class="d-flex align-items-center flex-column bg-white mx-2 my-4 p-2 rounded">

                <label class="text-center m-0 font-weight-light">
                    Степень жгутирования = <span id="value-simple" class="font-weight-bold">1</span>
                </label>
                <div class="col-12 m-0">
                    <div id="slider-simple"></div>
                </div>
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


            <div class="node_relations_outcoming node_relations">
            </div>

            <div class="node_relations_incoming node_relations">
            </div>
        </div>


    </div>
</div>


<script src="assets/lib/jquery/jquery-3.3.1.min.js"></script>
<script src="assets/lib/bootstrap4/bootstrap.bundle.min.js"></script>
<script src="assets/lib/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>
<script src="assets/lib/d3/d3.v4.js"></script>
<script src="assets/lib/d3/d3-selection-multi.v0.4.js"></script>
<script src="assets/lib/d3/d3-simple-slider.min.js"></script>

<script src="assets/js/index.js"></script>

<script>


</script>
</body>
</html>