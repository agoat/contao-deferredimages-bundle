# Contao 4 deferred images extension
___


[![Version](https://img.shields.io/packagist/v/agoat/contao-deferredimages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-deferredimages)
[![License](httsp://img.shields.io/packagist/l/agoat/contao-deferredimages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-deferredimages)
[![Downloads](https://img.shields.io/packagist/dt/agoat/contao-deferredimages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-deferredimages)

---

## Install
### Contao manager
Search for the package and install it
```bash
agoat/contao-deferredimages
```

### Managed edition
Add the package
```bash
# Using the composer
composer require agoat/contao-deferredimages
```
Registration and configuration is done by the manager-plugin automatically.

### Standard edition
Add the package
```bash
# Using the composer
composer require agoat/contao-deferredimages
```
Register the bundle in the AppKernel
```php
# app/AppKernel.php
class AppKernel
{
    // ...
    public function registerBundles()
    {
        $bundles = [
            // ...
            // after Contao\CoreBundle\ContaoCoreBundle
            new Agoat\DeferredImagesBundle\AgoatDeferredImagesBundle(),
        ];
    }
}
```


