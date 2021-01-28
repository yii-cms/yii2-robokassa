<?php

namespace robokassa\actions;

use yii\base\BaseObject;

class ResultOptions extends BaseObject
{
    public $outSum;
    public $invId;
    public $email;
    public $fee;
    public $signatureValue;
    public $culture;
    public $params;
}
