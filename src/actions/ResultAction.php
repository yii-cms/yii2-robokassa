<?php

namespace robokassa\actions;

use robokassa\Merchant;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * Class ResultAction
 * @package robokassa
 */
class ResultAction extends BaseAction
{
    /**
     * Runs the action.
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function run()
    {
        if (!isset($_REQUEST['OutSum'], $_REQUEST['InvId'], $_REQUEST['SignatureValue'])) {
            throw new BadRequestHttpException;
        }

        /** @var Merchant $merchant */
        $merchant = Yii::$app->get($this->merchant);

        $shp = [];
        foreach ($_REQUEST as $key => $param) {
            if (strpos(strtolower($key), 'shp') === 0) {
                $shp[$key] = $param;
            }
        }

        if ($merchant->checkSignature(
            $_REQUEST['SignatureValue'],
            $_REQUEST['OutSum'],
            $_REQUEST['InvId'],
            $merchant->sMerchantPass2,
            $shp)
        ) {
            return $this->callback($merchant, $_REQUEST['InvId'], $_REQUEST['OutSum'], $shp);
        }

        throw new BadRequestHttpException;
    }
}
