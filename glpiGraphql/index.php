<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';
//require_once __DIR__. '/../../../inc/dbmysql.class.php';


define('GLPI_ROOT', __DIR__. '/../../../');
include (GLPI_ROOT . "/inc/based_config.php");
include_once (GLPI_ROOT . "/inc/define.php");
require_once __DIR__. '/../../../config/config_db.php';
$DB = new DB();
use pjmd89\apiGraphQL\Api;
use pjmd89\GraphQL\GraphQL;
use pjmd89\GraphQL\Utils\BuildSchema;
use pjmd89\GraphQL\Utils\SchemaPrinter;
use pjmd89\GraphQL\Error\FormattedError;

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header('Access-Control-Allow-Origin: '.$origin);
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

try {
    Api::init(pathinfo(__DIR__)['basename']);
    Api::setReferee(setAllReferee());
    //Api::setHelpers([new \api\helpers\Filter(), new \api\helpers\Pagination()]);
    $schemaString = getSchemas();
    $schema = BuildSchema::build($schemaString);

    if (count($_GET) == 0) {
        //$_SESSION['defaultSite'] = setSite();
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        $query = $input['query'] ?? null;
        $variables = $input['variables'] ?? null;
        $result = [];
        if ($_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
            $result = GraphQL::executeQuery($schema, $query, [], null, $variables);
        }
    } elseif (isset($_GET, $_GET['schema'])) {
        if (isset($_GET['save'])) {
            header('Content-Type: application/graphql');
            header('Content-Disposition: attachment; filename=schema.gql');
            header('Pragma: no-cache');
            $result = $schemaString;
        } else {
            $result = SchemaPrinter::doPrint($schema, ['commentDescriptions' => true]);
            $result = '<pre>'.$result.'</pre>';
        }
        echo $result;
        exit();
    }
} catch (\Exception $e) {
    $result = ['errors' => [FormattedError::createFromException($e)]];
}

function setAllReferee() {
    $path = __DIR__.'/referee';
    $return = [];
    if(file_exists($path)){
        $files = scandir($path);
        foreach ($files as $file) {
            $file = $path.'/'.$file;
            $className = pathinfo($file)['filename'];
            $class = '\\api\\referee\\'.$className;
            $implement = 'pjmd89\apiGraphQL\Interfaces\Referee';

            if (!is_dir($file) && class_exists($class) && in_array($implement, class_implements($class))) {
                array_push($return, new $class());
            }
        }
    }
    

    return $return;
}

function setSite() {
    $sites = (new \api\components\site\Query())->sites([]);
    $thisSite = array_search($_SERVER['SERVER_NAME'], array_column($sites, 'url'));

    if ($thisSite === false) {
        session_destroy();
        throw new \api\helpers\Error('Forbidden');
    }

    return $sites[$thisSite]['_id']->__toString();
}

function getSchemas() {
    $path = __DIR__.'/schemas';
    $files = scandir($path);
    $schema = '';

    foreach ($files as $file) {
        if (strtolower(pathinfo($path.'/'.$file)['extension']) == 'gql') {
            $schema .= file_get_contents($path.'/'.$file)."\n";
        }
    }

    return $schema;
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($result);