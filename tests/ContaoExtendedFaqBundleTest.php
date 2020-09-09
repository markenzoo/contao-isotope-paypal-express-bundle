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

namespace Markenzoo\ContaoIsotopePaypalExpressBundle\Tests;

use Markenzoo\ContaoIsotopePaypalExpressBundle\ContaoIsotopePaypalExpressBundle;
use PHPUnit\Framework\TestCase;

class ContaoExtendedFaqBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new ContaoIsotopePaypalExpressBundle();

        $this->assertInstanceOf('Markenzoo\ContaoIsotopePaypalExpressBundle\ContaoIsotopePaypalExpressBundle', $bundle);
    }
}
