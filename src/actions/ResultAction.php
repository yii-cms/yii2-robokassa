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
        $options = new ResultOptions([
            'outSum' => $this->getParam('OutSum'),
            'invId' => $this->getParam('InvId'),
            'email' => $this->getParam('EMail'),
            'fee' => $this->getParam('Fee'),
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
                $merchant->password2,
                $options->params
            )
        ) {
            return $this->callback($merchant, $options);
        }

        throw new BadRequestHttpException();
    }
}
