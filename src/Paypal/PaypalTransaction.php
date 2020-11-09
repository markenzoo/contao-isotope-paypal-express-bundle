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

use Contao\Input;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;
use Isotope\Module\Checkout;
use Markenzoo\ContaoIsotopePaypalExpressBundle\Isotope\Model\Payment\PaypalExpress;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PaypalTransaction class.
 */
class PaypalTransaction
{
    /**
     * Only valid request header content type.
     */
    public const CONTENT_TYPE = 'application/json';

    /**
     * Response message if the post request doesn't provide a header of application/json.
     */
    public const MSG_UNSUPPORTED_CONTENT_TYPE = 'Invalid content type header';

    /**
     * Response message when provided request is invalid.
     */
    public const MSG_INVALID_REQUEST = 'Invalid request';

    /**
     * Response message when cart is null.
     */
    public const MSG_UNDEFINED_CART = 'Could not find Cart';

    /**
     * Response message when cart is empty.
     */
    public const MSG_EMPTY_CART = 'Shopping Cart is empty';

    /**
     * Response message when an error occurs.
     */
    public const MSG_INTERNAL_SERVER_EROR = 'Internal Server Error';

    /**
     * Paypal Client Id.
     *
     * @var string
     */
    protected $clientId;

    /**
     * Paypal Client Secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * use debugging.
     *
     * @var bool
     */
    protected $debug;

    /**
     * use logging.
     *
     * @var bool
     */
    protected $logging;

    /**
     * status id for new orders.
     *
     * @var int
     */
    protected $new_order_status;

    /**
     * page to redirect to after order is placed.
     *
     * @var PageModel|null
     */
    protected $objCheckoutPage;

    /**
     * Payment method for this collection, if payment is required.
     *
     * @var IsotopePayment
     */
    protected $objPayment;

    /**
     * Notifications from Notification Center.
     *
     * @var array
     */
    protected $isoNotifications;

    /**
     * Construct a new PaypalTransaction.
     *
     * @param PaypalExpress  $objPayment
     * @param PageModel|null $objCheckoutPage
     */
    public function __construct(IsotopePayment $objPayment, PageModel $objCheckoutPage = null, $isoNotifications = null)
    {
        $this->clientId = $objPayment->paypal_client;
        $this->clientSecret = $objPayment->paypal_secret;
        $this->debug = (bool) ($objPayment->debug);
        $this->logging = (bool) ($objPayment->logging);
        $this->new_order_status = $objPayment->new_order_status;
        $this->objCheckoutPage = $objCheckoutPage;
        $this->objPayment = $objPayment;
        $this->isoNotifications = $isoNotifications;
    }

    /**
     * Handle Paypal Transaction.
     *
     * @param Cart|null $objCart
     * @param Request   $request
     *
     * @return void
     */
    public function handleRequest($objCart, Request $request): void
    {
        $contentType = $request->headers->get('content-type');
        if (self::CONTENT_TYPE !== $contentType) {
            static::sendJsonResponse(
                self::MSG_UNSUPPORTED_CONTENT_TYPE,
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE
            );
        }

        $strStep = Input::get('PAYPAL_EXPRESS');
        if (!\in_array($strStep, ['CREATE', 'CAPTURE'], true)) {
            static::sendJsonResponse(
                self::MSG_INVALID_REQUEST,
                Response::HTTP_BAD_REQUEST
            );
        }

        if (null === $objCart) {
            static::sendJsonResponse(['message' => self::MSG_UNDEFINED_CART], Response::HTTP_BAD_REQUEST);
        }

        if ($objCart->isEmpty()) {
            static::sendJsonResponse(['message' => self::MSG_EMPTY_CART], Response::HTTP_BAD_REQUEST);
        }

        /** @var Order $objOrder */
        $objOrder = $objCart->getDraftOrder();

        switch ($strStep) {
            case 'CREATE':
                $this->createTransaction($objOrder);
            break;

            case 'CAPTURE':
                ['orderID' => $orderID] = (array) json_decode((string) $request->getContent(), true);
                $this->captureTransaction($objOrder, $orderID);
                break;

            // TODO: add Steps for Authorize Flow
        }
    }

    /**
     * Capture an order payment by passing the approved order ID as argument.
     *
     * @param mixed $orderID
     *
     * @return mixed
     */
    public function captureOrder($orderID)
    {
        $request = new OrdersCaptureRequest($orderID);
        $request->prefer('return=representation');

        $client = PayPalClient::client($this->clientId, $this->clientSecret, $this->debug);
        $response = $client->execute($request);

        return $response->result;
    }

    /**
     * Create a new Paypal order.
     *
     * @param IsotopePurchasableCollection $objOrder
     *
     * @return mixed
     */
    protected function createOrder(IsotopePurchasableCollection $objOrder)
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = PaypalRequest::buildRequestBody($objOrder);

        $client = PayPalClient::client($this->clientId, $this->clientSecret, $this->debug);

