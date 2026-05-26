<?php

namespace Polinome\Trieur\Source\DoctrineOrm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Exception;
use Polinome\Trieur\FilterTypes;
use Polinome\Trieur\Source\AbstractSource;
use Polinome\Trieur\Source\AbstractSourceFilter;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Contain;
use Polinome\Trieur\Source\DoctrineOrm\Filter\DateRange;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Exact;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class DoctrineOrm extends AbstractSource
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
        protected EntityManagerInterface $entityManager,
    ) {
        parent::__construct($config, $columns);

        $this->buildQuery();
    }

    public function getQuery(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function buildQuery(): void
    {
        $this->queryBuilder = $this->entityManager->createQueryBuilder();

        $this->queryBuilder->select((array) $this->config['select']);

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
            'join',
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

        if (isset($this->config['parameters'])) {
            foreach ($this->config['parameters'] as $key => $value) {
                $this->queryBuilder->setParameter($key, $value);
            }
        }
    }

    /**
     * @param string                                                         $joinType The join types 'innerJoin', 'leftJoin', 'rightJoin'
     * @param array{name: string, alias: string, type: string, cond: string} $joins    An array of joins (defined by an object with at least 'name', 'alias' and 'on' keys)
     */
    protected function buildJoins(string $joinType, array $joins): void
    {
        foreach ($joins as $join) {
            $this->queryBuilder->$joinType(
                $join['name'],
                $join['alias'],
                $join['type'],
                $join['cond']
            );
        }
    }

    public function getCount(): int
    {
        return $this->getCountQuery()->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws Exception
     */
    public function getData(): array
    {
        $dataQuery = $this->getDataQuery();

        return $dataQuery->getQuery()->getArrayResult();
    }

    /**
     * @throws Exception
     */
    public function getFilteredCount(): int
    {
        return $this->getFilteredCountQuery()->getQuery()->getSingleScalarResult();
    }

    public function getCountQuery(): QueryBuilder
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        $this->currentQueryBuilder
            ->select('COUNT('.$this->getDistinct().')')
        ;

        return $this->currentQueryBuilder;
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

        if (isset($this->config->group)) {
            $this->currentQueryBuilder->groupBy($this->config->group);
        }

        return $this->currentQueryBuilder;
    }

    /**
     * @throws Exception
     */
    public function getFilteredCountQuery(): QueryBuilder
    {
        $this->buildFilteredQuery();

        $this->currentQueryBuilder->select('COUNT(DISTINCT '.$this->getDistinct().')');
        $this->currentQueryBuilder->resetDQLPart('orderBy');

        return $this->currentQueryBuilder;
    }

    protected function getDistinct(): string
    {
        return $this->config['group'];
    }

    protected function getFilterTypes(): array
    {
        return [
            FilterTypes::CONTAIN => Contain::class,
            FilterTypes::DATE_RANGE => DateRange::class,
            FilterTypes::EXACT => Exact::class,
        ];
    }

    protected function processFilter(AbstractSourceFilter $filter): void
    {
        $filter->setQueryBuilder($this->currentQueryBuilder);
        $filter->filter();
    }

    /**
     * @throws Exception
     */
    protected function buildFilteredQuery(): void
    {
        $this->currentQueryBuilder = clone $this->queryBuilder;

        $this->filter();
    }
}
