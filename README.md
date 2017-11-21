# Deferred images library for Contao 4

[![Version](https://img.shields.io/packagist/v/agoat/contao-deferredimages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-deferredimages)
[![License](https://img.shields.io/packagist/l/agoat/contao-deferredimages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-deferredimages)
[![Downloads](https://img.shields.io/packagist/dt/agoat/contao-deferredimages.svg?style=flat-square)](http://packagist.org/packages/agoat/contao-deferredimages)

## About
Contao normally generates (resizes) the images on a page during page generation. This can lead to long page load times or, in the worst case, script timeouts due to server restrictions.

This library shifts the image calculation from page generation to the time when the image is loaded in the browser. As a result, pages will have very short loading times and script timeouts are avoided, even with hundreds of images on a page.


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


