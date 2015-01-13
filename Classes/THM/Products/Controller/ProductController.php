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

	public function createDummyAction() {
		$this->productRepository->removeAll();
		$this->productRepository->createDummyProducts();
		$this->redirect("list");
	}

	public function newAction() {

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



}