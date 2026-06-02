<?php

namespace Polinome\Trieur\Source\DoctrineOrm\Filter;

use Doctrine\DBAL\ParameterType;

class Is extends AbstractFilter
{
    public function filter(): void
    {
        $conditions = [];
        foreach ($this->columns as $colName) {
            $paramName = ':'.\uniqid('value_');

            if ('true' === $this->terms[0]) {
                $cond = $this->queryBuilder->expr()->eq($colName, $paramName);
                $this->queryBuilder->setParameter($paramName, true, ParameterType::BOOLEAN);
            } else {
                $cond = $this->queryBuilder->expr()->eq($colName, $paramName);
                $this->queryBuilder->setParameter($paramName, 'true' === $this->terms[0], ParameterType::BOOLEAN);
                $conditions[] = $cond;

                $cond = $this->queryBuilder->expr()->isNull($colName);
            }

            $conditions[] = $cond;
        }

        $this->queryBuilder->andWhere(implode(' OR ', $conditions));
    }
}
