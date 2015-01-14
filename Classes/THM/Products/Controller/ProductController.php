<?php
namespace THM\Products\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

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
		$products = $this->productRepository->findByParent(NULL);
		$this->view->assign("products", $products);
	}

	/**
	* @param \THM\Products\Domain\Model\Product $product
	* @return void
	*/
	public function showAction(\THM\Products\Domain\Model\Product $product) {
		$this->view->assign("product", $product);
	}

	public function createDummyAction() {
		$this->productRepository->removeAll();
		$this->productRepository->createDummyProducts();
		$this->redirect("list");
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
		$product = $property->getProduct();
		$product->addProperty($property);
		$this->productRepository->update($product);
		$this->redirect("show", NULL, NULL, array("product"=>$product));
	}



}