<?php

namespace Sxqibo\Weixin\WeChatPay;


/**
 * 退款功能
 * Class Order
 * @package Sxqibo\FastPayment\WeChatPay
 */
class Refund extends BaseService
{
    /**
     * 创建退款订单
     * @param array $data 退款参数
     * @return array
     */
    public function create($data)
    {
        $endPoint = [
            'url' => $this->base . "/refund/domestic/refunds",
            'method' => 'POST',
        ];

        $data = json_encode($data);

        $result = $this->client->requestApi($endPoint, [], $data, $this->headers, true);

        return $this->handleResult($result);
    }

    /**
     * 退款订单查询
     * @param string $refundNo 退款单号
     * @return array
     */
    public function query($refundNo)
    {
        $endPoint = [
            'url' => $this->base . "/refund/domestic/refunds/out-refund-no/{$refundNo}",
            'method' => 'GET',
        ];

        $result = $this->client->requestApi($endPoint, [], [], $this->headers, true);

        return $this->handleResult($result);
    }
}
