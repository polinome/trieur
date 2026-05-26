<?php

namespace Polinome\Trieur\Source\DoctrineOrm\Filter;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Contain extends AbstractFilter
{
    public function filter(): void
    {
        $stringSearch = implode(' ', $this->terms);

        $words = preg_split('`\s+`', $stringSearch, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $words = array_unique($words);

        $conditions = [];
        foreach ($words as $index => $word) {
            foreach ($this->columns as $colName) {
                $paramName = ':'.\uniqid('word_'.($index + 1).'_');

                $cond = $this->queryBuilder->expr()->like($colName, $paramName);
                $this->queryBuilder->setParameter($paramName, '%'.$word.'%');

                $conditions[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conditions));
    }
}
