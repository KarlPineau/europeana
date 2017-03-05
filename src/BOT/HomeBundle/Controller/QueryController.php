<?php

namespace BOT\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class QueryController extends Controller
{
    public function indexAction($query)
    {
        $messages = [
            "messages" => [
                ["text" => "Oups... It seems there is an issue"],
                ["text" => "Try again later :)"]
            ]
        ];

        $elements = [];
        $data = array(
            'query' => $query,
            'reusability' => 'open',
            'profile' => 'rich',
            'rows' => 9,
            'wt' => 'json',
            'wskey' => 'api2demo');

        foreach(json_decode(@file_get_contents('https://www.europeana.eu/api/v2/search.json?'.http_build_query($data)))->items as $key => $item) {
            if($key > 8) {break;}
            
            $europeana_id = $item->id;

            if(isset($item->edmPreview)) {
                $image_url = $item->edmPreview[0];
            } elseif(isset($item->edmIsShownBy)) {
                $image_url = $item->edmIsShownBy[0];
            } else {
                $image_url = null;
            }

            if(isset($item->title)) {
                $title = $item->title[0];
            } else {
                $title = "Discover a new artwork in Europeana";
            }

            if(isset($item->dcDescription)) {
                $description = $item->dcDescription[0];
            } else {
                $description = "This is a cultural heritage object provided by the Europeana foundation";
            }
            
            $elements[] = 
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
                ];

        }
        
        if($elements > 0) {
            $elements[] = [
                "title" => "Discover more on Europeana",
                "image_url" => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Europeana_logo_black.svg/1280px-Europeana_logo_black.svg.png',
                "subtitle" => "The European digital library",
                "buttons" => [
                    [
                        "type" => "web_url",
                        "url" => "http://www.europeana.eu/portal/fr/search?q=".$query."&per_page=96",
                        "title" => "View all results"
                    ]
                ]
            ];

            $messages = [
                "messages" =>
                [
                    ["text" => "Let's see the top-results of your search:"],
                    [
                        "attachment" => [
                            "type" => "template",
                            "payload" => [
                                "template_type" => "generic",
                                "elements" => $elements
                                ]
                            ]
                    ],
                    [
                        "attachment" => [
                            "type" => "template",
                            "payload" => [
                                "template_type" => "button",
                                "text" => "Want to see more ?",
                                "buttons" => [
                                    [
                                        "type" => "web_url",
                                        "url" => "http://www.europeana.eu/portal/fr/search?q=".$query."&per_page=96",
                                        "title" => "See all results"
                                    ],
                                    [
                                        "type" => "show_block",
                                        "title" => "New search",
                                        "block_names" => ["2-Query-Home"]
                                    ],
                                    [
                                        "type" => "show_block",
                                        "title" => "Want something else",
                                        "block_names" => ["0-Choices-Browse"]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ];
        } else {
            $messages = [
                "messages" => [
                    ["text" => "Oups... There is no result for this query :("],
                    ["text" => "Try something else :)"]
                ]
            ];
        }

        $response = new Response(json_encode($messages));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}