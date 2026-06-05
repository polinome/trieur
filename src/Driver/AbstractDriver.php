<?php

namespace Polinome\Trieur\Driver;

use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Filter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractDriver
{
    public function __construct(
        protected array $config,
        protected Columns $columns,
    ) {
    }

    abstract public function getOffset(): int;

    abstract public function getLength(): ?int;

    abstract public function getOrder(): mixed;

    /**
     * Return the filter terms for each column.
     *
     * @return array<Filter>
     */
    abstract public function getFilters(): array;

    abstract public function getResponse(array $data, ?int $count = null, ?int $filteredCount = null): mixed;
}
