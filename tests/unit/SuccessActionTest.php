<?php

namespace robokassa\tests\unit;

use robokassa\actions\SuccessOptions;
use robokassa\Merchant;
use robokassa\actions\SuccessAction;
use robokassa\tests\TestCase;
use Yii;
use yii\web\Controller;

class SuccessActionTest extends TestCase
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

        $action = new SuccessAction('success', $controller, [
            'callback' => function ($merchant, $options) {
                return 'SUCCESS';
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_1');
        $_GET['Culture'] = 'en';

        $return = $action->run();

        $this->assertEquals('SUCCESS', $return);
    }

    public function testSuccessParamsWithShp()
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

        $action = new SuccessAction('success', $controller, [
            'callback' => function ($merchant, $options) {
                return $options;
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_1:shp_2=param2');
        $_GET['Culture'] = 'en';
        $_GET['shp1'] = 'param1'; // invalid user param
        $_GET['shp_2'] = 'param2';

        // for sph params
        $_REQUEST = $_GET;

        $return = $action->run();

        $this->assertEquals(new SuccessOptions([
            'outSum' => 100,
            'invId' => 1,
            'signatureValue' => md5('100:1:password_1:shp_2=param2'),
            'culture' => 'en',
            'params' => ['shp_2' => 'param2'],
        ]), $return);
    }

    public function testBadSignatureRequest()
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

        $action = new SuccessAction('success', $controller, [
            'callback' => function ($merchant, $options) {
                return 'SUCCESS';
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['SignatureValue'] = md5('100:1:password_invalid');
        $_GET['Culture'] = 'en';

        $this->expectException('yii\\web\\BadRequestHttpException');

        $action->run();
    }

    public function testBadRequest()
    {
        $this->mockWebApplication();

        $controller = new Controller('merchant', Yii::$app);

        $action = new SuccessAction('success', $controller);

        $this->expectException('yii\\web\\BadRequestHttpException');

        $action->run();
    }
}
