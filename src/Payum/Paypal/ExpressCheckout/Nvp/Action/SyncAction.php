<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Action\ActionPaymentAware;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class SyncAction extends ActionPaymentAware
{
    public function execute($request)
    {
        /** @var $request SyncRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $request->getInstruction();
        
        if (false == $instruction->getToken()) {
            return;
        }
        
        try {
            $this->payment->execute(new GetExpressCheckoutDetailsRequest($instruction));
            
            foreach ($instruction->getPaymentrequestNTransactionid() as $paymentRequestN => $transactionId) {
                $this->payment->execute(new GetTransactionDetailsRequest($paymentRequestN, $instruction));
            }
        } catch (HttpResponseAckNotSuccessException $e) {
            $instruction->clearErrors();
            $request->getInstruction()->fromNvp($e->getResponse());
        }
    }

    public function supports($request)
    {
        return $request instanceof SyncRequest;
    }
}