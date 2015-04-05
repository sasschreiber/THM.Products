<?php
namespace THM\Products\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow,
	TYPO3\Flow\Error\Message,
	TYPO3\Flow\Exception,
	TYPO3\Flow\Mvc\Controller\ActionController,

	THM\Products\Domain\Model\Product,
	THM\Products\Domain\Model\Property,
	THM\Products\Domain\Repository\ProductRepository;

class ProductController extends ActionController {

	/**
	* @var ProductRepository
	* @Flow\Inject
	*/
	protected $productRepository;

	/**
	* @return void
	*/
	public function listAction() {
		$products = $this->productRepository->findByTopLevel(TRUE);
		$this->view->assign("products", $products);
	}

	/**
	* @param Product $product
	* @return void
	*/
	public function showAction(Product $product) {
		$this->view->assign("product", $product);
	}

	/**
	 * @param Product $parent
	 */
	public function newAction(Product $parent = NULL) {
		$this->view->assign("parent", $parent);
	}

	/**
	 * @param Product $product
	 */
	public function editAction(Product $product) {
		$this->view->assign('product', $product);
	}

	/**
	 * @param Product $product
	 */
	public function updateAction(Product $product) {
		$this->productRepository->update($product);
        $this->addFlashMessage("Updated changes to product \"" . $product->getTitle() . "\".", "Success", Message::SEVERITY_OK);
		$this->redirect("edit", NULL, NULL, array("product"=>$product));

    }

	/**
	 * @param Product $product
	 * @return void
	 */
	public function createAction(Product $product) {
		//Add the product to its parents storage if necessary (because of the bidirectional relation)
		$parent = $product->getParent();
		if ($parent) {
			// where is "cascade persist" to set? Without next line, we get error "The given object is unknown to the Persistence Manager.".
			$this->productRepository->add($product);
			$this->productRepository->update($parent);
		}
		else {
			//Mark the product as top level if it has no parent
			//This is necessary because we cannot use findByParent(NULL) for the list action
			$product->setToplevel(TRUE);
			$this->productRepository->add($product);
		}
		
		$this->addFlashMessage("New product created!");
		$this->redirect("edit", NULL, NULL, array("product"=>$product));
	}

	
	/**
	* Delete products
	* @param Product $product
	**/
	public function deleteAction(Product $product) {
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
	 * @param Property $property
	 * @return void
	 */
	public function addPropertyAction(Property $property) {
		try {
			$productIdentifier = $this->request->getReferringRequest()->getArgument('product')['__identity'];
		} catch (Exception $exception) {
			$this->addFlashMessage('Can not get identifier for Product from referring request.', 'Error accured while handling request.', Message::SEVERITY_ERROR);
			$this->redirect($this->request->getReferringRequest()->getControllerActionName(), NULL, NULL, $this->request->getReferringRequest()->getArguments());
		}

		/* @var $product Product */
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

		$property = new Property();
		$property->setName('Test');
		$property->setContent('jhdfskjhslakjgh  jhlfkjhflksjhflkj');

		$product = new Product();
		$product->setTitle('Product 001');
		$product->addProperty($property);


		$this->productRepository->add($product);
		$this->productRepository->flushDocumentManager();
		$this->redirect('list');
	}

}
