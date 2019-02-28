<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/7
 * Time: 9:25
 */

namespace app\admin\model;

use think\Db;

class StoreModel extends Model
{
    protected $table = 'T_Store';
    protected $pk = 'ID';
    //获取门店列表
    public function getStoreList($params){
        $fields = join(',',array_merge($params['fields'],['provinceName','cityName','AgentName','areaName']));
        $where  = array_merge($params['where'],[['IsDel','=','0'],['ParentId','>',0]]);
        $order  = empty($params['order']) ? $this->pk  : $params['order'];
        $limit_start  = $params['limit_s'];
        $limit_length = $params['limit_l'] >50 ? 50 : $params['limit_l'];
        $counts = Db::table('view_Store')->field($fields)->where($where)->count();
        $lists  = Db::table('view_Store')->field($fields)->where($where)->order($order)->limit($limit_start,$limit_length)->select();
        foreach ($lists as $key=>$value){
            $lists[$key]['IsState'] = $this->stateToString($value['IsState']);
/*            $lists[$key]['Nature']  = $this->natureToString($value['Nature']);
            $lists[$key]['Province']= $value['provinceName'];
            $lists[$key]['City']    = $value['cityName'];
            $lists[$key]['Area']    = $value['areaName'];*/
            $lists[$key]['AgentId']    = $value['AgentName'];
        }
        return ['total'=>$counts , 'list'=>$lists];
    }
    //获取商户列表
    public function getSellerList($params){
        $fields = join(',',array_merge($params['fields'],['provinceName','cityName','AgentName','areaName']));
        $where  = array_merge($params['where'],[['IsDel','=','0'],['ParentId','=',0]]);
        $order  = empty($params['order']) ? $this->pk  : $params['order'];
        $limit_start  = $params['limit_s'];
        $limit_length = $params['limit_l'] >50 ? 50 : $params['limit_l'];
        $counts = Db::table('view_Store')->field($fields)->where($where)->count();
        $lists  = Db::table('view_Store')->field($fields)->where($where)->order($order)->limit($limit_start,$limit_length)->select();
        foreach ($lists as $key=>$value){
            $lists[$key]['IsState'] = $this->stateToString($value['IsState']);
            $lists[$key]['Nature']  = $this->natureToString($value['Nature']);
            $lists[$key]['Province']= $value['provinceName'];
            $lists[$key]['City']    = $value['cityName'];
            $lists[$key]['Area']    = $value['areaName'];
            $lists[$key]['AgentId']    = $value['AgentName'];
        }
        return ['total'=>$counts , 'list'=>$lists];
    }
    //id获取门店信息
    public function getStoreInfoById($storeId){
        $store_info = $this->alias('s')
            ->join('T_Agent a','s.AgentId = a.ID','LEFT')
            ->join('T_Store t','s.ParentId = t.ID','LEFT')
            ->field('s.*,t.StoreName as ParentName,a.AgentName')
            ->where('s.ID',$storeId)
            ->find();
        return $store_info;
    }
    protected  function natureToString($value){
        $stateArr = [1=>'集团',2=>'公司',3=>'个体'];
        return $stateArr[intval($value)];
    }

    protected  function stateToString($value){
        $stateArr = [0=>'禁用',1=>'启用'];
        return $stateArr[intval($value)];
    }

    /**
     * 获取下拉所需树状数据
     *
     * @param bool $id
     * @param array $where
     * @param bool $ajax
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSelectTreeArray($id=false,$where=[],$ajax=false)
    {
        $where = array_merge(['IsDel'=>0,'IsState'=>1],$where);
        $storeModel = self::where($where)->field('id,ParentId as pid,StoreName as name,id as value')->select();
        $tree = make_tree_with_namepre($storeModel);
        $tree = json_decode(json_encode($tree),true);
        $store = split_tree($tree);
        if($id){
            foreach ($store as $key=>$val){
                if($val['value'] == $id){
                    $store[$key]['selected'] = 1;
                }
            }
        }
        return $ajax ? $store : array_to_string($store);
    }

    /**
     * 获取商户/门店下拉数据
     *
     * @param bool $id
     * @param array $where
     * @param bool $ajax
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getSelectArray($id=false,$where=[],$ajax=false)
    {
        $where = array_merge(['IsDel'=>0,'IsState'=>1],$where);
        $dataModel = self::where($where)->field('id,StoreName as name,id as value')->select()->toArray();
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