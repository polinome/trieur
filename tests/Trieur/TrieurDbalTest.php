<?php

namespace Polinome\Trieur\Tests\Trieur;

use PHPUnit\Framework\TestCase;
use Polinome\Trieur\Tests\TestCase\InitDatabaseTrait;
use Polinome\Trieur\Trieur;

class TrieurDbalTest extends TestCase
{
    use InitDatabaseTrait;

    private readonly Trieur $trieur;

    protected function setUp(): void
    {
        $this->setUpDatabase();
    }

    public function testFetchCsv(): void
    {
        $trieurConfig = require __DIR__.'/../config/customer-dbal-csv.php';
        $this->trieur = new Trieur($trieurConfig, $this->connection);

        /* @var resource $result */
        $result = $this->trieur->getResponse();

        $content = stream_get_contents($result);
        fclose($result);

        $this->assertEquals(<<<'CSV'
            1,John,Doe,john.doe@trieur.com
            2,Jane,Doe,jane.doe@trieur.com
            
            CSV, $content);
    }

    public function testFetchDatatablesWithTerm(): void
    {
        $trieurConfig = require __DIR__.'/../config/customer-dbal-datatables.php';
        $this->trieur = new Trieur($trieurConfig, $this->connection);

        /* @var array $result */
        $this->trieur->getDriver()->setRequest([
            'length' => 10,
            'start' => 0,
            'search' => [
                'value' => 'john',
                'regex' => false,
            ],
            'columns' => [
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                ],
            ],
        ]);
        $result = $this->trieur->getResponse();

        $this->assertEquals([
            'data' => [
                [
                    'id' => '1',
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john.doe@trieur.com',
                ],
                /*[
                    'id' => '2',
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'email' => 'jane.doe@trieur.com',
                ],*/
            ],
            'recordsTotal' => 2,
            'recordsFiltered' => 1,
        ], $result);
    }

    public function testFetchDatatablesWithColumnTermHappy(): void
    {
        $trieurConfig = require __DIR__.'/../config/customer-dbal-datatables.php';
        $this->trieur = new Trieur($trieurConfig, $this->connection);

        /* @var array $result */
        $this->trieur->getDriver()->setRequest([
            'length' => 10,
            'start' => 0,
            'search' => [],
            'columns' => [
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                    'search' => [
                        'value' => 'john',
                        'regex' => false,
                    ],
                ],
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                ],
            ],
        ]);
        $result = $this->trieur->getResponse();

        $this->assertEquals([
            'data' => [
                [
                    'id' => '1',
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john.doe@trieur.com',
                ],
                /*[
                    'id' => '2',
                    'firstname' => 'Jane',
                    'lastname' => 'Doe',
                    'email' => 'jane.doe@trieur.com',
                ],*/
            ],
            'recordsTotal' => 2,
            'recordsFiltered' => 1,
        ], $result);
    }

    public function testFetchDatatablesWithColumnTermNoResult(): void
    {
        $trieurConfig = require __DIR__.'/../config/customer-dbal-datatables.php';
        $this->trieur = new Trieur($trieurConfig, $this->connection);

        /* @var array $result */
        $this->trieur->getDriver()->setRequest([
            'length' => 10,
            'start' => 0,
            'search' => [],
            'columns' => [
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                ],
                [
                    'searchable' => true,
                    'search' => [
                        'value' => 'Tintin',
                        'regex' => false,
                    ],
                ],
                [
                    'searchable' => true,
                ],
            ],
        ]);
        $result = $this->trieur->getResponse();

        $this->assertEquals([
            'data' => [],
            'recordsTotal' => 2,
            'recordsFiltered' => 0,
        ], $result);
    }
}
