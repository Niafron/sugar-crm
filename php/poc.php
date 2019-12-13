<?php

namespace Synolia;

require (__DIR__.'/../vendor/autoload.php');

//first of all, let's init the controllers
$controllerContacts = new ControllerContacts();
$controllerCases = new ControllerCases();

//get some contacts from the API and dump them
$contactsList = $controllerContacts->list();
foreach ($contactsList as $key => $data) {
    dumpRecords($key, [$data->id, $data->first_name, $data->last_name, $data->email[0]->email_address]);
}

//for the first contact of the previous list, retrieve 5 cases max
$firstContactId = $contactsList[0]->id;
$cases = $controllerCases->getCases($firstContactId);
foreach ($cases as $key => $case) {
    dumpRecords($key, [$case->id, $case->name, $case->account_name, $case->account_id]);
}

//we retrieve the first accountId of the previous list
$accountId = $cases[0]->account_id;

//uncomment the lines below if you want to create a new case for this contact and this account
/*
$description = 'Zombie ipsum reversus ab viral inferno, nam rick grimes malum cerebro. De carne lumbering animata corpora quaeritis. Summus brains sit​​, morbo vel maleficia? De apocalypsi gorger omero undead survivor dictum mauris.';
$name = 'Foo';
$response = $controllerCases->createCase($firstContactId, $accountId, $description, $name);
echo PHP_EOL;
echo PHP_EOL;
var_dump($response->getStatusCode());
echo PHP_EOL;
*/

function dumpRecords($key, $values)
{
    echo PHP_EOL;
    echo PHP_EOL;
    echo "------->$key";
    echo PHP_EOL;
    foreach ($values as $value) {
        var_dump($value);
    }
}

echo PHP_EOL;
echo PHP_EOL;
echo "====END OF THE PHP POC====";
echo PHP_EOL;
echo PHP_EOL;
