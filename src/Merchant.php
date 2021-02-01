<?php

namespace robokassa;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\web\Response;

class Merchant extends BaseObject
{
    /**
     * @var string Идентификатор магазина
     */
    public $storeId;

    public $password1;
    public $password2;

    public $isTest = false;

    public $baseUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';
    //public $recurringUrl = 'https://auth.robokassa.ru/Merchant/Recurring';

    public $hashAlgo = 'md5';

    /**
     * @param PaymentOptions|array $options
     * @return \yii\console\Response|Response
     * @throws InvalidConfigException
     */
    public function payment(PaymentOptions $options)
    {
        $url = $this->getPaymentUrl($options);
        Yii::$app->user->setReturnUrl(Yii::$app->request->getUrl());
        return Yii::$app->response->redirect($url);
    }

    /**
     * @param PaymentOptions|array $options
     * @return string
     */
    public function getPaymentUrl(PaymentOptions $options)
    {
        if (is_array($options)) {
            $options = new PaymentOptions($options);
        }

        $url = $this->baseUrl;

        $url .= '?' . http_build_query(PaymentOptions::paymentParams($this, $options));

        return $url;
    }

    /**
     * @param $shp
     * @return string
     */
    private function buildShp($shp)
    {
        ksort($shp);

        foreach ($shp as $key => $value) {
            $shp[$key] = $key . '=' . $value;
        }

        return implode(':', $shp);
    }

    /**
     * @param PaymentOptions $options
     * @return string
     */
    public function generateSignature(PaymentOptions $options)
    {
        // MerchantLogin:OutSum:Пароль#1
        $signature = "{$this->storeId}:{$options->outSum}";

        if ($options->invId !== null) {
            // MerchantLogin:OutSum:InvId:Пароль#1
            $signature .= ":{$options->invId}";
        }
        if ($options->outSumCurrency !== null) {
            // MerchantLogin:OutSum:InvId:OutSumCurrency:Пароль#1
            $signature .= ":{$options->outSumCurrency}";
        }

        if ($options->userIP !== null) {
            // MerchantLogin:OutSum:InvId:OutSumCurrency:UserIp:Пароль#1
            $signature .= ":{$options->userIP}";
        }

        if (($receipt = $options->getJsonReciept()) !== null) {
            // MerchantLogin:OutSum:InvId:OutSumCurrency:UserIp:Receipt:Пароль#1
            $signature .= ":{$receipt}";
        }

        $signature .= ":{$this->password1}";

        $shp = $options->getShpParams();
        if (!empty($shp)) {
            $signature .= ':' . $this->buildShp($shp);
        }

        return strtolower($this->encryptSignature($signature));
    }

    /**
     * @param $sSignatureValue
     * @param $nOutSum
     * @param $nInvId
     * @param $sMerchantPass
     * @param array $shp
     * @return bool
     */
    public function checkSignature($sSignatureValue, $nOutSum, $nInvId, $sMerchantPass, $shp = [])
    {
        $signature = "{$nOutSum}:{$nInvId}:{$sMerchantPass}";

        if (!empty($shp)) {
            $signature .= ':' . $this->buildShp($shp);
        }

        return strtolower($this->encryptSignature($signature)) === strtolower($sSignatureValue);
    }

    /**
     * @param $signature
     * @return string
     */
    protected function encryptSignature($signature)
    {
        return hash($this->hashAlgo, $signature);
    }
}
