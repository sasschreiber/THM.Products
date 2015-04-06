<?php
namespace THM\Products\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Flow\Entity
 */
class Product {


	/**
	* @var string
    * @Flow\Validate(type="Text")
    * @Flow\Validate(type="StringLength", options={ "minimum"=2})
    *
	*/
	protected $title;

	/**
	 * @var boolean $topLevel
	 * @ORM\Column(type="boolean")
	 */
	protected $topLevel = TRUE;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Property>
     * @ORM\OneToMany(mappedBy="product", cascade={"persist", "remove"})
     */
    protected $properties;

    /**
     * @var \THM\Products\Domain\Model\Product $parent
     * @ORM\ManyToOne(inversedBy="children")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Product>
     * @ORM\OneToMany(mappedBy="parent", cascade={"persist", "remove"})
     */
    protected $children;

	public function __construct() {
		$this->properties = new ArrayCollection();
		$this->children = new ArrayCollection();
	}
	
	/**
	 * @param $topLevel
	 * @return void
	 */
	public function setTopLevel($topLevel) {
		$this->topLevel = $topLevel;
	}
	
	/**
	 * @return boolean
	 */
	public function isTopLevel() {
	  return $this->topLevel;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return ArrayCollection<Property>
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @param ArrayCollection $properties
	 * @return void
	 */
	public function setProperties(ArrayCollection $properties) {
		$this->properties = $properties;
	}

	/**
	 * @param Property $property
	 * @return void
	 */
	public function addProperty(Property $property) {
		if (!$this->properties->contains($property)) {
			$this->properties->add($property);
		}
		$property->setProduct($this);
	}

	/**
	 * @param Property $property
	 * @return void
	 */
	public function removeProperty(Property $property) {
		$this->properties->remove($property);
	}

	/**
	 * @return Product
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param Product $parent
	 * @param bool $registerByParent
	 * @return void
	 */
	public function setParent(Product $parent, $registerByParent = TRUE) {
		$this->parent = $parent;
		if ($registerByParent) {
			$parent->addChild($this);
		}
		$this->topLevel = FALSE;
	}

	/**
	 * @return ArrayCollection<Product>
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @param ArrayCollection $children
	 * @return void
	 */
	public function setChildren(ArrayCollection $children) {
		$this->children = $children;
	}

	/**
	 * @param Product $child
	 * @return void
	 */
	public function addChild(Product $child) {
		if (!$this->children->contains($child)) {
			$this->children->add($child);
		}
		$child->setParent($this, FALSE);
	}

	/**
	 * @param Product $child
	 * @return void
	 */
	public function removeChild(Product $child) {
		$this->children->remove($child);
	}

}