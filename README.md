# Contao 4 library
defer image calculation

___

###Install

Add to app/AppKernel.php (after ContaoCoreBundle)
```
new Agoat\DeferredImagesBundle\AgoatDeferredImagesBundleBundle(),
```

Add to app/config/routing.yml (at the beginning)
```
AgoatContaoDeferredImagesBundle:
    resource: "@AgoatImageondemandBundle/Resources/config/routing.yml"
```
