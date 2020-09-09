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

namespace Markenzoo\ContaoIsotopePaypalExpressBundle\Paypal;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PayPalClient
{
    /**
     * Returns PayPal HTTP client instance with environment that has access
     * credentials context. Use this instance to invoke PayPal APIs, provided the
     * credentials have access.
     */
    public static function client(string $clientId, string $clientSecret, bool $debug = false): PayPalHttpClient
    {
        /**
         * Set up and return PayPal PHP SDK environment with PayPal access credentials.
         */
        $environment = $debug ? new SandboxEnvironment($clientId, $clientSecret) : new ProductionEnvironment($clientId, $clientSecret);

        return new PayPalHttpClient($environment);
    }
}
