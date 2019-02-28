<?php
/**
 * 总代理商/代理商管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 13:56
 */

namespace app\admin\controller;


use app\admin\model\AgentModel;
use app\admin\validate\AgentValidate;
use think\Request;

class Agent extends Auth
{
    /**
     * 代理商列表
     *
     * @param Request $request
     * @return array|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(Request $request)
    {
        if($request->isPost()){

            $columns = $request->columns;

            $builder = AgentModel::where('IsDel',0);

            /* where 条件 */
            if($request->keyword){
                $builder->where($request->option_field,'like','%'.$request->keyword.'%');
            }
            if($request->IsState){
                $builder->where('IsState',$request->IsState == 1 ? : 0);
            }
            if($this->start_time && $this->end_time){
                $builder->whereTime('CreateTime','between',[$this->start_time,$this->end_time]);
            }
            if($request->province_code){
                $builder->where('Province',$request->province_code);
            }
            if($request->city_code){
                $builder->where('City',$request->city_code);
            }
            if($request->area_code){
                $builder->where('Area',$request->area_code);
            }
            if($request->id){
                $builder->where('ParentId',$request->id);
            }
            /* where end */

            /* get count */
            $total = $builder->count();

            /* order start */
            if($request->order){
                $order = $request->order[0];
                $order_column = $columns[$order['column']]['data'];
                $order_dir = $order['dir'];
                $builder->order($order_column,$order_dir);
            }
            /* order end */

            $builder->field('ID,ParentId,AgentName,Addr,ContactName,ContactPhone,IsState,IsDel,Province,City,Area,Remark,CreateTime');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['IsState'] = $val['IsState']?'<button class="btn btn-info btn-xs">正常</button>':'<button class="btn btn-danger btn-xs">禁用</button>';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }

        $agent = '';
        if($request->id){
            $agent = AgentModel::get($request->id);
        }

        $inputGroupField = "['AgentName'=>'名称','ContactName'=>'联系人','ContactPhone'=>'手机']";
        return view('',['inputGroupField'=>$inputGroupField,'agent'=>$agent]);
    }

    /**
     * 添加页面
     *
     * @param Request $request
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function create(Request $request)
    {
        return view('',['agent'=>$this->getAgentParentToSelect($request->id)]);
    }

    /**
     * 添加
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $post = $request->post();
        $post['Province'] = $post['Province_code'] ?? '';
        $post['City'] = $post['City_code'] ?? '';
        $post['Area'] = $post['Area_code'] ?? '';
        $post['IsState'] = isset($post['IsState']) ? $post['IsState'] == 'on' ? 1 : 0 : 0;

        $validate = new AgentValidate();
        if (!$validate->check($post)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $result = AgentModel::create($post);
        if($result){
            $this->notice('添加成功',url('/agent'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 编辑页面
     *
     * @param Request $request
     * @return \think\response\View
     * @throws \think\Exception\DbException
     */
    public function edit(Request $request)
    {
        $agent = AgentModel::get($request->id);

        return view('',['agent'=>$agent,'agent_parent'=>$this->getAgentParentToSelect()]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $post = $request->param();
        $post['Province'] = $post['Province_code'] ?? '';
        $post['City'] = $post['City_code'] ?? '';
        $post['Area'] = $post['Area_code'] ?? '';
        $post['IsState'] = isset($post['IsState']) ? $post['IsState'] == 'on' ? 1 : 0 : 0;

        $validate = new AgentValidate();
        if (!$validate->scene('edit')->check($post)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $result = AgentModel::update($post);
        if($result){
            $this->notice('编辑成功',url('/agent'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 删除
     *
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        if(db('T_agent')->where('ParentId',$id)->count()){              //注：这里只能用db 如果使用model会报错
            return ['errorCode'=>1,'errorMessage'=>'删除失败，代理商下还存在二级代理商'];
        }

        $result = AgentModel::update(['ID'=>$id,'IsDel'=>1]);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'网络异常'];
        }
    }

    /**
     * 获取一级代理商下拉数据
     *
     * @param bool $id  //默认选中
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAgentParentToSelect($id=false)
    {
        $agentModel = AgentModel::where(['ParentId'=>0,'IsDel'=>0,'IsState'=>1])->field('AgentName as name,ID as value')->select()->toArray();
        array_unshift($agentModel,['name'=>'一级代理','value'=>0]);
        foreach ($agentModel as $key=>$val){
            if($val['value'] == $id){
                $agentModel[$key]['selected'] = 1;
            }
        }
        return array_to_string($agentModel);
    }

    public function get_second_agent($id)
    {
        return AgentModel::getSelectArray(false,['ParentId'=>$id],true);
    }

    public function getAgentTreeToSelect(Request $request)
    {
        return AgentModel::getSelectTreeArray($request->id,[],true);
    }
}