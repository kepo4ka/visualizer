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
        'name' => $root, 'child' => []
    ]
];


$res = [];

$res[] = $root;

$k = 0;
$info = test($tree);


$temp = '';


function test_print($item, $key)
{
    echoBr("$key holds $item\n");
}

array_walk_recursive($info, 'test_print');

function implode_all($glue, $arr, $temp)
{

    for ($i = 0; $i < count($arr); $i++) {
        $temp = '.' . $arr[$i]['name'];

        if (!empty($arr[$i]['child'])) {

            $temp .= implode_all($glue, $arr[$i]['child'], $temp);
        }

    }


    return $temp;
}

function test($tree, $deep = 5)
{
    $length = rand(1, 3);
    $deep--;
    if ($deep < 1) {
        return [];
    }

//    if (empty($tree))
//    {
//        return getArray();
//    }

    for ($i = 0; $i < count($tree); $i++) {
        for ($j = 0; $j < $length; $j++) {
            if (count($tree) < $length) {
                $temp = getArray();
                $tree[$i]['child'][] = $temp;
                $tree[$i]['child'] = test($tree[$i]['child'], $deep);
            }
        }
    }
    return $tree;
}

function getArray()
{
    $temp = ['name' => uniqid(),
        'child' => []];
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


function generateTree($parent, $deep = 3)
{
    global $names;
    $length = rand(1, 5);

    $childs = [];

    for ($i = 0; $i < $length; $i++) {
        $temp_name = $names[0];
        $names = array_splice($names, 1);

        $path = "$parent.$temp_name";
        $childs[] = $path;
    }


    return $childs;
}


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

