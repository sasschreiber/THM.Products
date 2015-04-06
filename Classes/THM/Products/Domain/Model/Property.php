<?php
namespace THM\Products\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Property {

	/**
	* @var string
    * @Flow\Validate(type="Text")
    * @Flow\Validate(type="StringLength", options={ "minimum"=2, "maximum"=80 })
    * @ORM\Column(length=80)
	*/
	protected $name;
	
	/**
	 * @return string
	 */
	public function getName() {
	  return $this->name;
	}
	
	/**
	 * @param string $name
	 * @return void
	 */
	public function setName($name) {
	  $this->name = $name;
	}

	/**
	* @var string
    * @Flow\Validate(type="Text")
    * @ORM\Column(type="text")
	*/
	protected $content;
	
	/**
	 * @return string
	 */
	public function getContent() {
	  return $this->content;
	}
	
	/**
	 * @param string $content
	 * @return void
	 */
	public function setContent($content) {
	  $this->content = $content;
	}
	
	/**
	 * @var \THM\Products\Domain\Model\Product $product
	 * @ORM\ManyToOne(inversedBy="properties")
	 */
	protected $product;
	
	/**
	 * @return \THM\Products\Domain\Model\Product
	 */
	public function getProduct() {
	  return $this->product;
	}
	
	/**
	 * @param \THM\Products\Domain\Model\Product $product
	 * @return void
	 */
	public function setProduct(\THM\Products\Domain\Model\Product $product) {
		$this->product = $product;
	}

}