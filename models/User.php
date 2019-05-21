<?php

namespace app\models;


class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [];
    private static $user_key = [
        'id',
        'username',
        'password',
        'authKey',
        'accessToken'
    ];


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        self::getUsers();
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        self::getUsers();
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        self::getUsers();
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    private static function getUsers()
    {
        $db_user = \Yii::getAlias('@db_user');
        $users = file(\Yii::$app->basePath . '/' . $db_user);
        foreach ($users as $user_str) {
            $user_data = explode('|', $user_str);
            $user = array_combine(self::$user_key, $user_data);
            self::$users[$user['id']] = $user;
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
