<?php

namespace robokassa;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\web\Response;

class Merchant extends BaseObject
{
    public $sMerchantLogin;

    public $sMerchantPass1;
    public $sMerchantPass2;

    public $isTest = false;

    public $baseUrl = 'https://auth.robokassa.ru/Merchant/Index.aspx';
    public $recurringUrl = 'https://auth.robokassa.ru/Merchant/Recurring';

    public $hashAlgo = 'md5';

    /**
     * @param PaymentOptions $options
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
     * @param PaymentOptions $options
     * @return string
     */
    public function getPaymentUrl(PaymentOptions $options)
    {
        $url = $this->baseUrl;

        $url .= '?' . http_build_query([
                'MrchLogin' => $this->sMerchantLogin,
                'OutSum' => $options->outSum,
                'InvId' => $options->invId,
                'Desc' => $options->description,
                'SignatureValue' => $this->generateSignature($options),
                'IncCurrLabel' => $options->incCurrLabel,
                'Email' => $options->email,
                'Culture' => $options->culture,
                'IsTest' => $this->isTest ? 1 : null,
            ]);

        $shp = $options->getShpParams();
        if (!empty($shp) && ($query = http_build_query($shp)) !== '') {
            $url .= '&' . $query;
        }

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
    private function generateSignature(PaymentOptions $options)
    {
        if ($options->invId === null) {
            // MerchantLogin:OutSum:Пароль#1
            $signature = "{$this->sMerchantLogin}:{$options->outSum}:{$this->sMerchantPass1}";
        } else {
            // MerchantLogin:OutSum:InvId:Пароль#1
            $signature = "{$this->sMerchantLogin}:{$options->outSum}:{$options->invId}:{$this->sMerchantPass1}";
        }

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
