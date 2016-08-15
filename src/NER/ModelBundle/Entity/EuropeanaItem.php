<?php

namespace NER\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EuropeanaItem
 *
 * @ORM\Table(name="europeana_item")
 * @ORM\Entity(repositoryClass="NER\ModelBundle\Repository\EuropeanaItemRepository")
 */
class EuropeanaItem
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
     * @Assert\Url()
     * @ORM\Column(name="URI", type="string", length=255, nullable=true)
     */
    private $URI;

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
     * Set uRI
     *
     * @param string $uRI
     *
     * @return EuropeanaItem
     */
    public function setURI($uRI)
    {
        $this->URI = $uRI;

        return $this;
    }

    /**
     * Get uRI
     *
     * @return string
     */
    public function getURI()
    {
        return $this->URI;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return EuropeanaItem
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
