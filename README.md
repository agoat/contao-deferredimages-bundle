# Contao 4 library
defer image calculation

___

###Install

Add to app/AppKernel.php (after ContaoCoreBundle)
```
new Agoat\DeferredImagesBundle\AgoatDeferredImagesBundle(),
```

Add to app/config/routing.yml (at the beginning)
```
AgoatContaoDeferredImagesBundle:
    resource: "@AgoatDeferredImagesBundle/Resources/config/routing.yml"
```
