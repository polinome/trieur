<?php

namespace Polinome\Trieur\Source\DoctrineOrm;

/**
 * Doctrine filter class for Contain filter.
 *
 * @author  polinome <contact@polinome.com>
 * @license MIT http://mit-license.org/
 */
class Contain extends Filter
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
        if (is_array($this->terms)) {
            $stringSearch = implode(' ', $this->terms);
        } else {
            $stringSearch = $this->terms;
        }

        /*
         * On divise en mots (séparé par des espace)
         */
        $words = preg_split('`\s+`', $stringSearch, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $words = array_unique($words);

        $conds = [];
        foreach ($words as $index => $word) {
            foreach ($this->columns as $colName) {
                $paramName = ':' . \uniqid('word_' . ($index + 1) . '_');

                $cond = $this->queryBuilder->expr()->like($colName, $paramName);
                $this->queryBuilder->setParameter($paramName, '%' . $word . '%');

                $conds[] = $cond;
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conds));
    }
}
