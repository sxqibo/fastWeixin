<?php

namespace Sxqibo\Weixin\WeChatPay;


/**
 * 订单功能
 * Class Order
 * @package Sxqibo\FastPayment\WeChatPay
 */
class Order extends BaseService
{
    /**
     * 通过商户订单号查询订单
     * 
     * @param string $orderNo 订单单号
     * @return array
     */
    public function query($orderNo)
    {
        $endPoint = [
            'url' => $this->base . "pay/transactions/out-trade-no/{$orderNo}",
            'method' => 'GET',
        ];

        $result = $this->client->requestApi($endPoint, [], [], $this->headers, true);

        return $this->handleResult($result);
    }

    /**
     * 通过微信支付订单号查询
     *
     * @param $transactionId
     * @return array
     * @throws \Exception
     */
    public function queryByTransactionId($transactionId)
    {
        $endPoint = [
            'url' => $this->base . "pay/transactions/id/{$transactionId}",
            'method' => 'GET',
        ];

        $result = $this->client->requestApi($endPoint, [], [], $this->headers, true);

        return $this->handleResult($result);
    }
}
