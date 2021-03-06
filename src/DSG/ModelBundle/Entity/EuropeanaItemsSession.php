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
     * @var string
     * @Assert\Email()
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sentConfirmation", type="boolean", nullable=true)
     */
    private $sentConfirmation;

    /**
     * @var int
     *
     * @ORM\Column(name="numberOfItems", type="integer", nullable=true)
     */
    private $numberOfItems;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="string", length=255, nullable=true)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="qf", type="string", length=255, nullable=true)
     */
    private $qf;

    /**
     * @var string
     *
     * @ORM\Column(name="queryCursor", type="string", length=255, nullable=true)
     */
    private $queryCursor;

    /**
     * @var int
     *
     * @ORM\Column(name="start", type="integer", nullable=true)
     */
    private $start;

    /**
     * @var int
     *
     * @ORM\Column(name="dispatcher", type="integer", nullable=true)
     */
    private $dispatcher;

    /**
     * @var int
     *
     * @ORM\Column(name="resultsNumber", type="integer", nullable=true)
     */
    private $resultsNumber;

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

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EuropeanaItemsSession
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set sentConfirmation
     *
     * @param boolean $sentConfirmation
     *
     * @return EuropeanaItemsSession
     */
    public function setSentConfirmation($sentConfirmation)
    {
        $this->sentConfirmation = $sentConfirmation;

        return $this;
    }

    /**
     * Get sentConfirmation
     *
     * @return boolean
     */
    public function getSentConfirmation()
    {
        return $this->sentConfirmation;
    }

    /**
     * Set resultsNumber
     *
     * @param integer $resultsNumber
     *
     * @return EuropeanaItemsSession
     */
    public function setResultsNumber($resultsNumber)
    {
        $this->resultsNumber = $resultsNumber;

        return $this;
    }

    /**
     * Get resultsNumber
     *
     * @return integer
     */
    public function getResultsNumber()
    {
        return $this->resultsNumber;
    }

    /**
     * Set queryCursor
     *
     * @param string $queryCursor
     *
     * @return EuropeanaItemsSession
     */
    public function setQueryCursor($queryCursor)
    {
        $this->queryCursor = $queryCursor;

        return $this;
    }

    /**
     * Get queryCursor
     *
     * @return string
     */
    public function getQueryCursor()
    {
        return $this->queryCursor;
    }

    /**
     * Set start
     *
     * @param integer $start
     *
     * @return EuropeanaItemsSession
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return integer
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set dispatcher
     *
     * @param integer $dispatcher
     *
     * @return EuropeanaItemsSession
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Get dispatcher
     *
     * @return integer
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
}
