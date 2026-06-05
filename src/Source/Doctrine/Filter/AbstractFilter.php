<?php

namespace Polinome\Trieur\Source\Doctrine\Filter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Polinome\Trieur\Source\AbstractSourceFilter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractFilter extends AbstractSourceFilter
{
    protected Connection $connection;
    protected QueryBuilder $queryBuilder;

    public function setConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }
}
