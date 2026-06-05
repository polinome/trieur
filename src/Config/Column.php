<?php

namespace Polinome\Trieur\Config;

class Column
{
    public ?string $label = null {
        get {
            return $this->label;
        }
        set {
            $this->label = $value;
        }
    }
    public ?string $field = null {
        get {
            return $this->field;
        }
        set {
            $this->field = $value;
        }
    }
    public bool $sort = false {
        get {
            return $this->sort;
        }
        set {
            $this->sort = $value;
        }
    }
    protected ?string $sortField = null {
        get {
            return $this->sortField;
        }
        set {
            $this->sortField = $value;
        }
    }
    public bool $filter = false {
        get {
            return $this->filter;
        }
        set {
            $this->filter = $value;
        }
    }
    public ?string $filterType = null {
        get {
            return $this->filterType;
        }
        set {
            $this->filterType = $value;
        }
    }
    public ?string $filterField = null {
        get {
            return $this->filterField ?? $this->field;
        }
        set {
            $this->filterField = $value;
        }
    }
    public array $driverOptions = [] {
        get {
            return $this->driverOptions;
        }
        set {
            $this->driverOptions = $value;
        }
    }
    public bool $hide = false {
        get {
            return $this->hide;
        }
        set {
            $this->hide = $value;
        }
    }
    public ?array $format = null {
        get {
            return $this->format;
        }
        set {
            $this->format = $value;
        }
    }

    public function __construct(
        public string $name {
            get {
                return $this->name;
            }
            set {
                $this->name = $value;
            }
        },
        array $config,
    ) {
        foreach ($config as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Unknown column configuration key [%s]', $key));
            }

            $this->{$key} = $value;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }
}
