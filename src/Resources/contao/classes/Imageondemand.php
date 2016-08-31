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
 

namespace Agoat;

use Symfony\Component\HttpFoundation\Request;
use Contao\Database;


class Imageondemand
{
	// generate special image path to on demand script instead of generating the image now
	public function saveResizeConfiguration($OriginalPath, $TargetWidth, $TargetHeight, $ResizeMode, $CacheName, $fileObj, $TargetPath, $ImageData) 
	{ 
		// exclude some cases where we need to resize the image immediately
		if ($TargetPath) return false; // images are uploaded
		if (strpos($_SERVER['REQUEST_URI'], 'assets') !== false) return false; // image edit mode in file manager
		if ($TargetWidth == 699 && $TargetHeight == 524) return false; // image edit mode in file manager
		if ($TargetWidth == 80 && $TargetHeight == 60) return false; // preview images in image src selection wizard
		if ($fileObj->extension == 'svg') return false; // sgv images

		// get image cachename
		$imageName = array_reverse(explode('/', $CacheName))[0];
	
		// save image vars to database
		$db = Database::getInstance();
		$imageData = $db	->prepare("INSERT IGNORE INTO tl_image_generation (name, width, height, resizeMode, zoom, importantPartX, importantPartY, importantPartWidth, importantPartHeight, OriginalPath) VALUES (?,?,?,?,?,?,?,?,?, ?)")
							->execute($imageName,$TargetWidth,$TargetHeight,$ImageData->getResizeMode(), $ImageData->getZoomLevel(),$ImageData->getImportantPart()[x],$ImageData->getImportantPart()[y],$ImageData->getImportantPart()[width],$ImageData->getImportantPart()[height],$OriginalPath);

		// give back the special path with the image name
		return ('assets/images/g/' . $imageName);
	}

	
	// purge the image generation table
	public function purgeImageGenerationTable() 
	{ 
		// Truncate the tl_image_generation table
		$db = Database::getInstance();
		$db->execute("TRUNCATE TABLE tl_image_generation");
		
		// Add a log entry
		\System::log('Purged the image generation table', __METHOD__, TL_CRON);
		
	}
}

