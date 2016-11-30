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
 

namespace Agoat\DeferredImages;

use Symfony\Component\HttpFoundation\Request;
use Contao\Database;


class Controller
{
	// purge the image generation table
	public function purgeDeferredImageTable() 
	{ 
		// Truncate the tl_image_generation table
		$db = Database::getInstance();
		$db->execute("TRUNCATE TABLE tl_image_deferred");
		
		// Add a log entry
		\System::log('Purged the deferred images table', __METHOD__, TL_CRON);
		
	}
}
