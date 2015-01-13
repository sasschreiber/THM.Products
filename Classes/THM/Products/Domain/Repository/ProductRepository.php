<?php
namespace THM\Products\Domain\Repository;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class ProductRepository extends Repository {

	/**
	* @var \TYPO3\Flow\Persistence\Doctrine\PersistenceManager
	* @Flow\Inject
	*/
	protected $persistenceManager;

	/**
	* @return void
	*/
	public function createDummyProducts() {
		$productList = array(
			0 => "Dummy1",
			1 => "Dummy2",
			2 => "Dummy3"
		);

		foreach ($productList as $item) {
			$product = new \THM\Products\Domain\Model\Product();
			$product->setTitle($item);
			$this->add($product);
		}

		$this->persistenceManager->persistAll();


	}

}