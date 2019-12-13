<?php

namespace Synolia;

require (__DIR__.'/../vendor/autoload.php');

class ControllerContacts
{
    use Connector;

    /**
     * List 20 contacts max with first name starting with "a" or last name starting with "b"
     * @return array
     * @throws \Exception
     */
    public function list(): array
    {
        $httpQuery = http_build_query([
            "max_num" => 20,
            "offset" => 0,
            "fields" => "id,first_name,last_name,email",
            "order_by" => "date_entered",
            "favorites" => false,
            "my_items" => false,
        ]);
        $filter = '&filter[0][$or][0][first_name][$starts]=A&filter[0][$or][1][last_name][$starts]=Z';
        $uri = '/Contacts?'.$httpQuery.$filter;
        $response = $this->makeRequest('GET', $uri);

        return json_decode($response->getBody()->getContents())->records;
    }
}
