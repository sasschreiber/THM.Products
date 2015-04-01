<?php
namespace THM\Products\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Message;

class ProductController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	* @var \THM\Products\Domain\Repository\ProductRepository
	* @Flow\Inject
	*/
	protected $productRepository;


	/**
	* @return void
	*/
	public function listAction() {
		$products = $this->productRepository->findByIsTopLevel(TRUE);
		$this->view->assign("products", $products);
	}

	/**
	* @param \THM\Products\Domain\Model\Product $product
	* @return void
	*/
	public function showAction(\THM\Products\Domain\Model\Product $product) {
		$this->view->assign("product", $product);
	}

	public function newAction(\THM\Products\Domain\Model\Product $parent = NULL) {
		$this->view->assign("parent", $parent);
	}

	/**
	 * @param \THM\Products\Domain\Model\Product $product
	 */
	public function editAction(\THM\Products\Domain\Model\Product $product) {
		$this->view->assign('product', $product);
	}

	/**
	 * @param \THM\Products\Domain\Model\Product $product
	 */
	public function updateAction(\THM\Products\Domain\Model\Product $product) {
		$this->productRepository->update($product);
		$this->redirect("edit", NULL, NULL, array("product"=>$product));
	}

	/**
	 * @param \THM\Products\Domain\Model\Product $product
	 * @return void
	 */
	public function createAction(\THM\Products\Domain\Model\Product $product) {

		$this->productRepository->add($product);
		
		//Add the product to its parents storage if necessary (because of the bidirectional relation)
		$parent = $product->getParent();
		if ($parent) {
			$parent->addChild($product);
			$this->productRepository->update($parent);			
		}
		else {
			//Mark the product as top level if it has no parent
			//This is necessary because we cannot use findByParent(NULL) for the list action
			$product->setIsToplevel(TRUE);
			$this->productRepository->update($product);
		}
		
		$this->addFlashMessage("New product created!");
		$this->redirect("show", NULL, NULL, array("product"=>$product));
	}

	
	/**
	* Delete products
	* @param \THM\Products\Domain\Model\Product $product
	**/
	public function deleteAction(\THM\Products\Domain\Model\ Product $product) {
		if ($product->getChildren()->count() > 0) {
			$this->addFlashMessage("This Product still holds child products. Please delete them first.", "Warning", Message::SEVERITY_ERROR);
		}
		else {
			$this->productRepository->remove($product);
			$this->addFlashMessage("Product \"" . $product->getTitle() . "\" was deleted.", "Success", Message::SEVERITY_OK);
		}

		//Redirect to the correct spot (either list oder show, depending on the parent)
		if ($product->getParent() != NULL) {
			$this->redirect("show", NULL, NULL, array("product" => $product->getParent()));
		}
		$this->redirect("list");
	}

	/**
	 * @param \THM\Products\Domain\Model\Property $property
	 * @return void
	 */
	public function addPropertyAction(\THM\Products\Domain\Model\Property $property) {
		try {
			$productIdentifier = $this->request->getReferringRequest()->getArgument('product')['__identity'];
		} catch (\TYPO3\Flow\Exception $exception) {
			$this->addFlashMessage('Can not get identifier for Product from referring request.', 'Error accured while handling request.', Message::SEVERITY_ERROR);
		}

		/* @var $product \THM\Products\Domain\Model\Product */
		$product = $this->productRepository->findByIdentifier($productIdentifier);

		$product->addProperty($property);
		$this->productRepository->update($product);
		$this->redirect($this->request->getReferringRequest()->getControllerActionName(), NULL, NULL, array("product"=>$product));
	}

	/**
	 * Only for development, please remove this action.
	 */
	public function removeAllAction(){
		$this->productRepository->removeAll();
		$this->productRepository->flushDocumentManager();
		$this->redirect('list');
	}

	/**
	 * Only for development, please remove this action.
	 */
	public function addFullyAction() {

		$property = new \THM\Products\Domain\Model\Property();
		$property->setName('Test');
		$property->setContent('jhdfskjhslakjgh  jhlfkjhflksjhflkj');

		$product = new \THM\Products\Domain\Model\Product();
		$product->setTitle('Product 001');
		$product->addProperty($property);


		$this->productRepository->add($product);
		$this->productRepository->flushDocumentManager();
		$this->redirect('list');
	}

}
