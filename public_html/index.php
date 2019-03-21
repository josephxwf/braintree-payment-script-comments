<?php require_once("../includes/braintree_init.php"); ?>

<html>
<?php require_once("../includes/head.php"); ?>
<body>

    <?php require_once("../includes/header.php"); ?>

    <div class="wrapper">
        <div class="checkout container">

            <header>
                <h1>Hi, <br>Let's test a transaction</h1>
                <p>
                    Make a test payment with Braintree using PayPal or a card
                </p>
            </header>

            <form method="post" id="payment-form" action="/checkout.php">
                <section>
                    <label for="amount">
                        <span class="input-label">Amount</span>
                        <div class="input-wrapper amount-wrapper">
                            <input id="amount" name="amount" type="tel" min="1" placeholder="Amount" value="10">
                        </div>
                    </label>

                    <div class="bt-drop-in-wrapper">
                        <div id="bt-dropin"></div>
                    </div>
                </section>

                <input id="nonce" name="payment_method_nonce" type="hidden" />
                <input id="paypaldetails" name="payment_method_paypal_details" type="hidden" />
                <input id="devicedata" name="device_data" type="hidden" />
                <button class="button" type="submit"><span>Test Transaction</span></button>
            </form>
        </div>
    </div>

    <script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>

    <script>
        var form = document.querySelector('#payment-form');
        var client_token = "<?php echo($gateway->ClientToken()->generate()); ?>";
        //console.log(client_token);
        braintree.dropin.create({
          authorization: client_token,
          selector: '#bt-dropin',
          dataCollector: {
            //kount: true // Required if Kount fraud data collection is enabled
            paypal:true

          },
          paypal: {
            flow: 'vault',
            //billingAgreementDescription: 'Your agreement description',
            enableShippingAddress: true,
            shippingAddressEditable: true,

        },
          venmo:{},
          applePay: {
          displayName: 'My Store',
          paymentRequest: {
            total: {
              label: 'My Store',
              amount: '19.99'
            },
            // We recommend collecting billing address information, at minimum
            // billing postal code, and passing that billing postal code with all
            // Google Pay transactions as a best practice.
            requiredBillingContactFields: ["postalAddress"]
           }
          }
        }, function (createErr, instance) {
          if (createErr) {
            console.log('Create Error', createErr);
            return;
          }


          //this paymentMethodRequestable is triggered only after credit card, paypal, etc payment info is filled in the braintree form completely
          //event.type is the payment method selected, this func can be used to retrieve paypal data etc before the form submit
          instance.on('paymentMethodRequestable', function (event) {console.log(event.type);});

          //alert("this braintree script executes until meet form.addEventListener function below");
          //braintee script execution stops here after the braintree payment box loaded, wating for payment info input
          form.addEventListener('submit', function (event) {
            event.preventDefault();

            //instance.requestPaymentMethod will get payment method object that contain the nonce
            instance.requestPaymentMethod(function (err, payload) {

                  if (err) {
                    console.log('Request Payment Method Error', err);
                    alert("Please select a payment method!");
                    return;
                  }

                  //payload.type is the payment method selected
                  if (payload.type == 'CreditCard'){
                    console.log(JSON.stringify(payload.details));
                  }else{
                    console.log("Paypal: " + JSON.stringify(payload.details.email));
                    //When sending data to a web server, the data has to be a string. Convert a JavaScript object into a string with JSON.stringify()
                    document.querySelector('#paypaldetails').value = JSON.stringify(payload.details) ;
                  }
                  // Add the nonce to the form and submit
                  document.querySelector('#nonce').value = payload.nonce;
                  document.querySelector('#devicedata').value = JSON.stringify(payload.deviceData) ;

                  form.submit();
            });
          });
        });
    </script>
    <script src="javascript/demo.js"></script>
</body>
</html>
