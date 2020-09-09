<?php

declare(strict_types=1);

/*
 * This file is part of markenzoo/contao-isotope-paypal-express-bundle.
 *
 * Copyright (c) Felix Kästner
 *
 * @package   markenzoo/contao-isotope-paypal-express-bundle
 * @author    Felix Kästner <hello@felix-kaestner.com>
 * @copyright 2020 Felix Kästner
 * @license   https://github.com/markenzoo/contao-isotope-paypal-express-bundle/blob/master/LICENSE LGPL-3.0-or-later
 */

namespace Markenzoo\ContaoIsotopePaypalExpressBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Markenzoo\ContaoIsotopePaypalExpressBundle\ContaoIsotopePaypalExpressBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoIsotopePaypalExpressBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, 'isotope']),
        ];
    }
}
