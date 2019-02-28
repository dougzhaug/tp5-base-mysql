<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/28
 * Time: 17:00
 */

return[

    /*
    |--------------------------------------------------------------------------
    | Date Separator
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'separator' => env('DATE_SEPARATOR', ' ~ '),

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'format' => env('DATE_FORMAT', 'YYYY-MM-DD HH:mm:ss'),
];