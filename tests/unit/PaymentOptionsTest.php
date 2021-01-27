<?php

namespace robokassa\tests\unit;

use robokassa\Merchant;
use robokassa\PaymentOptions;
use robokassa\tests\TestCase;
use Yii;
use yii\web\Response;

class PaymentOptionsTest extends TestCase
{
    public function testParams()
    {
        $merchant = new PaymentOptions([
            'outSum' => 100,
            'invId' => 1,
            'email' => 'test@example.org',
            'params' => [
                'shp_2' => 'param2',
                'user_login' => 'user1',
                'user_id' => 1,
            ],
        ]);

        $this->assertEquals([
            'shp_2' => 'param2',
            'user_login' => 'user1',
            'user_id' => 1,
        ], $merchant->getParams());

        $this->assertEquals([
            'shp_shp_2' => 'param2',
            'shp_user_login' => 'user1',
            'shp_user_id' => 1,
        ], $merchant->getShpParams());
    }
    public function testParamsSphSort()
    {
        $merchant = new PaymentOptions([
            'outSum' => 100,
            'invId' => 1,
            'email' => 'test@example.org',
            'params' => [
                'a2' => 'param2',
                'a4' => 'param4',
                'a3' => 'param3',
                'a1' => 'param1',
            ],
        ]);

        $this->assertEquals('param1:param2:param3:param4', implode(':', $merchant->getShpParams()));
        $this->assertEquals('param2:param4:param3:param1', implode(':', $merchant->getParams()));
    }
}
