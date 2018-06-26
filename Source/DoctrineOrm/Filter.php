<?php

namespace Polinome\Trieur\Source\DoctrineOrm;

use Polinome\Trieur\SourceFilter;
use Doctrine\ORM\QueryBuilder;

/**
 * Doctrine abstract filter class.
 *
 * @author  polinome <contact@polinome.com>
 * @license MIT http://mit-license.org/
 */
abstract class Filter extends SourceFilter
{
    /**
     * QueryBuilder.
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Set the query builder.
     *
     * @param QueryBuilder $queryBuilder The querybuilder
     *
     * @return void
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get the query builder.
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}
