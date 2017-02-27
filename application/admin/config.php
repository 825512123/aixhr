<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
return [

    //模板参数替换
    'view_replace_str'       => array(
        '__ADMIN_LTE__'  => '/public/static/adminlte',
        '__CSS__'    => '/public/static/sui/css',
        '__JS__'     => '/public/static/sui/js',
        '__IMG__' => '/public/static/sui/img',
        '__ZUI__' => '/static/admin',
        '__STATIC__' => '/public/static',
        '__PUBLIC__' => '/public',
        '__INDEXVIEW__' => 'app/index/view',


        //网站配置
        'WEB_TITLE' => '小黄人',
        'INDEX_TITLE' => '首页',
        'INDEX_URL' => url('index/index/index'),
        'MEDIA_TITLE' => '任务',
        'MEDIA_URL' => url('index/media/index'),
        'USER_TITLE' => '个人',
        'USER_URL' => url('index/user/member'),
    ),

    //管理员状态
    'user_status' => [
        '1' => '正常',
        '2' => '禁止登录'
    ],
    //角色状态
    'role_status' => [
        '1' => '启用',
        '2' => '禁用'
    ],
    'captcha'  => [ // 验证码
        'imageH'   => 30, // 高度
        'imageW'   => 100, // 宽度
        'length'   => 5, // 位数
        'fontSize' => 25, // 字体大小
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY', // 字符集合
        'useCurve' => true, // 混淆
        'reset'    => true, // 验证成功后是否重置 
	],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------
    'template'               => [
        /*'layout_on' => true,
        'layout_name' => 'layout',*/
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '<{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}>',
        // 标签库标签开始标记
        'taglib_begin' => '<{',
        // 标签库标签结束标记
        'taglib_end'   => '}>',
    ],
];
