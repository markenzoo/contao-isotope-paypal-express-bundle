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

/*
 * Legend
 */
$GLOBALS['TL_LANG']['tl_content']['paypal_express_legend'] = 'Paypal Express - Einstellungen';
$GLOBALS['TL_LANG']['tl_content']['paypal_smart_buttons_legend'] = 'Paypal Smart Buttons - Einstellungen';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_content']['paypalExpressPaymentMethod'] = ['Paypal Express Bezahlungsmodul', 'Wählen Sie das dazugehörige Paypal Express Bezahlungsmodul aus.'];
$GLOBALS['TL_LANG']['tl_content']['paypalExpressCheckoutPage'] = ['Paypal Express Checkout-Seite', 'Wählen Sie die Seite aus, zu der ein Nutzer weitergeleitet werden soll, wenn er die Bestellnug abschließt.'];
$GLOBALS['TL_LANG']['tl_content']['isoNotifications'] = ['Benachrichtigung', 'Wählen Sie eine Benachrichtigung aus, die ausgeführt wird, wenn eine Bestellnug abgeschlossen wird.'];

$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonColor'] = ['Paypal Smart Button Farbe', 'Wählen Sie die Farbe des Paypal Smart Button aus.'];
$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonColor']['options'] = [
    'gold' => 'Gold',
    'blue' => 'Blau',
    'silver' => 'Silber',
    'white' => 'Weiß',
    'black' => 'Schwarz',
];

$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonShape'] = ['Paypal Smart Button Form', 'Wählen Sie die Form des Paypal Smart Button aus.'];
$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonShape']['options'] = [
    'pill' => 'Rund',
    'rect' => 'Quadratisch',
];

$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonSize'] = ['Paypal Smart Button Größe', 'Wählen Sie die Größe des Paypal Smart Button aus.'];
$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonSize']['options'] = [
    'small' => 'Klein (150px x 25px)',
    'medium' => 'Mittel (250px x 35px)',
    'large' => 'Groß (350px x 40px)',
    'responsive' => 'Dynamisch/Responsive (Empfohlen)',
];

$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonLabel'] = ['Paypal Smart Button Beschriftung', 'Wählen Sie die Beschriftung des Paypal Smart Button aus.'];
$GLOBALS['TL_LANG']['tl_content']['paypalSmartButtonLabel']['options'] = [
    'checkout' => 'Direkt zur Kasse',
    'pay' => 'Bezahlen',
    'buynow' => 'Jetzt Kaufen',
];
