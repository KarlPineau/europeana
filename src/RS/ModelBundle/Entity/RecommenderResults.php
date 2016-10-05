<?php

namespace RS\ModelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecommenderResults
 *
 * @ORM\Table(name="recommender_results")
 * @ORM\Entity(repositoryClass="RS\ModelBundle\Repository\RecommenderResultsRepository")
 */
class RecommenderResults
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
     * @var \stdClass
     *
     * @ORM\Column(name="recommenderSearch", type="object", nullable=true)
     */
    private $recommenderSearch;

    /**
     * @var string
     *
     * @ORM\Column(name="item", type="string", length=255, nullable=true)
     */
    private $item;

    /**
     * @var array
     *
     * @ORM\Column(name="results", type="array", nullable=true)
     */
    private $results;


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
     * Set recommenderSearch
     *
     * @param \stdClass $recommenderSearch
     *
     * @return RecommenderResults
     */
    public function setRecommenderSearch($recommenderSearch)
    {
        $this->recommenderSearch = $recommenderSearch;

        return $this;
    }

    /**
     * Get recommenderSearch
     *
     * @return \stdClass
     */
    public function getRecommenderSearch()
    {
        return $this->recommenderSearch;
    }

    /**
     * Set results
     *
     * @param array $results
     *
     * @return RecommenderResults
     */
    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Get results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set item
     *
     * @param string $item
     *
     * @return RecommenderResults
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
}
