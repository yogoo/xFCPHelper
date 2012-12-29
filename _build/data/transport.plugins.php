<?php
/**
 * xFCPhelper build script
 *
 * @package xFPCHelper
 * @subpackage build
 */

$plugins = array();

/* create the plugin object */
$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->set('id',1);
$plugins[0]->set('name',PKG_NAME);
$plugins[0]->set('description',"Extend xFCP by adding support for the following Resources tree's context menu actions: publish, unpublish, delete, undelete.");
$plugins[0]->set('plugincode', getFileContent($sources['plugins'].PKG_NAME_LOWER.'.plugin.php'));
$plugins[0]->set('category', 0);

/* set events */
$events = array();
$events['OnDocPublished']= $modx->newObject('modPluginEvent');
$events['OnDocPublished']->fromArray(array(
    'event' => 'OnDocPublished',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);
$events['OnDocUnPublished']= $modx->newObject('modPluginEvent');
$events['OnDocUnPublished']->fromArray(array(
    'event' => 'OnDocUnPublished',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);
$events['OnResourceDelete']= $modx->newObject('modPluginEvent');
$events['OnResourceDelete']->fromArray(array(
    'event' => 'OnResourceDelete',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);
$events['OnResourceUndelete']= $modx->newObject('modPluginEvent');
$events['OnResourceUndelete']->fromArray(array(
    'event' => 'OnResourceUndelete',
    'priority' => 0,
    'propertyset' => 0,
),'',true,true);

/* attach events */
if (is_array($events) && !empty($events)) {
    $plugins[0]->addMany($events);
    $modx->log(xPDO::LOG_LEVEL_INFO,'Packaged in '.count($events).' Plugin Events for '.PKG_NAME.'.'); flush();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR,'Could not find plugin events for '.PKG_NAME.'!');
}

unset($events);

return $plugins;
