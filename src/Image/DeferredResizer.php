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
     * @var ResizeCalculatorInterface
     */
	private $calculator;

	/**
     * @var Filesystem
     */
	private $filesystem;

    /**
     * @var string
     */
	private $cacheDir;

	/**
     * @var ResizeCoordinates
     */
    private $coordinates;

	/**
     * @var string
     */
    private $cachePath;

	/**
     * @var array
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
		
		parent::__construct($cacheDir, $calculator, $filesystem);
    }

	/**
     * {@inheritdoc}
     */
    public function resize(ImageInterface $image, ResizeConfigurationInterface $config, ResizeOptionsInterface $options)
    {
        if ($image->getDimensions()->isUndefined() || $config->isEmpty()) 
        {
            return parent::resize($image, $config, $options);
        } 
        else if ($options->getTargetPath() !== null) // Uploads
        {
            return parent::resize($image, $config, $options);
        }

        $this->coordinates = $this->calculator->calculate($config, $image->getDimensions(), $image->getImportantPart());
        $this->cachePath = $this->createCachePath($image->getPath(), $this->coordinates);
		
        if (in_array(strtolower(pathinfo($image->getPath(), PATHINFO_EXTENSION)), ['svg', 'svgz'])) // SVG images
        {
            return parent::resize($image, $config, $options);
        } 
        else if ($config->getWidth() == 699 && $config->getHeight() == 524) // Image editor in Filetree
        {
            return parent::resize($image, $config, $options);
        } 
        else 
        {
            return $this->deferreResize($image, $config, $options);
        }
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
    private function deferreResize(ImageInterface $image, ResizeConfigurationInterface $config, ResizeOptionsInterface $options)
    {
		if ($this->filesystem->exists($this->cacheDir.'/'.$this->cachePath) ) {
			return $this->createImage($image, $this->cacheDir.'/'.$this->cachePath);
		}
		
		// Create deferred image only once
		if (array_key_exists($this->cachePath, $this->deferredImageCache))
		{
			return $this->deferredImageCache[$this->cachePath];
		}
		else
		{
			$deferredImage = $this->createImage($image, $this->cacheDir.'/g/'.substr($this->cachePath, 1), $this->coordinates);
		
			// Save to database
			$db = Database::getInstance();
			
			$db	->prepare("INSERT IGNORE INTO tl_image_deferred (name, cachePath, filePath, sizeW, sizeH, cropX, cropY, cropW, cropH) VALUES (?,?,?,?,?,?,?,?,?)")
				->execute(
					basename($deferredImage->getPath()),
					$this->cachePath,
					$image->getPath(),
					$this->coordinates->getSize()->getWidth(),
					$this->coordinates->getSize()->getHeight(),
					$this->coordinates->getCropStart()->getX(),
					$this->coordinates->getCropStart()->getY(),
					$this->coordinates->getCropSize()->getWidth(),
					$this->coordinates->getCropSize()->getHeight()			
				);
			
			$this->deferredImageCache[$this->cachePath] = $deferredImage;

			// Disable page caching because of changing image urls
			global $objPage;

			if ($objPage)
			{
				$objPage->cache = false;
				$objPage->clientCache  = false;
			}
		
			// Return image (with virtual path)
			return $deferredImage;
		}
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
    private function createCachePath($path, ResizeCoordinatesInterface $coordinates)
    {
        $pathinfo = pathinfo($path);
        $hash = substr(md5(implode('|', [$path, filemtime($path), $coordinates->getHash()])), 0, 9);
        return substr($hash, 0, 1).'/'.$pathinfo['filename'].'-'.substr($hash, 1).'.'.$pathinfo['extension'];
    }
	
 }
