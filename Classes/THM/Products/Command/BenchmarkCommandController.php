<?php
namespace THM\Products\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow,
	TYPO3\Flow\Utility\Algorithms,
	Doctrine\Common\Collections\ArrayCollection,

	\THM\Products\Domain\Model\Product,
	\THM\Products\Domain\Model\Property;

use Symfony\Component\Console\Helper\Table;

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
	const BENCHMARK_WRITE_COUNT_OF_PRODUCTS_EACH_FLUSH = 100;

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

//		$this->outputFormatted('<b>Write benchmark for:</b>', array());
//		$this->output->outputTable(
//				array(
//					array('settings', $this->productsCount, $this->propertiesPerProduct, $this->childrenLength, $this->childrenDepth)
//				),
//				array('', 'products', 'properties/product', 'children/product', 'depth')
//			);
//		$this->generateAndAddProductsToRepository($this->productsCount, $this->childrenDepth);
//
//
//		$calculatedTotalGeneratedProducts = $this->calculateTheTotalNumberToGeneratingProducts();
//		$this->output->outputTable(
//			array(
//				array(' total  ', $calculatedTotalGeneratedProducts, $calculatedTotalGeneratedProducts * $this->propertiesPerProduct, $calculatedTotalGeneratedProducts - $this->productsCount)
//			),
//			array('', 'products', '    properties    ', ' children of it ', '     ')
//		);


		$this->generateAndAddProductsToRepository($this->productsCount, $this->childrenDepth);


		$this->outputLine('Generated Products: ' . $this->productsSum);

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

	}

	/**
	 * @param $generateProducts
	 * @return void
	 */
	protected function generateAndAddProductsToRepository($generateProducts) {
		for ($this->productsGenerated = 0; $this->productsGenerated < $generateProducts; $this->productsGenerated++) {
			$this->generateAndAddProductToRepository(NULL, $this->childrenDepth);
			//\TYPO3\Flow\var_dump($this);
//			if ($this->productsGenerated % $this->productsPerFlush == 0) {
//				$this->productRepository->flushDocumentManager();
//			}
			//$this->generateAndAddProductsToRepository(self::PRODUCT_CHILDREN_LENGTH, self::PRODUCT_CHILDREN_DEPTH, $childrenDepth - 1);
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
	 *
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
		return $products*2;
	}

}