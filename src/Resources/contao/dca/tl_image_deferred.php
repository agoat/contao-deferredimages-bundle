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
 * Table tl_image_deferred
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
