<?php

namespace Polinome\Trieur\Format;

use Polinome\Trieur\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
abstract class AbstractFormat
{
    /**
     * @throws Exception
     */
    final public function __construct(
        protected array $config,
        protected array $row,
        protected mixed $cell,
    ) {
        $this->init();
    }

    /**
     * @throws Exception
     */
    abstract protected function init();

    abstract public function render();
}
