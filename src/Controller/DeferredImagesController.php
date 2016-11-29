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
use Contao\Image\ResizeOptions;
use Contao\Image\Image;
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
		$deferredImageConfig = $db	->prepare("SELECT * FROM tl_image_generation WHERE name=?")
									->limit(1)
									->execute($name);

		// process image
		if ($deferredImageConfig->numRows) 
		{
			$cachePath = 'assets/images/'.substr(strstr($name, '-'), 1, 1);
			
			if (!$filesystem->exists($cachePath)) {
				$filesystem->mkdir($cachePath);
			}
		
			$options = (new ResizeOptions())
					->setImagineOptions(\System::getContainer()->getParameter('contao.image.imagine_options'))
					->setBypassCache(\System::getContainer()->getParameter('contao.image.bypass_cache'));
				
			
			$imagineOptions = System::getContainer()->getParameter('contao.image.imagine_options');
			

			$image = $imagine->open($deferredImageConfig->path)
				->resize(new Box($deferredImageConfig->sizeW, $deferredImageConfig->sizeH))
				->crop(new Point($deferredImageConfig->cropX, $deferredImageConfig->cropY), new Box($deferredImageConfig->cropW, $deferredImageConfig->cropH))
			;
			
			if (isset($imagineOptions['interlace'])) {
				try {
					$image->interlace($imagineOptions['interlace']);
				} catch (ImagineRuntimeException $e) {
					// Ignore failed interlacing
				}
			}
			
			$image->save($cachePath.'/'.$name, $imagineOptions);

			// send image file
			return (new BinaryFileResponse($cachePath.'/'.$name));
			
		}
		// or throw a 404 error
		else
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}		
		
		return '';
	}
}
