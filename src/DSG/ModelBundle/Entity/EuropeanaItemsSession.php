<?php

namespace DSG\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EuropeanaItemsSession
 *
 * @ORM\Table(name="europeana_items_session")
 * @ORM\Entity(repositoryClass="DSG\ModelBundle\Repository\EuropeanaItemsSessionRepository")
 */
class EuropeanaItemsSession
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
     * @var int
     *
     * @ORM\Column(name="numberOfItems", type="integer", nullable=false)
     */
    private $numberOfItems;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="string", length=255, nullable=false)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="qf", type="string", length=255, nullable=true)
     */
    private $qf;

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
     * Set numberOfItems
     *
     * @param integer $numberOfItems
     *
     * @return EuropeanaItemsSession
     */
    public function setNumberOfItems($numberOfItems)
    {
        $this->numberOfItems = $numberOfItems;

        return $this;
    }

    /**
     * Get numberOfItems
     *
     * @return integer
     */
    public function getNumberOfItems()
    {
        return $this->numberOfItems;
    }

    /**
     * Set query
     *
     * @param string $query
     *
     * @return EuropeanaItemsSession
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set qf
     *
     * @param string $qf
     *
     * @return EuropeanaItemsSession
     */
    public function setQf($qf)
    {
        $this->qf = $qf;

        return $this;
    }

    /**
     * Get qf
     *
     * @return string
     */
    public function getQf()
    {
        return $this->qf;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     *
     * @return EuropeanaItemsSession
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
