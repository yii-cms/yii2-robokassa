<?php

namespace robokassa\tests\unit;

use robokassa\Merchant;
use robokassa\tests\TestCase;
use yii\web\Response;

class MerchantTest extends TestCase
{
    protected function setUp()
    {
    }

    public function testRedirectUrl()
    {
        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $returnUrl = $merchant->payment(100, 1, 'Description', null, null, 'en', [], true);

        $this->assertEquals("https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=demo&OutSum=100&InvId=1&Desc=Description&SignatureValue=8a50b8d86ed28921edfc371cff6e156f&Culture=en&IsTest=1", $returnUrl);

        // disable test
        $merchant->isTest = false;

        $returnUrl = $merchant->payment(100, 1, 'Description', null, null, 'en', [], true);

        $this->assertEquals("https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=demo&OutSum=100&InvId=1&Desc=Description&SignatureValue=8a50b8d86ed28921edfc371cff6e156f&Culture=en", $returnUrl);
    }

    public function testRedirectUrlUserParams()
    {
        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $userParams = [
            'shp_id' => 1,
            'shp_login' => 'user1',
        ];

        $returnUrl = $merchant->payment(100, 1, 'Description', null, null, 'en', $userParams, true);

        $this->assertEquals("https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=demo&OutSum=100&InvId=1&Desc=Description&SignatureValue=938bb1d15a177ced68d59c1ca7dae32a&Culture=en&IsTest=1&shp_id=1&shp_login=user1", $returnUrl);
    }

    public function testResponseRedirect()
    {
        $this->mockWebApplication();

        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        // https://github.com/yiisoft/yii2/issues/15682
        $userStub = $this->createMock('yii\\web\\User');
        $userStub->method('setReturnUrl')->willReturn(false);
        \Yii::$app->set('user', $userStub);

        /** @var Response $response */
        $response = $merchant->payment(100, 1, 'Description', null, null, 'en', [], false);

        $this->assertInstanceOf(Response::className(), $response);
        $this->assertEquals('https://auth.robokassa.ru/Merchant/Index.aspx?MrchLogin=demo&OutSum=100&InvId=1&Desc=Description&SignatureValue=8a50b8d86ed28921edfc371cff6e156f&Culture=en&IsTest=1', $response->getHeaders()->get('Location'));
    }

    public function testSignature()
    {
        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $signature = md5('100:1:pass1'); // '1e8f0be69238c13020beba0206951535'

        $check = $merchant->checkSignature($signature, 100, 1, 'pass1');

        $this->assertInternalType('boolean', $check);

        $this->assertTrue($check);
    }

    public function testSignatureUserParams()
    {
        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $signature = md5('100:1:pass1:shp_id=1:shp_login=user1'); // 'd2b1beae30b0c2586eb4b4a7ce23aedd'

        $this->assertTrue($merchant->checkSignature($signature, 100, 1, 'pass1', [
            'shp_id' => 1,
            'shp_login' => 'user1',
        ]));
    }

    public function testSignatureInvalidSortUserParams()
    {
        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'md5',
            'isTest' => true,
        ]);

        $signatureInvalidSort = md5('100:1:pass1:shp_login=user1:shp_id=1');

        $this->assertFalse($merchant->checkSignature($signatureInvalidSort, 100, 1, 'pass1', [
            'shp_id' => 1,
            'shp_login' => 'user1',
        ]));
    }

    public function testSignatureAlgo()
    {
        $merchant = new Merchant([
            'sMerchantLogin' => 'demo',
            'sMerchantPass1' => 'password_1',
            'hashAlgo' => 'sha256', // <=== 'sha256'
            'isTest' => true,
        ]);

        $signature = hash('sha256', '100:1:pass1');

        $this->assertTrue($merchant->checkSignature($signature, 100, 1, 'pass1'));
    }
}
