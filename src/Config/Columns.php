<?php

namespace Polinome\Trieur\Config;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Columns implements \IteratorAggregate
{
    /**
     * List of columns with name index.
     *
     * @var array<Column>
     */
    protected array $columnsByName = [];

    /**
     * List of columns with numeric index.
     *
     * @var array<Column>
     */
    protected array $columnsByIndex = [];

    /**
     * @param array $columns Columns configuration
     */
    public function __construct(array $columns)
    {
        $index = 0;
        foreach ($columns as $name => $column) {
            $column = new Column($name, $column);
            $this->columnsByIndex[$index] = $column;
            $this->columnsByName[$name] = $column;

            ++$index;
        }
    }

    /**
     * Get a column by its offset or name.
     *
     * @param string|int $index Offset or name
     */
    public function get(string|int $index): Column
    {
        if ((string) ($index) === (string) (int) $index) {
            $index = (int) $index;

            if (!isset($this->columnsByIndex[$index])) {
                throw new \InvalidArgumentException(sprintf('Undefined index "%s" in the columns list', $index));
            }

            return $this->columnsByIndex[$index];
        }

        if (!isset($this->columnsByName[$index])) {
            throw new \InvalidArgumentException(sprintf('Undefined name "%s" in the columns list', $index));
        }

        return $this->columnsByName[$index];
    }

    /**
     * Method making possible to iterate through the list of columns.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->columnsByIndex);
    }
}
