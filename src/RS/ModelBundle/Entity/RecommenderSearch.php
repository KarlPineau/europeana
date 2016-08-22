<?php

namespace RS\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RecommenderSearch
 *
 * @ORM\Table(name="recommender_search")
 * @ORM\Entity(repositoryClass="RS\ModelBundle\Repository\RecommenderSearchRepository")
 */
class RecommenderSearch
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
     * @ORM\Column(name="item", type="string", length=255)
     */
    private $item;

    /**
     * @ORM\OneToOne(targetEntity="RS\ModelBundle\Entity\RecommenderParameters", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $recommenderParameters;


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
     * Set item
     *
     * @param string $item
     *
     * @return RecommenderSearch
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return string
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set recommenderParameters
     *
     * @param \RS\ModelBundle\Entity\RecommenderParameters $recommenderParameters
     *
     * @return RecommenderSearch
     */
    public function setRecommenderParameters(\RS\ModelBundle\Entity\RecommenderParameters $recommenderParameters)
    {
        $this->recommenderParameters = $recommenderParameters;

        return $this;
    }

    /**
     * Get recommenderParameters
     *
     * @return \RS\ModelBundle\Entity\RecommenderParameters
     */
    public function getRecommenderParameters()
    {
        return $this->recommenderParameters;
    }
}
