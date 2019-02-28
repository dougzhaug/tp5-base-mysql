<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/9
 * Time: 10:42
 */

namespace app\admin\validate;


use think\Validate;

class CashierValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'StoreId' => 'require',
        'Uname'     =>  'require|unique:T_sysuser',
        'Upassword' =>  'require|min:6|max:18',
        'Uemail'    =>  'email',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */
    protected $message = [
        'StoreId.require' => 'StoreId|请选择所属商户',
        'Uname.require' => 'Uname|请选填写用户名',
        'Uname.unique' => 'Uname|用户名已存在',
        'Upassword.require' => 'Upassword|请填写密码',
        'Upassword.min' => 'Upassword|密码最少需要6位',
        'Upassword.max' => 'Upassword|密码最长18位',
        'Uemail.email' => 'Uemail|邮箱格式错误',
    ];

    protected $scene = [
        'edit'  =>  ['StoreId','Uname','Upassword','Uemail'],
    ];
    public function sceneEdit()
    {
        return $this->remove([
            'Uname'=>'unique',
            'Upassword' => 'require',
            ]);
    }
}