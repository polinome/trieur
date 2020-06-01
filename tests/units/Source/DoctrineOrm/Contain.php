<?php

namespace Polinome\Trieur\test\units\Source\DoctrineOrm;

use atoum;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Setup;
use Polinome\Trieur\tests\data\Entity\Profil;

/**
 * Description of Contain.
 *
 * @author polinome
 */
class Contain extends atoum
{
    /**
     * @var EntityManager
     */
    public $connection = null;

    /**
     * @var array
     */
    public $columns = null;

    public function beforeTestMethod($testMethod)
    {
        $this->mockGenerator->shuntParentClassCalls();

        $this->mockGenerator->orphanize('__construct');
        $pdo = new \mock\PDO();

        $this->mockGenerator->orphanize('__construct');
        $db = new \mock\Doctrine\DBAL\Connection();
        $db->getMockController()->connect = function () {
        };
        $db->getMockController()->getEventManager = function () {
            return new EventManager();
        };
        $db->getMockController()->getDatabasePlatform = function () {
            return new MySqlPlatform();
        };
        $this->mockGenerator->unshuntParentClassCalls();

        $config = Setup::createYAMLMetadataConfiguration([
            TEST_DATA_DIR . '/doctrine-orm',
        ], true);

        $this->connection = EntityManager::create($db, $config);
    }

    /**
     * @return \Polinome\Trieur\Source\DoctrineOrm\Contain
     */
    public function testConstruct()
    {
        $this
            ->object($contain = $this->newTestedInstance(['p.nom'], 'dupont'))
        ;

        return $contain;
    }

    /**
     * @return \Polinome\Trieur\Source\DoctrineOrm\Contain
     */
    public function testGetQueryBuilder()
    {
        /* @var $contain \Polinome\Trieur\Source\DoctrineOrm\Contain */

        $this
            ->if($contain = $this->testConstruct())
            ->and($em = $this->connection)
            ->and($qB = new QueryBuilder($em))
            ->and($contain->setQueryBuilder($qB))
                ->object($contain->getQueryBuilder())
                    ->isInstanceOf(QueryBuilder::class)
        ;

        return $contain;
    }

    public function testFilter()
    {
        /* @var $contain \Polinome\Trieur\Source\DoctrineOrm\Contain */
        /* @var $qB QueryBuilder */
        $this
            ->if($contain = $this->testGetQueryBuilder())
            ->and($contain->filter())
                ->object($qB = $contain->getQueryBuilder())
            ->and($qB->select('p.nom'))
            ->and($qB->from(Profil::class, 'p'))
                ->string($contain->getQueryBuilder()->getDQL())
                    ->match('#^SELECT p\.nom FROM ' . \preg_quote(Profil::class, '#') . ' p WHERE p\.nom LIKE :word_1_\w+$#')
                ->string($contain->getQueryBuilder()->getQuery()->getSQL())
                    ->match('#^SELECT (\w+)\.nom AS (\w+) FROM profil \1 WHERE \1\.nom LIKE \?$#')
        ;
    }
}
