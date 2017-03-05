<?php

namespace DSG\AdministrationBundle\Controller;

use DSG\HomeBundle\Service\CsvResponse;
use DSG\ModelBundle\Entity\EuropeanaItem;
use DSG\ModelBundle\Entity\EuropeanaItemsSession;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;

class InfoOnDatasetController extends Controller
{
    public function queryAction(Request $request)
    {
        set_time_limit(0);
        $form = $this->createFormBuilder()
            ->add('urlFile', UrlType::class, array('mapped' => false, 'required' => true))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $urlFile = $form->get('urlFile')->getData();

            $returnList = $this->process($urlFile);
        }

        $this->get('session')->getFlashBag()->add('notice', 'Step 2: use parametersOf1000.csv' );
        return $this->render('RSAdministrationBundle:Query:query.html.twig', array(
            'form' => $form->createView(),
            'next' => 'rs_administration_score_query',
            'previous' => 'rs_administration_current_query'));
    }

    public function process($urlFile)
    {
        set_time_limit(0);
        $languages = [];
        $countries = [];
        $providers = [];

        $row = 1;
        if (($handle = fopen($urlFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $row++;
                foreach ($data[$row] as $entity) {
                    $response = json_decode($this->get('Buzz')->get('http://sol1.eanadev.org:9191/solr/search_1_shard1_replica2/select?q=europeana_id="' . $entity . '"&wt=json&indent=true')->getContent());
                    $language = $response->response->docs[0]->europeana_aggregation_edm_language[0];
                    $country = $response->response->docs[0]->europeana_aggregation_edm_country[0];
                    $provider = $response->response->docs[0]->provider_aggregation_edm_dataProvider[0];

                    if (key_exists($language, $languages)) {
                        $languages[$language] = $languages[$language] + 1;
                    } else {
                        $languages[$language] = 1;
                    }
                    if (key_exists($country, $countries)) {
                        $countries[$country] = $countries[$country] + 1;
                    } else {
                        $countries[$country] = 1;
                    }
                    if (key_exists($provider, $providers)) {
                        $providers[$provider] = $providers[$provider] + 1;
                    } else {
                        $providers[$provider] = 1;
                    }
                }
            }
        }

        $returnList = ["languages" => $languages, "countries" => $countries, "providers" => $providers];
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        try {
            $fs->dumpFile('data/infoOnDatasetEvaluation.json', json_encode($returnList));
            return 'data/infoOnDatasetEvaluation.json';
        }
        catch(IOException $e) {}
    }
}
