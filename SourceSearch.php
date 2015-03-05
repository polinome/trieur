<?php
namespace Solire\Trieur;

/**
 * Description of SourceSearch
 *
 * @author  thansen <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
abstract class SourceSearch
{
    /**
     * Columns where to look
     *
     * @var type
     */
    protected $columns;

    /**
     * Terms to look for
     *
     * @var type
     */
    protected $terms;

    /**
     * Constructor
     *
     * @param mixed $columns The columns where to search
     * @param mixed $terms   The terms to look for
     */
    public function __construct($columns, $terms)
    {
        $this->columns = $columns;
        $this->terms = $terms;
    }

    /**
     * Filter
     *
     * @return void
     */
    abstract public function filter();
}
