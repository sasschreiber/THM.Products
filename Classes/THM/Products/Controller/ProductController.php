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
//		$products = $this->productRepository->findByParent(NULL);
//		$products = $this->productRepository->findByParent('f49cb66745e5f0458ae108f2ff11781c');
		$products = $this->productRepository->findByTitle('P 0000');
		$products = $this->productRepository->findAll();
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
	* @return void
	*/
	public function createAction(\THM\Products\Domain\Model\Product $product) {
		$this->productRepository->add($product);
		$this->addFlashMessage("New product created!");
		$this->redirect("show", NULL, NULL, array("product"=>$product));
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
		$this->redirect("show", NULL, NULL, array("product"=>$product));
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
