<?php
namespace THM\Products\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow,
	TYPO3\Flow\Utility\Algorithms,
	TYPO3\Flow\Core\Booting\Scripts,

	\THM\Products\Domain\Model\Product,
	\THM\Products\Domain\Model\Property;

/**
 * @Flow\Scope("singleton")
 */
class BenchmarkCommandController extends \TYPO3\Flow\Cli\CommandController {

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
	 * @var \THM\Products\Domain\Repository\ProductRepository
	 * @Flow\Inject
	 */
	protected $productRepository;

	/**
	 * @var int
	 */
	private $productsGenerated;

	private $productsSum = 0;

	protected $verbose;

	protected $dryrun;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Http\Client\CurlEngine
	 */
	protected $browserRequestEngine;

	/**
	 * @var string
	 * @Flow\Inject(setting="persistence.backendOptions", package="Radmiraal.CouchDB")
	 */
	protected $persistenceSettings;

	/**
	 * @var array
	 * @Flow\InjectConfiguration(package="TYPO3.Flow")
	 */
	protected $settings;

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

		$this->output->outputTable(
			array(
				array('products', $this->productsCount),
				array('properties/product', $this->propertiesPerProduct),
				array('children/product', $this->childrenLength),
				array('depth', $this->childrenDepth)
			),
			array('Settings')
		);

		$calculatedTotalGeneratedProducts = $this->calculateTheTotalNumberToGeneratingProducts();
		$this->output->outputTable(
			array(
				array('products', $calculatedTotalGeneratedProducts),
				array('properties', $this->propertiesPerProduct * $calculatedTotalGeneratedProducts),
				array('children of it', $calculatedTotalGeneratedProducts - $this->productsCount),
			),
			array('Calculated total')
		);

		$starttime = microtime(TRUE);
		$this->generateAndAddProductsToRepository($this->productsCount, $this->childrenDepth);
		$endtime = microtime(TRUE);

		$this->outputLine('Generating of %s Products took %s seconds.' , array($this->productsSum, $endtime - $starttime));
	}

	/**
	 * @param $generateProducts
	 * @return void
	 */
	protected function generateAndAddProductsToRepository($generateProducts) {
		for ($this->productsGenerated = 0; $this->productsGenerated < $generateProducts; $this->productsGenerated++) {
			$this->generateAndAddProductToRepository(NULL, $this->childrenDepth);
			if ($this->productsGenerated % $this->productsPerFlush == 0) {
				$this->productRepository->flushDocumentManager();
			}
		}
	}

	/**
	 * Generates agregate root product with children products
	 *
	 * @param null $parent
	 * @param int $childrenDepth
	 * @return Product
	 */
	protected function generateAndAddProductToRepository($parent = NULL, $childrenDepth = 0) {
		$leftSpacing = ($this->childrenDepth - $childrenDepth) * 2;

		if ($this->verbose && $parent == NULL) {
			$this->outputLine(str_repeat(' ', $leftSpacing) . 'Product Nr: ' . $this->productsGenerated);
		}

		$product = new Product();
		$product->setTitle(Algorithms::generateRandomString(self::PRODUCT_TITLE_LENGTH));
		// add properties
		for ($propertiesGenerated = 0; $propertiesGenerated < $this->propertiesPerProduct; $propertiesGenerated++) {
			if ($this->verbose) {
				$this->outputLine(str_repeat(' ', $leftSpacing+2) . 'Propery Nr: ' . $propertiesGenerated);
			}
			$property = new Property();
			$property->setName(Algorithms::generateRandomString(self::PROPERTY_NAME_LENGTH));
			$property->setContent(Algorithms::generateRandomString(self::PROPERTY_CONTENT_LENGTH));
			$product->addProperty($property);
		}
		// add children
		if ($childrenDepth > 0) {
			for ($childrenGenerated = 0; $childrenGenerated < $this->childrenLength; $childrenGenerated++) {
				if ($this->verbose) {
					$this->outputLine(str_repeat(' ', $leftSpacing+2) . ' Child Nr: ' . $childrenGenerated);
				}
				$this->generateAndAddProductToRepository($product, $childrenDepth -1);
			}
		}

		if ($parent !== NULL) {
			$product->setParent($parent);
			$parent->addChild($product);
		}

		$this->productsSum++;
		if (!$this->dryrun) {
			$this->productRepository->add($product);
		}
		
		return $product;
	}


	/**
	 * Cleans database
	 */
	public function cleanDBCommand() {
		$uri = vsprintf('http://%s:%s/%s', array(
				$this->persistenceSettings['ip'],
				$this->persistenceSettings['port'],
				$this->persistenceSettings['databaseName'])
		);

		$this->browserRequestEngine->setOption(CURLOPT_USERPWD, $this->persistenceSettings['username'] . ':' . $this->persistenceSettings['password']);
		$this->browser->setRequestEngine($this->browserRequestEngine);

		$response = $this->browser->request($uri, 'DELETE');
		if ($response->getStatusCode() == 200) {
			$this->outputLine('Database successfully deleted. Trying to migrate designs...');
			Scripts::executeCommand('Radmiraal.CouchDB:migrate:designs', $this->settings);
			$this->outputLine();
		} else {
			$this->outputLine('Could not delete Database.');
			$this->output($response->getContent());
			$this->sendAndExit($response->getStatusCode());
		}
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

}