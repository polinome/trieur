<?php

namespace Polinome\Trieur;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Filter
{
    public function __construct(
        /**
         * @var array<string>
         */
        public array $columns {
            get => $this->columns;
        },
        public string $type {
            get => $this->type;
        },
        public array $terms {
            get => $this->terms;
        },
    ) {
    }
}
