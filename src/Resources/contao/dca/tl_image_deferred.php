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
 * Table tl_cron
 */
$GLOBALS['TL_DCA']['tl_image_deferred'] = array
(

	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'name' => 'unique'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'                     => "int(10) unsigned NOT NULL auto_increment"
		),
		'name' => array
		(
			'sql'                     => "varchar(128) NULL"
		),
		'cachePath' => array
		(
			'sql'                     => "varchar(255) NULL"
		),
		'filePath' => array
		(
			'sql'                     => "varchar(1022) NULL"
		),
		'sizeW' => array
		(
			'sql'                     => "int(10) NULL"
		),
		'sizeH' => array
		(
			'sql'                     => "int(10) NULL"
		),
		'cropX' => array
		(
			'sql'                     => "int(10) NULL"
		),
		'cropY' => array
		(
			'sql'                     => "int(10) NULL"
		),
		'cropW' => array
		(
			'sql'                     => "int(10) NULL"
		),
		'cropH' => array
		(
			'sql'                     => "int(10) NULL"
		),
	)
);
