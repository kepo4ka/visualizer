<?php
require_once __DIR__ . '/init.php';


$info = generate($length);

$info = clearEmptyReferences($info, PRIMARY_FIELD, REFEREFCES_FIELD);

echo json_encode($info, JSON_UNESCAPED_UNICODE);
exit;


function generate($length = 100)
{

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
    generateList($list, $info, $length * 5);


    $array = [];

    for ($i = 0; $i < $length; $i++) {
        $temp = [];
        $rand = rand(0, count($list) - 1);
        $temp['name'] = $list[$rand];
        $temp['size'] = rand(10, 10000);
        $rand1 = rand(1, 10);

        for ($j = 0; $j < $rand1; $j++) {
            $rand2 = rand(0, count($list) - 2);
            $item = $list[$rand2];

            if ($item == $temp['name']) {
                $item = $rand2 + 1;
            }
            $temp['imports'][] = $item;
        }
        $temp['imports'] = array_unique($temp['imports']);

        $array[] = $temp;
    }

    return $array;

}

function generateList(&$list, $tree, $length = 1000)
{

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
            generateList($list, $tree[$i]['child'], $length);
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

    $parents = array_unique($parents);
    shuffle($parents);

    return $parents;
}
