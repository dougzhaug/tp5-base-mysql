<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/17
 * Time: 16:50
 */

namespace app\service\Statistics;

use app\service\Service;

abstract class StatisticsAbstract extends Service
{
    abstract public static function make($scene=false,$time=false);
}