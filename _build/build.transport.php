<?php
/**
 * xFCP helper build script
 *
 * @package xFCPHelper
 * @subpackage build
 */

$tstart = explode(' ', microtime());
$tstart = $tstart[1] + $tstart[0];
set_time_limit(0);

/**
 * string getFileContent(string $filename = '') {
 *
 * Strip php tags from the content of the file filename
 * and return it.
 *
 * @param string $filename The name of the file.
 * @return string The file's content.
 * @author splittingred, modified by Jérôme Perrin
 */
function getFileContent($filename = '') {
    $o = file_get_contents($filename);
    $o = trim(str_replace(array('<?php','?>'),'',$o));
    return $o;
}

/* define package names */
define('PKG_NAME', 'xFCPHelper');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'rc1');

/* define build paths */
$root = dirname(dirname(__FILE__)).'/';
$sources = array (
    'root' => $root,
    'build' => $root .'_build/',
    'resolvers' => $root . '_build/resolvers/',
    'data' => $root . '_build/data/',
    'source_core' => $root.'core/components/'.PKG_NAME_LOWER,
    'source_assets' => $root.'assets/components/'.PKG_NAME_LOWER,
    'plugins' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/plugins/',
    'snippets' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/snippets/',
    'lexicon' => $root . 'core/components/'.PKG_NAME_LOWER.'/lexicon/',
    'docs' => $root.'core/components/'.PKG_NAME_LOWER.'/docs/',
    'model' => $root.'core/components/'.PKG_NAME_LOWER.'/model/',
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once dirname(__FILE__) . '/build.config.php';

/* instantiate modx */
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
echo 'Packing '.PKG_NAME_LOWER.'-'.PKG_VERSION.'-'.PKG_RELEASE.'<pre>'; flush();

/* start building */
$modx->loadClass('transport.modPackageBuilder','',false,true);
$builder = new modPackageBuilder($modx);
$builder->directory = dirname(dirname(__FILE__)).'/_packages/';
$builder->createPackage(PKG_NAME_LOWER,PKG_VERSION,PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER,false,true, '{core_path}components/'.PKG_NAME_LOWER.'/');
// $modx->getService('lexicon','modLexicon');

/* pack in plugins */
$plugins = include $sources['data'].'transport.plugins.php';
if (!is_array($plugins)) { $modx->log(modX::LOG_LEVEL_FATAL,'Adding plugins failed.'); }
$attributes= array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'PluginEvents' => array(
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array('pluginid','event'),
        ),
    ),
);
foreach ($plugins as $plugin) {
    $vehicle = $builder->createVehicle($plugin, $attributes);
    $builder->putVehicle($vehicle);
}
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($plugins).' plugins.'); flush();
unset($plugins,$plugin,$attributes);

/* create the category */
// $category = $modx->newObject('modCategory');
// $category->set('id',1);
// $category->set('category',PKG_NAME);
// $modx->log(modX::LOG_LEVEL_INFO,'Packaged in category.'); flush();

/* add snippets */
// $snippets = include $sources['data'].'transport.snippets.php';
// if (!is_array($snippets)) { $modx->log(modX::LOG_LEVEL_FATAL,'Adding snippets failed.'); }
// else {
//   $category->addMany($snippets,'Snippets');
//   $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($snippets).' snippets.'); flush();
//   unset($snippets);
// }

/* add chunks */
// $chunks = include $sources['data'].'transport.chunks.php';
// if (!is_array($chunks)) { $modx->log(modX::LOG_LEVEL_FATAL,'Adding chunks failed.'); }
// else {
//   $category->addMany($chunks);
//   $modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($chunks).' chunks.'); flush();
//   unset($chunks);
// }

/* create the category vehicle */
// $attr = array(
//   xPDOTransport::UNIQUE_KEY => 'category',
//   xPDOTransport::PRESERVE_KEYS => false,
//   xPDOTransport::UPDATE_OBJECT => true,
//   xPDOTransport::RELATED_OBJECTS => true,
//   xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
//     // 'Snippets' => array(
//     //   xPDOTransport::PRESERVE_KEYS => false,
//     //   xPDOTransport::UPDATE_OBJECT => true,
//     //   xPDOTransport::UNIQUE_KEY => 'name',
//     // ),
//     // 'Chunks' => array(
//     //   xPDOTransport::PRESERVE_KEYS => false,
//     //   xPDOTransport::UPDATE_OBJECT => true,
//     //   xPDOTransport::UNIQUE_KEY => 'name',
//     // ),
//   ),
// );

/* pack in the category and related objects */
// $vehicle = $builder->createVehicle($category, $attr);
// unset($category);

/* pack in resolvers */
$vehicle->resolve('file', array(
  'source' => $sources['source_core'],
  'target' => "return MODX_CORE_PATH . 'components/';",
));
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in resolvers.'); flush();

/* pack in the vehicule */
$builder->putVehicle($vehicle);
$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in vehicle.'); flush();
unset($vehicle);

/* pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
  'license' => file_get_contents($sources['docs'] . 'license.txt'),
  'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
  'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
));
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in package attributes.'); flush();

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO,'Packing...'); flush();
$builder->pack();
unset($builder);
$modx->log(modX::LOG_LEVEL_INFO,'Packed into zip archive.'); flush();

/* calculate build time */
$tend= explode(" ", microtime());
$tend= $tend[1] + $tend[0];
$totalTime= sprintf("%2.4f s",($tend - $tstart));
$modx->log(modX::LOG_LEVEL_INFO,"Package Built.<br>\nExecution time: {$totalTime}\n");
