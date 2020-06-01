<?php

namespace Polinome\Trieur\Source\DoctrineOrm;

/**
 * Doctrine ORM filter class for Exact filter.
 *
 * @author  polinome <contact@polinome.com>
 * @license MIT http://mit-license.org/
 */
class Exact extends Filter
{
    /**
     * Filter.
     *
     * @return void
     */
    public function filter()
    {
        /*
         * Variable qui contient la chaine de recherche
         */
        if (!is_array($this->terms)) {
            $terms = [$this->terms];
        } else {
            $terms = $this->terms;
        }

        $conds = [];
        foreach ($terms as $index => $term) {
            foreach ($this->columns as $colName) {
                $paramName = ':' . \uniqid('word_' . ($index + 1) . '_');

                $cond = $this->queryBuilder->expr()->like($colName, $paramName);
                $this->queryBuilder->setParameter($paramName, $term);
                $conds[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
    }
}
