<?php

namespace NER\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UploadFile
 *
 * @ORM\Table(name="upload_file")
 * @ORM\Entity(repositoryClass="NER\ModelBundle\Repository\UploadFileRepository")
 */
class UploadFile
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
     * @ORM\Column(name="urlFile", type="string", length=255, nullable=true)
     */
    private $urlFile;

    /**
     * @var string
     * 
     * @ORM\Column(name="fields", type="string", length=255, nullable=true)
     */
    private $fields;

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
     * Set urlFile
     *
     * @param string $urlFile
     *
     * @return UploadFile
     */
    public function setUrlFile($urlFile)
    {
        $this->urlFile = $urlFile;

        return $this;
    }

    /**
     * Get urlFile
     *
     * @return string
     */
    public function getUrlFile()
    {
        return $this->urlFile;
    }

    /**
     * Set fields
     *
     * @param string $fields
     *
     * @return UploadFile
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get fields
     *
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }
}
