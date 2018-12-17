<?php
use Elasticsearch\ClientBuilder;

require '../vendor/autoload.php';
$client = ClientBuilder::create()->setHosts(['http://127.0.0.1:8088'])
    ->build();
$now = time();
$params = [
   'index' => 'nginx_access',
   'type' => 'doc',
   'body' => [
       "query" => [
           "bool" => [
               "must" => [
                   [
                       "range" => [
                           "timestamp" => [
                               "gte" => strtotime(date('Y-m-d'.'00:00:00',time())) * 1000,
                               "lt" => strtotime(date('Y-m-d'.'00:00:00',time() + 3600 * 24)) * 1000,
                               "format" => "epoch_millis"
                           ]
                       ]
                   ],
                   [
                       "match" => [
                           "response" => "200"
                       ]
                   ]
               ]
           ]
       ],
       'size' => 10,
       'sort' => ['request_time' => 'desc']
   ],
];

$params = [
    'index' => 'nginx_access',
    'type' => 'doc',
    'body' => [
        "size" => 0,
        "aggs" => [
            "ips" => [
                "terms" => [
                    "field" => "http_x_forwarded_for.keyword",
                    "order" => ["_term" => "desc"],
                ]
            ],
        ],
        "query" => [
            "bool" => [
/*                "must_not" => [
                    [
                        "match_phrase" => [
                            "http_x_forwarded_for.keyword" => [
                                "query" => "118.178.191.9"
                            ]
                        ]
                    ]
                ],*/
                "must" => [
                    [
                        "range" => [
                            "timestamp" => [
                                "gte" => ($now - 3600) * 1000,
                                "lt" => $now * 1000,
                                "format" => "epoch_millis"
                            ]
                        ]
                    ]
                ]
            ],
        ]
    ]
];

$response = $client->search($params);