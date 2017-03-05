<?php

namespace DSG\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use DSG\ModelBundle\Entity\EuropeanaItem;
use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class EuropeanaAssessmentController extends Controller
{
    public function statisticsLangAction()
    {
        $response = $this->statisticsLang();
        return $this->render('DSGAdministrationBundle:EuropeanaAssessment:statisticsLang.html.twig', array(
            'langs' => $response[0],
            'langCountTotal' => $response[1],
            'countTotalEuropeana' => $response[2],
        ));
    }

    protected function statisticsLang()
    {
        /* -- COUNT GLOBAL EUROPEANA -- */
        $countTotalEuropeana = $this->get('dsg_model.dsgenerator')->queryCountEuropeana('*', null)[0];

        /* -- LANGUAGE NORMALISATION -- */
        $langs =
            [
                'bg' => ['language' => 'Bulgarian', 'iso' => ['bg', 'bul']],
                'ca' => ['language' => 'Catalan', 'iso' => ['ca']],
                'cs' => ['language' => 'Czech', 'iso' => ['cs', 'cz']],
                'da' => ['language' => 'Danish', 'iso' => ['da', 'danish']],
                'de' => ['language' => 'German', 'iso' => ['de', 'deu']],
                'el' => ['language' => 'Greek', 'iso' => ['el', 'gre', 'gr']],
                'en' => ['language' => 'English', 'iso' => ['en', 'eng', 'ENG', 'EN', 'English']],
                'es' => ['language' => 'Spanish', 'iso' => ['es']],
                'et' => ['language' => 'Estonian', 'iso' => ['et', 'est']],
                'fi' => ['language' => 'Finnish', 'iso' => ['fi', 'fin']],
                'fr' => ['language' => 'French', 'iso' => ['fr']],
                'hr' => ['language' => 'Croatian', 'iso' => ['hr']],
                'hu' => ['language' => 'Hungarian', 'iso' => ['hu', 'hun']],
                'it' => ['language' => 'Italian', 'iso' => ['it', 'IT', 'ita']],
                'is' => ['language' => 'Icelandic', 'iso' => ['is', 'IS']],
                'la' => ['language' => 'Latin', 'iso' => ['la']],
                'lt' => ['language' => 'Lithuanian', 'iso' => ['lt', 'LT']],
                'lv' => ['language' => 'Latvian', 'iso' => ['lv']],
                'nl' => ['language' => 'Dutch', 'iso' => ['nl', 'nld']],
                'no' => ['language' => 'Norwegian', 'iso' => ['no', 'NOR']],
                'pl' => ['language' => 'Polish', 'iso' => ['pl', 'pol', 'PL']],
                'pt' => ['language' => 'Portuguese', 'iso' => ['pt']],
                'ro' => ['language' => 'Romanian', 'iso' => ['ro']],
                'ru' => ['language' => 'Russian', 'iso' => ['ru', 'rus']],
                'se' => ['language' => 'Northern Sami', 'iso' => ['se']],
                'si' => ['language' => 'Sinhalese', 'iso' => ['SI']],
                'sk' => ['language' => 'Slovak', 'iso' => ['sk']],
                'sl' => ['language' => 'Slovene', 'iso' => ['sl']],
                'sv' => ['language' => 'Swedish', 'iso' => ['sv', 'sw']]
            ];

        /* -- COUNT LANGUAGE -- */
        $langReturn = array();
        $countTotal = 0;
        foreach($langs as $lang => $langContainer) {
            $countForLang = 0;
            foreach($langContainer['iso'] as $langVariation) {
                $query = 'LANGUAGE:'.$langVariation;
                $queryFacet = null;
                $responseArray = $this->get('dsg_model.dsgenerator')->queryCountEuropeana($query, $queryFacet);
                $countForLang += $responseArray[0];
                $countTotal += $responseArray[0];
            }

            $langReturn[$lang] = $countForLang;
        }

        return [$langReturn, $countTotal, $countTotalEuropeana];
    }

    public function statisticsCountryAction()
    {
        $response = $this->statisticsCountry();

        return $this->render('DSGAdministrationBundle:EuropeanaAssessment:statisticsCountry.html.twig', array(
            'countTotalEuropeana' => $response[0],
            'countries' => $response[1],
            'countryCountTotal' => $response[2],
        ));
    }

    protected function statisticsCountry()
    {
        /* -- COUNT GLOBAL EUROPEANA -- */
        $countTotalEuropeana = $this->get('dsg_model.dsgenerator')->queryCountEuropeana('*', null)[0];

        $countries = array("Albania", "Andorra", "Austria", "Belgium", "Bulgaria", "Czech Republic", "Denmark", "Estonia", "Finland", "France", "Germany", "Greece", "Hungary", "Iceland", "Ireland", "Israel", "Italy", "Latvia", "Liechtenstein", "Lithuania", "Luxembourg", "Malta", "Monaco", "Netherlands", "Norway", "Poland", "Portugal", "Romania", "Russia", "Slovakia", "Slovenia", "Spain", "Sweden", "Switzerland", "Ukraine", "United Kingdom");

        /* -- COUNT COUNTRY -- */
        $countryReturn = array();
        $countryCountTotal = 0;
        foreach($countries as $country) {
            $query = 'COUNTRY:"'.strtolower($country).'"';
            $queryFacet = null;
            $responseArray = $this->get('dsg_model.dsgenerator')->queryCountEuropeana($query, $queryFacet);
            $countryCountTotal += $responseArray[0];
            $countryReturn[$country] = $responseArray[0];
        }

        return [$countTotalEuropeana, $countryReturn, $countryCountTotal];
    }

    public function statisticsTypeAction()
    {
        $response = $this->statisticsType();

        return $this->render('DSGAdministrationBundle:EuropeanaAssessment:statisticsType.html.twig', array(
            'countTotalEuropeana' => $response[0],
            'types' => $response[1],
            'typeCountTotal' => $response[2],
        ));
    }

    protected function statisticsType()
    {
        /* -- COUNT GLOBAL EUROPEANA -- */
        $countTotalEuropeana = $this->get('dsg_model.dsgenerator')->queryCountEuropeana('*', null)[0];

        $types = array("IMAGE", "TEXT", "SOUND", "VIDEO", "3D");

        /* -- COUNT COUNTRY -- */
        $typeReturn = array();
        $typeCountTotal = 0;
        foreach($types as $type) {
            $query = 'TYPE:"'.strtoupper($type).'"';
            $queryFacet = null;
            $responseArray = $this->get('dsg_model.dsgenerator')->queryCountEuropeana($query, $queryFacet);
            $typeCountTotal += $responseArray[0];
            $typeReturn[$type] = $responseArray[0];
        }

        return [$countTotalEuropeana, $typeReturn, $typeCountTotal];
    }

    public function statisticsCompletenessAction()
    {
        $response = $this->statisticsCompleteness();

        return $this->render('DSGAdministrationBundle:EuropeanaAssessment:statisticsCompleteness.html.twig', array(
            'countTotalEuropeana' => $response[0],
            'completenesses' => $response[1],
            'completenessCountTotal' => $response[2],
        ));
    }

    protected function statisticsCompleteness()
    {
        /* -- COUNT GLOBAL EUROPEANA -- */
        $countTotalEuropeana = $this->get('dsg_model.dsgenerator')->queryCountEuropeana('*', null)[0];

        $completenesses = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

        /* -- COUNT COUNTRY -- */
        $completenessReturn = array();
        $completenessCountTotal = 0;
        foreach($completenesses as $completeness) {
            $query = 'COMPLETENESS:'.$completeness;
            $queryFacet = null;
            $responseArray = $this->get('dsg_model.dsgenerator')->queryCountEuropeana($query, $queryFacet);
            $completenessCountTotal += $responseArray[0];
            $completenessReturn[$completeness] = $responseArray[0];
        }

        return [$countTotalEuropeana, $completenessReturn, $completenessCountTotal];
    }

    public function statisticsDatasetAction()
    {
        $response = $this->statisticsDataset();

        return $this->render('DSGAdministrationBundle:EuropeanaAssessment:statisticsDataset.html.twig', array(
            'countTotalEuropeana' => $response[0],
            'datasets' => $response[1],
            'datasetCountTotal' => $response[2]
        ));
    }

    protected function statisticsDataset()
    {
        //http://www.europeana.eu/api/v2/search.json?wskey=api2demo&query=*&profile=facets&facet=edm_datasetName&f.edm_datasetName.facet.limit=200000&rows=0
        $response = json_decode($this->get('Buzz')->get('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=*%3A*&wt=json&rows=0&indent=true&facet=true&facet.field=edm_datasetName&f.edm_datasetName.facet.limit=200000')->getContent());
        $datasets = $response->facet_counts->facet_fields->edm_datasetName;
        $countTotalEuropeana = $response->response->numFound;

        /* -- COUNT COUNTRY -- */
        $datasetReturn = array();
        foreach($datasets as $key => $dataset) {
            if($key % 2 == 0) {
                $datasetReturn[$dataset] = 0;
            } else {
                $keyEnd = key( array_slice( $datasetReturn, -1, 1, TRUE ) );
                $datasetReturn[$keyEnd] = $dataset;
            }
        }

        $datasetCountTotal = count($datasetReturn);

        return [$countTotalEuropeana, $datasetReturn, $datasetCountTotal];
    }

    public function processAction()
    {
        set_time_limit(0);
        $statisticsDataset = $this->statisticsDataset(); //[$langReturn, $countTotal, $countTotalEuropeana]

        $list = array();
        foreach($statisticsDataset[1] as $dataset => $count)
        {
            $rows = round(($count*2000)/$statisticsDataset[0]);

            if($rows > 0) {
                $range  = $count / $rows;
                for($i = 0; $i < $rows ; $i++) {
                    $cursor = $range*$i;
                    $list[] = ['http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=edm_datasetName:'.urlencode($dataset).'&fl=europeana_id&rows=1&start='.urlencode(intval($cursor)).'&wt=json&sort='.urlencode('europeana_id asc')];
                }

            } elseif( $count > 5000) {
                $list[] = ['http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=edm_datasetName:'.urlencode($dataset).'&fl=europeana_id&rows=1&wt=json&start=0&sort='.urlencode('europeana_id asc')];
            }
        }

        $response = new CsvResponse($list, 200, explode(', ', 'queries'));
        $response->setFilename("queries.csv");
        return $response;
    }
}
