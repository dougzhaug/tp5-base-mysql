<?php

namespace app\admin\validate;

use think\Db;
use think\Validate;

class IndustryValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'IndustryName'  => 'require|unique:t_industry',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'IndustryName.require'        => '行业名称必填',
        'IndustryName.unique'         => '行业名称已存在',
    ];

    protected $scene = [
        'edit'  =>  [''],   //如果都不验证这样写
    ];


}
