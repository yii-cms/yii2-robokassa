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

    public $hashAlgo = 'md5';

    /**
     * @param mixed $nOutSum Требуемая к получению сумма (буквально — стоимость заказа, сделанного клиентом).
     * Формат представления — число, разделитель — точка, например: 123.45.
     * @param mixed $nInvId Номер счета в магазине.
     * @param null|string $sInvDesc Описание покупки,
     * можно использовать только символы английского или русского алфавита,
     * цифры и знаки препинания. Максимальная длина — 100 символов.
     * @param null|string $sIncCurrLabel Предлагаемый способ оплаты.
     * @param null|string $sEmail Email покупателя автоматически подставляется в платёжную форму ROBOKASSA.
     * @param null|string $sCulture Язык общения с клиентом (в соответствии с ISO 3166-1).
     * @param array $shp Дополнительные пользовательские параметры
     * @param bool $returnLink
     * @return string|Response
     * @throws InvalidConfigException
     */
    public function payment($nOutSum, $nInvId, $sInvDesc = null, $sIncCurrLabel = null, $sEmail = null, $sCulture = null, $shp = [], $returnLink = false)
    {
        $url = $this->baseUrl;

        $sSignatureValue = $this->generateSignature($nOutSum, $nInvId, $shp);

        $url .= '?' . http_build_query([
                'MrchLogin' => $this->sMerchantLogin,
                'OutSum' => $nOutSum,
                'InvId' => $nInvId,
                'Desc' => $sInvDesc,
                'SignatureValue' => $sSignatureValue,
                'IncCurrLabel' => $sIncCurrLabel,
                'Email' => $sEmail,
                'Culture' => $sCulture,
                'IsTest' => $this->isTest ? 1 : null,
            ]);

        if (!empty($shp) && ($query = http_build_query($shp)) !== '') {
            $url .= '&' . $query;
        }

        if (!$returnLink) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->getUrl());
            return Yii::$app->response->redirect($url);
        } else {
            return $url;
        }
    }

    /**
     * @param $shp
     * @return string
     */
    private function implodeShp($shp)
    {
        ksort($shp);

        foreach ($shp as $key => $value) {
            $shp[$key] = $key . '=' . $value;
        }

        return implode(':', $shp);
    }

    private function generateSignature($nOutSum, $nInvId, $shp = [])
    {
        if ($nInvId === null) {
            // MerchantLogin:OutSum:Пароль#1
            $signature = "{$this->sMerchantLogin}:{$nOutSum}:{$this->sMerchantPass1}";
        } else {
            // MerchantLogin:OutSum:InvId:Пароль#1
            $signature = "{$this->sMerchantLogin}:{$nOutSum}:{$nInvId}:{$this->sMerchantPass1}";
        }

        if (!empty($shp)) {
            $signature .= ':' . $this->implodeShp($shp);
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
            $signature .= ':' . $this->implodeShp($shp);
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
