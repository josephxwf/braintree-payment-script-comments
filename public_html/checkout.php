<?php
require_once("../includes/braintree_init.php");

$amount = $_POST["amount"];
$nonce = $_POST["payment_method_nonce"];
$details = json_decode($_POST["payment_method_paypal_details"]);
//var_dump($details);
//var_dump($_POST['device_data']);
//exit;

$result = $gateway->transaction()->sale([
    'amount' => $amount,
    'paymentMethodNonce' => $nonce,
    'options' => [
        'submitForSettlement' => true,
        //'verifyCard' => true
    ],
    'deviceData' => $_POST['device_data']
]);

//$verification = $result->creditCardVerification;
//debug($verification->status);
//exit;
//var_dump($result);
//exit;


if ($result->success || !is_null($result->transaction)) {
    $transaction = $result->transaction;
    //var_dump($transaction);
    header("Location: transaction.php?id=" . $transaction->id);
} else {
    $errorString = "";

    foreach($result->errors->deepAll() as $error) {
        $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
    }

    $_SESSION["errors"] = $errorString;
    header("Location: index.php");
}
