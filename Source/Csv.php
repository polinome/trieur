<?php

namespace Solire\Trieur\Source;

use Solire\Trieur\Source;
use Solire\Trieur\SourceFilter;
use Solire\Trieur\Columns;
use Solire\Trieur\Exception;
use Solire\Conf\Conf;

/**
 * Csv connection wrapper.
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class Csv extends Source
{
    /**
     * The csv file handle.
     *
     * @var resource
     */
    protected $handle = null;

    /**
     * Number lines in the csv file.
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Number lines matching the request in the csv file.
     *
     * @var int
     */
    protected $filteredCount = 0;

    /**
     * Header of the csv file.
     *
     * @var array
     */
    protected $header = [];

    /**
     * Lines of the csv file matching the request.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Current row
     *
     * @var array
     */
    protected $row = null;

    /**
     * State of the request (to ensure we don't parse the file multiple times)
     *
     * @var string
     */
    protected $md5 = null;

    /**
     * Constructeur.
     *
     * @param Conf    $conf       Configuration for the csv parse (length,
     *                            delimiter, enclosure)
     * @param Columns $columns    Configuration des colonnes
     * @param string  $connection Chemin du fichier csv
     *
     * @throws Exception Si le fichier source n'existe pas ou n'est pas lisible
     */
    public function __construct(
        Conf $conf,
        Columns $columns,
        $connection
    ) {
        parent::__construct($conf, $columns, $connection);

        if (!file_exists($this->connection)) {
            throw new Exception('No csv file founed : "' . $this->connection . '"');
        }

        if (!is_readable($this->connection)) {
            throw new Exception('Csv file not readable : "' . realpath($this->connection) . '"');
        }

        if (!isset($this->conf->length)) {
            $this->conf->length = 0;
        }

        if (!isset($this->conf->delimiter)) {
            $this->conf->delimiter = ',';
        }

        if (!isset($this->conf->enclosure)) {
            $this->conf->enclosure = '"';
        }

        if (!isset($this->conf->headerRow)) {
            $this->conf->headerRow = null;
        } elseif (!isset($this->conf->headerLength)) {
            $this->conf->headerLength = (int) $this->conf->headerRow + 1;
        }

        if (!isset($this->conf->headerLength)) {
            $this->conf->headerLength = 0;
        }

        $this->handle();
    }

    /**
     * Opens the csv file.
     */
    protected function handle()
    {
        $this->handle = fopen($this->connection, 'r');

        for ($ii = 0; $ii < $this->conf->headerLength; $ii++) {
            $row = $this->fetch(true);
            if ($this->conf->headerRow === $ii) {
                $this->header = $row;
            }
        }
    }

    /**
     * Fetches a line from csv file.
     *
     * @param bool $raw Do not change keys with header cells
     *
     * @return array
     * @throws Exception
     */
    protected function fetch($raw = false)
    {
        $row = fgetcsv(
            $this->handle,
            $this->conf->length,
            $this->conf->delimiter,
            $this->conf->enclosure
        );

        if (empty($row)) {
            return null;
        }

        $this->row = $row;

        if ($raw || empty($this->header)) {
            return true;
        }

        $formatedRow = [];
        foreach ($row as $ii => $cell) {
            if (!isset($this->header[$ii])) {
                throw new Exception(sprintf('no offset [%s] in %s', $ii, print_r($this->header, true)));
            }

            $formatedRow[$this->header[$ii]] = $cell;
        }

        return $this->row = $formatedRow;
    }

    /**
     * Closes the handle to the csv file.
     *
     * @return void
     */
    protected function close()
    {
        fclose($this->handle);
    }

    /**
     * Return the total of available lines.
     *
     * @return int Total number
     * @throws Exception
     */
    public function getCount()
    {
        $this->parse();

        return $this->count;
    }

    /**
     * Return the total of available lines filtered by the current filters.
     *
     * @return int Total number
     */

    /**
     * Return the total of available lines filtered by the current filters.
     *
     * @return int Total number
     * @throws Exception
     */
    public function getFilteredCount()
    {
        $this->parse();

        return $this->filteredCount;
    }

    /**
     * Returns the data filtered by the current filters.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->parse();

        if (!empty($this->header)) {
            $data = $this->data;
            $this->data = [];

            foreach ($data as $row) {
                $fRow = [];
                foreach ($row as $ind => $cell) {
                    $fRow[$this->header[$ind]] = $cell;
                }
                $this->data[] = $fRow;
            }
        }

        return $this->data;
    }

    /**
     * Add the current row to the data following the defined orders.
     *
     * @throws Exception
     */
    protected function addToEligible()
    {
        $newOffset = count($this->data);

        foreach ($this->data as $offset => $row) {
            if ($this->lowerThan($this->row, $row)) {
                $newOffset = $offset;
                break;
            }
        }

        $this->insertToEligible($newOffset);
    }

    /**
     * Inserts a row in the data at a defined offset.
     *
     * @param int $offset The offset
     */
    protected function insertToEligible($offset)
    {
        $this->data = array_merge(
            array_slice($this->data, 0, $offset),
            [$this->row],
            array_slice($this->data, $offset)
        );
    }

    /**
     * Compare two rows of data.
     *
     * @param type $row1 The first row
     * @param type $row2 The second row
     *
     * @return bool Returns true if $row1 is less than $row2,
     *              false if $row1 is greater or equal than $row2, following the defined
     *              orders
     */
    protected function lowerThan($row1, $row2)
    {
        if (empty($this->orders)) {
            return false;
        }

        foreach ($this->orders as $order) {
            list($column, $dir) = $order;

            $test = strnatcasecmp(
                $this->getCell($row1, $column->sourceName),
                $this->getCell($row2, $column->sourceName)
            );

            if ($test == 0) {
                continue;
            }

            if (strtolower($dir) == 'asc') {
                return $test < 0;
            }

            return $test > 0;
        }

        return false;
    }

    /**
     * Parse the csv file, and build the data array.
     *
     * @throws Exception
     */
    protected function parse()
    {
        $currentMd5 = md5(serialize([
            $this->filters,
            $this->orders,
            $this->offset,
            $this->length,
        ]));

        if ($this->md5 === $currentMd5) {
            return;
        }

        $this->md5 = $currentMd5;
        $this->count = 0;
        $this->filteredCount = 0;
        $this->handle();

        $this->data = [];

        while ($this->fetch()) {
            if ($this->filter()) {
                $this->addToEligible();
                $this->filteredCount++;
            }
            $this->count++;
        }

        $this->close();

        $this->data = array_slice(
            $this->data,
            $this->offset,
            $this->length
        );
    }

    /**
     * Process the filter.
     *
     * @param SourceFilter $filter The filter class
     */
    protected function processFilter(SourceFilter $filter)
    {
        $filter->setRow($this->row);
        $filter->setSource($this);

        $filter->filter();
    }

    /**
     * Get cell in a row by its index or key (column header cell value).
     *
     * @param array      $row        Row
     * @param string|int $indexOrKey Index or key
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function getCell(array $row, $indexOrKey)
    {
        $index = $indexOrKey;
        if (!empty($this->header)) {
            $index = array_search($indexOrKey, $this->header);

            if ($index === false) {
                throw new Exception(sprintf('header key [%s] does not exist', $indexOrKey));
            }
        }

        return $row[$index];
    }
}
