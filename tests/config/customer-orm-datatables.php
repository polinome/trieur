<?php

use Polinome\Trieur\Driver\DataTablesDriver;
use Polinome\Trieur\FilterTypes;
use Polinome\Trieur\Source\DoctrineOrm\DoctrineOrm;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Exact;
use Polinome\Trieur\Tests\Context\Entity\Customer;

return [
    'source' => [
        'class' => DoctrineOrm::class,
        'config' => [
            'select' => [
                'c.id',
                'c.firstname',
                'c.lastname',
                'c.email',
                'c.gender',
            ],
            'from' => [
                'name' => Customer::class,
                'alias' => 'c',
            ],
            'group' => 'c.id',
        ],
    ],
    'driver' => [
        'class' => DataTablesDriver::class,
        'config' => [
            'itemName' => 'client',
            'itemsName' => 'clients',
        ],
    ],
    'columns' => [
        'id' => [
            'label' => 'ID',
            'field' => 'c.id',
            'filter' => true,
            'sort' => true,
            'filterType' => Exact::class,
            'driverOptions' => [
                'html' => 'input',
                'type' => 'number',
            ],
        ],
        'firstname' => [
            'label' => 'Prénom',
            'field' => 'c.firstname',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
            'driverOptions' => [
                'html' => 'input',
                'type' => 'text',
            ],
        ],
        'lastname' => [
            'label' => 'Nom',
            'field' => 'c.lastname',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
            'driverOptions' => [
                'html' => 'input',
                'type' => 'text',
            ],
        ],
        'email' => [
            'label' => 'Email',
            'field' => 'c.email',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
            'driverOptions' => [
                'html' => 'input',
                'type' => 'text',
            ]
        ],
        'gender' => [
            'label' => 'Genre',
            'field' => 'c.gender',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
            'driverOptions' => [
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
                ]
            ],
        ],
    ],
];
