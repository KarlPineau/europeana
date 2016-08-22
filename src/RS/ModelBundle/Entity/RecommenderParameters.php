<?php

namespace RS\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecommenderParameters
 *
 * @ORM\Table(name="recommender_parameters")
 * @ORM\Entity(repositoryClass="RS\ModelBundle\Repository\RecommenderParametersRepository")
 */
class RecommenderParameters
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
     * @var bool
     *
     * @ORM\Column(name="isDcSubject", type="boolean")
     */
    private $isDcSubject;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDcType", type="boolean")
     */
    private $isDcType;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDcCreator", type="boolean")
     */
    private $isDcCreator;

    /**
     * @var bool
     *
     * @ORM\Column(name="isDcContributor", type="boolean")
     */
    private $isDcContributor;

    /**
     * @var bool
     *
     * @ORM\Column(name="isTitle", type="boolean")
     */
    private $isTitle;


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
     * Set isDcSubject
     *
     * @param boolean $isDcSubject
     *
     * @return RecommenderParameters
     */
    public function setIsDcSubject($isDcSubject)
    {
        $this->isDcSubject = $isDcSubject;

        return $this;
    }

    /**
     * Get isDcSubject
     *
     * @return bool
     */
    public function getIsDcSubject()
    {
        return $this->isDcSubject;
    }

    /**
     * Set isDcType
     *
     * @param boolean $isDcType
     *
     * @return RecommenderParameters
     */
    public function setIsDcType($isDcType)
    {
        $this->isDcType = $isDcType;

        return $this;
    }

    /**
     * Get isDcType
     *
     * @return bool
     */
    public function getIsDcType()
    {
        return $this->isDcType;
    }

    /**
     * Set isDcCreator
     *
     * @param boolean $isDcCreator
     *
     * @return RecommenderParameters
     */
    public function setIsDcCreator($isDcCreator)
    {
        $this->isDcCreator = $isDcCreator;

        return $this;
    }

    /**
     * Get isDcCreator
     *
     * @return bool
     */
    public function getIsDcCreator()
    {
        return $this->isDcCreator;
    }

    /**
     * Set isDcContributor
     *
     * @param boolean $isDcContributor
     *
     * @return RecommenderParameters
     */
    public function setIsDcContributor($isDcContributor)
    {
        $this->isDcContributor = $isDcContributor;

        return $this;
    }

    /**
     * Get isDcContributor
     *
     * @return bool
     */
    public function getIsDcContributor()
    {
        return $this->isDcContributor;
    }

    /**
     * Set isTitle
     *
     * @param boolean $isTitle
     *
     * @return RecommenderParameters
     */
    public function setIsTitle($isTitle)
    {
        $this->isTitle = $isTitle;

        return $this;
    }

    /**
     * Get isTitle
     *
     * @return bool
     */
    public function getIsTitle()
    {
        return $this->isTitle;
    }
}

