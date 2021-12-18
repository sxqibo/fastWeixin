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

    /**
     * @var Client
     */
    protected $client;
    protected $corpid;
    protected $corpsecret;

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

        } catch (RequestException $e) {
            throw new Exception('请求异常' . $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccessToken()
    {
        // step1: 请求信息
        $url    = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=' . $this->corpid . '&corpsecret=' . $this->corpsecret;
        $res    = $this->client->request('GET', $url);
        $data   = json_decode($res->getBody(), true);
        $result = $this->handleResult($data);

        // step2: 返回
        if ($result['code'] != 0) {
            throw new InvalidArgumentException("获取Token失败" . $result['data']['errmsg']);
        }

        return $result['data']['access_token'];
    }

    /**
     * 处理返回结果
     *
     * @param $result
     * @return array
     */
    public function handleResult($result)
    {
        if (isset($result['code'])) {
            return ['code' => -1, 'code_text' => $result['code'], 'message' => $result['message'], 'data' => []];
        }

        return ['code' => 0, 'message' => '成功', 'data' => $result];
    }
}