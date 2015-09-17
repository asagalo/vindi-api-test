<?php

require __DIR__.'/config.php';
require __DIR__.'/vendor/autoload.php';

ini_set('default_charset', 'utf-8');
date_default_timezone_set('America/Sao_Paulo');

if($_SERVER['REQUEST_METHOD'] == "POST")
{
    $subscriptionManager = new \Vindi\Subscription();
    $customerManager     = new \Vindi\Customer();
    $planManager         = new \Vindi\Plan();

    $payment_method = 'bank_slip';
    $start_at       = new \DateTime();

    try {

        $name  = $_POST['name'];
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

        if(empty($name) OR empty($email))
            throw new Exception('Dados Inválidos!');

        $plan = $planManager->get(3614);

        $customer = $customerManager->create([
            'name' => $name,
            'email' => $email
        ]);

        $sub = [
            "plan_id"             => $plan->id,
            "billing_trigger_day" => $start_at->format("d"),
            "customer_id"         => $customer->id,
            "payment_method_code" => $payment_method,
            "product_items"       => array()
        ];

        foreach($plan->plan_items as $item)
        {
            $sub['product_items'][] = [
                'product_id' => $item->product->id,
                'cycles' => $item->cycles,
            ];
        }

        // echo json_encode($sub);
        // exit;

        /**/
        $subscription = postHack($sub, $subscriptionManager);

        $redirect = $subscription->bill->charges[0]->print_url;

        header('Location: ' . $redirect);

    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}

function postHack($data, $resource)
{
    $vindi = new \Vindi\Vindi();

    $data_string = json_encode($data);

    $ch = curl_init(sprintf('%s%s', $vindi::$apiBase, $resource->url()));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $vindi->getApiKey() . ":");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string))
    );

    $result = curl_exec($ch);

    return json_decode($result);
}

?>

<form action="?" method="post">
    <label>Nome:</label>
    <input name="name" type="text">
    <label>E-mail:</label>
    <input name="email" type="email">
    <button type="submit">Enviar</button>
</form>
