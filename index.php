<?php

require_once('init.php');


$length = 100;
$beta = 1;

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}

if (!empty($_GET['b'])) {
    $beta = (float)$_GET['b'];
}


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Elibrary Graph</title>

    <link rel="stylesheet" href="/assets/lib/bootstrap4/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/lib/jquery-nice-select-1.1.0/css/nice-select.css">

    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<style>

    .node {
        font: 10px sans -serif;
    }

    .link {
        stroke: steelblue;
        stroke -opacity: 0.5;
        fill: none;
        pointer -events: none;
    }


</style>
<div class="container">
    <svg width="1980" height="1080">

    </svg>
</div>


<script defer src="/assets/lib/jquery/jquery-3.3.1.min.js"></script>
<script defer src="/assets/lib/bootstrap4/bootstrap.bundle.min.js"></script>
<script defer src="assets/lib/d3/d3.v4.js"></script>

<script defer src="assets/lib/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>

<script defer src="assets/js/functions.js"></script>
<script defer src="assets/js/graph.js"></script>
</body>
</html>