        try {
            $response = $client->execute($request);

            return $response->result;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /**
     * Create a new Paypal Transaction.
     *
     * @param IsotopePurchasableCollection $objOrder
     *
     * @return void
     */
    protected function createTransaction(IsotopePurchasableCollection $objOrder): void
    {
        /** @var object|string $response */
        $response = $this->createOrder($objOrder);

        if (\is_string($response) || $this->debug) {
            static::sendJsonResponse($response);
        }

        static::sendJsonResponse(['id' => $response->id]);
    }

    /**
     * Capture a new Paypal Transaction.
     *
     * @param IsotopePurchasableCollection $objOrder
     * @param mixed                        $orderID
     *
     * @return void
     */
    protected function captureTransaction(IsotopePurchasableCollection $objOrder, $orderID): void
    {
        /**
         * capture transaction.
         *
         * @var object $paymentData
         */
        $paypalData = $this->captureOrder($orderID);

        /**
         * include complete payment response in debug mode.
         *
         * @var object $response
         */
        $response = $this->debug ? clone $paypalData : new \stdClass();

        // store payment information in database record
        $success = $this->processPayment($objOrder, $paypalData);

        if ($success) {
            // redirect to success page
            $response->href = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, $this->objCheckoutPage);
        } else {
            // redirect to error page
            $response->href = Checkout::generateUrlForStep(Checkout::STEP_FAILED);
        }

        static::sendJsonResponse($response);
    }

    /**
     * @param Order $objOrder
     * @param mixed $paypalData
     *
     * @return Address
     */
    protected function getShippingAddress(Order $objOrder, $paypalData): Address
    {
        $objShippingAddress = new Address();

        $objShippingAddress->pid = $objOrder->id;
        $objShippingAddress->tstamp = time();
        $objShippingAddress->ptable = 'tl_iso_product_collection';
        $objShippingAddress->store_id = $objOrder->getStoreId();
        $objShippingAddress->firstname = $paypalData->payer->name->given_name;
        $objShippingAddress->lastname = $paypalData->payer->name->surname;
        $objShippingAddress->email = $paypalData->payer->email_address;
        $objShippingAddress->street_1 = $paypalData->purchase_units[0]->shipping->address->address_line_1;
        $objShippingAddress->city = $paypalData->purchase_units[0]->shipping->address->admin_area_2;
        $objShippingAddress->postal = $paypalData->purchase_units[0]->shipping->address->postal_code;
        $objShippingAddress->country = strtolower($paypalData->purchase_units[0]->shipping->address->country_code);

        // TODO: what does subdivision stand for? In Germany we have 'Bundesländer' but those are to long fot the database column
        // $objShippingAddress->subdivision = $paypalData->purchase_units[0]->shipping->address->admin_area_1;

        $objShippingAddress->isDefaultBilling = true;
        $objShippingAddress->isDefaultShipping = true;

        $objShippingAddress->save();

        return $objShippingAddress;
    }

    /**
     * Store the Payment Information of a Request.
     *
     * @param Order $objOrder
     * @param mixed $paypalData
     *
     * @return bool
     */
    protected function processPayment(Order $objOrder, $paypalData): bool
    {
        /** @var Address $objShippingAddress */
        $objShippingAddress = $this->getShippingAddress($objOrder, $paypalData);

        // Store Payment Method
        $objOrder->setPaymentMethod($this->objPayment);

        // Store Address Information
        // TODO: use billing address - has to be activated by paypal support
        $objOrder->setBillingAddress($objShippingAddress);
        $objOrder->setShippingAddress($objShippingAddress);

        // Store Paypal Data for future reference
        $this->storePayment($objOrder, $paypalData);
        $this->storeHistory($objOrder, $paypalData);

        // set notifications from notification center
        $objOrder->nc_notification = $this->isoNotifications;

        if (!$objOrder->checkout()) {
            /*
             * @psalm-suppress DeprecatedMethod
             */
            System::log('Checkout for Order ID "'.$objOrder->getId().'" failed', __METHOD__, TL_ERROR);

            return false;
        }

        // set paid time to now
        $objOrder->setDatePaid(time());

        // set new order status
        if (!$objOrder->updateOrderStatus($this->new_order_status)) {
            /*
             * @psalm-suppress DeprecatedMethod
             */
            System::log('Checkout for Order ID "'.$objOrder->getId().'" failed', __METHOD__, TL_ERROR);

            return false;
        }

        // persist
        $objOrder->save();

        return true;
    }

    /**
     * @param Order $objOrder
     *
     * @return array
     */
    protected function retrievePayment(Order $objOrder): array
    {
        $paymentData = (array) StringUtil::deserialize($objOrder->payment_data, true);

        return \array_key_exists('PAYPAL', $paymentData) ? $paymentData['PAYPAL'] : [];
    }

    /**
     * @param Order $objOrder
     * @param mixed                    $paypalData
     */
    protected function storePayment(Order $objOrder, $paypalData): void
    {
        $paymentData = (array) StringUtil::deserialize($objOrder->payment_data, true);
        $paymentData['PAYPAL'] = \is_array($paypalData) ? $paypalData : json_decode(json_encode($paypalData), true);

        $objOrder->payment_data = $paymentData;
        $objOrder->save();
    }

    /**
     * @param Order $objOrder
     * @param mixed                    $paypalData
     */
    protected function storeHistory(Order $objOrder, $paypalData): void
    {
        $paymentData = (array) StringUtil::deserialize($objOrder->payment_data, true);

        if (!\is_array($paymentData['PAYPAL_HISTORY'])) {
            $paymentData['PAYPAL_HISTORY'] = [];
        }

        $paymentData['PAYPAL_HISTORY'][] = \is_array($paypalData) ? $paypalData : json_decode(json_encode($paypalData), true);

        $objOrder->payment_data = $paymentData;
        $objOrder->save();
    }

    /**
     * Create a http response with content type set to application/json and message provided as data.
     *
     * @param mixed $data
     * @param int   $status
     *
     * @return void
     */
    protected static function sendJsonResponse($data, int $status = 200): void
    {
        $response = new JsonResponse($data, $status);
        $response->send();
        exit();
    }
}
