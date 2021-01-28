<?php

namespace robokassa\tests\unit;

use robokassa\actions\FailAction;
use robokassa\actions\FailOptions;
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
            'callback' => function ($merchant, $options) {
                /** @var FailOptions $options */
                return "SUCCESS:{$options->outSum}:{$options->invId}:{$options->culture}";
            }
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['Culture'] = 'en';

        $return = $action->run();

        $this->assertEquals("SUCCESS:100:1:en", $return);
    }
    public function testSuccessPost()
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
            'callback' => function ($merchant, $options) {
                /** @var FailOptions $options */
                return "SUCCESS:{$options->outSum}:{$options->invId}:{$options->culture}";
            }
        ]);

        $_POST['OutSum'] = 100;
        $_POST['InvId'] = 1;
        $_POST['Culture'] = 'en';

        $return = $action->run();

        $this->assertEquals("SUCCESS:100:1:en", $return);
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
            'callback' => function ($merchant, $options) {
                return $options;
            }
        ]);

        $expectedOptions = new FailOptions([
            'outSum' => 100,
            'invId' => 1,
            'culture' => 'en',
            'params' => [],
        ]);

        $_GET['OutSum'] = 100;
        $_GET['InvId'] = 1;
        $_GET['Culture'] = 'en';

        $return = $action->run();

        $this->assertEquals($expectedOptions, $return);
    }

    public function testBadRequest()
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

        $action = new FailAction('success', $controller);

        $this->expectException('yii\\web\\BadRequestHttpException');

        $action->run();
    }
}
