<?php

require_once __DIR__ . '/init.php';


$length = 100;

if (!empty($_GET['l'])) {
    $length = (int)$_GET['l'];
}

$names = generateNames();

$root = 'flare';
$tree = [
    [
        'full_path' => 'flare',
        'name' => $root,
        'child' => []
    ]
];


$res = [];

$res[] = $root;

$k = 0;

$deep = 5;
$info = generateTree($tree, $deep);


$list = [];
generateList($info);
$info = $list;

function generateList($tree, $length = 1000)
{
    global $list;

    if (count($list) >= $length) {
        return false;
    }

    for ($i = 0; $i < count($tree); $i++) {
        if (count($list) >= $length) {
            return false;
        }
        if (empty($tree[$i]['child'])) {
            $list[] = $tree[$i]['full_path'];
        } else {
            generateList($tree[$i]['child'], $length);
        }
    }
}

function generateTree($tree, $deep, $name = '')
{
    $length = rand(12, 15);

    $deep--;
    if ($deep < 1) {

        return null;
    }


    if (empty($tree)) {
        for ($i = 0; $i < $length; $i++) {
            $temp = getArray($deep);
            $temp['full_path'] = $name . '.' . $temp['name'];
            $tree[] = $temp;
        }
    }


    for ($i = 0; $i < count($tree); $i++) {
        if (!empty($name)) {
            $parent = $name . '.' . $tree[$i]['name'];
        } else {
            $parent = $tree[$i]['name'];
        }
        $tree[$i]['child'] = generateTree($tree[$i]['child'], $deep, $parent);
    }

    return $tree;
}

function getArray($deep)
{
    $res_name = uniqid();

    $res_name = substr($res_name, $deep);


    $temp = [
        'name' => $res_name,
        'child' => []
    ];
    return $temp;
}


//function test($tree, $deep = 2)
//{
//    $deep--;
//
//    if ($deep > 0) {
//        foreach ($tree as $key => $value) {
//            $tree = [uniqid() => test($tree, $deep)];
//        }
//    } else {
//        return rand(0, 1000);
//    }
//    return $tree;
//}


function generateNames($length = 1000)
{
    $parents = [];

    for ($i = 0; $i < $length; $i++) {
        $parents[] = uniqid();
    }
    shuffle(array_unique($parents));

    return $parents;
}


echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;

