<?php

namespace Sxqibo\Weixin;

use GuzzleHttp\Exception\RequestException;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use WechatPay\GuzzleMiddleware\Util\SensitiveInfoCrypto;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use EasyWeChat\Factory;
use Exception;

class Service
{

    // +----------------------------------------------------------------------
    // | 支付即服务
    // | 接口地址： https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter8_4_1.shtml
    // | 1、 服务人员注册API  add
    // | 2、 服务人员分配API  assign
    // | 3、 服务人员查询API  find
    // | 4、 服务人员信息更新API  modify
    // +----------------------------------------------------------------------

    private $serviceUrl = 'https://api.mch.weixin.qq.com/v3/smartguide/guides';
    private $headers;
    private $encryptor;

    private $corpId;
    private $storeId;
    private $contactUserAccount;
    private $contactSecret;


    private $merchantId;
    private $key;
    private $publicKeyPath;
    private $privateKeyPath;

    private $client;

    /**
     * @throws Exception
     */
    public function __construct($params = [])
    {
        try {
            $this->headers = [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ];

            $this->corpId             = $params['corpid'];
            $this->storeId            = $params['store_id'];
            $this->contactUserAccount = $params['contact_user_account'];
            $this->contactSecret      = $params['contact_secret'];

            $this->merchantId     = $params['merchant_id'];
            $this->key            = $params['key'];
            $this->publicKeyPath  = $params['public_key_path'];
            $this->privateKeyPath = $params['private_key_path'];

            $this->client = new Client();

        } catch (\Exception $e) {
            throw new Exception('初始化实例异常');
        }

    }


    /**
     * 获取API地址信息
     * @param string $key 键名
     * @return mixed
     * @throws Exception
     */
    public function getApi(string $key)
    {
        $endpoints = [
            'add'    => [
                'method' => 'POST',
                'url'    => $this->serviceUrl,
                'remark' => '服务人员注册API'
            ],
            'assign' => [
                'method' => 'POST',
                'url'    => $this->serviceUrl . '/{guide_id}/assign',
                'remark' => '服务人员分配API'
            ],
            'find'   => [
                'method' => 'GET',
                'url'    => $this->serviceUrl,
                'remark' => '服务人员查询API'
            ],
            'modify' => [
                'method' => 'PATCH',
                'url'    => $this->serviceUrl . '/{guide_id}',
                'remark' => '服务人员信息更新API'
            ]
        ];

        if (isset($endpoints[$key])) {

            return $endpoints[$key];
        } else {
            throw new Exception('未找到对应的接口信息 ' . $key);
        }
    }

    public function getEncrypt($str)
    {
        //$str是待加密字符串
        $public_key_path = $this->publicKeyPath;
        $public_key      = file_get_contents($public_key_path);
        $encrypted       = '';
        if (openssl_public_encrypt($str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码
            $sign = base64_encode($encrypted);
        } else {
            throw new Exception('encrypt failed');
        }
        return $sign;
    }

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
            'corp_id' => $this->corpId,
            'secret'  => $this->contactSecret,
        ];

        $contacts = Factory::work($config);

        $result = $contacts->user->get($userAccountId);

        if (!$result['errcode'] == 0) {
            throw new Exception('获取人员信息失败！');
        }

        return $result;
    }

    /**
     * 处理返回结果
     *
     * @param $result
     */
    private function handleResult($result)
    {
        $code    = 0;
        $data    = [];
        $retCode = $result['retcode'] ?? '';
        $message = $result['retmsg'] ?? '';


        return ['code' => $code, 'message' => $message, 'data' => $data];
    }

    /**
     * step1: 服务人员注册API
     * @doc https://pay.weixin.qq.com/wiki/doc/apiv3/apis/chapter8_4_1.shtml
     *
     * @param string $groupQrcode 群二维码URL,需要查看文档获取
     *
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function add($groupQrcode = null)
    {
        // 商户相关配置
        $merchantId           = $this->merchantId; // 商户号
        $merchantSerialNumber = '4C37A517B259E3F93EAFFB4EF56285AB4D705055'; // 商户API证书序列号
        $merchantPrivateKey   = PemUtil::loadPrivateKey($this->privateKeyPath); // 商户私钥
        $wechatpayCertificate = PemUtil::loadCertificate($this->publicKeyPath); // 微信支付平台证书

        // 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
            ->withWechatPay([$wechatpayCertificate]) // 可传入多个微信支付平台证书，参数类型为array
            ->build();

        // 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');

        // 创建Guzzle HTTP Client时，将HandlerStack传入
        $client = new Client(['handler' => $stack]);

        // 微信企业用户信息
        $userInfo = $this->getCompanyUserInfo($this->contactUserAccount);

        //加密手机号
        $mobile = $this->getEncrypt($userInfo['mobile']);

        $dataArray = [
            'corpid'       => $this->corpId,        //参数1：企业ID
            'store_id'     => $this->storeId,       //参数2：门店ID
            'userid'       => $this->contactUserAccount,    //参数3：企业微信的员工ID
            'name'         => $userInfo['name'],        //参数4：企业微信的员工姓名
            'mobile'       => $mobile,                  //参数5：手机号码
            'qr_code'      => $userInfo['qr_code'],   //参数6：员工个人二维码
            'avatar'       => $userInfo['avatar'],     //参数7：头像URL
            'group_qrcode' => $groupQrcode ?? '',   //参数8：群二维码URL,需要查看文档获取
        ];
        $data = json_encode($dataArray);

        $endPoint = $this->getApi('add');

        // 接下来，正常使用Guzzle发起API请求，WechatPayMiddleware会自动地处理签名和验签
        try {
            $resp = $client->request($endPoint['method'], $endPoint['url'], [ // 注意替换为实际URL
                'json'    => $data,
                'headers' => ['Accept' => 'application/json'],
            ]);

            echo $resp->getStatusCode() . ' ' . $resp->getReasonPhrase() . "\n";
            echo $resp->getBody() . "\n";

        } catch (RequestException $e) {
            // 进行错误处理
            echo $e->getMessage() . "\n";
            if ($e->hasResponse()) {
                echo $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase() . "\n";
                echo $e->getResponse()->getBody();
            }
            return;
        }

    }


    //服务人员分配API
    public function assign()
    {

    }

    //服务人员查询API
    public function find()
    {

    }

    //服务人员信息更新API
    public function modify()
    {

    }

}