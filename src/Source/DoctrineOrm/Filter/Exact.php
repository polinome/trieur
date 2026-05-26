<?php

namespace Polinome\Trieur\Source\DoctrineOrm\Filter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Exact extends AbstractFilter
{
    public function filter(): void
    {
        if (!is_array($this->terms)) {
            $terms = [$this->terms];
        } else {
            $terms = $this->terms;
        }

        $conditions = [];
        foreach ($terms as $index => $term) {
            foreach ($this->columns as $colName) {
                $paramName = ':'.\uniqid('word_'.($index + 1).'_');

                $cond = $this->queryBuilder->expr()->like($colName, $paramName);
                $this->queryBuilder->setParameter($paramName, $term);
                $conditions[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conditions));
    }
}
