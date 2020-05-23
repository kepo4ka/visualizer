<?php

require_once __DIR__ . '/include/init.php';

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}

$source = 'generate';
if (!empty($_GET['source'])) {
    $source = $_GET['source'];
}




switch ($source) {
    case 'generate':
        require_once __DIR__ . '/include/generator.php';
        break;

    case 'elibrary':
        require_once __DIR__ . '/include/citation.php';
        break;
    case 'covid':
        require_once __DIR__ . '/include/avia.php';
        break;

    default:
        exit;

}
