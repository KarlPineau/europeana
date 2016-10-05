<?php

namespace NER\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Field
 *
 * @ORM\Table(name="field")
 * @ORM\Entity()
 */
class Field
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
     * @ORM\ManyToOne(targetEntity="NER\ModelBundle\Entity\Entity")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $entity;

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
     *
     * @ORM\Column(name="language", type="string", length=255, nullable=true)
     */
    private $language;

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
     * Set literal
     *
     * @param string $literal
     *
     * @return Field
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
     * @return Field
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
     * Set language
     *
     * @param string $language
     *
     * @return Field
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Field
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
     * Set entity
     *
     * @param \NER\ModelBundle\Entity\Entity $entity
     *
     * @return Field
     */
    public function setEntity(\NER\ModelBundle\Entity\Entity $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return \NER\ModelBundle\Entity\Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
