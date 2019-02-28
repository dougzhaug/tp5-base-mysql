<?php
/**
 * 权限管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/10
 * Time: 15:49
 */

namespace app\admin\controller;


use think\Request;

class Permission extends Auth
{
    protected $permission = [];

    public function index(Request $request)
    {
        if($request->isPost()){
            //数据
            $data = [
                [
                    'id'=>1,
                    'name' => '首页',
                    'pid' => 0,
                    'url' => '',
                    'icon' => 'fa fa-home',
                    'is_nav' => '是',
                ],
                [
                    'id'=>2,
                    'name' => '代理商管理',
                    'pid' => 0,
                    'url' => '',
                    'icon' => 'fa fa-user',
                    'is_nav' => '是',
                ],
                [
                    'id'=>3,
                    'name' => '代理商列表',
                    'pid' => 2,
                    'url' => '',
                    'icon' => 'fa fa-user',
                    'is_nav' => '是',
                ],
                [
                    'id'=>4,
                    'name' => '商户管理',
                    'pid' => 0,
                    'url' => '',
                    'icon' => 'fa fa-user',
                    'is_nav' => '是',
                ],
                [
                    'id'=>5,
                    'name' => '商户列表',
                    'pid' => 4,
                    'url' => '',
                    'icon' => 'fa fa-user',
                    'is_nav' => '是',
                ],
            ];
            $permissions = make_tree_with_namepre(json_decode(json_encode($data)));
            $data = $this->getPermission(json_decode(json_encode($permissions),true));

            //总条数
            $total = 20;
            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }
        $inputField = "['user_name'=>'用户名','user'=>'姓名','phone'=>'手机号']";
        return view('',['inputField'=>$inputField]);
    }

    private function getPermission($data)
    {
        foreach ($data as $k => $v) {
            //若$v仍为数组 则调用自身
            if (isset($v['children']) && $v['children']){
                $this->permission[] = $v;
                $this->getPermission($v['children']);

            }else{
                $this->permission[] = $v;
            }
        }
        return $this->permission;

    }

    public function create()
    {
        return view();
    }

    public function edit()
    {
        return view();
    }
}