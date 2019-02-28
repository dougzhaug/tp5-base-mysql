<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/25
 * Time: 17:44
 */

namespace app\admin\model;

class IndustryModel extends Model
{
    protected $table = 'T_Industry';
    protected $pk = 'Id';

    public function getIndustryList($params){
        $ret = [];
        $tmpids = [];
        $fields = join(',',$params['fields']);
        $where  = array_merge([['IsDel','=',0]],$params['where']);
        $order  = empty($params['order']) ? $this->pk  : $params['order'];
        $limit_start  = $params['limit_s'];
        $limit_length = $params['limit_l'] >50 ? 50 : $params['limit_l'];

        $counts = $this->where($where)->count();

        $lists = $this->field($fields)->where($where)->order($order)->limit($limit_start,$limit_length)->select();

        foreach ($lists as $value) {
            if(in_array($value['Id'],$tmpids)) continue;
            $tmpids[] = $value['Id'];
            //处理第一层
            $value['IsState'] = $this->getStateStr($value['IsState']);
            $ret[]    = $value;
            $tmp_id =  $value['ParentId'] >0 ? $value['ParentId'] :$value['Id'] ;
            $tmparr = $this->getchildsById($tmp_id);
            foreach ($tmparr as $val) {
                if(in_array($val['Id'],$tmpids)) continue ;
                //处理子层
                $tmpids[] = $val['Id'];
                $val['IsState'] = $this->getStateStr($val['IsState']);
                $val['IndustryName'] = '└─'.$val['IndustryName'];
                $ret[] = $val;
            }
        }
        unset($tmpids);
        return ['total'=>$counts , 'list'=>$ret];

    }

    protected function getStateStr($value){
        $str = [0=>'禁用',1=>'启用'];
        if (isset($str[$value])){
            return $str[$value];
        }else{
            return '未知';
        }
    }

    protected function getchildsById($id){
        return $this->where('ParentId',$id)
                    ->where('IsDel',0)
                    ->order('Id asc')
                    ->select();
    }


}