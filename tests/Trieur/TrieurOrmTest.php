<?php

namespace Polinome\Trieur\Tests\Trieur;

use PHPUnit\Framework\TestCase;
use Polinome\Trieur\Tests\TestCase\InitDatabaseTrait;
use Polinome\Trieur\Trieur;

class TrieurOrmTest extends TestCase
{
    use InitDatabaseTrait;

    private readonly Trieur $trieur;

    protected function setUp(): void
    {
        $this->setUpDatabase();
    }

    public function testFetchCsv(): void
    {
        $trieurConfig = require __DIR__.'/../config/customer-orm-csv.php';
        $this->trieur = new Trieur($trieurConfig, $this->entityManager);

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
        $trieurConfig = require __DIR__.'/../config/customer-orm-datatables.php';
        $this->trieur = new Trieur($trieurConfig, $this->entityManager);

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
                    'gender' => 'MALE',
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
        $trieurConfig = require __DIR__.'/../config/customer-orm-datatables.php';
        $this->trieur = new Trieur($trieurConfig, $this->entityManager);

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
                    'gender' => 'MALE',
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

        $this->assertEquals([
            'processing' => true,
            'serverSide' => true,
            'ajax' => [
                'url' => null,
                'type' => null,
            ],
            'columns' => [
                [
                    'orderable' => true,
                    'searchable' => true,
                    'data' => 'id',
                    'name' => 'id',
                    'title' => 'ID',
                ],
                [
                    'orderable' => true,
                    'searchable' => true,
                    'data' => 'firstname',
                    'name' => 'firstname',
                    'title' => 'Prénom',
                ],
                [
                    'orderable' => true,
                    'searchable' => true,
                    'data' => 'lastname',
                    'name' => 'lastname',
                    'title' => 'Nom',
                ],
                [
                    'orderable' => true,
                    'searchable' => true,
                    'data' => 'email',
                    'name' => 'email',
                    'title' => 'Email',
                ],
                [
                    'orderable' => true,
                    'searchable' => true,
                    'data' => 'gender',
                    'name' => 'gender',
                    'title' => 'Genre',
                ],
            ],
            'language' => [
                'emptyTable' => 'Aucun client trouvé',
                'info' => 'clients _START_ à  _END_ sur _TOTAL_ clients',
                'infoEmpty' => 'Aucun client',
                'infoFiltered' => '(filtre sur _MAX_ clients)',
                'lengthMenu' => 'Montrer _MENU_ clients par page',
                'paginate' => [
                    'first' => 'première page',
                    'last' => 'dernière page',
                    'next' => 'page suivante',
                    'previous' => 'page précédente',
                ],
                'processing' => 'Chargement',
                'search' => 'Recherche',
                'searchPlaceholder' => 'Recherche',
                'thousands' => '&nbsp;',
                'zeroRecords' => 'Aucun client',

            ],
        ], $this->trieur->getDriver()->getJsConfig());
        $this->assertEquals([
            [
                'html' => 'input',
                'type' => 'number',
            ],
            [
                'html' => 'input',
                'type' => 'text',
            ],
            [
                'html' => 'input',
                'type' => 'text',
            ],
            [
                'html' => 'input',
                'type' => 'text',
            ],
            [
                'html' => 'select',
                'values' => [
                    [
                        'value' => 'MAN',
                        'label' => 'Homme',
                    ],
                    [
                        'value' => 'WOMAN',
                        'label' => 'Femme',
                    ],
                    [
                        'value' => 'UNKNOWN',
                        'label' => 'Inconnu',
                    ]
                ],
            ],
        ], $this->trieur->getDriver()->getColumnFilterConfig());
    }

    public function testFetchDatatablesWithColumnTermNoResult(): void
    {
        $trieurConfig = require __DIR__.'/../config/customer-orm-datatables.php';
        $this->trieur = new Trieur($trieurConfig, $this->entityManager);

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
