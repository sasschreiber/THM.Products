<?php
namespace THM\Products\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow,
	Doctrine\ODM\CouchDB\Mapping\Annotations as ODM,
	Doctrine\Common\Collections\ArrayCollection;

/**
 * @ODM\Document(indexed=true)
 */
class Product {

	/**
	 * @var string
	 * @ODM\Id(type="string")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @Flow\Validate(type="Text")
	 * @Flow\Validate(type="StringLength", options={ "minimum"=2})
	 *
	 * @ODM\Field(type="string")
	 *
	 */
	protected $title;

	/**
	 * @var boolean $topLevel
	 * @ODM\Field(type="boolean")
	 * @ODM\Index
	 */
	protected $topLevel = TRUE;

	/**
	 * @var ArrayCollection<Property>
	 *
	 * @ODM\EmbedMany(targetDocument="Property")
	 */
	protected $properties;

	/**
	 * @var Product
	 *
	 * @ODM\ReferenceOne(targetDocument="Product")
	 */
	protected $parent;

	/**
	 * @var ArrayCollection<Product>
	 *
	 * @ODM\ReferenceMany(targetDocument="Product", cascade={"persist"})
	 *
	 * @todo: cascade persist does not work with current implementation of Radmiraal.CouchDB
	 */
	protected $children;

	public function __construct() {
		$this->properties = new ArrayCollection();
		$this->children = new ArrayCollection();
	}

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
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
		$this->properties->add($property);
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