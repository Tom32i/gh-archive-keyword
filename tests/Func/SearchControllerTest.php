<?php

namespace App\Tests\Func;

use App\DataFixtures\EventFixtures;
use App\Entity\Event;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class SearchControllerTest extends WebTestCase
{
    protected AbstractDatabaseTool $databaseTool;
    protected EventRepository $eventRepository;
    private static $client;

    protected function setUp(): void
    {
        static::$client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->databaseTool->loadFixtures(
            [EventFixtures::class]
        );
    }

    public function testEmptySearch()
    {
        $client = static::$client;

        $client->request('GET', '/api/search');

        self::assertResponseStatusCodeSame(404);
        self::assertStringContainsString('No search provided.', $client->getResponse()->getContent());
    }

    public function testSearch()
    {
        $client = static::$client;

        $client->request('GET', '/api/search', [
            'date' => (new \DateTimeImmutable('2015-01-01 09:00:00'))->format('Y-m-d'),
            'keyword' => 'foo'
        ]);

        self::assertResponseStatusCodeSame(200);

        $expectedJson = <<<JSON
            {
               "meta":{
                  "totalEvents":1,
                  "totalPullRequests":0,
                  "totalCommits":0,
                  "totalComments":1
               },
               "data":{
                  "events":[
                     {
                        "id":1,
                        "type":"MSG",
                        "repo":{
                           "id":1,
                           "name":"yousign\/test",
                           "url":"https:\/\/api.github.com\/repos\/yousign\/backend-test"
                        },
                        "createAt":"2015-01-01T09:00:00+00:00",
                        "comment":"Test comment initiate by fixture "
                     }
                  ],
                  "stats":[
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":1 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 },
                     { "COM":0, "PR":0, "MSG":0 }
                  ]
               }
            }
            JSON;

        self::assertJsonStringEqualsJsonString($expectedJson, $client->getResponse()->getContent());
    }
}
