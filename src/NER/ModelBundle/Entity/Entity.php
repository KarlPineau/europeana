<?php

namespace NER\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity
 *
 * @ORM\Table(name="entity")
 * @ORM\Entity()
 */
class Entity
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
     * @ORM\ManyToOne(targetEntity="NER\ModelBundle\Entity\UploadFile")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $uploadFile;

    /**
     * @var string
     * @Assert\Url()
     * @ORM\Column(name="europeanaURI", type="string", length=255, nullable=true)
     */
    private $europeanaURI;

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
     * Set europeanaURI
     *
     * @param string $europeanaURI
     *
     * @return Entity
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
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return Entity
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
     * Set uploadFile
     *
     * @param \NER\ModelBundle\Entity\UploadFile $uploadFile
     *
     * @return Entity
     */
    public function setUploadFile(\NER\ModelBundle\Entity\UploadFile $uploadFile)
    {
        $this->uploadFile = $uploadFile;

        return $this;
    }

    /**
     * Get uploadFile
     *
     * @return \NER\ModelBundle\Entity\UploadFile
     */
    public function getUploadFile()
    {
        return $this->uploadFile;
    }
}
