<?php
// 定义系统编码
header("Content-Type: text/html;charset=utf-8");
// 定义应用路径
define('APP_PATH', './Application/');
//定义根路径
define('APP_REALPATH',dirname(__FILE__));
// 定义缓存路径
define('RUNTIME_PATH', './Runtime/');
// 定义备份路径
define('DATABASE_PATH', './Database/');
// 定义钱包路径
define('COIN_PATH', './Coin/');
// 定义备份路径
define('UPLOAD_PATH', './Upload/');

// 后台安全入口
define('ADMIN_KEY', '123456');

// 定义数据库类型
define('DB_TYPE', 'mysql');
// 定义数据库地址
define('DB_HOST', '127.0.0.1');
// 定义数据库名
define('DB_NAME', 'my1993mobi');
// 定义数据库账号
define('DB_USER', 'my1993mobi');
// 定义数据库密码
define('DB_PWD', '7SE8rceTN9T3Swkbgvb');
// 定义数据库端口
define('DB_PORT', '3306');

// 锚定货币单位（需要配合多处改才能生效，慎重）
define('Anchor_CNY', 'cnc');

// 开启演示模式
define('APP_DEMO',0);
// 短信模式 0是演示模式  1是正式模式
define('MOBILE_CODE',1);
// 开始调试模式
define('M_DEBUG', 1);

// 定义授权码
define('MSCODE', '95D3A7E98EE9F913B462B87C73DS');
// 定义互转APIKEY,两端必须一致
define('BBAPIKEY', 'RkAyda9huaQYux6R');

// 定义OTC网址,已弃用
define('BBCOIN', '');

// 定义手机端网址
/*if($_SERVER['HTTP_HOST'] == "m.xxx.com"){
    define('BIND_MODULE','Mobile');
}*/


function wherecome()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_CLIENT']) && ('PhoneClient' == $_SERVER['HTTP_CLIENT'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    }

    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');

        if (preg_match('/(' . implode('|', $clientkeywords) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }

    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && ((strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false) || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }

    return false;
}

// 判断访问入口
if(wherecome()) {
    define('WHERECOME','Mobile');
} else {
    define('WHERECOME','Home');
}

//define('WHERECOME','Home');
// define('APP_DEBUG',true);

// 引入入口文件
require './ThinkPHP/ThinkPHP.php';
?>