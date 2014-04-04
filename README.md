yii2-robokassa
==============

```php
'components' => [
    'robokassa' => [
        'class' => '\robokassa\Merchant',
        'sMerchantLogin' => '',
        'sMerchantPass1' => '',
        'sMerchantPass2' => '',
    ]
    ...
]
```

```php
class PaymentController extends Controller
{
	/**
	 * @inheritdoc
	 */
    public function actions()
    {
        return [
            'result' => [
                'class' => 'robokassa\SuccessAction',
                'successCallback' => [$this, 'resultCallback'],
            ],
            'success' => [
                'class' => 'robokassa\SuccessAction',
                'successCallback' => [$this, 'successCallback'],
            ],
            'fail' => [
                'class' => 'robokassa\FailAction',
                'successCallback' => [$this, 'failCallback'],
            ],
        ]
    }

	/**
	 * Callback.
     * @param robokassa\Merchant $merchant merchant.
     * @param $nInvId invoice ID.
     * @param $nOutSum sum.
     * @param $shp user attributes.
	 */
    public function successCallback($merchant, $nInvId, $nOutSum, $shp)
    {

    }
    public function resultCallback($merchant, $nInvId, $nOutSum, $shp)
    {

    }
    public function failCallback($merchant, $nInvId, $nOutSum, $shp)
    {

    }
}
```
