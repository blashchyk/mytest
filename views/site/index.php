<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">
    <h1><?php echo Yii::t('app', 'Добрый день,') . Yii::$app->user->identity->username ?></h1>
</div>
