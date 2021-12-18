<?php
// +----------------------------------------------------------------------
// | NewThink [ Think More,Think Better! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://www.sxqibo.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：山西岐伯信息科技有限公司
// +----------------------------------------------------------------------
// | Author:  apple  Date:2021/12/17 Time:6:22 PM
// +----------------------------------------------------------------------

require_once __DIR__ . '/../../vendor/autoload.php';

use \Sxqibo\Weixin\Customer\BaseService;

$data = [
    'corpid'     => 'wwa2f92ffcdc49a813',
    'corpsecret' => 'GXJtt3R5Qd8D_y9VGpAkUr8w9m02G2SI2ptOiOjqI0U'
];
try {
    $token = new BaseService($data);
    echo $token->getAccessToken();
} catch (Exception $e) {
    print_r($e->getMessage());
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    print_r($e->getMessage());
}

