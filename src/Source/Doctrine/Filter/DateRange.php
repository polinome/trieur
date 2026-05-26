<?php

namespace Polinome\Trieur\Source\Doctrine\Filter;

use Doctrine\DBAL\Exception;

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

    /**
     * @throws Exception
     */
    public function filter(): void
    {
        if (preg_match(self::MASK, $this->from)) {
            $this->queryBuilder->andWhere(
                $this->queryBuilder->expr()->gte(
                    $this->columns[0],
                    $this->connection->quote($this->from)
                )
            );
        }

        if (preg_match(self::MASK, $this->to)) {
            $this->queryBuilder->andWhere(
                $this->queryBuilder->expr()->lte(
                    $this->columns[0],
                    $this->connection->quote($this->to)
                )
            );
        }
    }
}
