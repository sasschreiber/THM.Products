<?php
namespace THM\Products\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow,
	TYPO3\Flow\Utility\Algorithms,
	TYPO3\Flow\Core\Booting\Scripts,
	TYPO3\Flow\Cli\CommandController,
	TYPO3\Flow\Http\Client\Browser,
	TYPO3\Flow\Http\Client\CurlEngine,


	THM\Products\Domain\Model\Product,
	THM\Products\Domain\Model\Property,
	THM\Products\Domain\Repository\ProductRepository;

/**
 * @Flow\Scope("singleton")
 */
class BenchmarkCommandController extends CommandController {

	/**
	 * Default value for title length of product.
	 */
	const PRODUCT_TITLE_LENGTH = 25;
	const PRODUCT_CHILDREN_LENGTH = 0;
	const PRODUCT_CHILDREN_DEPTH = 1;

	const PROPERTIES_COUNT_PER_PRODUCT = 5;
	const PROPERTY_NAME_LENGTH = 12;
	const PROPERTY_CONTENT_LENGTH = 340;

	const BENCHMARK_WRITE_COUNT_OF_PRODUCTS = 1000;
	const BENCHMARK_WRITE_COUNT_OF_PRODUCTS_EACH_FLUSH = 1;

	/**
	 * @var int
	 */
	protected $productsCount;

	/**
	 * @var int
	 */
	protected $propertiesPerProduct;

	/**
	 * @var int
	 */
	protected $productsPerFlush;

	/**
	 * @var int
	 */
	protected $childrenLength;

	/**
	 * @var int
	 */
	protected $childrenDepth;

	/**
	 * @var ProductRepository
	 * @Flow\Inject
	 */
	protected $productRepository;

	/**
	 * @var int
	 */
	private $productsGenerated;

	private $productsSum = 0;

	private $topLevelProductsSum = 0;

	protected $verbose;

	protected $dryrun;

	protected $startTime;

	protected $endTime;

	/**
	 * @Flow\Inject
	 * @var Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var CurlEngine
	 */
	protected $browserRequestEngine;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * 
	 * @var \TYPO3\Flow\Persistence\Doctrine\PersistenceManager
	 * @FLow\Inject
	 */
	protected $persistenceManager;

	/**
	 * A benchmark write command. <b>Please use this command only in Production context!</b>
	 *
	 * This command writes products in the database and prints the elapsed time.
	 *
	 *
	 * @param int $productsCount number of products to create.
	 * @param int $propertiesPerProduct number of properties per Product.
	 * @param int $productsPerFlush number of products for each flush.
	 * @param int $childrenDepth depth by generating of child Products disabled per default
	 * @param int $childrenLength
	 * @param bool $verbose
	 * @param bool $dryrun
	 * @return void
	 */
	public function writeCommand($productsCount = self::BENCHMARK_WRITE_COUNT_OF_PRODUCTS,
								 $propertiesPerProduct = self::PROPERTIES_COUNT_PER_PRODUCT,
								 $productsPerFlush = self::BENCHMARK_WRITE_COUNT_OF_PRODUCTS_EACH_FLUSH,
								 $childrenDepth = self::PRODUCT_CHILDREN_DEPTH,
								 $childrenLength = self::PRODUCT_CHILDREN_LENGTH,
								 $verbose = FALSE,
								 $dryrun = FALSE) {

		$this->productsCount = $productsCount;
		$this->propertiesPerProduct = $propertiesPerProduct;
		$this->productsPerFlush = $productsPerFlush;
		$this->childrenDepth = $childrenDepth;
		$this->childrenLength = $childrenLength;
		$this->verbose = $verbose;
		$this->dryrun = $dryrun;

		$this->printSummaryAsTable();

		$this->startTime = microtime(TRUE);
		$this->generateAndAddTopLevelProductsToRepository($this->productsCount, $this->childrenDepth);
		$this->endTime = microtime(TRUE);

		$this->outputLine('Generating of %s Products took %s seconds.' , array($this->productsSum, $this->endTime - $this->startTime));
	}


	/**
	 * Prints table with summary of this test.
	 */
	protected function printSummaryAsTable() {
		$this->output->outputTable(
			array(
				array('products', $this->productsCount),
				array('properties/product', $this->propertiesPerProduct),
				array('children/product', $this->childrenLength),
				array('depth', $this->childrenDepth),
				array('products/flush', $this->productsPerFlush)
			)
			, array('Settings')
		);

		$calculatedTotalGeneratedProducts = $this->calculateTheTotalNumberToGeneratingProducts();
		$this->output->outputTable(
			array(
				array('products', $calculatedTotalGeneratedProducts),
				array('properties', $this->propertiesPerProduct * $calculatedTotalGeneratedProducts),
				array('children of it', $calculatedTotalGeneratedProducts - $this->productsCount),
			)
			, array('Calculated total')
		);
	}

