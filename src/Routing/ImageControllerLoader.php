<?php

/*
 * This file is part of the contao permalink extension.
 *
 * Copyright (c) 2017 Arne Stappen
 *
 * @license LGPL-3.0+
 */

namespace Agoat\DeferredImagesBundle\Routing;

use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Adds routes for the Contao front end.
 *
 * @author Arne Stappen <https://github.com/agoat>
 */
class ImageControllerLoader extends Loader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
		$collection = new RouteCollection();
		
		$collection->add(
			'contao_deferredimages',
			new Route(
				'/assets/images/g/{name}', 
				array(
					'_scope' => ContaoCoreBundle::SCOPE_FRONTEND,
					'_controller' => 'AgoatDeferredImagesBundle:DeferredImages:resize',
				),
				array(
					'name' => '.*'
				)
			)
		);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'contao_deferredimages' === $type;
    }
}