<?php

namespace App\Service;

use App\Util\Search\Constants;
use App\Util\GenericUtil;
use App\Util\Search\MyCriteriaParam;
use Doctrine\ORM\QueryBuilder;
use Stripe\Charge;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StripeService 
{
    private $params;
    private $secretKey;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        Stripe::setApiKey($this->params->get('stripe_secret_key'));
        $this->secretKey = $_ENV['STRIPE_SECRET_KEY'];
    }

    public function createCharge($token, $amount, $params): ?string
    {
        $params = array_merge([], $params);
        $params['amount'] = $amount * 100;
        $params['currency'] = $this->params->get('stripe_currency');
        $params['source'] = $token;
        $charge = Charge::create($params);
        return $charge->id;
    }

    /**
     * @return object
     */
    public function paymentIntent($amount):?PaymentIntent
    {
        \Stripe\Stripe::setApiKey($this->secretKey); 
        
        $intentStripe = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' =>  'eur',
            'payment_method_types' =>  ['card']
        ]);

        return $intentStripe;
    }


    public function intentSecret($paymentIntentId)
    {
        $intent = $this->getPaymentIntent($paymentIntentId);

        return $intent['client_secret'] ?? null;
    }

    
    public function getPaymentIntent($id)
    {
        return \Stripe\PaymentIntent::retrieve($id, []);
    }


}