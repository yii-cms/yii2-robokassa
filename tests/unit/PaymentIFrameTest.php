<?php

namespace robokassa\tests\unit;

use robokassa\Merchant;
use robokassa\PaymentOptions;
use robokassa\tests\TestCase;
use robokassa\widgets\PaymentIFrame;
use Yii;
use yii\web\Controller;

class PaymentIFrameTest extends TestCase
{
    public function testSuccess()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'storeId' => 'demo',
            'password1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $signatureHash = md5('demo:100:1:password_1:shp_login=user1:shp_user_id=1');

        $paymentOptions = new PaymentOptions([
            'outSum' => 100,
            'invId' => 1,
            'description' => 'Description',
            'culture' => 'en',
            'params' => [
                'user_id' => 1,
                'login' => 'user1',
            ],
        ]);

        $out = PaymentIFrame::widget([
            'merchant' => $merchant,
            'paymentOptions' => $paymentOptions,
        ]);
        $this->assertEmpty($out);
    }
    public function testAsArraySuccess()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'storeId' => 'demo',
            'password1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $signatureHash = md5('demo:100:1:password_1:shp_login=user1:shp_user_id=1');

        $paymentOptions = [
            'outSum' => 100,
            'invId' => 1,
            'description' => 'Description',
            'culture' => 'en',
            'params' => [
                'user_id' => 1,
                'login' => 'user1',
            ],
        ];

        $out = PaymentIFrame::widget([
            'merchant' => $merchant,
            'paymentOptions' => $paymentOptions,
        ]);
        $this->assertEmpty($out);
    }
}
