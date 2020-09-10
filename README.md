# Contao Isotope Paypal Express Bundle (WIP/EXPERIMENTAL)

[![Version](http://img.shields.io/packagist/v/markenzoo/contao-isotope-paypal-express-bundle.svg?style=for-the-badge&label=Latest)](http://packagist.org/packages/markenzoo/contao-isotope-paypal-express-bundle)
[![GitHub issues](https://img.shields.io/github/issues/markenzoo/contao-isotope-paypal-express-bundle?style=for-the-badge&logo=github)](https://github.com/markenzoo/contao-isotope-paypal-express-bundle/issues)
[![License](http://img.shields.io/packagist/l/markenzoo/contao-isotope-paypal-express-bundle?style=for-the-badge&label=License)](http://packagist.org/packages/markenzoo/contao-isotope-paypal-express-bundle)
[![Build Status](http://img.shields.io/travis/markenzoo/contao-isotope-paypal-express-bundle/main.svg?style=for-the-badge&logo=travis)](https://travis-ci.org/markenzoo/contao-isotope-paypal-express-bundle)
[![Downloads](http://img.shields.io/packagist/dt/markenzoo/contao-isotope-paypal-express-bundle?style=for-the-badge&label=Downloads)](http://packagist.org/packages/markenzoo/contao-isotope-paypal-express-bundle)

## Disclaimer

This is an experimental project. 
Use it at your own risk.
We do not provide any warranty for failure or corruption in any circumstance.

**Contributions in any form are welcome, simply submit a PR, it would be highly appreciated!**

## Features

Provide an integration for Smart Paypment Buttons from Paypal using the [JavaScript SDK](https://developer.paypal.com/docs/checkout/integrate/#2-add-the-paypal-javascript-sdk-to-your-web-page).

*Notice*
Currently only digital goods with no shipping are supported.

## Todo

 - Shipping Address
 - Billing Address 
 - Logging
 - Shipping Fees
 - Platform Fees
 - Authorization Workflow
 - [Pass the Partner Attribution ID](https://developer.paypal.com/docs/checkout/reference/server-integration/setup-sdk/#pass-the-partner-attribution-id)

## Requirements

- PHP >=7.2
- Contao ~4.9 LTS

## Install

### Managed edition

When using the managed edition it's pretty simple to install the package. Just search for the package in the
Contao Manager and install it. Alternatively you can use the CLI.

```bash
# Using the contao manager
$ php contao-manager.phar.php composer require markenzoo/contao-isotope-paypal-express-bundle

# Using composer directly
$ php composer.phar require markenzoo/contao-isotope-paypal-express-bundle

# Using global composer installation
$ composer require markenzoo/contao-isotope-paypal-express-bundle
```

### Symfony application

If you use Contao in a symfony application without contao/manager-bundle, you have to register the bundle manually:

```php

class AppKernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Markenzoo\ContaoIsotopePaypalExpressBundle\ContaoIsotopePaypalExpressBundle()
        ];
    }
}

```

## Note to self

Run the PHP-CS-Fixer and the unit test before you release your bundle:

```bash
vendor/bin/php-cs-fixer fix -v
vendor/bin/phpunit
vendor/bin/psalm
vendor/bin/psalter --issues=all --dry-run
```
