<?php

namespace Solire\Trieur\Driver;

use \Solire\Trieur\Config;
use \Solire\Trieur\Driver as DriverInterface;

/**
 * Datatables driver
 *
 * @author  Thomas <thansen@solire.fr>
 * @license MIT http://mit-license.org/
 */
class DataTables extends Driver implements DriverInterface
{
    /**
     * Constructor
     *
     * @param Config $config The configuration
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $config->setDriverName('dataTables');
    }

    /**
     * Return the filter term
     *
     * @return array
     */
    public function getFilterTerm()
    {
        return $this->request['search']['value'];
    }

    /**
     * Return the searchable columns
     *
     * @return array
     */
    public function getSearchableColumns()
    {
        $clientColumns = $this->request['columns'];
        $serverColumns = $this->config->getColumns();

        foreach ($serverColumns as $index => $serverColumn) {
            $column = array_merge($serverColumn, $clientColumns[$index]);

            if ($column['searchable']
                && $column['filter']
                && ($column['sql'] || $column['filterSql'])
            ) {
                $sql = $column['sql'];
                if (isset($column['filterSql'])) {
                    $sql = $column['filterSql'];
                }

                if (is_array($sql)) {
                    $searchableColumns = array_merge($filteredColumns, $column['sql']);
                } else {
                    $searchableColumns[] = $column['sql'];
                }
            }
        }

        return $searchableColumns;
    }

    /**
     * Return the filter terms for each columns
     *
     * @return array
     */
    public function getFilterTermByColumns()
    {
        $clientColumns = $this->request['columns'];
        $serverColumns = $this->config->getColumns();

        foreach ($serverColumns as $index => $serverColumn) {
            $column = array_merge($serverColumn, $clientColumns[$index]);

            $term = $column['search']['value'];
            if ($term !== ''
                && $column['searchable']
                && $column['filter']
                && ($column['sql'] || $column['filterSql'])
            ) {
                $sql = $column['sql'];
                if (isset($column['filterSql'])) {
                    $sql = $column['filterSql'];
                }

                if (isset($column['filterType'])
                    && $column['filterType'] == 'date-range'
                ) {
                    $terms = explode('~', $term);

                    $col = array();

                    if (!empty($terms[0])) {
                        /*
                         * @todo translate from date format sent to sql
                         */
                        $col[0] = $terms[0];
                    } else {
                        $col[0] = '';
                    }

                    if (!empty($terms[1])) {
                        /*
                         * @todo translate from date format sent to sql
                         */
                        $col[1] = $terms[1];
                    } else {
                        $col[1] = '';
                    }

                    $filteredColumns[$sql] = $col;
                } else {
                    if (!is_array($sql)) {
                        $sql = array($sql);
                    }
                    foreach ($sql as $row) {
                        $filteredColumns[$row] = $term;
                    }
                }
            }
        }

        return $filteredColumns;
    }

    /**
     * Return the number of lines
     *
     * @return int
     */
    public function length()
    {
        return $this->request['length'];
    }

    /**
     * Return the offset
     *
     * @return int
     */
    public function offset()
    {
        return $this->request['start'];
    }

    /**
     * Return the list of columns for the sort with the direction
     *
     * @return array
     */
    public function order()
    {
        $orders = array();
        $ordersClient = $this->request['order'];
        foreach ($ordersClient as $order) {
            $columnName = $this->config->getColumn($order['column'], 'name');
            $dir        = $order['dir'];

            $orders[] = array(
                $columnName,
                $dir,
            );
        }
        return $orders;
    }

    /**
     * Return the jquery dataTables columns configuration array
     *
     * @return array
     */
    public function getJsColsConfig()
    {
        $cols = array();
        $columns = $this->config->getColumns();
        foreach ($columns as $ii => $col) {
            $dCol = array(
                'orderable'     => (bool) $col['sort'],
                'searchable'    => (bool) $col['filter'],
                'data'          => $col['name'],
                'name'          => $col['name'],
                'title'         => $col['label'],
            );

            if (isset($col['width'])) {
                $dCol['width'] = $col['width'];
            }

            if (isset($col['class'])) {
                $dCol['className'] = $col['class'];
            }

            $cols[] = $dCol;
        }
        return $cols;
    }

