<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 15:58
 */

namespace app\admin\validate;


use think\Validate;

class AdminValidate extends Validate
{
    protected $rule = [
        'name'     =>  'require|max:25',
        'password' =>  'min:6|max:18',
        'phone'    =>  'mobile',
        'role'     =>  'require|GT:0',
    ];
    protected $message  =   [
        'name.require'      => 'name|名称必须',
        'name.max'          => 'name|名称不能超过25个字符',
        'phone'             => 'phone|手机号格式错误',
        'password.require'  => 'password|密码必填',
        'password.min'      => 'password|密码最短6位',
        'password.max'      => 'password|密码最长18位',
        'role.require'      => 'role|请选择角色',
        'role.GT'           => 'role|请选择角色',
    ];

    protected $scene = [
        'edit'  =>  ['name','phone','password','role'],
    ];
    public function sceneEdit()
    {
        return $this->remove('password', 'require');
    }

}