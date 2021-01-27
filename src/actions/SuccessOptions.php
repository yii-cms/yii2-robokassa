<?php

namespace robokassa\actions;

use yii\base\BaseObject;

class SuccessOptions extends BaseObject
{
    public $outSum;
    public $invId;
    public $signatureValue;
    public $culture;
    public $params;
}
