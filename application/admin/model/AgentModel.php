<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 10:33
 */

namespace app\admin\model;

class AgentModel extends Model
{
    protected $table = 'T_Agent';
    protected $pk = 'ID';

    public static function getSelectArray($id=false,$where=[],$ajax=false)
    {
        $where = array_merge(['IsDel'=>0,'IsState'=>1],$where);

        $dataModel = self::where($where)->field('id,AgentName as name,id as value')->select()->toArray();

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

    public static function getSelectTreeArray($id=false,$where=[],$ajax=false)
    {
        $where = array_merge(['IsDel'=>0,'IsState'=>1],$where);

        $dataModel = self::where($where)->field('id,ParentId as pid,AgentName as name,id as value')->select();

        $data = make_tree_with_namepre($dataModel);

        array_unshift($data,['name'=>'请选择','value'=>0]);

        $data = json_decode(json_encode($data),true);

        $data = split_tree($data);

        if($id){
            foreach ($data as $key=>$val){
                if($val['value'] == $id){
                    $data[$key]['selected'] = 1;
                }
            }
        }

        return $ajax ? $data : array_to_string($data);
    }
}