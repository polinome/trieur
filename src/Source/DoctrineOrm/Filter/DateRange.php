<?php

namespace Polinome\Trieur\Source\DoctrineOrm\Filter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class DateRange extends AbstractFilter
{
    public const string MASK = '#^\d{4}-\d{2}-\d{2}$#';

    protected string $from;
    protected string $to;

    public function __construct($columns, array $terms)
    {
        parent::__construct($columns, $terms);

        [$this->from, $this->to] = $this->terms;
    }

    public function filter(): void
    {
        if (preg_match(self::MASK, $this->from)) {
            $paramName = ':'.\uniqid('from_');
            $cond = $this->queryBuilder->expr()->gte($this->columns[0], $paramName);
            $this->queryBuilder->andWhere($cond)->setParameter($paramName, $this->from);
        }

        if (preg_match(self::MASK, $this->to)) {
            $paramName = ':'.\uniqid('to_');
            $cond = $this->queryBuilder->expr()->lte($this->columns[0], $paramName);
            $this->queryBuilder->andWhere($cond)->setParameter($paramName, $this->to);
        }
    }
}
