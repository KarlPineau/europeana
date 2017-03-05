<?php

namespace BOT\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BrowseController extends Controller
{
    public function indexAction($type)
    {
        $messages = [
            "messages" => [
                ["text" => "Oups ... It seems there is an issue"],
                ["text" => "Try again later :)"]
            ]
        ];

        $count = 0;
        while($count <= 10) {
            $queryResponse = $this->query($type);
            if ($queryResponse !== FALSE) {
                $content = json_decode($queryResponse);
                if (isset($content->items) AND count($content->items) > 0) {
                    $randItem = rand(0, count($content->items)-1);

                    $messages = $this->afterQuery($content, $randItem);
                    break;
                } else {
                    $count++;
                }
            }
        }

        $response = new Response(json_encode($messages));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function query($type)
    {
        $countries = array("Austria", "Belgium", "Bulgaria", "Czech Republic", "Denmark", "Estonia", "Finland", "France", "Germany", "Greece", "Hungary", "Iceland", "Ireland", "Israel", "Italy", "Latvia", "Lithuania", "Luxembourg", "Malta", "Netherlands", "Norway", "Poland", "Portugal", "Romania", "Russia", "Slovakia", "Slovenia", "Spain", "Sweden", "Switzerland", "Ukraine", "United Kingdom");
        $selectCountry = rand(0, (count($countries)-1));

        $art = 'qf=(DATA_PROVIDER:"Östasiatiska museet" NOT TYPE:TEXT) OR (DATA_PROVIDER:"Medelhavsmuseet") OR (DATA_PROVIDER:"Rijksmuseum") OR (europeana_collectionName: "91631_Ag_SE_SwedishNationalHeritage_shm_art") OR (DATA_PROVIDER:"Bibliothèque municipale de Lyon") OR (DATA_PROVIDER:"Museu Nacional d\'Art de Catalunya") OR (DATA_PROVIDER:"Victoria and Albert Museum") OR (DATA_PROVIDER:"Slovak national gallery") OR (DATA_PROVIDER:"Thyssen-Bornemisza Museum") OR (DATA_PROVIDER:"Museo Nacional del Prado") OR (DATA_PROVIDER:"Statens Museum for Kunst") OR (DATA_PROVIDER:"Hungarian University of Fine Arts, Budapest") OR (DATA_PROVIDER:"Hungarian National Museum") OR (DATA_PROVIDER:"Museum of Applied Arts, Budapest") OR (DATA_PROVIDER:"Szépművészeti Múzeum") OR (DATA_PROVIDER:"Museum of Fine Arts - Hungarian National Gallery, Budapest") OR (DATA_PROVIDER:"Schola Graphidis Art Collection. Hungarian University of Fine Arts - High School of Visual Arts, Budapest") OR (PROVIDER:"Ville de Bourg-en-Bresse") OR (DATA_PROVIDER:"Universitätsbibliothek Heidelberg") OR ((what:("fine art" OR "beaux arts" OR "bellas artes" OR "belle arti" OR "schone kunsten" OR konst OR "bildende kunst" OR "Opere d\'arte visiva" OR "decorative arts" OR konsthantverk OR "arts décoratifs" OR paintings OR schilderij OR pintura OR peinture OR dipinto OR malerei OR måleri OR målning OR sculpture OR skulptur OR sculptuur OR beeldhouwwerk OR drawing OR poster OR tapestry OR gobelin OR jewellery OR miniature OR prints OR träsnitt OR holzschnitt OR woodcut OR lithography OR chiaroscuro OR "old master print" OR estampe OR porcelain OR mannerism OR rococo OR impressionism OR expressionism OR romanticism OR "Neo-Classicism" OR "Pre-Raphaelite" OR Symbolism OR Surrealism OR Cubism OR "Art Deco" OR "Art Déco" OR Dadaism OR "De Stijl" OR "Pop Art" OR "art nouveau" OR "art history" OR "http://vocab.getty.edu/aat/300041273" OR "histoire de l\'art" OR kunstgeschichte OR "estudio de la historia del arte" OR Kunstgeschiedenis OR "illuminated manuscript" OR buchmalerei OR enluminure OR "manuscrito illustrado" OR "manoscritto miniato" OR boekverluchting OR kalligrafi OR calligraphy OR exlibris)) AND (provider_aggregation_edm_isShownBy:*)) NOT (what: "printed serial" OR what:"printedbook" OR "printing paper" OR "printed music" OR DATA_PROVIDER:"NALIS Foundation" OR DATA_PROVIDER:"Ministère de la culture et de la communication, Musées de France" OR DATA_PROVIDER:"CER.ES: Red Digital de Colecciones de museos de España" OR PROVIDER:"OpenUp!" OR PROVIDER:"BHL Europe" OR PROVIDER:"EFG - The European Film Gateway" OR DATA_PROVIDER: "Malta Aviation Museum Foundation" OR DATA_PROVIDER:"National Széchényi Library - Digital Archive of Pictures" OR PROVIDER:"Swiss National Library")';
        $music = 'qf=(PROVIDER:"Europeana Sounds" AND provider_aggregation_edm_isShownBy:* AND music) OR (DATA_PROVIDER: "National Library of France" AND musique) OR (DATA_PROVIDER:"Sächsische Landesbibliothek - Staats- und Universitätsbibliothek Dresden" AND TYPE:SOUND) OR (edm_datasetName:" 09301_Ag_EU_Judaica_mcy78") OR (DATA_PROVIDER:"Kirsten Flagstadmuseet") OR (DATA_PROVIDER:"Ringve Musikkmuseum") OR (DATA_PROVIDER:"Netherlands Institute for Sound and Vision" AND provider_aggregation_edm_isShownBy:* AND (music OR muziek)) OR  (DATA_PROVIDER:"TV3 Televisió de Catalunya (TVC)" AND provider_aggregation_edm_isShownBy:* AND musica) OR (PROVIDER:"Institut National de l\'Audiovisuel" AND (musique OR opera OR pop OR rock OR concert OR chanson OR interpretation)) OR ((what:(music OR musique OR musik OR musica OR musicales OR "zenés előadás" OR "notated music" OR "folk songs" OR jazz OR "sheet music" OR score OR "musical instrument" OR partitur OR partituras OR gradual OR libretto OR oper OR concerto OR symphony OR sonata OR fugue OR motet OR saltarello OR organum OR ballade OR chanson OR laude OR madrigal OR pavane OR toccata OR cantata OR minuet OR partita OR sarabande OR sinfonia OR hymnes OR lied OR "music hall" OR quartet OR quintet OR requiem OR rhapsody OR scherzo OR "sinfonia concertante" OR waltz OR ballet OR zanger OR sangerin OR chanteur OR chanteuse OR cantante OR composer OR compositeur OR orchestra OR orchester OR orkester OR orchestre OR concierto OR konsert OR konzert OR koncert OR gramophone OR "record player" OR phonograph OR fonograaf OR fonograf OR grammofon OR skivspelare OR "wax cylinder" OR jukebox OR "cassette deck" OR "cassette player")) AND (provider_aggregation_edm_isShownBy:*)) OR ("gieddes samling") OR (musik AND DATA_PROVIDER:"Universitätsbibliothek Heidelberg") OR (antiphonal AND DATA_PROVIDER:"Bodleian Libraries, University of Oxford") OR (edm_datasetName:"2059208_Ag_EU_eSOUNDS_1020_CNRS-CREM") OR (title:(gradual OR antiphonal) AND edm_datasetName: "2021003_Ag_FI_NDL_fragmenta") NOT (DATA_PROVIDER:"Progetto ArtPast- CulturaItalia" OR DATA_PROVIDER:"Internet Culturale" OR DATA_PROVIDER:"Accademia Nazionale di Santa Cecilia" OR DATA_PROVIDER:"Regione Umbria" OR DATA_PROVIDER:"Regione Emilia Romagna" OR DATA_PROVIDER:"Regione Lombardia" OR DATA_PROVIDER:"Regione Piemonte" OR DATA_PROVIDER:"National Széchényi Library - Hungarian Electronic Library" OR DATA_PROVIDER:"Rijksdienst voor het Cultureel Erfgoed" OR DATA_PROVIDER:"Phonogrammarchiv - Österreichische Akademie der Wissenschaften; Austria" OR DATA_PROVIDER:"Ministère de la culture et de la communication, Musées de France" OR DATA_PROVIDER:"CER.ES: Red Digital de Colecciones de museos de España" OR DATA_PROVIDER:"MuseiD-Italia" OR DATA_PROVIDER:"Narodna biblioteka Srbije - National Library of Serbia" OR DATA_PROVIDER:"National and University Library in Zagreb" OR DATA_PROVIDER:"National Széchényi Library - Digital Archive of Pictures" OR DATA_PROVIDER:"Vast-Lab" OR DATA_PROVIDER:"Herzog August Bibliothek Wolfenbüttel" OR DATA_PROVIDER:"Centro de Documentación de FUNDACIÓN MAPFRE" OR PROVIDER:"OpenUp!" OR edm_datasetName:"9200123_Ag_EU_TEL_a1023_Sibiu" OR edm_datasetName:"2048319_Ag_EU_ApeX_NLHaNA" OR edm_datasetName:"2059202_Ag_EU_eSOUNDS_1004_Rundfunk" OR edm_datasetName:"09335_Ag_EU_Judaica_cfmj4" OR edm_datasetName:"09326_Ag_EU_Judaica_cfmj3" OR what:"opere d\'arte visiva" OR what:"operating rooms" OR what:"operating systems" OR what:"co-operation" OR what:operation)';

        $data = array(
            'query' => '*',
            'qf' => array(),
            'reusability' => 'open',
            'profile' => 'rich',
            'rows' => 300,
            'wt' => 'json',
            'wskey' => 'api2demo');

        if(strtoupper($type) == 'IMAGE') {
            return @file_get_contents('https://www.europeana.eu/api/v2/search.json?thumbnail=true&qf=TYPE:'.strtoupper($type).'&qf=COUNTRY:'.strtolower($countries[$selectCountry]).'&'.http_build_query($data));
        } elseif(strtoupper($type) == 'TEXT') {
            return @file_get_contents('https://www.europeana.eu/api/v2/search.json?qf=TEXT_FULLTEXT:true&qf=TYPE:'.strtoupper($type).'&qf=COUNTRY:'.strtolower($countries[$selectCountry]).'&'.http_build_query($data));
        } elseif(strtoupper($type) == 'ART') {
            return @file_get_contents('https://www.europeana.eu/api/v2/search.json?'.urlencode($art).'&qf=COUNTRY:'.strtolower($countries[$selectCountry]).'&'.http_build_query($data));
        } elseif(strtoupper($type) == 'MUSIC') {
            return @file_get_contents('https://www.europeana.eu/api/v2/search.json?'.urlencode($music).'&qf=COUNTRY:'.strtolower($countries[$selectCountry]).'&qf=SOUND_DURATION:very_short&qf=SOUND_DURATION:short&qf=SOUND_DURATION:medium&qf=SOUND_DURATION:long&qf=SOUND_HQ:true&'.http_build_query($data));
        }
    }

    public function afterQuery($content, $randItem)
    {
        $europeana_id = $content->items[$randItem]->id;

        if(isset($content->items[$randItem]->edmPreview)) {
            $image_url = $content->items[$randItem]->edmPreview[0];
        } elseif(isset($content->items[$randItem]->edmIsShownBy)) {
            $image_url = $content->items[$randItem]->edmIsShownBy[0];
        } else {
            $image_url = null;
        }

        if(isset($content->items[$randItem]->title)) {
            $title = $content->items[$randItem]->title[0];
        } else {
            $title = "Discover a new artwork in Europeana";
        }

        if(isset($content->items[$randItem]->dcDescription)) {
            $description = $content->items[$randItem]->dcDescription[0];
        } else {
            $description = "This is a cultural heritage object provided by the Europeana foundation";
        }

        $messages = [
            "messages" => [
                [
                    "text" => "Here, ".$title
                ]
            ]
        ];
        if(isset($content->items[$randItem]->proxies[0]->dcCreator)) {
            $messages["messages"][] =
                [
                    "text" => "Created by ".implode(', ', $content->items[$randItem]->proxies[0]->dcCreator)
                ];
        }
        $messages["messages"][] =
            [
                "text" => "A document from ".ucfirst(implode(', ', $content->items[$randItem]->country))
            ];

        if(isset($content->items[$randItem]->aggregations[0]->edmDataProvider)) {
            $messages["messages"][] =
                [
                    "text" => "Created by ".ucfirst(implode(', ', $content->items[$randItem]->aggregations[0]->edmDataProvider))
                ];
        } else {
            $messages["messages"][] =
                [
                    "text" => "Provided by " .ucfirst(implode(', ', $content->items[$randItem]->provider))
                ];
        }

        $messages["messages"][] =
            [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "generic",
                        "elements" => [
                            [
                                "title" => $title,
                                "image_url" => $image_url,
                                "subtitle" => $description,
                                "buttons" => [
                                    [
                                        "type" => "web_url",
                                        "url" => "http://europeana.eu/portal/record".$europeana_id.".html",
                                        "title" => "View on Europeana"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        $messages["messages"][] =
            [
                "text" =>  "Continue browsing ?",
                "quick_replies" => [
                    [
                        "set_attributes" =>
                        [
                            "europeana_id_first" => preg_replace('/\/(\S*)\/(\S*)/', '$1', $europeana_id),
                            "europeana_id_second" => preg_replace('/\/(\S*)\/(\S*)/', '$2', $europeana_id),
                        ],
                        "title" => "Continue",
                        "block_names" => ["1-Browse-similarItems"]
                    ],
                    [
                        "title" => "Want something else",
                        "block_names" => ["0-Choices-Browse"]
                    ]
                ]
            ];

        return $messages;
    }
}