<?php

use Polinome\Trieur\Driver\CsvDriver;
use Polinome\Trieur\Source\DoctrineOrm\DoctrineOrm;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Contain;
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
            ],
            'from' => [
                'name' => Customer::class,
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
