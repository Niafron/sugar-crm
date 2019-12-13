<?php

namespace Synolia;

use Psr\Http\Message\ResponseInterface;

require (__DIR__.'/../vendor/autoload.php');

class ControllerCases
{
    use Connector;

    /**
     * List 5 cases max related to a contact identified with his id
     * @param string $contactId
     * @return array
     * @throws \Exception
     */
    public function getCases(string $contactId): array
    {
        $httpQuery = http_build_query([
            "max_num" => 5,
            "fields" => "id,name,account_name,account_id",
        ]);
        $uri = '/Contacts/'.$contactId.'/link/cases?'.$httpQuery;
        $response = $this->makeRequest('GET', $uri);

        //var_dump($response->getBody()->getContents());

        return json_decode($response->getBody()->getContents())->records;
    }

    /**
     * Create a new case for a contact identified with his id. Warning : there is no duplicateCheck!
     */
    public function createCase(string $contactId, string $accountId, string $description, string $name, \DateTime $date = null, string $priority = 'P2'): ResponseInterface
    {
        $date = ($date === null) ? new \DateTime() : $date;

        $newCase = [
            'deleted' => false,
            'portal_viewable' => false,
            'account_id' => $accountId,
            'assigned_user_id' => '1',
            'tag' => [],
            'priority' => $priority,
            'type' => 'Administration',
            'source' => 'php-rest-api-test',
            'status' => 'New',
            'team_name' => [
                [
                    'id' => '1',
                    'display_name' => 'Global',
                    'name' => 'Global',
                    'name_2' => '',
                    'primary' => true,
                    'selected' => false,
                ]
            ],
            'follow_up_datetime' => $date->format('c'),
            'description' => $description,
            'name' => $name,
        ];

        $uri = '/Contacts/'.$contactId.'/link/cases';

        return $this->makeRequest('POST', $uri, $newCase);
    }
}
