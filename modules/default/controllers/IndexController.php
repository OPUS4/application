<?php
/**
 *
 */

/**
 * This controller is called on every initial
 * page request. It currently configures the view for greeting the user and
 * sets up the main menu.
 *
 * @category    Application
 * @package     Module_Default
 * @subpackage  Index
 */
class IndexController extends Zend_Controller_Action {

	/**
	 * Just to be there. No actions taken.
	 *
	 * @return void
	 *
	 */
	public function indexAction() {
		$this->view->title = 'Index';
	}

}