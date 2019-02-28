<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 18:21
 */

namespace app\admin\model;


use think\Db;

class AdminModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'admin';

    protected $pk = 'id';
}