<?php

namespace Polinome\Trieur\Driver;

use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class CsvDriver extends AbstractDriver
{
    public function __construct(array $config, Columns $columns)
    {
        if (!isset($config->length)) {
            $config['length'] = 0;
        }
        if (!isset($config->delimiter)) {
            $config['delimiter'] = ',';
        }
        if (!isset($config->enclosure)) {
            $config['enclosure'] = '"';
        }

        parent::__construct($config, $columns);
    }

    public function getOffset(): int
    {
        return 0;
    }

    public function getLength(): ?int
    {
        return null;
    }

    public function getOrder(): array
    {
        return [];
    }

    public function getFilters(): array
    {
        return [];
    }

    /**
     * @return resource The file handle of the csv content
     *
     * @throws Exception
     */
    public function getResponse(array $data, ?int $count = null, ?int $filteredCount = null): mixed
    {
        $handle = tmpfile();

        if (false === $handle) {
            throw new Exception('Unable to create temporary file');
        }

        foreach ($data as $row) {
            fputcsv($handle, $row, $this->config['delimiter'], $this->config['enclosure'], escape: '\\');
        }

        rewind($handle);

        return $handle;
    }
}
