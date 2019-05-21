<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Имя'),
            'password' => Yii::t('app', 'Пароль'),
            'rememberMe' => Yii::t('app', 'Запомнить меня')
        ];
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        $cookies = Yii::$app->request->cookies;
        if ($cookies->has('block_time')) {
            $time_coocky = $cookies->getValue('block_time');
            if ((60 * 5 - (time() - $time_coocky)) < 0) {
                $cookies = Yii::$app->response->cookies;
                $cookies->remove('block_time');
            } else {
                $time_block = 60 * 5 - (time() - $time_coocky);
                $this->addError(
                    'password',
                    'Попробуйте еще раз через' . ' ' . $this->pluralForm ($time_block, 'секундy', 'секунды', 'секунд')
                );
            }
        }
        return parent::beforeValidate();
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Неверные данные'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        } else {
            $this->blockValidation();
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * @param $n
     * @param $form1
     * @param $form2
     * @param $form5
     * @return string
     */
    public function pluralForm($n, $form1, $form2, $form5)
    {
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return $n . ' ' . $form5;
        }
        if ($n1 > 1 && $n1 < 5) {
            return $n . ' ' . $form2;
        }
        if ($n1 == 1) return $n . ' ' .  $form1;
        return $n . ' ' . $form5;
    }

    public function blockValidation()
    {
        $cookies = Yii::$app->request->cookies;
        if ($cookies->has('attempt') && !$cookies->has('block_time')) {
            $num = (int)$cookies->getValue('attempt');
            if ($num < 3) {
                $cookies = Yii::$app->response->cookies;
                $cookies->remove('attempt');
                $cookies->add(new \yii\web\Cookie([
                    'name' => 'attempt',
                    'value' => $num + 1,
                ]));
                var_dump($cookies->getValue('attempt'));
            } else {
                $cookies = Yii::$app->response->cookies;
                $cookies->remove('attempt');
                $cookies->add(new \yii\web\Cookie([
                    'name' => 'block_time',
                    'value' => time(),
                ]));

            }
        } else {
            $cookies = Yii::$app->response->cookies;
            $cookies->add(new \yii\web\Cookie([
                'name' => 'attempt',
                'value' => '1',
            ]));

        }
    }
}
