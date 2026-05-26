README
======

## TRIEUR
Trieur is a php library to sort, filter data with differents
* data source (database, csv file...) managed by the <a href="https://github.com/Polinome/Trieur/tree/master/Source">Source classes</a>
* query format (array like $_POST...) managed by the <a href="https://github.com/Polinome/Trieur/tree/master/Driver">Driver classes</a>
* output format (array, csv formated string...) managed by the <a href="https://github.com/Polinome/Trieur/tree/master/Driver">Driver classes</a>

The main classes is a Dependency Injection Container (it extends the famous <a href="https://github.com/silexphp/Pimple">Pimple</a>).
It instanciates a driver class, a source class and a columns configuration class.
Each source and driver class each extend an abstract containing basic methods to communicate through the main class.

It was originally build to print data in ower backend solution to display data, and to export them.
We use dataTables jquery pluggin. Therefore one of the driver available is made for this javascript pluggin.

## USAGE
```php
use App\Entity\Customer;
use Polinome\Trieur\Driver\DataTablesDriver;
use Polinome\Trieur\Source\DoctrineOrm\DoctrineOrm;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Contain;
use Polinome\Trieur\Source\DoctrineOrm\Filter\Exact;

// Defining the trieur configuration
$trieurConf = new Conf;
$trieurConf
    ->set('csv', 'driver', 'name')
    ...
    ->set('doctrine', 'source', 'name')
    ...
;
$trieurConf = [
    'source' => [
        'class' => DoctrineOrm::class,
        'config' => [
            'select' => [
                'c.id',
                'c.firstname',
                'c.lastname',
                'c.email',
            ],
            'from' => [
                'name' => Customer::class,
                'alias' => 'c',
            ],
            'group' => 'c.id',
        ],
    ],
    'driver' => [
        'class' => DataTablesDriver::class,
        'config' => [
            'itemName' => 'client',
            'itemsName' => 'clients',
        ],
    ],
    'columns' => [
        'id' => [
            'label' => 'ID',
            'field' => 'c.id',
            'filter' => true,
            'sort' => true,
            'filterType' => Exact::class,
        ],
        'firstname' => [
            'label' => 'Prénom',
            'field' => 'c.firstname',
            'filter' => true,
            'sort' => true,
            'filterType' => Contain::class,
        ],
        (...)
    ],
]

/* @var \Doctrine\ORM\EntityManagerInterface $entityManager */
/* @var \Symfony\Component\HttpFoundation\Request::class $request */

// Then here goes the magic
$trieur = new Trieur($conf, $entityManager);
$trieur->setRequest($request->request->request->all());

$response = $trieur->getResponse();

header('Content-type: application/json');
echo json_encode($response);
```
