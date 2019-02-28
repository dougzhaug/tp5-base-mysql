<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 17:44
 */

namespace app\admin\model;


class RoleModel extends Model
{
    protected $table = 'role';
    protected $pk = 'id';

    public static function getSelectArray($id=false,$where=[],$ajax=false)
    {
        $where = array_merge(['status'=>1],$where);

        $dataModel = self::where($where)->field('id,name,id as value')->select()->toArray();

        array_unshift($dataModel,['name'=>'请选择','value'=>0]);

        if($id){
            foreach ($dataModel as $key=>$val){
                if($val['value'] == $id){
                    $store[$key]['selected'] = 1;
                }
            }
        }

        return $ajax ? $dataModel : array_to_string($dataModel);
    }
}