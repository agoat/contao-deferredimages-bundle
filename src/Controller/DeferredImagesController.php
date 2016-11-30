<?php

/*
 * Contao ImageOnDemand Extension
 *
 * Copyright (c) 2016 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\DeferredImagesBundle\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Contao\CoreBundle\Exception\PageNotFoundException;

use Contao\Database;
use Imagine\Image\Box;
use Imagine\Image\Point;

/**
 * Handles the image on demand resizing.
 *
 * @author Arne Stappen <https://github.com/agoat>
 *
 */
class DeferredImagesController
{
 	/**
	 * Handles the image resize process.
	 *
	 * @author Arne Stappen <https://github.com/agoat>
	 */
	public function resizeAction($name)
	{
		$db = Database::getInstance();

		$imagine = \System::getContainer()->get('contao.image.imagine');
		$filesystem = \System::getContainer()->get('filesystem');
		
		// get image resize configuration
		$deferredImageConfig = $db	->prepare("SELECT * FROM tl_image_deferred WHERE name=?")
									->limit(1)
									->execute($name);

		// process image
		if ($deferredImageConfig->numRows) 
		{
			// Generate cache dir the same way as in the contao.image.resizer arguments
			$cacheDir = \System::getContainer()->getParameter('kernel.root_dir').'/../'.\System::getContainer()->getParameter('contao.image.target_path');

			if (!$filesystem->exists(dirname($cacheDir.'/'.$deferredImageConfig->cachePath))) {
				$filesystem->mkdir(dirname($cacheDir.'/'.$deferredImageConfig->cachePath));
			}

			$image = $imagine->open($deferredImageConfig->filePath)
							 ->resize(new Box($deferredImageConfig->sizeW, $deferredImageConfig->sizeH))
							 ->crop(new Point($deferredImageConfig->cropX, $deferredImageConfig->cropY), new Box($deferredImageConfig->cropW, $deferredImageConfig->cropH));
			
			$imagineOptions = \System::getContainer()->getParameter('contao.image.imagine_options');

			if (isset($imagineOptions['interlace'])) {
				try {
					$image->interlace($imagineOptions['interlace']);
				} catch (ImagineRuntimeException $e) {
					// Ignore failed interlacing
				}
			}
			
			// Save image to cache patch
			$image->save($cacheDir.'/'.$deferredImageConfig->cachePath, $imagineOptions);

			// Send image to browser
			return (new BinaryFileResponse($cacheDir.'/'.$deferredImageConfig->cachePath));
			
		}
		// or throw a 404 error
		else
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}		
	}
}
