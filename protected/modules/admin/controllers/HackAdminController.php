<?php

class HackAdminController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array(
					'admin','view','create','update','delete', 'report',
					'downloadReportCsv', 'downloadHackCsv', 'uploadHackCsv',
				),
				'expression'=>'$user->isAdmin',
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Hack;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Hack']))
		{
			$model->attributes=$_POST['Hack'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Hack']))
		{
			$model->attributes=$_POST['Hack'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$this->layout='//layouts/column1';
		$model=new Hack('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Hack']))
			$model->attributes=$_GET['Hack'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * report
	 */
	public function actionReport()
	{
		$this->layout='//layouts/column1';
		$model=new Hack('search');
		//$model->unsetAttributes();  // clear any default values

		//if(isset($_GET['Hack']))
		//	$model->attributes=$_GET['Hack'];

		$this->render('report',array(
			'model'=>$model,
		));
	}

	public function actionDownloadHackCsv()
	{
		$model = Hack::model();
		/* @var $data Hack[] */
		$data = $model->findAllByAttributes(array('isApproved'=>true), array('order'=>'id'));
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=hacks.csv');
		//header('Content-Type: text/plain;charset=utf-8');
		$f = fopen('php://output', 'w');
		fputcsv($f, array(
			$model->getAttributeLabel('id'),
			$model->getAttributeLabel('userFullName'),
			$model->getAttributeLabel('userTwitterName'),
			$model->getAttributeLabel('title'),
			$model->getAttributeLabel('isApproved'),
			$model->getAttributeLabel('sequence'),
		));
		foreach ($data as $hack) {
			fputcsv($f, array(
				$hack->id,
				$hack->user->fullName,
				'@' . $hack->user->twitterName,
				$hack->title,
				$hack->isApproved,
				$hack->sequence,
			));
		}
		fclose($f);
	}

	public function actionUploadHackCsv()
	{
		$file = CUploadedFile::getInstanceByName('csv');
		$f = fopen($file->tempName, 'r');
		fgetcsv($f); // header
		do {
			$row = fgetcsv($f);
			$csv[] = $row;
		}while(!empty($row));
		fclose($f);

		foreach ($csv as $row) {
			$id = $row[0];
			$twitter = $row[2];
			$seq = $row[5];
			/* @var $hack Hack */
			$hack = Hack::model()->findByPk($id);
			if ($hack && '@' . $hack->user->twitterName == $twitter) {
				$hack->sequence = $seq;
				$hack->save(false);
			}
		}

		$this->redirect(array('admin'));
	}

	/**
	 * report csv
	 */
	public function actionDownloadReportCsv()
	{
		$model = Hack::model();
		$data = $model->getReportData();
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=report.csv');
		//header('Content-Type: text/plain;charset=utf-8');
		$f = fopen('php://output', 'w');
		fputcsv($f, array(
			$model->getAttributeLabel('sequence'),
			$model->getAttributeLabel('title'),
			$model->getAttributeLabel('userFullName'),
			$model->getAttributeLabel('userTwitterName'),
			$model->getAttributeLabel('totalPoints'),
			$model->getAttributeLabel('totalReviewers'),
			$model->getAttributeLabel('averagePoints'),
			$model->getAttributeLabel('totalComments'),
		));
		foreach ($data as $hack) {
			fputcsv($f, array(
				$hack->sequence,
				$hack->title,
				$hack->user->fullName,
				'@' . $hack->user->twitterName,
				$hack->totalPoints,
				$hack->totalReviewers,
				sprintf('%0.2f', $hack->averagePoints),
				$hack->totalComments,
			));
		}
		fclose($f);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Hack the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Hack::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Hack $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='hack-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