	/**
	 * @param $generateProducts
	 * @return void
	 */
	protected function generateAndAddTopLevelProductsToRepository($generateProducts) {
		for ($this->productsGenerated = 0; $this->productsGenerated < $generateProducts; $this->productsGenerated++) {
			$this->generateAndAddProductToRepository(NULL, $this->childrenDepth);
			if ($this->productsGenerated % $this->productsPerFlush == 0) {
				$this->persistenceManager->persistAll();
			}
		}
	}

	/**
	 * Generates agregate root product with children products
	 *
	 * @param null|Product $parent
	 * @param int $childrenDepth
	 * @return Product
	 */
	protected function generateAndAddProductToRepository(Product $parent = NULL, $childrenDepth = 0) {
		$leftSpacing = ($this->childrenDepth - $childrenDepth) * 2;

		if ($this->verbose && $parent == NULL) {
			$this->outputLine(str_repeat(' ', $leftSpacing) . 'Product Nr: ' . $this->productsGenerated);
		}

		$product = new Product();
		if ($parent == NULL) {
			$product->setTitle('The searchable top level Product Nr. ' . $this->topLevelProductsSum . '.');
			$this->topLevelProductsSum++;
		} else {
			$product->setTitle('The searchable Child-Product Nr. ' . $this->productsSum . ' from Parent with title "' . $parent->getTitle() . '".');
			$product->setParent($parent);
		}
		// add properties
		for ($propertiesGenerated = 0; $propertiesGenerated < $this->propertiesPerProduct; $propertiesGenerated++) {
			if ($this->verbose) {
				$this->outputLine(str_repeat(' ', $leftSpacing+2) . 'Propery Nr: ' . $propertiesGenerated);
			}
			$property = new Property();
			$property->setName('Property ' . $propertiesGenerated);
			$property->setContent( 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua '
				. $propertiesGenerated . '. '
				. Algorithms::generateRandomString(15) . ' ');
			$product->addProperty($property);
		}
		$this->productsSum++;
		// add children
		if ($childrenDepth > 0) {
			for ($childrenGenerated = 0; $childrenGenerated < $this->childrenLength; $childrenGenerated++) {
				if ($this->verbose) {
					$this->outputLine(str_repeat(' ', $leftSpacing+2) . ' Child Nr: ' . $childrenGenerated);
				}
				$this->generateAndAddProductToRepository($product, $childrenDepth -1);
			}
		}

		if (!$this->dryrun) {
			$this->productRepository->add($product);
		}
		
		return $product;
	}

	/**
	 * Cleans database
	 */
	public function cleanDBCommand() {
		$this->productRepository->removeAll();
	}


	/**
	 * Calculates the number to generating products
	 *
	 * @return int
	 */
	protected function calculateTheTotalNumberToGeneratingProducts() {

		$products = 0;
		for ($depth = $this->childrenDepth-1; $depth >= 0; --$depth) {
			$products += $this->childrenLength * pow($this->childrenLength, $depth);;
		}
		$products = $products * $this->productsCount + $this->productsCount;
		return $products;
	}

	
	/**
	 * This command simply runs a findAll query and logs the time.
	 * @return void
	 */
	public function findAllCommand() {
		$starttime = microtime(TRUE);
		$products = $this->productRepository->findAll();
		$endtime = microtime(TRUE);

		$this->outputLine("FindAll Test: Took " . ($endtime - $starttime) . " seconds.");
		unset($products);
	}


	public function readAllTopLevelProductsCommand() {
		$this->startTime = microtime(TRUE);

		$topLevelProducts = $this->productRepository->findByTopLevel(TRUE);
		$readProducts = 0;
		foreach ($topLevelProducts as $topLevelProduct) {
			// make sure we catch all properties and references independently of lazy loading.
			$dumpedTopLevelProduct = \TYPO3\Flow\var_dump($topLevelProduct, NULL, TRUE);
			$readProducts++;
		}
		$this->endTime = microtime(TRUE);
		$elapsedTime = $this->endTime - $this->startTime;

		$this->outputFormatted('Reading of %s top level Products took %s seconds.', array($readProducts, $elapsedTime));
	}

	public function findNProductsByTitleCommand($number = 1 ) {
		$topLevelProducts = $this->productRepository->findByTopLevel(TRUE);
		$numberOfTopLevelProducts = $topLevelProducts->countAll();
		\TYPO3\Flow\var_dump($numberOfTopLevelProducts);
	}

}