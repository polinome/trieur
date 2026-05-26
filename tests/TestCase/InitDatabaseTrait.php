<?php

namespace Polinome\Trieur\Tests\TestCase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Polinome\Trieur\Tests\Context\Entity\Customer;

trait InitDatabaseTrait
{
    private readonly Connection $connection;
    private readonly EntityManager $entityManager;

    protected function setUpDatabase(): void
    {
        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => 'mysql',
            'port' => 3306,
            'dbname' => 'trieur',
            'user' => 'trieur',
            'password' => 'trieur',
            'serverVersion' => '8.0.0',
            'charset' => 'utf8mb4',
        ]);

        $config = ORMSetup::createAttributeMetadataConfig([__DIR__.'/Context/Entity'], true);
        $config->enableNativeLazyObjects(true);
        $this->entityManager = new EntityManager($this->connection, $config);

        $this->connection->executeStatement(<<<'SQL'
            DROP TABLE IF EXISTS customer;
            SQL
        );
        $this->connection->executeStatement(<<<'SQL'
            CREATE TABLE customer (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                firstname VARCHAR(255) NOT NULL,
                lastname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            SQL
        );

        $customer = new Customer();
        $customer->setFirstname('John');
        $customer->setLastname('Doe');
        $customer->setEmail('john.doe@trieur.com');
        $this->entityManager->persist($customer);

        $customer = new Customer();
        $customer->setFirstname('Jane');
        $customer->setLastname('Doe');
        $customer->setEmail('jane.doe@trieur.com');
        $this->entityManager->persist($customer);

        $this->entityManager->flush();
    }
}
