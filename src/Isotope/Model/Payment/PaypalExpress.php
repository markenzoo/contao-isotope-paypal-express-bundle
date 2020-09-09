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

namespace Markenzoo\ContaoIsotopePaypalExpressBundle\Isotope\Model\Payment;

use Isotope\Model\Payment\PaypalPlus;

/**
 * PayPal Express payment method.
 *
 * Usually Paypal Express is used directly on the page of the cart to speed up the payment.
 * Using Paypal Express as a Payment Method inside the Checkout step would be the same as using
 * PaypalPlus, since we simply inherit from PaypalPlus here. This gives us  the ability to simply
 * include only a single PaypalExpress module which can be used to render smart buttons on the cart
 * page and the normal PaypalPlus Payment on the Checkout page.
 *
 * @see https://developer.paypal.com/docs/checkout/integrate/#2-add-the-paypal-javascript-sdk-to-your-web-page
 */
class PaypalExpress extends PaypalPlus
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'iso_payment_paypal_plus';
}
