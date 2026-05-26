<?php

namespace Polinome\Trieur\Source\Doctrine;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Exception;
use Polinome\Trieur\FilterTypes;
use Polinome\Trieur\Source\AbstractSource;
use Polinome\Trieur\Source\AbstractSourceFilter;
use Polinome\Trieur\Source\Doctrine\Filter\Contain;
use Polinome\Trieur\Source\Doctrine\Filter\DateRange;
use Polinome\Trieur\Source\Doctrine\Filter\Exact;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Doctrine extends AbstractSource
{
    /**
     * The main doctrine query builder (cloned for each query).
     */
    protected QueryBuilder $queryBuilder;

    /**
     * The main doctrine query builder (cloned for each query).
     */
    protected QueryBuilder $currentQueryBuilder;

    public function __construct(
        array $config,
        Columns $columns,
        protected DoctrineConnection $connection,
    ) {
        parent::__construct($config, $columns);

        $this->buildQuery();
    }

    protected function getFilterTypes(): array
    {
        return [
            FilterTypes::CONTAIN => Contain::class,
            FilterTypes::DATE_RANGE => DateRange::class,
            FilterTypes::EXACT => Exact::class,
        ];
    }

    protected function getDistinct(): string
    {
        return $this->config['group'] ?? implode(
            ', ',
            $this->config['select']
        );
    }

    protected function buildQuery(): void
    {
        $this->queryBuilder = $this->connection->createQueryBuilder();

        $this->queryBuilder->select(...$this->config['select']);

        /*
         * Main table
         */
        $this->queryBuilder->from(
            $this->config['from']['name'],
            $this->config['from']['alias'],
        );

        /*
         * Inner join, right join, left join
         */
        $joinTypes = [
            'innerJoin',
            'leftJoin',
            'rightJoin',
        ];

        foreach ($joinTypes as $joinType) {
            if (isset($this->config[$joinType])) {
                $joins = $this->config[$joinType];
                $this->buildJoins($joinType, $joins);
            }
        }

        /*
         * Condition
         */
        if (isset($this->config['where'])) {
            foreach ($this->config['where'] as $where) {
                $this->queryBuilder->andWhere($where);
            }
        }
    }

    /**
     * @param string                                                $joinType The join types 'innerJoin', 'leftJoin', 'rightJoin'
     * @param array<array{name: string, alias: string, on: string}> $joins    An array of joins (defined by an object with at least 'name', 'alias' and 'on' keys)
     */
    protected function buildJoins(string $joinType, array $joins): void
    {
        foreach ($joins as $join) {
            $this->queryBuilder->$joinType(
                $this->config['from']['alias'],
                $join['name'],
                $join['alias'],
                $join['on'],
            );
        }
    }

    public function getQuery(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @throws Exception
     */
    protected function buildFilteredQuery(): void
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        $this->filter();
    }

    protected function processFilter(AbstractSourceFilter $filter): void
    {
        $filter->setConnection($this->connection);
        $filter->setQueryBuilder($this->currentQueryBuilder);
        $filter->filter();
    }

    /**
     * @throws Exception
     */
    public function getDataQuery(): QueryBuilder
    {
        $this->buildFilteredQuery();

        if (null !== $this->offset) {
            $this->currentQueryBuilder->setFirstResult($this->offset);
        }

        if (null !== $this->length) {
            $this->currentQueryBuilder->setMaxResults($this->length);
        }

        foreach ($this->orders as $order) {
            [$column, $dir] = $order;

            $this->currentQueryBuilder->addOrderBy(
                $column->sourceSort,
                $dir
            );
        }

        if (isset($this->config['group'])) {
            $this->currentQueryBuilder->groupBy($this->config['group']);
        }

        return $this->currentQueryBuilder;
    }

    public function getCountQuery(): QueryBuilder
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        $this->currentQueryBuilder->select('COUNT(DISTINCT '.$this->getDistinct().')');

        return $this->currentQueryBuilder;
    }

    /**
     * @throws Exception
     */
    public function getFilteredCountQuery(): QueryBuilder
    {
        $this->buildFilteredQuery();

        $this->currentQueryBuilder
            ->select('COUNT(DISTINCT '.$this->getDistinct().')');

        return $this->currentQueryBuilder;
    }

    /**
     * @throws DBALException
     */
    public function getCount(): int
    {
        return $this->getCountQuery()
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @throws DBALException|Exception
     */
    public function getFilteredCount(): int
    {
        return $this->getFilteredCountQuery()
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @throws DBALException|Exception
     */
    public function getData(): array
    {
        return $this->getDataQuery()
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
