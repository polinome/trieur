<?php

namespace Polinome\Trieur\Source\DoctrineOrm\Filter;

use Doctrine\ORM\QueryBuilder;
use Polinome\Trieur\Source\AbstractSourceFilter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractFilter extends AbstractSourceFilter
{
    protected QueryBuilder $queryBuilder;

    public function setQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
