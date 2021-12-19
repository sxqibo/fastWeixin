<?php
// +----------------------------------------------------------------------
// | NewThink [ Think More,Think Better! ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://www.sxqibo.com All rights reserved.
// +----------------------------------------------------------------------
// | 版权所有：山西岐伯信息科技有限公司
// +----------------------------------------------------------------------
// | Author:  apple  Date:2021/12/17 Time:6:45 PM
// +----------------------------------------------------------------------

namespace Sxqibo\Weixin\Customer;

use Exception;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;
use Sxqibo\Weixin\Common\Client;

class BaseService
{
    protected $base = 'https://qyapi.weixin.qq.com/cgi-bin/kf/';
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $corpid;

    /**
     * @var mixed
     */
    protected $corpsecret;

    protected $access_token;

    /**
     * @throws Exception
     */
    public function __construct($options)
    {
        try {
            if (empty($options['corpid'])) {
                throw new InvalidArgumentException("Missing Config -- [corpid]");
            }
            if (empty($options['corpsecret'])) {
                throw new InvalidArgumentException("Missing Config -- [corpsecret]");
            }

            //step1:企业微信客服配置
            $this->corpid     = $options['corpid'];
            $this->corpsecret = $options['corpsecret'];

            //step2:引入guzzle
            $this->client = new \GuzzleHttp\Client();

            //step3:获取accessToken
            $this->access_token = $this->getAccessToken();

        } catch (RequestException $e) {
            throw new Exception('请求异常' . $e->getMessage());

        } catch (\InvalidArgumentException $e) {
            throw new Exception($e->getMessage());

        }
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccessToken()
    {
        // step1: 请求信息
        $url    = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=' . $this->corpid . '&corpsecret=' . $this->corpsecret;
        $res    = $this->client->request('GET', $url);
        $result = $this->handleResult($res);

        return $result['access_token'];
    }

    /**
     * 处理返回结果
     *
     * @param $res
     * @return array
     */
    public function handleResult($res)
    {
        $result = json_decode($res->getBody(), true);

        if ($result['errcode'] != 0) {
            throw new InvalidArgumentException("获取失败" . $result['errmsg']);
        }

        return $result;
    }
}