    /**
     * Return the jquery dataTables language configuration array
     *
     * @return array
     *
     * @link http://datatables.net/reference/option/#Internationalisation
     * official documentation
     */
    public function getJsLanguageConfig()
    {
        return array(
//            // language.aria : Language strings used for WAI-ARIA specific attributes
//            'aria' => array(
//                // language.aria.sortAscending : Language strings used for WAI-ARIA specific attributes
//                'sortAscending'  => null,
//                // language.aria.sortDescending : Language strings used for WAI-ARIA specific attributes
//                'sortDescending' => null,
//            ),
//            // language.decimal : Decimal place character
//            'decimal' => null,
            // language.emptyTable : Table has no records string
            'emptyTable' => 'Aucun ' . $this->config->getDriverConfig('itemName')
                . ' trouvé' . $this->config->getDriverConfig('itemGenre'),
            // language.info : Table summary information display string
            'info' => '' . $this->config->getDriverConfig('itemsName')
                . ' _START_ à  _END_ sur _TOTAL_ ' . $this->config->getDriverConfig('itemsName'),
            // language.infoEmpty : Table summary information string used when the table is empty or records
            'infoEmpty' => 'Aucun ' . $this->config->getDriverConfig('itemName') . '',
            // language.infoFiltered : Appended string to the summary information when the table is filtered
            'infoFiltered' => '(filtre sur _MAX_ ' . $this->config->getDriverConfig('itemsName') . ')',
//            // language.infoPostFix : String to append to all other summary information strings
//            'infoPostFix' => null,
            // language.lengthMenu : Page length options string
            'lengthMenu' => 'Montrer _MENU_ ' . $this->config->getDriverConfig('itemsName') . ' par page',
//            // language.loadingRecords : Loading information display string - shown when Ajax loading data
//            'loadingRecords' => null,
            // language.paginate : Pagination specific language strings
            'paginate' => array(
                // language.paginate.first : Pagination 'first' button string
                'first' => 'première page',
                // language.paginate.last : Pagination 'last' button string
                'last' => 'dernière page',
                // language.paginate.next : Pagination 'next' button string
                'next' => 'page suivante',
                // language.paginate.previous : Pagination 'previous' button string
                'previous' => 'page précédente',
            ),
            // language.processing : Processing indicator string
            'processing' => 'Chargement',
            // language.search : Search input string
            'search' => 'Recherche',
            // language.searchPlaceholder : Search input element placeholder attribute
            'searchPlaceholder' => 'Recherche',
            // language.thousands : Thousands separator
            'thousands' => '&nbsp;',
            // language.zeroRecords : Table empty as a result of filtering string
            'zeroRecords' => 'Aucun ' . $this->config->getDriverConfig('itemName'),
        );
    }

    /**
     * The jquery dataTables configuration array
     *
     * @return array
     */
    public function getJsConfig()
    {
        $defaultSort = array();
        foreach ($this->config->getDriverConfig('defaultSort') as $l) {
            $defaultSort[] = explode('|', $l);
        }

        $config = array(
            'processing' => true,
            'serverSide' => true,
            'ajax'       => array(
                'url'  => $this->config->getDriverConfig('requestUrl'),
                'type' => $this->config->getDriverConfig('requestMethod'),
            ),
            'columns'    => $this->getJsColsConfig(),
            'autoWidth'  => true,
            'ordering'   => $defaultSort,
            'jQueryUI'   => true,
            'dom'        => $this->config->getDriverConfig('dom'),
            'language'   => $this->getJsLanguageConfig(),
        );

        return $config;
    }
}
