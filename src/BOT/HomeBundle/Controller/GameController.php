<?php

namespace BOT\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    public function indexAction()
    {
        $messages = [
            "messages" => [
                ["text" => "Oups ... It seems there is an issue"],
                ["text" => "Try again later :)"]
            ]
        ];

        $count = 0;
        while($count <= 10) {
            $queryResponse = $this->query();
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

    public function query()
    {
        $data = array(
            'query' => 'PROVIDER:"Europeana 280"',
            'profile' => 'rich',
            'rows' => 500,
            'wt' => 'json',
            'wskey' => 'api2demo');

        return @file_get_contents('https://www.europeana.eu/api/v2/search.json?'.http_build_query($data));

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
            "set_variables" =>
            [
                "europeana_id_first" => preg_replace('/\/(\S*)\/(\S*)/', '$1', $europeana_id),
                "europeana_id_second" => preg_replace('/\/(\S*)\/(\S*)/', '$2', $europeana_id),
            ],
            "messages" => [
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
                ]
            ]
        ];

        $question = $this->setQuestion($content->items, $content->items[$randItem]);
        if($question != false) {
            $propositions = $question[0];
            $questionLabel = $question[1];
            $field = $question[2];

            $jsonProposition = array();
            foreach($propositions as $proposition) {
                $jsonProposition[] =
                    [
                        "title" => ucfirst($proposition),
                    ];
            }

            $messages['messages'][] =
            [
                "text" =>  "What is the ".$questionLabel." of this item ?",
                "quick_replies" => $jsonProposition
            ];
            $messages['set_variables']["field"] = $field;
        } else {
            $messages['messages'][] = ["text" => "Oups... There is an error"];
        }

        //array_unshift($messages, $content->items[$randItem]);

        return $messages;
    }

    public function setQuestion($items, $item)
    {
        $setCountry = false;
        $setYear = false;
        if(isset($item->country) AND isset($item->year)) {
            $randNb = rand(0,1);
            if($randNb == 0) { $setCountry = true;} elseif($randNb == 1) { $setYear = true; }
        }
        elseif(isset($item->country)) {$setCountry = true;}
        elseif(isset($item->year)) {$setYear = true;}

        if($setCountry == true) {
            $field = "country";
            $label = 'country';
        } elseif($setYear == true) {
            $field = "year";
            $label = 'year';
        } else { return false; }

        $count = 0;
        $setOfValues = array();
        $setOfValues[] = $item->{$field}[0];
        foreach($this->find($items, $field) as $key => $itemReturn) {
            if($count >= 2) {break;}
            if(!in_array($itemReturn->{$field}[0], $setOfValues)) {
                $setOfValues[] = $itemReturn->{$field}[0];
                $count ++;
            }
        }
        shuffle($setOfValues);
        return [$setOfValues, $label, $field];
    }

    public function find($items, $field)
    {
        $neededObject = array_filter(
            $items,
            function ($e) use (&$field){
                if(isset($e->{$field})) {
                    return $e->{$field} != null;
                }
            }
        );

        return $neededObject;
    }

    public function checkAction($value, $field, $europeana_id_first, $europeana_id_second)
    {
        $messages = [
            "messages" => [
                [
                    "text" => "Oups... It seems there is an issue.",
                    "quick_replies" => [
                        [
                            "title" => "Try again",
                            "block_names" => ["3-Game-Query"]
                        ],
                        [
                            "title" => "Want something else",
                            "block_names" => ["0-Choices-Browse"]
                        ]
                    ]
                ]
            ]
        ];

        $europeana_id = '/'.$europeana_id_first.'/'.$europeana_id_second;

        $data = array(
            'query' => 'europeana_id:"'.$europeana_id.'"',
            'profile' => 'rich',
            'rows' => 1,
            'wt' => 'json',
            'wskey' => 'api2demo');

        $queryResponse = @file_get_contents('https://www.europeana.eu/api/v2/search.json?'.http_build_query($data));

        if ($queryResponse !== FALSE) {
            $content = json_decode($queryResponse);
            if (isset($content->items[0])) {
                $item = $content->items[0];

               if(isset($item->{$field}) or array_key_exists($field, $item)) {
                    if(strtolower($item->{$field}[0]) == strtolower($value)) {
                        $messages = [
                            "messages" => [
                                [
                                    "text" => "This is the correct answer! :)",
                                    "quick_replies" => [
                                        [
                                            "title" => "Continue",
                                            "block_names" => ["3-Game-Query"]
                                        ],
                                        [
                                            "title" => "Want something else",
                                            "block_names" => ["0-Choices-Browse"]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    } else {
                        $messages = [
                            "messages" => [
                                ["text" => "Ho... This isn't the correct answer :("],
                                [
                                    "text" => "It was ".ucfirst($item->{$field}[0]).' :)',
                                    "quick_replies" => [
                                        [
                                            "title" => "Continue",
                                            "block_names" => ["3-Game-Query"]
                                        ],
                                        [
                                            "title" => "Want something else",
                                            "block_names" => ["0-Choices-Browse"]
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                } else {
                    $messages = [
                        "messages" => [
                            [
                                "text" => "Oups... It seems there is an issue.",
                                "quick_replies" => [
                                    [
                                        "title" => "Try again",
                                        "block_names" => ["3-Game-Query"]
                                    ],
                                    [
                                        "title" => "Want something else",
                                        "block_names" => ["0-Choices-Browse"]
                                    ]
                                ]
                            ]
                        ]
                    ];
                }
            }
        } else {
            $messages = [
                "messages" => [
                    [
                        "text" => "Oups... It seems there is an issue.",
                        "quick_replies" => [
                            [
                                "title" => "Try again",
                                "block_names" => ["3-Game-Query"]
                            ],
                            [
                                "title" => "Want something else",
                                "block_names" => ["0-Choices-Browse"]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $response = new Response(json_encode($messages));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}