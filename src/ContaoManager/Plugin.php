<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Agoat\DeferredImagesBundle\ContaoManager;

use Agoat\DeferredImagesBundle\AgoatDeferredImagesBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

/**
 * Plugin for the Contao Manager.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(AgoatDeferredImagesBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['deferredimages']),
        ];
    }
	
	public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
	{
		return $resolver
			->resolve(__DIR__ . '/../Resources/config/routing.yml')
			->load(__DIR__ . '/../Resources/config/routing.yml')
		;
	}
}