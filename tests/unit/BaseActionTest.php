<?php

namespace robokassa\tests\unit;

use robokassa\Merchant;
use robokassa\actions\SuccessAction;
use robokassa\tests\TestCase;
use Yii;
use yii\web\Controller;

class BaseActionTest extends TestCase
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

        Yii::$app->set('robokassa', $merchant);

        $controller = new Controller('merchant', Yii::$app);

        $action = new SuccessAction('success', $controller, [
            //'callback' => function ($merchant, $nInvId, $nOutSum, $shp) { return 'SUCCESS'; }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_1');
        $_GET['Culture'] = 'en';

        $this->expectException('yii\\base\\InvalidConfigException');
        $this->expectExceptionMessage('"robokassa\actions\SuccessAction::callback" should be a valid callback.');

        $action->run();
    }
}
