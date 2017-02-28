<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @package  	 ImageOnDemand
 * @author   	 Arne Stappen
 * @license  	 LGPL-3.0+ 
 * @copyright	 Arne Stappen 2011-2016
 */
 
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
