<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 10:54
 */

use think\facade\Session;


if (!function_exists('validate_data')) {
    /**
     * 处理validate返回的数据信息（规则为 '字段|错误信息'）
     *
     * @param $data
     * @return array
     */
    function validate_data($data)
    {
        list($key,$value) = explode('|',$data);
        return [$key=>$value];
    }
}

if (! function_exists('old')) {
    /**
     * 获取错误表单旧数据
     *
     * @param null $key
     * @param null $default
     * @return null
     */
    function old($key = null, $default = null)
    {
        if($key){
            $old_array = Session::get('_old_input','old');
            if($old_array && isset($old_array[$key])){
                $old = $old_array[$key];
                //实现一次性数据
                unset($old_array[$key]);
                Session::set('_old_input',$old_array,'old');

                return $old;
            }
        }
        return $default;
    }
}

if (! function_exists('hasInputError')) {
    /**
     * 判断是否存在错误信息
     *
     * @param $key
     * @return bool
     */
    function hasInputError($key)
    {
        if($key){
            $errors = Session::get('_old_input_errors','old');

            return isset($errors[$key]);
        }
        return false;
    }
}

if (! function_exists('inputError')) {
    /**
     * 返回错误信息
     *
     * @param $key
     * @param null $default
     * @return null
     */
    function inputError($key,$default = null)
    {
        if($key){
            $errors = Session::get('_old_input_errors','old');

            if($errors && isset($errors[$key])){
                $err = $errors[$key];
                unset($errors[$key]);
                Session::set('_old_input_errors',$errors,'old');

                return $err;
            }
        }
        return $default;
    }
}

if (! function_exists('mk_pwd')) {
    /**
     * 生成密码密文
     *
     * @param $password
     * @return bool|string
     */
    function mk_pwd($password)
    {
        return password_hash($password,PASSWORD_DEFAULT);
    }
}

if (! function_exists('verify_pwd')) {
    /**
     * 验证密码
     *
     * @param $password
     * @param $encryption
     * @return bool
     */
    function verify_pwd($password,$encryption)
    {
        return password_verify($password,$encryption);
    }
}