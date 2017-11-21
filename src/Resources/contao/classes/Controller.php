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

namespace Agoat\DeferredImages;

use Symfony\Component\HttpFoundation\Request;
use Contao\Database;


/**
 * Controller class
 */
class Controller
{
	/**
	 * Purge the deferred images table
	 */
	public function purgeDeferredImageTable() 
	{ 
		// Truncate the tl_image_generation table
		$db = Database::getInstance();
		$db->execute("TRUNCATE TABLE tl_image_deferred");
		
		// Add a log entry
		\System::log('Purged the deferred images table', __METHOD__, TL_CRON);
	}
}
