<?php

namespace Polinome\Trieur\Source\Doctrine\Filter;

use Doctrine\DBAL\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Exact extends AbstractFilter
{
    /**
     * @throws Exception
     */
    public function filter(): void
    {
        if (!is_array($this->terms)) {
            $terms = [$this->terms];
        } else {
            $terms = $this->terms;
        }

        $conditions = [];
        foreach ($terms as $term) {
            foreach ($this->columns as $colName) {
                $conditions[] = $colName.' = '.$this->connection->quote($term);
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conditions));
    }
}
