<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/7
 * Time: 11:00
 */

namespace app\admin\validate;


use think\Validate;

class DeviceValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'Store_Id' => 'require',
        'DevName' => 'require|unique:T_dev_info',
        'DeviceSn' => 'require|unique:T_dev_info',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'Store_Id.require' => 'Store_Id|请填选择所属商户',
        'DevName.require' => 'DevName|请填写名称',
        'DevName.unique' => 'DevName|该名称已经存在',
        'DeviceSn.require' => 'DeviceSn|请填写设备编号',
        'DeviceSn.unique' => 'DeviceSn|设备编号已存在',
    ];

    protected $scene = [
        'edit'  =>  ['Store_Id','DevName','DeviceSn'],
    ];
    public function sceneEdit()
    {
        return $this->remove([
            'DevName'=>'unique',
            'DeviceSn'=>'unique'
        ]);
    }
}