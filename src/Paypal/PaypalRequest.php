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

use Contao\System;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Config;

class PaypalRequest
{
    /**
     * Setting up the JSON request body for creating the order with minimum request body.
     *
     * @param IsotopePurchasableCollection $order
     *
     * @see https://developer.paypal.com/docs/api/orders/v2/
     *
     * @return array
     */
    public static function buildRequestBody(IsotopePurchasableCollection $objCollection): array
    {
        $currency_code = $objCollection->getCurrency();
        $items = [];
        $item_total = 0;
        $tax_total = 0;

        foreach ($objCollection->getItems() as $item) {
            /*
             * Sum up the total of all items and the total tax, since that is
             * validated by Paypal
             * @see https://developer.paypal.com/docs/api/orders/v2/#definition-purchase_unit_request
             */
            $item_total += $item->getTaxFreeTotalPrice();
            $tax_total += $item->getTotalPrice() - $item->getTaxFreeTotalPrice();

            /**
             * Define each Product as Item.
             *
             * @see https://developer.paypal.com/docs/api/orders/v2/#definition-item
             */
            $row = [
              'name' => strip_tags($item->name),
              'unit_amount' => [
                'currency_code' => $currency_code,
                'value' => number_format($item->getTaxFreePrice(), 2),
              ],
              'tax' => [
                'currency_code' => $currency_code,
                'value' => number_format($item->getPrice() - $item->getTaxFreePrice(), 2),
              ],
              'quantity' => $item->quantity,
            ];

            if ($item->sku) {
                $row['sku'] = $item->sku;
            }

            $items[] = $row;
        }

        /**
         * Set up the breakdoen of the total
         * Since each item defines a unit amount, we have to specify
         * item_total which must equal the sum of
         * (items[].unit_amount * items[].quantity) for all items and
         * tax_total which must be the sum of (items[].tax * items[].quantity)
         * for all items.
         *
         * @see https://developer.paypal.com/docs/api/orders/v2/#definition-amount_breakdown
         */
        $breakdown = [
          'item_total' => [
            'currency_code' => $currency_code,
            'value' => number_format($item_total, 2),
          ],
          'tax_total' => [
            'currency_code' => $currency_code,
            'value' => number_format($tax_total, 2),
          ],
        ];

        // TODO: add Shipping - currently only digital goods with no shipping are supported
        if ($objCollection->hasShipping()) {
            /*
             * @psalm-suppress DeprecatedMethod
             */
            System::log(
              'Shipping is currently no supported.',
              __METHOD__,
              TL_ERROR
          );
        }

        /**
         * Set the amount object.
         *
         * @see https://developer.paypal.com/docs/api/orders/v2/#definition-amount_with_breakdown
         */
        $amount = [
          'currency_code' => $currency_code,
          'value' => number_format($objCollection->getTotal(), 2),
          'breakdown' => $breakdown,
        ];

        // TODO: set billing and shipping Address
        // $billingAddress = $objCollection->getBillingAddress();
        // $shippingAddress = $objCollection->getShippingAddress();

        /** @var Config $objConfig */
        $objConfig = $objCollection->getConfig();

        $application_context = [
          'user_action' => 'PAY_NOW',
        ];

        // Set company name, if it is set in the shop configuration
        if (!empty($objConfig->company)) {
            $application_context['brand_name'] = $objConfig->company;
        }

        // Set locale, if it is set in the shop configuration
        if (!empty($objConfig->country)) {
            $application_context['locale'] = $objConfig->country;
        }

        return [
          'intent' => 'CAPTURE',
          'application_context' => $application_context,
          'purchase_units' => [
              0 => [
                  'amount' => $amount,
                  'items' => $items,
                ],
            ],
        ];
    }
}
