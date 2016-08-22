<?php

namespace NER\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NameEntityRecognition
 *
 * @ORM\Table(name="name_entity_recognition")
 * @ORM\Entity(repositoryClass="NER\ModelBundle\Repository\NameEntityRecognitionRepository")
 */
class NameEntityRecognition
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
     * @var string
     *
     * @ORM\Column(name="literal", type="string", length=255)
     */
    private $literal;

    /**
     * @var string
     *
     * @ORM\Column(name="field", type="string", length=255)
     */
    private $field;

    /**
     * @var string
     * @Assert\Url()
     * @ORM\Column(name="europeanaURI", type="string", length=255, nullable=true)
     */
    private $europeanaURI;

    /**
     * @var string
     * @Assert\Url()
     * @ORM\Column(name="wikidataURI", type="string", length=255, nullable=true)
     */
    private $wikidataURI;

    /**
     * @var array
     *
     * @ORM\Column(name="synsets", type="array", nullable=true)
     */
    private $synsets;

    /**
     * @var string
     *
     * @ORM\Column(name="synset", type="string", length=255, nullable=true)
     */
    private $synset;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set literal
     *
     * @param string $literal
     *
     * @return NameEntityRecognition
     */
    public function setLiteral($literal)
    {
        $this->literal = $literal;

        return $this;
    }

    /**
     * Get literal
     *
     * @return string
     */
    public function getLiteral()
    {
        return $this->literal;
    }

    /**
     * Set field
     *
     * @param string $field
     *
     * @return NameEntityRecognition
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set europeanaURI
     *
     * @param string $europeanaURI
     *
     * @return NameEntityRecognition
     */
    public function setEuropeanaURI($europeanaURI)
    {
        $this->europeanaURI = $europeanaURI;

        return $this;
    }

    /**
     * Get europeanaURI
     *
     * @return string
     */
    public function getEuropeanaURI()
    {
        return $this->europeanaURI;
    }

    /**
     * Set wikidataURI
     *
     * @param string $wikidataURI
     *
     * @return NameEntityRecognition
     */
    public function setWikidataURI($wikidataURI)
    {
        $this->wikidataURI = $wikidataURI;

        return $this;
    }

    /**
     * Get wikidataURI
     *
     * @return string
     */
    public function getWikidataURI()
    {
        return $this->wikidataURI;
    }

    /**
     * Set synsets
     *
     * @param array $synsets
     *
     * @return NameEntityRecognition
     */
    public function setSynsets($synsets)
    {
        $this->synsets = $synsets;

        return $this;
    }

    /**
     * Get synsets
     *
     * @return array
     */
    public function getSynsets()
    {
        return $this->synsets;
    }

    /**
     * Set synset
     *
     * @param string $synset
     *
     * @return NameEntityRecognition
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
     * @return NameEntityRecognition
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
     * @return NameEntityRecognition
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
}
