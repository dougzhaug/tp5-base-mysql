<?php
/**
 * 管理员管理
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/10
 * Time: 9:40
 */

namespace app\admin\controller;


use app\admin\model\RoleModel;
use think\Db;
use think\Request;
use app\admin\model\AdminModel;
use app\admin\validate\AdminValidate;


class Admin extends Auth
{
    public function index(Request $request)
    {
        if($request->isPost()){

            $columns = $request->columns;

            $builder = AdminModel::where('status',1);

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

            $builder->field('id,name,phone,role,status,create_time');
            $data = $builder->select();

            foreach ($data as $key=>$val){
                $data[$key]['status'] = $val['status']?'<button class="btn btn-info btn-xs">正常</button>':'<button class="btn btn-danger btn-xs">禁用</button>';
            }

            return [
                'draw' => intval($request->draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data,
            ];
        }

        $inputGroupField = "['name'=>'名称','phone'=>'手机号']";
        return view('',['inputGroupField'=>$inputGroupField]);
    }

    /**
     * 添加
     *
     * @return \think\response\View
     */
    public function create()
    {
        return view('',['role'=>RoleModel::getSelectArray()]);
    }

    /**
     * 添加程序
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
        $post = $request->post();
        $post['status'] = isset($post['status']) ? $post['status'] == 'on' ? 1 : 0 : 0;

        $validate = new AdminValidate();
        if (!$validate->check($post)) {
            $this->alerts(validate_data($validate->getError()));
        }

        $post['password'] = mk_pwd($post['password']);

        $result = AdminModel::create($post);
        if($result){
            $this->notice('添加成功',url('/admin'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 编辑
     *
     * @param Request $request
     * @return \think\response\View
     * @throws \think\Exception\DbException
     */
    public function edit(Request $request)
    {
        $admin = AdminModel::get($request->id);
        return view('',['admin'=>$admin,'role'=>RoleModel::getSelectArray($admin['role'])]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        $post = $request->param();
        $post['status'] = isset($post['status']) ? $post['status'] == 'on' ? 1 : 0 : 0;

        $validate = new AdminValidate();
        if (!$validate->scene('edit')->check($post)) {
            $this->alerts(validate_data($validate->getError()));
        }

        if(isset($post['password']) && !empty($post['password'])){
            $post['password'] = mk_pwd($post['password']);
        }else{
            unset($post['password']);
        }

        $result = AdminModel::update($post);
        if($result){
            $this->notice('编辑成功',url('admin/index'));
        }else{
            $this->alerts('网络异常');
        }
    }

    /**
     * 删除操作
     *
     * @param $id
     * @return array
     */
    public function delete($id)
    {
        $result = AdminModel::destroy($id);

        if($result){
            return ['errorCode'=>0,'errorMessage'=>'删除成功'];
        }else{
            return ['errorCode'=>1,'errorMessage'=>'网络异常'];
        }
    }
}