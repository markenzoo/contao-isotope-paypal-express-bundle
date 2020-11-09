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

namespace Markenzoo\ContaoIsotopePaypalExpressBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Environment;
use Contao\PageModel;
use Contao\Template;
use Isotope\Isotope;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Cart;
use Markenzoo\ContaoIsotopePaypalExpressBundle\Paypal\PaypalTransaction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(category="texts")
 */
class PaypalExpressButtonController extends AbstractContentElementController
{
    /**
     * @return Response
     */
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $objMethod = Payment::findByPk($model->paypalExpressPaymentMethod);
        $template->hasCart = false;

        /** @var Cart|null */
        $objCart = Isotope::getCart();

        if (Environment::get('isAjaxRequest')) {
            $objCheckoutPage = null;
            if (null !== $model->paypalExpressCheckoutPage) {
                $objCheckoutPage = PageModel::findById($model->paypalExpressCheckoutPage);
            }

            $transaction = new PaypalTransaction($objMethod, $objCheckoutPage, $model->isoNotifications);
            $transaction->handleRequest($objCart, $request);
        }

        // Do not show Paypal Smart Buttons if no Cart exists or the Cart is empty
        if (null === $objCart || $objCart->isEmpty()) {
            return $template->getResponse();
        }

        /* @var PageModel $objPage */
        global $objPage;

        $template->hasCart = true;

        // get product collection from cart
        $objOrder = $objCart->getDraftOrder();

        // Configure Style of Buttons
        $btnStyle = [];
        if (!empty($model->paypalSmartButtonColor)) {
            $btnStyle['color'] = $model->paypalSmartButtonColor;
        }
        if (!empty($model->paypalSmartButtonShape)) {
            $btnStyle['shape'] = $model->paypalSmartButtonShape;
        }
        if (!empty($model->paypalSmartButtonSize)) {
            $btnStyle['size'] = $model->paypalSmartButtonSize;
        }
        if (!empty($model->paypalSmartButtonLabel)) {
            $btnStyle['label'] = $model->paypalSmartButtonLabel;
        }
        if (!empty($btnStyle)) {
            $btnStyle = 'style: '.\json_encode($btnStyle).',';
        } else {
            $btnStyle = '';
        }

        // Set client id
        $template->SB_CLIENT_ID = $objMethod->paypal_client;
        $template->createActionLink = $objPage->getFrontendUrl().'?PAYPAL_EXPRESS=CREATE';
        $template->captureActionLink = $objPage->getFrontendUrl().'?PAYPAL_EXPRESS=CAPTURE';
        $template->btnStyle = $btnStyle;
        $template->order = $objOrder;
        $template->payment = $objMethod;
        $template->loadMessage = $GLOBALS['TL_LANG']['MSC']['payment_loading'];
        $template->selector = 'paypal-button-container-'.$model->id;

        return $template->getResponse();
    }
}
