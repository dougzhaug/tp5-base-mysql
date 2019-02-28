<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/11
 * Time: 15:54
 */

namespace app\admin\model;


class SettlesStoreModel extends Model
{
    protected $table = 'T_Settles_Store';
    protected $pk = 'Id';

    public static function getType($type)
    {
        switch ($type){
            case 1:
                return '现金';
                break;
            case 2:
                return '汇款';
                break;
            case 3:
                return '在线';
                break;
            default:
                return '';
        }
    }
}