<?php

namespace Polinome\Trieur\Source;

use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Exception;
use Polinome\Trieur\Filter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractSource
{
    /**
     * An array of arrays where the first element is an array of columns or
     * expressions and the second element is an array of terms to look for.
     *
     * @var array<Filter>
     */
    protected array $filters = [];

    /**
     * An associative array where keys are a sql column or expression and values
     * are a string 'ASC' or 'DESC'.
     */
    protected array $orders = [];

    /**
     * Offset of the query.
     */
    protected ?int $offset = null;

    /**
     * Length of the query.
     */
    protected ?int $length = null;

    public function __construct(protected ?array $config, protected Columns $columns)
    {
    }

    /**
     * @param array<Filter> $filters
     */
    final public function setFilters(array $filters): void
    {
        $this->filters = [];
        $this->addFilters($filters);
    }

    /**
     * @param array<Filter> $filters
     */
    final public function addFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    final public function addFilter(Filter $filter): void
    {
        $this->filters[] = $filter;
    }

    final public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    final public function setLength(?int $length): void
    {
        $this->length = $length;
    }

    final public function setOrders(array $orders): void
    {
        $this->orders = [];
        $this->addOrders($orders);
    }

    final public function addOrders(array $orders): void
    {
        foreach ($orders as $order) {
            [$column, $dir] = $order;
            $this->addOrder($column, $dir);
        }
    }

    /**
     * @param string $column    A column
     * @param string $direction A direction string 'ASC' or 'DESC'
     */
    final public function addOrder(string $column, string $direction = 'ASC'): void
    {
        $column = $this->columns->get($column);
        $this->orders[] = [
            $column->field,
            $direction,
        ];
    }

    /**
     * @throws Exception
     */
    final public function filter(): void
    {
        foreach ($this->filters as $filter) {
            $filter = $this->instantiateFilter($filter);
            $this->processFilter($filter);
        }
    }

    /**
     * @throws Exception
     */
    private function instantiateFilter(Filter $filter): AbstractSourceFilter
    {
        $className = $this->getFilterClassName($filter->type);

        $columns = [];
        foreach ($filter->columns as $columnName) {
            $column = $this->columns->get($columnName);
            $columns[] = $column->filterField ?? $column->field;
        }

        return new $className($columns, $filter->terms);
    }

    /**
     * @return class-string<AbstractSourceFilter>
     *
     * @throws Exception
     */
    private function getFilterClassName(string $filterType): string
    {
        $filterTypes = $this->getFilterTypes();

        if (!isset($filterTypes[$filterType])) {
            throw new Exception(sprintf('No filter class found for type [%s]', $filterType));
        }

        return $filterTypes[$filterType];
    }

    abstract protected function getFilterTypes(): array;

    abstract protected function processFilter(AbstractSourceFilter $filter): void;

    abstract public function getCount(): int;

    abstract public function getFilteredCount(): int;

    abstract public function getData(): mixed;
}
