<?php

namespace Polinome\Trieur\Source;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractSourceFilter
{
    public function __construct(
        protected array $columns,
        protected array $terms,
    ) {
    }

    abstract public function filter(): void;
}
