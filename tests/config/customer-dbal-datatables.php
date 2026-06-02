<?php

use Polinome\Trieur\Driver\DataTablesDriver;
use Polinome\Trieur\FilterTypes;
use Polinome\Trieur\Source\Doctrine\Doctrine;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Exact;

return [
    'source' => [
        'class' => Doctrine::class,
        'config' => [
            'select' => [
                'c.id',
                'c.firstname',
                'c.lastname',
                'c.email',
                'c.gender',
            ],
            'from' => [
                'name' => 'customer',
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
        ],
        'firstname' => [
            'label' => 'Prénom',
            'field' => 'c.firstname',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
        ],
        'lastname' => [
            'label' => 'Nom',
            'field' => 'c.lastname',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
        ],
        'email' => [
            'label' => 'Email',
            'field' => 'c.email',
            'filter' => true,
            'sort' => true,
            'filterType' => FilterTypes::CONTAIN,
        ],
    ],
];
