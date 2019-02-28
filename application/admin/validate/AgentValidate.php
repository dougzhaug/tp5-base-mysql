<?php

namespace app\admin\validate;

use think\Validate;

class AgentValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        'AgentName' => 'require|unique:T_agent',
        'ContactName' => 'require',
        'ContactPhone' => 'require|mobile',
        'Province' => 'require',
        'City' => 'require',
        'Area' => 'require',
    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        'AgentName.require' => 'AgentName|请填写名称',
        'AgentName.unique' => 'AgentName|名称已存在',
        'ContactName.require' => 'ContactName|请填写联系人',
        'ContactPhone.require' => 'ContactPhone|请填写联系电话',
        'ContactPhone.mobile' => 'ContactPhone|联系电话格式错误',
        'Province.require' => 'Province|请选择省份',
        'City.require' => 'City|请选择城市',
        'Area.require' => 'City|请选择区域',
    ];

    protected $scene = [
        'edit'  =>  ['AgentName','ContactName','ContactPhone','Province','City','Area'],
    ];
    public function sceneEdit()
    {
        return $this->remove('AgentName', 'unique');
    }
}
