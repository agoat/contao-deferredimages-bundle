<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\DeferredImagesBundle\Image;

use Contao\Image\Image;
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
use Contao\Database;
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
     * @var string
     */
    private $deferredImageCache = array();

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
dump($options->getTargetPath());
		if ($image->getDimensions()->isUndefined() || $config->isEmpty()) 
		{
            $image = $this->createImage($image, $image->getPath());
        } 
		else if ($options->getTargetPath() !== null) 
		{
            $image = $this->processResize($image, $config, $options);
		} 
		else if ($config->getWidth() == 699 && $config->getHeight() == 524) 
		{
            $image = $this->processResize($image, $config, $options);
		} 
		else 
		{
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
		
		$deferredCacheKey = $image->getPath().$coordinates->getHash();
		
		// Create deferred image only once
		if (array_key_exists($image->getPath().$coordinates->getHash(), $this->deferredImageCache))
		{
			return $this->deferredImageCache[$deferredCacheKey];
		}
		else
		{
			// Skip resizing if it would have no effect
			if ($coordinates->isEqualTo($image->getDimensions()->getSize()) && !$image->getDimensions()->isRelative()) {
				return $this->createImage($image, $image->getPath());
			}
			
			$cachePath = $this->cacheDir.'/'.$this->createCachePath($image->getPath(), $coordinates);
			
			if ($this->filesystem->exists($cachePath) && !$options->getBypassCache()) {
				return $this->createImage($image, $cachePath);
			}

			$deferredPath = $this->cacheDir.'/'.$this->createDeferredPath($image->getPath(), $coordinates);
			
			$deferredImage = $this->createImage($image, $deferredPath, $coordinates);
		
			// Save to database
			$db = Database::getInstance();
			
			$db	->prepare("INSERT IGNORE INTO tl_image_generation (name, path, sizeW, sizeH, cropX, cropY, cropW, cropH) VALUES (?,?,?,?,?,?,?,?)")
				->execute(
					basename($deferredImage->getPath()),
					$image->getPath(),
					$coordinates->getSize()->getWidth(),
					$coordinates->getSize()->getHeight(),
					$coordinates->getCropStart()->getX(),
					$coordinates->getCropStart()->getY(),
					$coordinates->getCropSize()->getWidth(),
					$coordinates->getCropSize()->getHeight()			
				);
			
			$this->deferredImageCache[$deferredCacheKey] = $deferredImage;
		}
	
		// Return Image (with virtual path)
		return $deferredImage;
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
    private function processResize(ImageInterface $image, ResizeConfigurationInterface $config, ResizeOptionsInterface $options)
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

        return $this->executeResize($image, $coordinates, $cachePath, $options);
    }

    /**
     * Executes the resize operation via Imagine.
     *
     * @param ImageInterface             $image
     * @param ResizeCoordinatesInterface $coordinates
     * @param string                     $path
     * @param ResizeOptionsInterface     $options
     *
     * @return ImageInterface
     *
     * @internal Do not call this method in your code; it will be made private in a future version
     */
    protected function executeResize(ImageInterface $image, ResizeCoordinatesInterface $coordinates, $path, ResizeOptionsInterface $options)
    {
        if (!$this->filesystem->exists(dirname($path))) {
            $this->filesystem->mkdir(dirname($path));
        }

        $imagineOptions = $options->getImagineOptions();

        $imagineImage = $image
            ->getImagine()
            ->open($image->getPath())
            ->resize($coordinates->getSize())
            ->crop($coordinates->getCropStart(), $coordinates->getCropSize())
        ;

        if (isset($imagineOptions['interlace'])) {
            try {
                $imagineImage->interlace($imagineOptions['interlace']);
            } catch (ImagineRuntimeException $e) {
                // Ignore failed interlacing
            }
        }

        $imagineImage->save($path, $imagineOptions);

        return $this->createImage($image, $path);
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
    protected function createImage(ImageInterface $image, $path, $coordinates = null)
    {
        return new Image($path, $image->getImagine(), $this->filesystem, $coordinates);
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