<?php

use Polinome\Trieur\Driver\CsvDriver;
use Polinome\Trieur\Source\Doctrine\Doctrine;
use Polinome\Trieur\Source\Doctrine\Filter\Contain;
use Polinome\Trieur\Source\Doctrine\Filter\Exact;

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
        'class' => CsvDriver::class,
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
            'filterType' => Contain::class,
        ],
        'lastname' => [
            'label' => 'Nom',
            'field' => 'c.lastname',
            'filter' => true,
            'sort' => true,
            'filterType' => Contain::class,
        ],
        'email' => [
            'label' => 'Email',
            'field' => 'c.email',
            'filter' => true,
            'sort' => true,
            'filterType' => Contain::class,
        ],
    ],
];
