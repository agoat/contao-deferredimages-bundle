<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\DeferredImagesBundle\Image;

use Contao\Config;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\File;
use Contao\Image as LegacyImage;
use Contao\Image\ImageInterface;
use Contao\Image\ResizeConfigurationInterface;
use Contao\Image\ResizeCoordinatesInterface;
use Contao\Image\ResizeOptionsInterface;
use Contao\Image\ResizeCalculatorInterface;
use Contao\Image\Resizer as ImageResizer;
use Contao\System;
use Symfony\Component\Filesystem\Filesystem;
use Imagine\Gd\Imagine as GdImagine;

/**
 * Resizes Image objects via Contao\Image\Resizer and executes legacy hooks.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class DeferredResizer extends ImageResizer
{
    use FrameworkAwareTrait;

    /**
     * @var LegacyImage
     */
    private $legacyImage;

    /**
     * @var ResizeCalculatorInterface
     */
    private $calculator;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * Constructor.
     *
     * @param string                         $cacheDir
     * @param ResizeCalculatorInterface|null $calculator
     * @param Filesystem|null                $filesystem
     */
    public function __construct($cacheDir, ResizeCalculatorInterface $calculator = null, Filesystem $filesystem = null)
    {
        if (null === $calculator) {
            $calculator = new ResizeCalculator();
        }
        if (null === $filesystem) {
            $filesystem = new Filesystem();
        }
        $this->cacheDir = (string) $cacheDir;
        $this->calculator = $calculator;
        $this->filesystem = $filesystem;
    }

	/**
     * {@inheritdoc}
     */
    public function resize(ImageInterface $image, ResizeConfigurationInterface $config, ResizeOptionsInterface $options)
    {
        if ($image->getDimensions()->isUndefined() || $config->isEmpty()) {
            $image = $this->createImage($image, $image->getPath());
        } else {
            $image = $this->deferredResize($image, $config, $options);
        }
		
		return $image;
		
    }

    /**
     * Processes the resize and executes it if not already cached.
     *
     * @param ImageInterface               $image
     * @param ResizeConfigurationInterface $config
     * @param ResizeOptionsInterface       $options
     *
     * @return ImageInterface
     */
    private function deferredResize(ImageInterface $image, ResizeConfigurationInterface $config, ResizeOptionsInterface $options)
    {
		$coordinates = $this->calculator->calculate($config, $image->getDimensions(), $image->getImportantPart());

		// Skip resizing if it would have no effect
        if ($coordinates->isEqualTo($image->getDimensions()->getSize()) && !$image->getDimensions()->isRelative()) {
			return $this->createImage($image, $image->getPath());
		}
		
		$cachePath = $this->cacheDir.'/'.$this->createCachePath($image->getPath(), $coordinates);
        
		if ($this->filesystem->exists($cachePath) && !$options->getBypassCache()) {
            return $this->createImage($image, $cachePath);
        }

		$deferredPath = $this->cacheDir.'/'.$this->createDeferredPath($image->getPath(), $coordinates);
 		dump('deferred');
		
		return $this->createImage($image, $deferredPath);
	}
	
    /**
     * Creates a new image instance for the specified path.
     *
     * @param ImageInterface $image
     * @param string         $path
     *
     * @return ImageInterface
     *
     * @internal Do not call this method in your code; it will be made private in a future version
     */
    protected function createImage(ImageInterface $image, $path)
    {
        return new Image($path, $image->getImagine(), $this->filesystem);
    }

    /**
     * Creates the target cache path.
     *
     * @param string                     $path
     * @param ResizeCoordinatesInterface $coordinates
     *
     * @return string The realtive target path
     */
    private function createDeferredPath($path, ResizeCoordinatesInterface $coordinates)
    {
        $pathinfo = pathinfo($path);
        $hash = substr(md5(implode('|', [$path, filemtime($path), $coordinates->getHash()])), 0, 9);
        return 'g/'.$pathinfo['filename'].'-'.substr($hash, 1).'.'.$pathinfo['extension'];
    }
 
	/**
     * Creates the target cache path.
     *
     * @param string                     $path
     * @param ResizeCoordinatesInterface $coordinates
     *
     * @return string The realtive target path
     */
    private function createCachePath($path, ResizeCoordinatesInterface $coordinates)
    {
        $pathinfo = pathinfo($path);
        $hash = substr(md5(implode('|', [$path, filemtime($path), $coordinates->getHash()])), 0, 9);
        return substr($hash, 0, 1).'/'.$pathinfo['filename'].'-'.substr($hash, 1).'.'.$pathinfo['extension'];
    }

	
 }
