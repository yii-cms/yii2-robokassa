<?php

namespace robokassa\actions;

use robokassa\Merchant;
use yii\base\Action;
use yii\base\InvalidConfigException;

/**
 * Class BaseAction
 * @package robokassa
 */
class BaseAction extends Action
{
    public $merchant = 'robokassa';

    public $callback;

    /**
     * @param Merchant $merchant Merchant.
     * @param $options
     * @return mixed
     * @throws InvalidConfigException
     */
    protected function callback(Merchant $merchant, $options)
    {
        if (!is_callable($this->callback)) {
            throw new InvalidConfigException('"' . get_class($this) . '::callback" should be a valid callback.');
        }
        return call_user_func($this->callback, $merchant, $options);
    }

    /**
     * @param $name
     * @param null $defaultValue
     * @return mixed|null
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getParam($name, $defaultValue = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $defaultValue);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getSph()
    {
        $shp = [];
        foreach ($_REQUEST as $key => $param) {
            if (strpos(strtolower($key), 'shp_') === 0) {
                $shp[$key] = $param;
            }
        }
        return $shp;
    }
}
