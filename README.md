yii2-robokassa
==============

[![Latest Stable Version](https://poser.pugx.org/yii-cms/yii2-robokassa/v/stable.png)](https://packagist.org/packages/yii-cms/yii2-robokassa)
[![Total Downloads](https://poser.pugx.org/yii-cms/yii2-robokassa/downloads.png)](https://packagist.org/packages/yii-cms/yii2-robokassa)
[![Build Status](https://travis-ci.org/yii-cms/yii2-robokassa.svg?branch=master)](https://travis-ci.org/yii-cms/yii2-robokassa)
[![Coverage Status](https://coveralls.io/repos/github/yii-cms/yii2-robokassa/badge.svg)](https://coveralls.io/github/yii-cms/yii2-robokassa)
[![codecov](https://codecov.io/gh/yii-cms/yii2-robokassa/branch/master/graph/badge.svg)](https://codecov.io/gh/yii-cms/yii2-robokassa)


## Install via Composer

~~~
composer require yii-cms/yii2-robokassa
~~~

## Configuration

```php
[
    'components' => [
        'robokassa' => [
            'class' => '\robokassa\Merchant',
            'baseUrl' => 'https://auth.robokassa.ru/Merchant/Index.aspx',
            'sMerchantLogin' => '',
            'sMerchantPass1' => '',
            'sMerchantPass2' => '',
            'isTest' => !YII_ENV_PROD,
        ],
        // ...
    ],
];
```

## Методы
```php
/**
 * Перенаправление на страницу оплаты с заданными параметрами.
 * 
 * @param \robokassa\PaymentOptions $options
 * @return \yii\web\Response
 */
\robokassa\Merchant::payment($options);
```

```php
/**
 * Получение ссылки на оплату с заданными параметрами.
 * 
 * @param \robokassa\PaymentOptions $options
 * @return string
 */
\robokassa\Merchant::getPaymentUrl($options);
```

## Примеры

### Пример работы с компонентом

```php
/**
 * @property integer $id
 * @property float $sum
 * @property integer $status
 */
class Invoice extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_FAIL = 1;
    const STATUS_ACCEPTED = 9;
    const STATUS_SUCCESS = 10;
}

class PaymentController extends \yii\web\Controller
{
    public function actionInvoice()
    {
        $model = new Invoice();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            /** @var \robokassa\Merchant $merchant */
            $merchant = Yii::$app->get('robokassa');
            return $merchant->payment(new \robokassa\PaymentOptions([
                'outSum' => 100,
                'description' => 'Пополнение счета',
                // 'incCurrLabel' => '',
                'invId' => $model->id,
                'culture' => 'ru',
                'encoding' => Yii::$app->charset,
                'email' => Yii::$app->user->identity->email,
                // 'expirationDate' => '', // ISO 8601 (YYYY-MM-DDThh:mm:ss.fffffffZZZZZ)
                // 'outSumCurrency' => 'USD',
                'userIP' => Yii::$app->request->userIP,
                // Дополнительные пользовательские параметры (shp_)
                'params' => [
                    'user_id' => 1,
                    'login' => 'user1',
                ],
            ]));
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
     * Переадресация пользователя при успешной оплате на SuccessURL.
     * Переход пользователя по данному адресу означает, что оплата Вашего заказа успешно выполнена.
     * Однако для дополнительной защиты желательно, чтобы факт оплаты проверялся скриптом, исполняемым при переходе на ResultURL
     * @param \robokassa\Merchant $merchant merchant.
     * @param \robokassa\actions\SuccessOptions $options
     * @return mixed
     */
    public function successCallback($merchant, $options)
    {
        $model = $this->loadModel($options->invId);
        
        if (in_array($model->status, [Invoice::STATUS_ACCEPTED, Invoice::STATUS_SUCCESS])) {
            return $this->goBack();
        }
        
        $model->updateAttributes(['status' => Invoice::STATUS_ACCEPTED]);
        
        // ...
    }
    
   /**
     * Оповещение об оплате на ResultURL
     * ResultURL предназначен для получения Вашим сайтом оповещения об успешном платеже в автоматическом режиме.
     * В случае успешного проведения оплаты ROBOKASSA делает запрос на ResultURL 
     * Ваш скрипт работает правильно и повторное уведомление с нашей стороны не требуется. 
     * Результат должен содержать  текст OK и параметр InvId. 
     * Например, для номера счёта 5 должен быть возвращён вот такой ответ: OK5.
     * @param \robokassa\Merchant $merchant merchant.
     * @param \robokassa\actions\ResultOptions $options
     * @return mixed
     */
    public function resultCallback($merchant, $options)
    {
        $model = $this->loadModel($options->invId);
        $model->updateAttributes(['status' => Invoice::STATUS_SUCCESS]);
        return 'OK' . $options->invId;
    }
    
   /**
     * Переадресация пользователя при отказе от оплаты на FailURL
     * В случае отказа от исполнения платежа Покупатель перенаправляется по данному адресу.
     * Необходимо для того, чтобы Продавец мог, например, разблокировать заказанный товар на складе.
     * Переход пользователя по данному адресу, строго говоря, не означает окончательного отказа 
     * Покупателя от оплаты, нажав кнопку «Back» в браузере он может вернуться на страницы ROBOKASSA.
     * @param \robokassa\Merchant $merchant merchant.
     * @param \robokassa\actions\FailAction $options
     * @return mixed
     */
    public function failCallback($merchant, $nInvId, $nOutSum, $shp)
    {
        $model = $this->loadModel($nInvId);
        if ($model->status == Invoice::STATUS_PENDING) {
            $model->updateAttributes(['status' => Invoice::STATUS_FAIL]);
        }
        // ...
    }

    /**
     * @param integer $id
     * @return Invoice
     * @throws \yii\web\BadRequestHttpException
     */
    protected function loadModel($id) {
        $model = Invoice::findOne($id);
        if ($model === null) {
            throw new \yii\web\BadRequestHttpException();
        }
        return $model;
    }
}
```
