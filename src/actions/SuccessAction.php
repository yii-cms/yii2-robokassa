<?php

namespace robokassa\actions;

use robokassa\Merchant;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * Class SuccessAction
 * @package robokassa
 */
class SuccessAction extends BaseAction
{
    /**
     * Runs the action.
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function run()
    {
        $options = new SuccessOptions([
            'outSum' => $this->getParam('OutSum'),
            'invId' => $this->getParam('InvId'),
            'signatureValue' => $this->getParam('SignatureValue'),
            'culture' => $this->getParam('Culture'),
            'params' => $this->getSph(),
        ]);

        if (
            $options->outSum === null ||
            $options->invId === null ||
            $options->signatureValue === null
        ) {
            throw new BadRequestHttpException();
        }

        /** @var Merchant $merchant */
        $merchant = Yii::$app->get($this->merchant);

        if (
            $merchant->checkSignature(
                $options->signatureValue,
                $options->outSum,
                $options->invId,
                $merchant->sMerchantPass1,
                $options->params
            )
        ) {
            return $this->callback($merchant, $options);
        }

        throw new BadRequestHttpException();
    }
}
