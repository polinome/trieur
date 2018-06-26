<?php

namespace Polinome\Trieur\test\units;

use atoum;
use Solire\Conf\Loader;

/**
 * Description of Format.
 *
 * @author polinome
 */
class Format extends atoum
{
    public function testConstruct()
    {
        $conf = Loader::load([
            'nom' => [
                'format' => [],
            ],
        ]);
        $columns = new \Polinome\Trieur\Columns($conf);
        $this
            ->if($f = $this->newTestedInstance($columns))
            ->exception(function () use ($f) {
                $f->format([
                    [
                        'nom' => 'polinome',
                    ],
                ]);
            })
            ->hasMessage('Undefined format class for column [nom]')
        ;

        $conf = Loader::load([
            'nom' => [
                'format' => [
                    'class' => 'arg',
                ],
            ],
        ]);
        $columns = new \Polinome\Trieur\Columns($conf);
        $this
            ->if($f = $this->newTestedInstance($columns))
            ->exception(function () use ($f) {
                $f->format([
                    [
                        'nom' => 'polinome',
                    ],
                ]);
            })
            ->hasMessage('Format class [arg] for column [nom] does not exist')
        ;

        $conf = Loader::load([
            'nom' => [
                'format' => [
                    'class' => '\DateTime',
                ],
            ],
        ]);
        $columns = new \Polinome\Trieur\Columns($conf);
        $this
            ->if($f = $this->newTestedInstance($columns))
            ->exception(function () use ($f) {
                $f->format([
                    [
                        'nom' => 'polinome',
                    ],
                ]);
            })
            ->hasMessage('Format class [\DateTime] does not extend abstract class [\Polinome\Trieur\AbstractFormat]')
        ;

        $conf = Loader::load([
            'nom' => [
                'format' => [
                    'class' => 'Callback',
                    'name' => 'strtoupper',
                    'cell' => 'str',
                ],
            ],
            'prenom' => [
                'format' => [
                    'class' => 'Polinome\Trieur\Format\Callback',
                    'name' => 'ucfirst',
                    'cell' => 'str',
                ],
            ],
            'age' => [],
        ]);
        $columns = new \Polinome\Trieur\Columns($conf);

        $this
            ->if($f = $this->newTestedInstance($columns))
            ->array($f->format([
                    [
                        'nom' => 'polinome',
                        'prenom' => 'thomas',
                    ],
                ]))
                ->isEqualTo([
                    [
                        'nom' => 'POLINOME',
                        'prenom' => 'Thomas',
                        'age' => '',
                    ],
                ])
        ;
    }
}
