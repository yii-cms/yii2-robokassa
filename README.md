yii2-robokassa
==============

## Install via Composer

~~~
composer require yii-cms/yii2-robokassa
~~~

## Configuration

```php
'components' => [
    'robokassa' => [
        'class' => '\robokassa\Merchant',
        'baseUrl' => YII_ENV_PROD ? 'https://auth.robokassa.ru/Merchant/Index.aspx' : 'http://test.robokassa.ru/Index.aspx',
        'sMerchantLogin' => '',
        'sMerchantPass1' => '',
        'sMerchantPass2' => '',
    ]
    ...
]
```

## Example

```php
class PaymentController extends Controller
{
    public function actionInvoice()
    {
        $model = new Invoice();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /** @var \robokassa\Merchant $merchant */
            $merchant = Yii::$app->get('robokassa');
            return $merchant->payment($model->sum, $model->id, 'Пополнение счета', null, Yii::$app->user->identity->email);
        } else {
            return $this->render('invoice', [
                'model' => $model,
            ]);
        }
    }

	/**
	 * @inheritdoc
	 */
    public function actions()
    {
        return [
            'result' => [
                'class' => '\robokassa\ResultAction',
                'callback' => [$this, 'resultCallback'],
            ],
            'success' => [
                'class' => '\robokassa\SuccessAction',
                'callback' => [$this, 'successCallback'],
            ],
            'fail' => [
                'class' => '\robokassa\FailAction',
                'callback' => [$this, 'failCallback'],
            ],
        ];
    }

	/**
	 * Callback.
     * @param \robokassa\Merchant $merchant merchant.
     * @param integer $nInvId invoice ID.
     * @param float $nOutSum sum.
     * @param array $shp user attributes.
	 */
    public function successCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_ACCEPTED]);
        return $this->goBack();
    }
    public function resultCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $this->loadModel($nInvId)->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
        return 'Ok';
    }
    public function failCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $model = $this->loadModel($nInvId);
        if ($model->status == Invoice::STATUS_PENDING) {
            $model->updateAttributes(['status' => Invoice::STATUS_FAIL]);
            return 'Ok';
        } else {
            return 'Status has not changed';
        }
    }

    /**
     * @param integer $id
     * @return Invoice
     * @throws \yii\web\BadRequestHttpException
     */
    protected function loadModel($id) {
        $model = Invoice::find($id);
        if ($model === null) {
            throw new BadRequestHttpException;
        }
        return $model;
    }
}
```
