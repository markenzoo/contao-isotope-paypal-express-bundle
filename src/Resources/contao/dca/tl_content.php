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

$GLOBALS['TL_DCA']['tl_content']['palettes']['paypal_express_button'] =
    '{type_legend},type;{paypal_express_legend},paypalExpressPaymentMethod,paypalExpressCheckoutPage,isoNotifications;{paypal_smart_buttons_legend},paypalSmartButtonColor,paypalSmartButtonShape,paypalSmartButtonSize,paypalSmartButtonLabel;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop;'
;

$GLOBALS['TL_DCA']['tl_content']['fields']['paypalExpressPaymentMethod'] = [
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['maxlength' => 16, 'tl_class' => 'w50'],
    'options_callback' => ['tl_content_paypal_express', 'getPaypalExpressPaymentMethods'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['paypalExpressCheckoutPage'] = [
        'exclude' => true,
        'inputType' => 'pageTree',
        'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
        'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['isoNotifications'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => ['NotificationCenter\tl_module', 'getNotificationChoices'],
    'eval' => ['multiple' => true, 'csv' => ',', 'includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'clr w50'],
    'sql' => "varchar(255) NOT NULL default ''",
    'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_nc_notification'],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['paypalSmartButtonColor'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonColor'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => $GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonColor']['options'],
    'eval' => ['includeBlankOption' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['paypalSmartButtonShape'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonShape'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => $GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonShape']['options'],
    'eval' => ['includeBlankOption' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['paypalSmartButtonSize'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonSize'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => $GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonSize']['options'],
    'eval' => ['includeBlankOption' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['paypalSmartButtonLabel'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonLabel'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => $GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonLabel']['options'],
    'eval' => ['includeBlankOption' => true, 'maxlength' => 16, 'tl_class' => 'w50'],
    'sql' => "varchar(16) NOT NULL default ''",
];

class tl_content_paypal_express extends Backend
{
    /**
     * Get all Payment Modules that are Paypal Express.
     */
    public function getPaypalExpressPaymentMethods(): array
    {
        $arrPaymentMethods = [];
        $objPayment = \Isotope\Model\Payment::findBy('type', 'paypal_express');
        foreach ($objPayment as $payment) {
            $arrPaymentMethods[$payment->id] = $payment->name.' (Id: '.$payment->id.')';
        }

        return $arrPaymentMethods;
    }
}
