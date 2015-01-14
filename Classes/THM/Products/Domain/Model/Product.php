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
class Product {

	/**
	* @var string
    * @Flow\Validate(type="Text")
    * @Flow\Validate(type="StringLength", options={ "minimum"=2, "maximum"=80 })
    * @ORM\Column(length=80)
	*/
	protected $title;
	
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
     * @var \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Property>
     * @ORM\OneToMany(mappedBy="product")
     */
    protected $properties;
    
    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Property>
     */
    public function getProperties() {
      return $this->properties;
    }
    
    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\THM\Products\Domain\Model\Property> $properties
     * @return void
     */
    public function setProperties(\Doctrine\Common\Collections\ArrayCollection $properties) {
      $this->properties = $properties;
    }
    
    /**
     * @param \THM\Products\Domain\Model\Property $property
     * @return void
     */
    public function addProperty(\THM\Products\Domain\Model\Property $property) {
      $this->properties->attach($property);
    }
    
    /**
     * @param \THM\Products\Domain\Model\Property $property
     * @return void
     */
    public function removeProperty(\THM\Products\Domain\Model\Property $property) {
      $this->properties->detach($property);
    }


}