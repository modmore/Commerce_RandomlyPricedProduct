<?php
/* Get the core config */
if (!file_exists(dirname(dirname(__FILE__)).'/config.core.php')) {
    die('ERROR: missing '.dirname(dirname(__FILE__)).'/config.core.php file defining the MODX core path.');
}

echo "<pre>";
/* Boot up MODX */
echo "Loading modX...\n";
require_once dirname(dirname(__FILE__)) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
echo "Initializing manager...\n";
$modx->initialize('mgr');
$modx->getService('error','error.modError', '', '');
$modx->setLogTarget('HTML');

$componentPath = dirname(__DIR__);

//$modx->setOption('commerce_randomlypricedproduct.core_path', $componentPath.'/core/components/commerce_randomlypricedproduct/');
//$commerce_randomlypricedproduct = $modx->getService('commerce_randomlypricedproduct','Commerce', $componentPath.'/core/components/commerce_randomlypricedproduct/model/commerce_randomlypricedproduct/');


/* Namespace */
if (!createObject('modNamespace',array(
    'name' => 'commerce_randomlypricedproduct',
    'path' => $componentPath.'/core/components/commerce_randomlypricedproduct/',
    'assets_path' => $componentPath.'/assets/components/commerce_randomlypricedproduct/',
),'name', false)) {
    echo "Error creating namespace commerce_randomlypricedproduct.\n";
}

/* Path settings */
if (!createObject('modSystemSetting', array(
    'key' => 'commerce_randomlypricedproduct.core_path',
    'value' => $componentPath.'/core/components/commerce_randomlypricedproduct/',
    'xtype' => 'textfield',
    'namespace' => 'commerce_randomlypricedproduct',
    'area' => 'Paths',
    'editedon' => time(),
), 'key', false)) {
    echo "Error creating commerce_randomlypricedproduct.core_path setting.\n";
}

if (!createObject('modSystemSetting', array(
    'key' => 'commerce_randomlypricedproduct.assets_path',
    'value' => $componentPath.'/assets/components/commerce_randomlypricedproduct/',
    'xtype' => 'textfield',
    'namespace' => 'commerce_randomlypricedproduct',
    'area' => 'Paths',
    'editedon' => time(),
), 'key', false)) {
    echo "Error creating commerce_randomlypricedproduct.assets_path setting.\n";
}

/* Fetch assets url */
$requestUri = $_SERVER['REQUEST_URI'] ?: __DIR__ . '/_bootstrap/index.php';
$bootstrapPos = strpos($requestUri, '_bootstrap/');
$requestUri = rtrim(substr($requestUri, 0, $bootstrapPos), '/').'/';
$assetsUrl = "{$requestUri}assets/components/commerce_randomlypricedproduct/";

if (!createObject('modSystemSetting', array(
    'key' => 'commerce_randomlypricedproduct.assets_url',
    'value' => $assetsUrl,
    'xtype' => 'textfield',
    'namespace' => 'commerce_randomlypricedproduct',
    'area' => 'Paths',
    'editedon' => time(),
), 'key', false)) {
    echo "Error creating commerce_randomlypricedproduct.assets_url setting.\n";
}

$path = $modx->getOption('commerce.core_path', null, MODX_CORE_PATH . 'components/commerce/') . 'model/commerce/';
$params = ['mode' => $modx->getOption('commerce.mode')];
/** @var Commerce|null $commerce */
$commerce = $modx->getService('commerce', 'Commerce', $path, $params);
if (!($commerce instanceof Commerce)) {
    die("Couldn't load Commerce class");
}

// Make sure our module can be loaded. In this case we're using a composer-provided PSR4 autoloader.
include $componentPath . '/core/components/commerce_randomlypricedproduct/vendor/autoload.php';

// Grab the path to our namespaced files
$modulePath = $componentPath . '/core/components/commerce_randomlypricedproduct/src/';

// Instruct Commerce to load modules from our directory, providing the base namespace and module path twice
$commerce->loadModulesFromDirectory($modulePath, 'modmore\Example\RandomlyPricedProduct\\', $modulePath);

// Clear the cache
$modx->cacheManager->refresh();

echo "Done.";


/**
 * Creates an object.
 *
 * @param string $className
 * @param array $data
 * @param string $primaryField
 * @param bool $update
 * @return bool
 */
function createObject ($className = '', array $data = array(), $primaryField = '', $update = true) {
    global $modx;
    /* @var xPDOObject $object */
    $object = null;

    /* Attempt to get the existing object */
    if (!empty($primaryField)) {
        if (is_array($primaryField)) {
            $condition = array();
            foreach ($primaryField as $key) {
                $condition[$key] = $data[$key];
            }
        }
        else {
            $condition = array($primaryField => $data[$primaryField]);
        }
        $object = $modx->getObject($className, $condition);
        if ($object instanceof $className) {
            if ($update) {
                $object->fromArray($data);
                return $object->save();
            } else {
                $condition = $modx->toJSON($condition);
                echo "Skipping {$className} {$condition}: already exists.\n";
                return true;
            }
        }
    }

    /* Create new object if it doesn't exist */
    if (!$object) {
        $object = $modx->newObject($className);
        $object->fromArray($data, '', true);
        return $object->save();
    }

    return false;
}
