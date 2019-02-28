<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 15:58
 */

namespace app\admin\validate;


use think\Validate;

class AgentUserValidate extends Validate
{
    protected $rule = [
        'Uname'     =>  'require|unique:T_sysuser|max:25',
        'Upassword' =>  'require|min:6|max:18',
        'Uemail'    =>  'email',
        'Role'      =>  'require|GT:0',
        'AgentId'   =>  'require|GT:0',
    ];
    protected $message  =   [
        'Uname.require'      => 'Uname|请填写用户名',
        'Uname.unique'      => 'Uname|用户名已存在',
        'Uname.max'          => 'Uname|用户名不能超过25个字符',
        'Uemail'             => 'Uemail|邮箱格式错误',
        'Upassword.require'  => 'Upassword|密码必填',
        'Upassword.min'      => 'Upassword|密码最短6位',
        'Upassword.max'      => 'Upassword|密码最长18位',
        'Role.require'       => 'Role|请选择角色',
        'Role.GT'            => 'Role|请选择角色',
        'AgentId.require'       => 'AgentId|请选择代理商',
        'AgentId.GT'            => 'AgentId|请选择代理商',
    ];

    protected $scene = [
        'edit'  =>  ['Uname','Upassword','Uemail','Role','AgentId'],
    ];
    public function sceneEdit()
    {
        return $this->remove([
            'Uname' => 'unique',
            'Upassword'=> 'require'
        ]);
    }

}