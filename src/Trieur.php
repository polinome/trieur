<?php

namespace Polinome\Trieur;

use Polinome\Trieur\Config\Columns;
use Polinome\Trieur\Driver\AbstractDriver;
use Polinome\Trieur\Format\Format;
use Polinome\Trieur\Source\AbstractSource;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Trieur
{
    protected ?Columns $columns = null;
    protected ?array $sourceConfig = null;
    protected ?array $driverConfig = null;
    private mixed $sourceModel = null;

    protected ?AbstractDriver $driver = null;
    protected ?AbstractSource $source = null;
    protected ?Format $format = null;

    public function __construct(array $config, mixed $sourceModel = null)
    {
        if (!isset($config['source'])) {
            throw new \InvalidArgumentException('The source configuration is missing.');
        }

        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException('The driver configuration is missing.');
        }

        if (!isset($config['columns'])) {
            throw new \InvalidArgumentException('The columns configuration is missing.');
        }

        $this->sourceConfig = $config['source'];
        $this->driverConfig = $config['driver'];
        $this->columns = new Columns($config['columns']);

        if (null !== $sourceModel) {
            $this->sourceModel = $sourceModel;
        }
    }

    public function getDriver(): AbstractDriver
    {
        if (null === $this->driver) {
            $class = $this->driverConfig['class'];

            $this->driver = new $class($this->driverConfig['config'] ?? [], $this->columns);
        }

        return $this->driver;
    }

    public function setDriver(AbstractDriver $driver): void
    {
        $this->driver = $driver;
    }

    public function getSource(): AbstractSource
    {
        if (null === $this->source) {
            $class = $this->sourceConfig['class'];

            $this->source = new $class($this->sourceConfig['config'] ?? [], $this->columns, $this->sourceModel);
        }

        return $this->source;
    }

    public function setSource(AbstractSource $source): void
    {
        $this->source = $source;
    }

    protected function getFormat(): Format
    {
        if (null !== $this->format) {
            return $this->format;
        }

        return $this->format = new Format(
            $this->columns,
        );
    }

    public function prepare(): static
    {
        $filters = $this->getDriver()->getFilters();
        if (!empty($filters)) {
            $this->getSource()->addFilters($filters);
        }

        $this->getSource()->setLength($this->getDriver()->getLength());
        $this->getSource()->setOffset($this->getDriver()->getOffset());
        $this->getSource()->setOrders($this->getDriver()->getOrder());

        return $this;
    }

    /**
     * @throws Exception
     */
    public function fetch(): mixed
    {
        $data = $this->getSource()->getData();

        return $this->getDriver()->getResponse(
            $this->getFormat()->format($data),
            $this->getSource()->getCount(),
            $this->getSource()->getFilteredCount()
        );
    }

    /**
     * @throws Exception
     */
    public function getResponse(): mixed
    {
        return $this->prepare()->fetch();
    }
}
