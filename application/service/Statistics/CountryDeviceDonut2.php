<?php
/**
 * 国家交易金额统计--甜甜圈
 *
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 17:24
 */

namespace app\service\Statistics;

class CountryDeviceDonut2 extends StatisticsAbstract
{
    public static function make($scene = false,$time=false)
    {
        $data = [
            [
                'label' => '中国',
                'value'=>818,
                'color'=>'red',
                'highlight'=>'red',
            ],
            [
                'label' => '泰国',
                'value'=> 204,
                'color'=>'#00a65a',
                'highlight'=>'#00a65a',
            ],
            [
                'label' => '新加坡',
                'value'=> 451,
                'color'=>'#f39c12',
                'highlight'=>'#f39c12',
            ],
            [
                'label' => '印度尼西亚',
                'value'=> 266,
                'color'=>'#00c0ef',
                'highlight'=>'#00c0ef',
            ]
        ];

        return $data;
    }
}