<?php

namespace robokassa\actions;

use robokassa\Merchant;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * Class FailAction
 * @package robokassa
 */
class FailAction extends BaseAction
{
    /**
     * Runs the action.
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     */
    public function run()
    {
        $options = new FailOptions([
            'outSum' => $this->getParam('OutSum'),
            'invId' => $this->getParam('InvId'),
            'culture' => $this->getParam('Culture'),
            'params' => $this->getSph(),
        ]);

        if ($options->outSum === null || $options->invId === null || $options->culture === null) {
            throw new BadRequestHttpException();
        }

        /** @var Merchant $merchant */
        $merchant = Yii::$app->get($this->merchant);

        return $this->callback($merchant, $options);
    }
}
