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

namespace Agoat\DeferredImagesBundle\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Database;
use Imagine\Image\Box;
use Imagine\Image\Point;


/**
 * Handles the deferred images
 *
 */
class DeferredImagesController
{
 	/**
	 * Renders the image
	 *
	 * @param string $name The image name
	 *
	 * @return Response The resized image as BinaryFileResponse
	 */
	public function resizeAction($name)
	{
		$db = Database::getInstance();

		$imagine = \System::getContainer()->get('contao.image.imagine');
		$filesystem = \System::getContainer()->get('filesystem');
		
		// Get image resizer configuration
		$deferredImageConfig = $db	->prepare("SELECT * FROM tl_image_deferred WHERE name=?")
									->limit(1)
									->execute($name);

		// Render the image
		if ($deferredImageConfig->numRows) 
		{
			// Generate cache dir the same way as in the contao.image.resizer arguments
			$cacheDir = \System::getContainer()->getParameter('contao.image.target_dir');

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
			return ((new BinaryFileResponse($cacheDir.'/'.$deferredImageConfig->cachePath))->setPrivate());
			
		}
		// Or throw a 404 error
		else
		{
			throw new PageNotFoundException('Page not found: ' . \Environment::get('uri'));
		}		
	}
}
