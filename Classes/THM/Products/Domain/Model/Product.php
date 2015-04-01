<?php
namespace THM\Products\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "THM.Products".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ODM\CouchDB\Mapping\Annotations as ODM,
	Radmiraal\CouchDB\Persistence\AbstractDocument;

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
	 * @Flow\Validate(type="StringLength", options={ "minimum"=2, "maximum"=80 })
	 *
	 * @ODM\Field(type="string")
	 *
	 */
	protected $title;


	/**
	 * @var boolean $isTopLevel
	 * @ODM\Field(type="boolean")
	 * @ODM\Index
	 */
	protected $isTopLevel;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Property>
	 *
	 * @ODM\EmbedMany(targetDocument="THM\Products\Domain\Model\Property")
	 */
	protected $properties;

	/**
	 * @var \THM\Products\Domain\Model\Product
	 *
	 * @ODM\ReferenceOne(targetDocument="\THM\Products\Domain\Model\Product")
	 */
	protected $parent;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Product>
	 *
	 * @ODM\ReferenceMany(targetDocument="\THM\Products\Domain\Model\Product")
	 */
	protected $children;

	public function __construct(){
		$this->properties = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @return boolean
	 */
	public function getIsTopLevel() {
	  return $this->isTopLevel;
	}
	
	/**
	 * @param boolean $isTopLevel
	 * @return void
	 */
	public function setIsTopLevel($isTopLevel) {
	  $this->isTopLevel = $isTopLevel;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection<THM\Products\Domain\Model\Property>
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @param \Doctrine\Common\Collections\ArrayCollection<THM\Products\Domain\Model\Property> $properties
	 * @return void
	 */
	public function setProperties(\Doctrine\Common\Collections\ArrayCollection $properties) {
		$this->properties = $properties;
	}

	/**
	 * @param \THM\Products\Domain\Model\Property $property
	 * @return void
	 */
	public function addProperty(\THM\Products\Domain\Model\Property $property)
	{
		$this->properties->add($property);
	}

	/**
	 * @param \THM\Products\Domain\Model\Property $property
	 * @return void
	 */
	public function removeProperty(\THM\Products\Domain\Model\Property $property)
	{
		$this->properties->remove($property);
	}

	/**
	 * @return \THM\Products\Domain\Model\Product
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param \THM\Products\Domain\Model\Product $parent
	 * @return void
	 */
	public function setParent(\THM\Products\Domain\Model\Product $parent)
	{
		$this->parent = $parent;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Product>
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * @param \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Product> $children
	 * @return void
	 */
	public function setChildren(\Doctrine\Common\Collections\ArrayCollection $children)
	{
		$this->children = $children;
	}

	/**
	 * @param \THM\Products\Domain\Model\Product $child
	 * @return void
	 */
	public function addChild(\THM\Products\Domain\Model\Product $child)
	{
		$this->children->add($child);
	}

	/**
	 * @param \THM\Products\Domain\Model\Product $child
	 * @return void
	 */
	public function removeChild(\THM\Products\Domain\Model\Product $child)
	{
		$this->children->remove($child);
	}


}