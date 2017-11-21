<?php

/*
 * Deferred images library for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2017
 * @package    contao-deferredimages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */


/**
 * Set global class names
 */
class_alias('Agoat\DeferredImages\VirtualImage', 'Image');
 

/**
 * Register back end module (additional css)
 */
$GLOBALS['BE_MOD']['content']['article']['stylesheet'][] = 'bundles/agoatdeferredimages/style.css';

$bundles = \System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoNewsBundle']))
{
	$GLOBALS['BE_MOD']['content']['news']['stylesheet'][] = 'bundles/agoatdeferredimages/style.css';
}


/**
 * Purge jobs
 */
$GLOBALS['TL_PURGE']['tables']['deferredimages'] = array
(
	'callback' => array('Agoat\\DeferredImages\\Controller', 'purgeDeferredImageTable'),
	'affected' => array('tl_image_deferred')
);
