<?php

namespace robokassa\widgets;

use robokassa\Merchant;
use robokassa\PaymentOptions;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Json;
use yii\web\View;

class PaymentIFrame extends Widget
{
    /**
     * @var Merchant
     */
    public $merchant;

    /**
     * @var PaymentOptions
     */
    public $paymentOptions;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty($this->merchant)) {
            throw new InvalidConfigException('"merchant" must be set.');
        }
        if (empty($this->paymentOptions)) {
            throw new InvalidConfigException('"paymentOptions" must be set.');
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->registerClientScript();
    }

    /**
     * @throws InvalidConfigException
     */
    public function registerClientScript()
    {
        $view = $this->getView();

        $view->registerJsFile('https://auth.robokassa.ru/Merchant/bundle/robokassa_iframe.js', [
            'position' => View::POS_BEGIN,
            'appendTimestamp' => true,
        ], 'robokassa-iframe-bundle');

        $encOptions = Json::htmlEncode(PaymentOptions::paymentParams($this->merchant, $this->paymentOptions));
        $view->registerJs(
            "(function(){Robokassa.StartPayment($encOptions)})();",
            View::POS_READY,
            'robokassa-iframe-start-payment'
        );
    }
}
