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

namespace Agoat\DeferredImagesBundle\ContaoManager;

use Agoat\DeferredImagesBundle\AgoatDeferredImagesBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Plugin for the Contao Manager.
 *
 * @return BundleConfig
 */
class Plugin implements BundlePluginInterface, RoutingPluginInterface
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


	/**
	 * {@inheritdoc}
	 */
	public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
	{
		return $resolver
			->resolve('@contao.routing.image_loader', 'contao_deferredimages')
			->load('@contao.routing.image_loader')
		;
	}
}
