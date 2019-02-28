<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 12:26
 */

Route::domain('admin', function () {
    //登陆
    Route::get('login', 'Auth.login/showLoginForm');
    Route::post('login', 'Auth.login/login');

    //注册
    Route::get('register', 'Auth.register/showRegistrationForm');
    Route::post('register', 'Auth.register/register');

    //修改密码
    Route::get('reset', 'Auth.ResetPassword/showResetForm');
    Route::post('reset', 'Auth.ResetPassword/reset');

    //登出
    Route::get('logout', 'Auth.login/logout');

    //首页
    Route::get('/','index/index');

    //代理商管理
    Route::get('agent/index/[:id]','agent/index');
    Route::get('agent/create/[:id]','agent/create');
    Route::post('agent/index/[:id]','agent/index');
    Route::resource('agent','agent');
    Route::get('agent/get_second_agent/[:id]','agent/get_second_agent');
    Route::get('agent/get_agent_tree_select/[:id]','agent/getAgentTreeToSelect');

    //订单管理
    Route::resource('order','order');
    Route::post('order/statistics','order/statistics')->name('order_statistics');
    Route::post('order','order/index')->name('order');

    //商户管理
    Route::resource('seller','seller');
    Route::delete('seller/delete/:id','seller/delete')->name('seller_delete');
    Route::get('seller/addstore/:id','seller/addstore')->name('seller_addstore');
    Route::post('seller/addstore','seller/addstore')->name('seller_addstore');
    Route::post('seller/uploadImg','seller/uploadImg')->name('seller_uploadImg');
    Route::post('seller/create','seller/create')->name('seller_create');
    Route::post('seller/update','seller/update')->name('seller_update');
    Route::get('seller/edit/:id','seller/edit')->name('seller_edit');
    Route::post('seller','seller/index')->name('seller');
    Route::get('seller/get_seller/[:id]','seller/get_seller');

    //行业管理
    Route::resource('industry','industry');
    Route::delete('industry/delete/:id','industry/delete')->name('industry_delete');
    Route::get('industry/create/:id','industry/create')->name('industry_create');
    Route::post('industry/create','industry/create')->name('industry_create');
    Route::post('industry/edit','industry/edit')->name('industry_edit');
    Route::get('industry/edit/:id','industry/edit')->name('industry_edit');
    Route::post('industry','industry/index')->name('industry');

    //门店管理
    Route::resource('store','store');
    Route::delete('store/delete/:id','store/delete')->name('store_delete');
    Route::get('store/edit/:id','store/edit')->name('store_edit');
    Route::post('store/edit','store/edit')->name('store_edit');
    Route::post('store','store/index')->name('store');
    Route::get('store/get_store/[:id]','store/get_store');

    //收银员管理
    Route::resource('cashier','cashier');
    Route::post('cashier/index','cashier/index');

    //设备管理
    Route::resource('device','device');
    Route::post('device/index','device/index')->name('device/index');

    //结算管理
    //代理商结算
    Route::get('settles/agent/read/:id','settles/agent_read');
    Route::post('settles/agent/read/:id','settles/agent_read');
    Route::get('settles/agent','settles/agent');
    Route::post('settles/agent/index','settles/agent');

    //商户结算
    Route::get('settles/seller/read/:id','settles/seller_read');
    Route::post('settles/seller/read/:id','settles/seller_read');
    Route::get('settles/seller','settles/seller');
    Route::post('settles/seller/index','settles/seller');

    //结算单
    Route::resource('settles','settles');
    Route::post('settles','settles/index')->name('settles');

    //统计
    Route::get('statistics/order','statistics/order');
    Route::post('statistics/get_statistics_data','statistics/getStatisticsData');
    Route::post('statistics/get_statistics_order_data','statistics/getStatisticsOrderData');
    Route::get('statistics/trend','statistics/trend')->name('statistics_trend');
    Route::get('statistics/order_chart','statistics/order_chart');
    Route::get('statistics','statistics/index')->name('statistics');

    //管理员管理
    Route::post('admin/index','admin/index');
    Route::resource('admin','admin');
    Route::post('admin/save','admin/save');
//    Route::put('admin/update','admin/update')->name('admin_update');
//    Route::get('admin/delete/:Uid','admin/delete')->name('admin_delete');


    //代理商登录账号管理
    Route::post('agent_user/index','agent_user/index');
    Route::resource('agent_user','agent_user');

    //后台角色管理
    Route::post('role/index','role/index');
    Route::post('role/get_permissions/[:id]','role/get_permissions')->name('role_get_permissions');
    Route::resource('role','role');
    Route::get('role/get_permissions','role/get_permissions')->name('role_get_permissions');

    //代理商角色管理
    Route::post('agent_role/index','agent_role/index');
    Route::post('agent_role/get_permissions/[:id]','agent_role/get_permissions');
    Route::resource('agent_role','agent_role');
    Route::get('agent_role/get_permissions','agent_role/get_permissions');

    //后台菜单管理
    Route::post('menu/index','menu/index');
    Route::post('menu/save','menu/save');
    Route::get('menu/create/[:id]','menu/create');
    Route::resource('menu','menu');

    //代理商菜单管理
    Route::post('agent_menu/index','agent_menu/index');
    Route::post('agent_menu/save','agent_menu/save');
    Route::get('agent_menu/create/[:id]','agent_menu/create');
    Route::resource('agent_menu','agent_menu');

})->bind('admin');