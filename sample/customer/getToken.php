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


$data = [
    'corpid'     => '',
    'corpsecret' => ''
];
try {
    $account = new \Sxqibo\Weixin\Customer\Account($data);
    $data = $account->listAccount();
    print_r($data);

} catch (Exception $e) {
    print_r($e->getMessage());
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    print_r($e->getMessage());
}

