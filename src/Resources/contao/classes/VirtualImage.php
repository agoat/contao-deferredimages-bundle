<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\DeferredImages;


/**
 * Virtual image handling
 *
 * @author Arne Stappen (alias aGOAT) <https://github.com/agoat>
 */
class VirtualImage extends \Contao\Image
{

	/**
	 * Generate an image tag and return it as string
	 *
	 * @param string $src        The image path
	 * @param string $alt        An optional alt attribute
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