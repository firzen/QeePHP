<?php
class Controller_Default extends Controller_Abstract {
	function actionIndex()
	{
	}
	function actionAbout(){
		
	}
	function actionNews(){
		
	}
	function actionCommunity(){
		
	}
	function actionDocs(){
		
	}
	function actionDownload(){
		
	}
	function actionRedirectMessage(){
		return $this->_redirectMessage('Caption', 'Message', '#');
	}
}
