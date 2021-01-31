<?php

namespace robokassa\tests\unit;

use robokassa\actions\ResultOptions;
use robokassa\Merchant;
use robokassa\actions\ResultAction;
use robokassa\tests\TestCase;
use Yii;
use yii\web\Controller;

class ResultActionTest extends TestCase
{
    public function testSuccess()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'storeId' => 'demo',
            'password1' => 'password_1',
            'password2' => 'password_2',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        Yii::$app->set('robokassa', $merchant);

        $controller = new Controller('merchant', Yii::$app);

        $action = new ResultAction('success', $controller, [
            'callback' => function ($merchant, $options) {
                return 'SUCCESS';
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_2'); // using password2

        $return = $action->run();

        $this->assertEquals('SUCCESS', $return);
    }

    public function testSuccessParamsWithShp()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'storeId' => 'demo',
            'password1' => 'password_1',
            'password2' => 'password_2',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        Yii::$app->set('robokassa', $merchant);

        $controller = new Controller('merchant', Yii::$app);

        $action = new ResultAction('success', $controller, [
            'callback' => function ($merchant, $options) {
                return $options;
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_2:shp_2=param2'); // using password2
        $_GET['shp1'] = 'param1';
        $_GET['shp_2'] = 'param2';
        // sph
        $_REQUEST = $_GET;

        $expectedOptions = new ResultOptions([
            'outSum' => 100,
            'invId' => 1,
            'signatureValue' => md5('100:1:password_2:shp_2=param2'),
            'culture' => null,
            'params' => ['shp_2' => 'param2'],
        ]);

        $return = $action->run();

        $this->assertEquals($expectedOptions, $return);
    }

    public function testBadSignatureRequest()
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

        $action = new ResultAction('success', $controller, [
            'callback' => function ($merchant, $options) {
                return 'SUCCESS';
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_invalid');

        $this->expectException('yii\\web\\BadRequestHttpException');

        $action->run();
    }

    public function testBadRequest()
    {
        $this->mockWebApplication();

        $controller = new Controller('merchant', Yii::$app);

        $action = new ResultAction('success', $controller);

        $this->expectException('yii\\web\\BadRequestHttpException');

        $action->run();
    }
}
