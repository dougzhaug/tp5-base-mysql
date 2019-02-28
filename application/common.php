<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 子模版参数默认值设置 处理include包含文件中参数（[name]）没有默认值的问题  注：此函数只适用于字符串类型参数
 *
 * @param $param
 * @param $name
 * @param string $default
 * @return string
 */
function templateParamDefault($param,$name,$default='')
{
    $newParam = trim($param,'[]');

    if($newParam == $name) return $default;

    return $param;
}

//配合下面方法用   不需直接调用
function make_tree($arr,$pid='pid') {
    if (!function_exists('make_tree1')) {

        function make_tree1($arr,$pid='pid', $parent_id = 0) {
            $new_arr = array();
            foreach ($arr as $k => $v) {
                if ($v->$pid == $parent_id) {
                    $new_arr[] = $v;
                    unset($arr[$k]);
                }
            }
            foreach ($new_arr as &$a) {
                $a->children = make_tree1($arr,$pid, $a->id);
            }
            return $new_arr;
        }

    }
    return make_tree1($arr,$pid);
}

//配合下面方法用   不需直接调用
function make_tree_with_namepre($arr,$pid='pid') {
    $arr = make_tree($arr,$pid);
    if (!function_exists('add_namepre1')) {

        function add_namepre1($arr, $prestr = '') {
            $new_arr = array();
            foreach ($arr as $v) {
                if ($prestr) {
                    if ($v == end($arr)) {
                        $v->name = $prestr . '└─ ' . $v->name;
                    } else {
                        $v->name = $prestr . '├─ ' . $v->name;
                    }
                }

                if ($prestr == '') {
                    $prestr_for_children = '&nbsp;&nbsp;';
                } else {
                    if ($v == end($arr)) {
                        $prestr_for_children = $prestr . '&nbsp;&nbsp;&nbsp;&nbsp;';
                    } else {
                        $prestr_for_children = $prestr . '│ ';
                    }
                }
                $v->children = add_namepre1($v->children, $prestr_for_children);

                $new_arr[] = $v;
            }
            return $new_arr;
        }

    }
    return add_namepre1($arr);
}

//把数组结构变二维
function split_tree($arr) {
    if (!function_exists('treeToArray')) {

        function treeToArray($tree){
            static $arr = [];
            foreach($tree as $val){
                if(isset($val['children'])){
                    $children = $val['children'];
                    unset($val['children']);
                    $arr[] = $val;
                    if(!empty($children)){
                        treeToArray($children);
                    }
                }else{
                    $arr[] = $val;
                }
            }
            return $arr;
        }

    }
    return treeToArray($arr);
}

/**
 * 把数组变类 [[id=>2],[id=>3]] 字符串
 *
 * @param $arr
 * @return string
 */
function array_to_string($arr) {
    if (!function_exists('arrayToString')) {

        function arrayToString($arr){
            static $string = '[';
            foreach($arr as $key=>$val){

                $first = $end = $arr;

                if(is_array($val)){
                    arrayToString($val);
                }else{
                    reset($first);
                    end($end);
                    if(key($first) == $key){
                        $string .= '["' . $key . '"=>"' .str_replace('&nbsp;','   ',$val) . '",';
                    }elseif(key($end) == $key){
                        $string .= '"' . $key . '"=>"' .str_replace('&nbsp;','   ',$val) . '"],';
                    }else{
                        $string .= '"' . $key . '"=>"' .str_replace('&nbsp;','   ',$val) . '",';
                    }
                }
            }
            return $string.']';
        }
    }
    return arrayToString($arr);
}


//把数组结构变二维
function make_left_menu($arr) {
    $arr = make_tree($arr);
    if (!function_exists('treeToArray')) {
        function makeLeftMenu($arr){
            $html = '';
            foreach ($arr as $val){
                if(empty($val['children'])){
                    $html .= '<li class="left-nav-li" id="nav-'.$val['id'].'"><a href="'. url($val['url']) .'"><i class="fa ' . $val["icon"] . '"></i><span> '.$val["name"].' </span></a></li>';
                }else{
                    $html .= '<li class="treeview"><a href="' . url($val['url']) . '"><i class="fa ' . $val["icon"] . '"></i> <span>' . $val['name'] . '</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
                    $html .= '<ul class="treeview-menu">';
                    $html .= makeLeftMenu($val['children']);
                    $html .= '</ul>';
                    $html .= '</li>';
                }
            }
            return $html;
        }

    }
    return makeLeftMenu($arr);
}

/**
 * 某个数组是否完全包含另一个数组
 *
 * @param $full
 * @param $sub
 * @return bool
 */
function is_contain_array($full,$sub){
    foreach ($sub as $value){
        if(!in_array($value,$full)){
            return false;
        }
    }
    return true;
}
