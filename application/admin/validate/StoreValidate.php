<?php

namespace app\admin\validate;

use think\Validate;

class StoreValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'StoreName'   => 'require',
        'AgentId'     => 'require',
        'ContactName' => 'require',
        'ContactPhone'=> 'mobile',
        'ContactMail' => 'email',
        'Nature'      => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'StoreName.require'   => 'StoreName|请填写名称',
        'StoreName.unique'    => 'StoreName|名称已存在',
        'ParentId.require'    => 'ParentId|请选择上级代理',
        'ContactName.require' => 'ContactName|请填写联系人',
        'ContactPhone'        => '手机格式有误',
        'ContactMail'         => '邮箱格式有误',
        'Nature'              => '公司性质必选',
    ];

    protected $scene = [
        'edit'  =>  [''],   //如果都不验证这样写
    ];
}
