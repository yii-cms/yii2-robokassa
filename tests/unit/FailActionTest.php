<?php

namespace robokassa\tests\unit;

use robokassa\FailAction;
use robokassa\Merchant;
use robokassa\tests\TestCase;
use Yii;
use yii\web\Controller;

class FailActionTest extends TestCase
{
    public function testSuccess()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        Yii::$app->set('robokassa', $merchant);

        $controller = new Controller('merchant', Yii::$app);

        $action = new FailAction('success', $controller, [
            'callback' => function ($merchant, $nInvId, $nOutSum, $shp) { return 'SUCCESS'; }
        ]);

        $_REQUEST['OutSum'] = 100;
        $_REQUEST['InvId'] = 1;
        $_REQUEST['SignatureValue'] = md5('100:1:password_1');

        $return = $action->run();

        $this->assertEquals('SUCCESS', $return);
    }

    public function testBadSignatureRequestIgnored()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        Yii::$app->set('robokassa', $merchant);

        $controller = new Controller('merchant', Yii::$app);

        $action = new FailAction('success', $controller, [
            'callback' => function ($merchant, $nInvId, $nOutSum, $shp) { return [$nInvId, $nOutSum, $shp]; }
        ]);

        $_REQUEST['OutSum'] = 100;
        $_REQUEST['InvId'] = 1;
        $_REQUEST['SignatureValue'] = md5('100:1:password_invalid');

        $return = $action->run();

        $this->assertEquals([1, 100, []], $return);
    }

    public function testSuccessParams()
    {
        $this->mockWebApplication();

        $controller = new Controller('merchant', Yii::$app);

        $action = new FailAction('success', $controller, [
            'callback' => function ($merchant, $nInvId, $nOutSum, $shp) { return "{$nInvId}:{$nOutSum}"; }
        ]);

        $_REQUEST['OutSum'] = 100;
        $_REQUEST['InvId'] = 1;

        $return = $action->run();

        $this->assertEquals('1:100', $return);
    }

    public function testSuccessParamsWithShp()
    {
        $this->mockWebApplication();

        $controller = new Controller('merchant', Yii::$app);

        $action = new FailAction('success', $controller, [
            'callback' => function ($merchant, $nInvId, $nOutSum, $shp) { return ["{$nInvId}:{$nOutSum}", $shp]; }
        ]);

        $_REQUEST['OutSum'] = 100;
        $_REQUEST['InvId'] = 1;
        $_REQUEST['shp1'] = 'param1';
        $_REQUEST['shp_2'] = 'param2';

        $return = $action->run();

        $this->assertEquals(['1:100', ['shp1' => 'param1', 'shp_2' => 'param2']], $return);
    }
}
