<?php

namespace Sxqibo\Weixin\PayService;

use EasyWeChat\Factory;
use Exception;
use Sxqibo\Weixin\Common\Utility;


class Service extends BaseService
{

    // +----------------------------------------------------------------------
    // | 支付即服务
    // | 接口地址： https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter8_4_1.shtml
    // | 1、 服务人员注册API  add
    // | 2、 服务人员分配API  assign
    // | 3、 服务人员查询API  find
    // | 4、 服务人员信息更新API  modify
    // +----------------------------------------------------------------------

    /**
     * 微信企业号的相关信息
     *
     * @param string $userAccountId 微信企业人员的账号
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws Exception
     */
    public function getCompanyUserInfo(string $userAccountId)
    {
        $config = [
            'corp_id' => $this->config['corpid'],
            'secret'  => $this->config['contact_secret'],
        ];

        $contacts = Factory::work($config);

        $result = $contacts->user->get($userAccountId);

        if (!$result['errcode'] == 0) {
            throw new Exception('获取人员信息失败！');
        }

        return $result;
    }


    /**
     * step1: 服务人员注册API
     *
     * @doc https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter8_4_1.shtml
     *
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws Exception
     */
    public function add()
    {
        // step1: 基本信息
        //微信企业用户信息
        $userInfo = $this->getCompanyUserInfo($this->config['contact_user_account']);

        $newData = [
            'corpid'   => $this->config['corpid'],                 //参数1：企业ID
            'store_id' => (int)$this->config['store_id'],               //参数2：门店ID
            'userid'   => $this->config['contact_user_account'],   //参数3：企业微信的员工ID
            'name'     => Utility::getEncryptData($userInfo['name'], $this->config['platform_public']),    //参数4：企业微信的员工姓名
            'mobile'   => Utility::getEncryptData($userInfo['mobile'], $this->config['platform_public']),  //参数5：手机号码
            'qr_code'  => $userInfo['qr_code'],                    //参数6：员工个人二维码
            'avatar'   => $userInfo['avatar'],                     //参数7：头像URL
        ];
        $newData = json_encode($newData);

        $endPoint = [
            'url'    => $this->base,
            'method' => 'POST',
        ];

        $result = $this->client->requestApi($endPoint, [], $newData, $this->headers, true);

        return $this->handleResult($result);

    }

    /**
     * 服务人员分配API
     * @param $guideId
     * @param $outTradeNo
     * @return array
     * @throws Exception
     */
    public function assign($guideId, $outTradeNo): array
    {
        $newData = [
            'out_trade_no' => (string)$outTradeNo,      //参数2：商户系统内部订单号
        ];
        $newData = json_encode($newData, JSON_UNESCAPED_UNICODE);

        $endPoint = [
            'url'    => $this->base . '/' . $guideId . '/assign',
            'method' => 'POST',
        ];

        $result = $this->client->requestApi($endPoint, [], $newData, $this->headers, true);

        return $this->handleResult($result);

    }

    /**
     * 服务人员查询API
     * @return array
     * @throws Exception
     */
    public function find(): array
    {
        $newData = [
            'store_id' => $this->config['store_id'],         //参数1：门店在微信支付商户平台的唯一标识
        ];
        $newData = json_encode($newData, JSON_UNESCAPED_UNICODE);

        $endPoint = [
            'url'    => $this->base,
            'method' => 'GET',
        ];

        $result = $this->client->requestApi($endPoint, [], $newData, $this->headers, true);

        return $this->handleResult($result);
    }

    /**
     * 服务人员信息更新API
     * @param $guideId
     * @return array
     * @throws Exception
     */
    public function modify($guideId): array
    {
        $newData = [
            'guide_id' => $guideId,         //参数1：门店在微信支付商户平台的唯一标识
        ];
        $newData = json_encode($newData, JSON_UNESCAPED_UNICODE);

        $endPoint = [
            'url'    => $this->base . '/' . $guideId,
            'method' => 'PATCH',
        ];

        $result = $this->client->requestApi($endPoint, [], $newData, $this->headers, true);

        return $this->handleResult($result);
    }


}