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
 * Purge jobs
 */
$GLOBALS['TL_PURGE']['tables']['imagegeneration'] = array
(
	'callback' => array('Agoat\DeferredImages', 'purgeImageGenerationTable'),
	'affected' => array('tl_image_generation')
);

