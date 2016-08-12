<?php

class Magestore_Fbcomment_Model_Observer {

	public function saveFbcommentConfig($observer)
	{
		$fbcomment = Mage::getModel('fbcomment/fbcomment');
		$fbcomment->setEditTime(now());
		try{
			$fbcomment->save();
		}catch(Exception $e){
			Mage::getSingleton('core/session')->addError($e);
		}
	}
	
	public function controller_action_predispatch_adminhtml($observer)
	{
		$controller = $observer->getControllerAction();
		if($controller->getRequest()->getControllerName() != 'system_config'
			|| $controller->getRequest()->getActionName() != 'edit')
			return;
		$section = $controller->getRequest()->getParam('section');
		if($section != 'fbcomment')
			return;
		
	}			
}