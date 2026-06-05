<?php

namespace Polinome\Trieur\Source\Doctrine\Filter;

use Doctrine\DBAL\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Contain extends AbstractFilter
{
    /**
     * @throws Exception
     */
    public function filter(): void
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
         * We split words (separated by space)
         */
        $words = preg_split('`\s+`', $stringSearch, -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) > 1) {
            array_unshift($words, $stringSearch);
        }

        $words = array_unique($words);

        $conditions = [];
        $orderBy = [];
        foreach ($words as $word) {
            foreach ($this->columns as $colName) {
                /**
                 * @todo add a weighting array to the constructor
                 */
                $weight = 1;

                $cond = $colName.' LIKE '.$this->connection->quote('%'.$word.'%');
                $conditions[] = $cond;
                $orderBy[] = 'IF('.$cond.', '.mb_strlen((string) $word) * $weight.', 0)';
            }
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conditions));
        $this->queryBuilder->addOrderBy(implode(' + ', $orderBy), 'DESC');
    }
}
