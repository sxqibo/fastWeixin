<?php

namespace Sxqibo\Weixin\PayService;

use WechatPay\GuzzleMiddleware\Validator;

class NoopValidator implements Validator
{
    public function validate(\Psr\Http\Message\ResponseInterface $response)
    {
        return true;
    }
}

