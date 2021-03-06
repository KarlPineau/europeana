<?php

namespace NER\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Synset
 *
 * @ORM\Table(name="synset")
 * @ORM\Entity()
 */
class Synset
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="NER\ModelBundle\Entity\Field")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $field;


    /**
     * @var string
     *
     * @ORM\Column(name="synset", type="string", length=255, nullable=true)
     */
    private $synset;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="errorStatement", type="string", length=255, nullable=true)
     */
    private $errorStatement;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="createDate", type="datetime", nullable=false)
     */
    protected $createDate;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Set synset
     *
     * @param string $synset
     *
     * @return Synset
     */
    public function setSynset($synset)
    {
        $this->synset = $synset;

        return $this;
    }

    /**
     * Get synset
     *
     * @return string
     */
    public function getSynset()
    {
        return $this->synset;
    }

    /**
     * Set errorStatement
     *
     * @param string $errorStatement
     *
     * @return Synset
     */
    public function setErrorStatement($errorStatement)
    {
        $this->errorStatement = $errorStatement;

        return $this;
    }

    /**
     * Get errorStatement
     *
     * @return string
     */
    public function getErrorStatement()
    {
        return $this->errorStatement;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Synset
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Synset
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set field
     *
     * @param \NER\ModelBundle\Entity\Field $field
     *
     * @return Synset
     */
    public function setField(\NER\ModelBundle\Entity\Field $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return \NER\ModelBundle\Entity\Field
     */
    public function getField()
    {
        return $this->field;
    }
}
