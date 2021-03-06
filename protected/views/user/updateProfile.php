<?php
/* @var $model User */

$this->breadcrumbs=array(
	$model->twitterName=>array('profile'),
	Yii::t('app', 'Change Profile'),
);

$this->menu=array(
	array(
		'label'=>Yii::t('app', 'Personal Home'),
		'icon'=>'home',
		'url'=>array('profile'),
	),
	array(
		'label'=>Yii::t('app', 'Remove me!'),
		'icon'=>'fire',
		'url'=>'#',
		'linkOptions'=>array(
			'submit'=>array('removeMe'),
			'confirm'=>Yii::t('app', 'Are you sure you want to leave from this service eternally?')
		)
	),
);
?>

<h1><?php echo CHtml::encode($model->twitterName); ?></h1>

<p><?php echo Yii::t('app', 'Change your profile in this application.'); ?></p>

<?php echo $this->renderPartial('_form',array(
	'model'=>$model,
)); ?>