<?php

namespace Polinome\Trieur\Driver;

use Polinome\Trieur\Config\Column;
use Polinome\Trieur\Filter;
use Polinome\Trieur\FilterTypes;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class DataTablesDriver extends AbstractDriver
{
    protected mixed $request;

    public function setRequest(mixed $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function getFilterTerm(): ?string
    {
        return $this->request['search']['value'] ?? null;
    }

    protected function getColumnTerm(array $column): ?string
    {
        return $column['search']['value'] ?? null;
    }

    public function getFilters(): array
    {
        /* @var array<Filter> $filters */
        $filters = [];
        /* @var array<string> $filterableColumns */
        $filterableColumns = [];

        if (empty($this->request)) {
            return $filters;
        }

        /* @var Column $column */
        foreach ($this->columns as $index => $column) {
            $clientColumn = $this->request['columns'][$index] ?? [
                'searchable' => false,
            ];

            if (
                !$clientColumn['searchable']
                || !$column->filter
            ) {
                continue;
            }

            $filterType = $column->filterType;

            $filterableColumns[] = $column->name;

            $term = $this->getColumnTerm($clientColumn);
            if (null === $term || '' === $term) {
                continue;
            }

            if (isset($this->config['separator'])
                && !empty($this->config['separator'])
            ) {
                $terms = explode($this->config['separator'], $term);
            } else {
                $terms = [$term];
            }

            $filters[] = new Filter(
                [$column->name],
                $filterType,
                $terms
            );
        }

        if ($term = $this->getFilterTerm()) {
            $filters[] = new Filter(
                $filterableColumns,
                FilterTypes::CONTAIN,
                [$term]
            );
        }

        return $filters;
    }

    public function getLength(): int
    {
        return $this->request['length'];
    }

    public function getOffset(): int
    {
        return $this->request['start'];
    }

    public function getOrder(): array
    {
        $orders = [];

        if (empty($this->request['order'])) {
            return $orders;
        }

        $ordersClient = $this->request['order'];
        foreach ($ordersClient as $order) {
            $orders[] = [
                $order['column'],
                $order['dir'],
            ];
        }

        return $orders;
    }

    public function getResponse(array $data, ?int $count = null, ?int $filteredCount = null): array
    {
        return [
            'data' => $data,
            'recordsTotal' => $count,
            'recordsFiltered' => $filteredCount,
        ];
    }

    /**
     * Return the jquery dataTables columns configuration array.
     *
     * @see http://datatables.net/reference/option/#Columns
     * official documentation
     */
    public function getJsColsConfig(): array
    {
        $cols = [];
        foreach ($this->columns as $column) {
            $dCol = [
                'orderable' => (bool) $column->sort,
                'searchable' => (bool) $column->filter,
                'data' => $column->name,
                'name' => $column->name,
                'title' => $column->label,
            ];

            if (isset($column->driverHidden) && $column->driverHidden) {
                $dCol['visible'] = false;
                $dCol['className'] = 'never';
            }

            if (isset($column->width)) {
                $dCol['width'] = $column->width;
            }

            if (isset($column->class)) {
                $dCol['className'] = $column->class;
            }

            $cols[] = $dCol;
        }

        return $cols;
    }

    /**
     * Return the jquery dataTables language configuration array.
     *
     * @see http://datatables.net/reference/option/#Internationalisation
     * official documentation
     */
    public function getJsLanguageConfig(): array
    {
        return [
            // language.aria : Language strings used for WAI-ARIA specific attributes
            // 'aria' => [
            //     // language.aria.sortAscending : Language strings used for WAI-ARIA specific attributes
            //     'sortAscending'  => null,
            //     // language.aria.sortDescending : Language strings used for WAI-ARIA specific attributes
            //     'sortDescending' => null,
            // ],
            // language.decimal : Decimal place character
            // 'decimal' => null,
            // language.emptyTable : Table has no records string
            'emptyTable' => 'Aucun '.$this->config['itemName'].' trouvé'.($this->config['itemGenre'] ?? ''),
            // language.info : Table summary information display string
            'info' => ''.$this->config['itemsName'].' _START_ à  _END_ sur _TOTAL_ '.$this->config['itemsName'],
            // language.infoEmpty : Table summary information string used when the table is empty or records
            'infoEmpty' => 'Aucun '.$this->config['itemName'],
            // language.infoFiltered : Appended string to the summary information when the table is filtered
            'infoFiltered' => '(filtre sur _MAX_ '.$this->config['itemsName'].')',
            // language.infoPostFix : String to append to all other summary information strings
            // 'infoPostFix' => null,
            // language.lengthMenu : Page length options string
            'lengthMenu' => 'Montrer _MENU_ '.$this->config['itemsName'].' par page',
            // language.loadingRecords : Loading information display string - shown when Ajax loading data
            // 'loadingRecords' => null,
            // language.paginate : Pagination specifarray(ic language strings
            'paginate' => [
                // language.paginate.first : Pagination 'first' button string
                'first' => 'première page',
                // language.paginate.last : Pagination 'last' button string
                'last' => 'dernière page',
                // language.paginate.next : Pagination 'next' button string
                'next' => 'page suivante',
                // language.paginate.previous : Pagination 'previous' button string
                'previous' => 'page précédente',
            ],
            // language.processing : Processing indicator string
            'processing' => 'Chargement',
            // language.search : Search input string
            'search' => 'Recherche',
            // language.searchPlaceholder : Search input element placeholder attribute
            'searchPlaceholder' => 'Recherche',
            // language.thousands : Thousands separator
            'thousands' => '&nbsp;',
            // language.zeroRecords : Table empty as a result of filtering string
            'zeroRecords' => 'Aucun '.$this->config['itemName'],
        ];
    }

    /**
     * The jquery dataTables configuration array.
     *
     * @see http://datatables.net/reference/option/ official documentation
     */
    public function getJsConfig(): array
    {
        $config = [
            'processing' => true,
            'serverSide' => true,
            'ajax' => [
                'url' => $this->config['requestUrl'] ?? null,
                'type' => $this->config['requestMethod'] ?? null,
            ],
            'columns' => $this->getJsColsConfig(),
            'language' => $this->getJsLanguageConfig(),
        ];

        if (isset($this->config->defaultSort)) {
            $config['order'] = $this->config['defaultSort'];
        }
        if (isset($this->config->autoWidth)) {
            $config['autoWidth'] = $this->config->autoWidth;
        }
        if (isset($this->config->dom)) {
            $config['dom'] = $this->config->dom;
        }

        if (!empty($this->config->config)) {
            $config = array_merge($config, (array) $this->config->config);
        }

        return $config;
    }

    /**
     * The jquery dataTables light columnfilter configuration array.
     */
    public function getColumnFilterConfig(): array
    {
        $config = [];

        /* @var Column $column */
        foreach ($this->columns as $index => $column) {
            if (isset($column->driverHidden) && $column->driverHidden) {
                continue;
            }

            if (!$column->filter) {
                continue;
            }

            $config[$index] = $column->driverOptions;
        }

        return $config;
    }
}
