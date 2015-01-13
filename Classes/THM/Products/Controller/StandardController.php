<?php
namespace THM\Products\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @return void
	 */
	public function indexAction() {
		if ($this->request->hasArgument("product")) {
			$this->redirect("show", "Product", NULL, $this->request->getArguments());
		}
		$this->redirect("list", "Product", NULL, $this->request->getArguments());
	}


}