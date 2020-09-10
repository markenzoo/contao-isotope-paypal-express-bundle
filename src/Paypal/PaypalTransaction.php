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
use Isotope\Interfaces\IsotopePurchasableCollection;
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
     * Construct a new PaypalTransaction.
     *
     * @param PaypalExpress  $objPayment
     * @param PageModel|null $objCheckoutPage
     */
    public function __construct(PaypalExpress $objPayment, PageModel $objCheckoutPage = null)
    {
        $this->clientId = $objPayment->paypal_client;
        $this->clientSecret = $objPayment->paypal_secret;
        $this->debug = (bool) ($objPayment->debug);
        $this->logging = (bool) ($objPayment->logging);
        $this->new_order_status = $objPayment->new_order_status;
        $this->objCheckoutPage = $objCheckoutPage;
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

            return;
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
                $this->captureTransaction($objOrder, $request);
                break;

            // TODO: add Steps for Authorize Flow
        }
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
        $response = $client->execute($request);

        return $response->result;
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
        try {
            /** @var object|string $response */
            $response = $this->createOrder($objOrder);

            if (\is_string($response) || $this->debug) {
                static::sendJsonResponse($response);
            }

            static::sendJsonResponse(['id' => $response->id]);
        } catch (\Throwable $th) {
            static::sendJsonResponse(['message' => self::MSG_INTERNAL_SERVER_EROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Capture an order payment by passing the approved order ID as argument.
     *
     * @param mixed $orderId
     *
     * @return mixed
     */
    public function captureOrder($orderId)
    {
        $request = new OrdersCaptureRequest($orderId);

        $client = PayPalClient::client($this->clientId, $this->clientSecret, $this->debug);
        $response = $client->execute($request);

        return $response->result;
    }

    /**
     * Capture a new Paypal Transaction.
     *
     * @param IsotopePurchasableCollection $objOrder
     * @param Request                      $request
     *
     * @return void
     */
    protected function captureTransaction(IsotopePurchasableCollection $objOrder, Request $request): void
    {
        try {
            /** @var object $orderData */
            $orderData = json_decode((string) $request->getContent(false));
            if (!isset($orderData->orderID) || null === $orderData->orderID || empty($orderData->orderID)) {
                throw new \Exception();
            }
        } catch (\Throwable $th) {
            static::sendJsonResponse(
                self::MSG_INVALID_REQUEST,
                Response::HTTP_BAD_REQUEST
            );
        }

        /**
         * capture transaction.
         *
         * @var object $paymentData
         */
        $paymentData = $this->captureOrder($orderData->orderID);

        /**
         * include complete payment response in debug mode.
         *
         * @var object $response
         */
        $response = $this->debug ? clone $paymentData : new \stdClass();

        try {
            // store payment information in database record
            $this->storePayment($objOrder, $paymentData);

            // redirect to success page
            $response->href = Checkout::generateUrlForStep(Checkout::STEP_COMPLETE, $objOrder, $this->objCheckoutPage);
        } catch (\Throwable $th) {
            // redirect to error page
            $response->href = Checkout::generateUrlForStep(Checkout::STEP_FAILED);
        }

        static::sendJsonResponse($response);
    }

    /**
     * Store the Payment Information of a Request.
     *
     * @param Order $objOrder
     * @param mixed $paymentData
     *
     * @return void
     */
    protected function storePayment(Order $objOrder, $paymentData): void
    {
        // Store Paypal Data for future reference
        $arrPayment = (array) StringUtil::deserialize($objOrder->payment_data, true);
        $arrPayment['PAYPAL'] = (array) $paymentData;
        $objOrder->payment_data = $arrPayment;

        if (!$objOrder->checkout()) {
            /*
             * @psalm-suppress DeprecatedMethod
             */
            System::log('Checkout for Order ID "'.$objOrder->getId().'" failed', __METHOD__, TL_ERROR);
            throw new \Exception();
        }

        // set paid time to now
        $objOrder->setDatePaid(time());

        // set new order status
        $objOrder->updateOrderStatus($this->new_order_status);

        // persist
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
