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

namespace Agoat\DeferredImagesBundle\Contao;


/**
 * Virtual image class
 */
class VirtualImage extends Image
{
	/**
	 * Generate an image tag and return it as string
	 *
	 * @param string $src The image path
	 * @param string $alt An optional alt attribute
	 * @param string $attributes A string of other attributes
	 *
	 * @return string The image HTML tag
	 */
	public static function getHtml($src, $alt='', $attributes='')
	{
		$src = static::getPath($src);

		if ($src == '')
		{
			return '';
		}

		// Return virtual (deferred) images without width/height
		if (strpos($src, '/g/') !== false)
		{
			return '<img src="' . $static . \System::urlEncode($src) . '" alt="' . \StringUtil::specialchars($alt) . '"' . (($attributes != '') ? ' ' . $attributes : '') . '>';
		}

		return parent::getHtml($src, $alt, $attributes);
	}
}