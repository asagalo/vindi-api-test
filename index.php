<?php

require __DIR__.'/config.php';
require __DIR__.'/vendor/autoload.php';

ini_set('default_charset', 'utf-8');
date_default_timezone_set('America/Sao_Paulo');

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    $name  = $_POST['name'];
    $email = $_POST['email'];

    $customerManager     = new \Vindi\Customer();
    $planManager         = new \Vindi\Plan();
    $subscriptionManager = new \Vindi\Product();
    $billManager         = new \Vindi\Bill();

    $payment_method = 'bank_slip';
    $start_at       = new \DateTime();

    try {

        $plan = $planManager->get('3614');

        $customer = $customerManager->create([
            'name' => $name,
            'email' => $email
        ]);

        $items = array();

        foreach($plan->plan_items as $item)
            $items[] = (object) array('product_id' => $item->product->id);

        $sub = array(
            'plan_id'             => $plan->id,
            'customer_id'         => $customer->id,
            'payment_method_code' => $payment_method,
            'product_items'       => $items
        );

        $subscription = $subscriptionManager->create($sub);

    } catch(\Vindi\Exceptions\ValidationException $e) {
        echo $e->getMessage();
    }
}

?>

<form action="?" method="post">
    <label>Nome:</label>
    <input name="name" type="text">
    <label>E-mail:</label>
    <input name="email" type="email">
    <button type="submit">Enviar</button>
</form>
