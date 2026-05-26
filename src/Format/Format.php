<?php

namespace Polinome\Trieur\Format;

use Polinome\Trieur\Config\Column;
use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
readonly class Format
{
    public function __construct(private Columns $columns)
    {
    }

    /**
     * @throws Exception
     */
    public function format(array $data): array
    {
        $dataFormated = [];

        foreach ($data as $row) {
            $dataFormated[] = $this->formateRow($row);
        }

        return $dataFormated;
    }

    /**
     * @throws Exception
     */
    protected function formateRow(array $row): array
    {
        $rowFormated = [];
        /* @var Column $column */
        foreach ($this->columns as $column) {
            if ($column->hide) {
                continue;
            }

            $cellFormated = $this->formateCell($row, $column);
            $rowFormated[$column->name] = $cellFormated;
        }

        return $rowFormated;
    }

    /**
     * @throws Exception
     */
    protected function formateCell(array $row, Column $column): string
    {
        if (null === $column->format) {
            return $this->getCell($row, $column);
        }

        $className = $this->getFormatClassName($column);

        $formatInstance = new $className($column->format, $row, $this->getCell($row, $column));

        return $formatInstance->render();
    }

    protected function getCell(array $row, Column $column): string
    {
        if (!isset($row[$column->name])) {
            return '';
        }

        return $row[$column->name];
    }

    /**
     * @throws Exception
     */
    private function getFormatClassName(Column $column): ?string
    {
        if (!isset($column->format['class'])) {
            throw new Exception(sprintf('Undefined format class for column [%s]', $column->name));
        }

        $className = $column->format['class'];

        if (!class_exists($className)) {
            throw new Exception(sprintf('Format class [%s] for column [%s] does not exist', $className, $column->name));
        }

        if (!is_subclass_of($className, AbstractFormat::class)) {
            throw new Exception(sprintf('Format class [%s] does not extend abstract class [%s]', $className, AbstractFormat::class));
        }

        return $className;
    }
}
