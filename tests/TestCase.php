<?php
namespace robokassa\tests;

use yii\di\Container;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Base class for the test cases.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    protected function tearDown()
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
        ], $config));
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'test',
                    'scriptFile' => __DIR__ .'/index.php',
                    'scriptUrl' => '/index.php',
                    'url' => '/',
                ],
                'assetManager' => [
                    'bundles' => [
                        // отрубаем публикацию ассетов
                        'yii\grid\GridViewAsset' => false,
                        'yii\web\JqueryAsset' => false,
                    ],
                ],
                'user' => [
                    'identityClass' => '\\tests\\User',
                    'enableSession' => false,
                ],
                'robokassa' => [
                    'class' => '\robokassa\Merchant',
                    'baseUrl' => 'https://auth.robokassa.ru/Merchant/Index.aspx',
                    'sMerchantLogin' => 'demo',
                    'sMerchantPass1' => '',
                    'sMerchantPass2' => '',
                    'isTest' => true,
                ],
            ]
        ], $config));
    }

    /**
     * @return string vendor path
     */
    protected function getVendorPath()
    {
        return dirname(dirname(__DIR__)) . '/vendor';
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }
}
