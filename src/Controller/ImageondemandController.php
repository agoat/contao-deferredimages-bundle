<?php

/*
 * Contao ImageOnDemand Extension
 *
 * Copyright (c) 2016 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\ImageondemandBundle\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Contao\CoreBundle\Exception\PageNotFoundException;

use Contao\Database;
use Contao\File;
use Contao\Image;


/**
 * Handles the image on demand resizing.
 *
 * @author Arne Stappen <https://github.com/agoat>
 *
 */
class ImageondemandController
{
 	/**
	 * Handles the image resize process.
	 *
	 * @author Arne Stappen <https://github.com/agoat>
	 */
	public function resizeAction($imgID)
	{
		
		$db = Database::getInstance();

		// get image resize configuration
		$resizeConfiguration = $db	->prepare("SELECT width, height, resizeMode, zoom, importantPartX, importantPartY, importantPartWidth, importantPartHeight, OriginalPath FROM tl_image_generation WHERE name=?")
									->limit(1)
									->execute($imgID);
		
		// process image
		if ($resizeConfiguration->numRows) 
		{
			// resize image
			$imageObj = new Image(new File($resizeConfiguration->OriginalPath));
			$imageSRC = $imageObj	->setTargetWidth($resizeConfiguration->width)
									->setTargetHeight($resizeConfiguration->height)
									->setResizeMode($resizeConfiguration->resizeMode)
									->setZoomLevel($resizeConfiguration->zoom)
									->setImportantPart(array('x' => $resizeConfiguration->importantPartX, 'y' => $resizeConfiguration->importantPartY, 'width' => $resizeConfiguration->importantPartWidth, 'height' => $resizeConfiguration->importantPartHeight))
									->executeResize()
									->getResizedPath();
		
			// send image file
			return (new BinaryFileResponse($imageSRC));
			
		}
		// or throw a 404 error
		else
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}		
	}
}
