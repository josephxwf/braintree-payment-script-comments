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

                    <div id="paypal-button"></div> 
                </section>

                <input id="nonce" name="payment_method_nonce" type="hidden" />
                <button class="button" type="submit"><span>Test Transaction</span></button>

            </form>
        </div>
    </div>

    <!-- Load PayPal's checkout.js Library. -->
<script src="https://www.paypalobjects.com/api/checkout.js" data-version-4 log-level="warn"></script>

<!-- Load the client component. -->
<script src="https://js.braintreegateway.com/web/3.39.0/js/client.min.js"></script>

<!-- Load the PayPal Checkout component. -->
<script src="https://js.braintreegateway.com/web/3.39.0/js/paypal-checkout.min.js"></script>


    <script>
        var form = document.querySelector('#payment-form');
        var client_token = "<?php echo($gateway->ClientToken()->generate()); ?>";





        // Create a client.
braintree.client.create({
  authorization: client_token
}, function (clientErr, clientInstance) {

  // Stop if there was a problem creating the client.
  // This could happen if there is a network error or if the authorization
  // is invalid.
  if (clientErr) {
    console.error('Error creating client:', clientErr);
    return;
  }

  // Create a PayPal Checkout component.
  braintree.paypalCheckout.create({
    client: clientInstance
  }, function (paypalCheckoutErr, paypalCheckoutInstance) {

    // Stop if there was a problem creating PayPal Checkout.
    // This could happen if there was a network error or if it's incorrectly
    // configured.
    if (paypalCheckoutErr) {
      console.error('Error creating PayPal Checkout:', paypalCheckoutErr);
      return;
    }

    // Set up PayPal with the checkout.js library
    paypal.Button.render({
      env: 'sandbox', // or 'sandbox'

      payment: function () {
        return paypalCheckoutInstance.createPayment({
          // Your PayPal options here. For available options, see
          // http://braintree.github.io/braintree-web/current/PayPalCheckout.html#createPayment


     flow: 'vault'



        });
      },

      onAuthorize: function (data, actions) {
        return paypalCheckoutInstance.tokenizePayment(data, function (err, payload) {
          // Submit `payload.nonce` to your server.
          // Add the nonce to the form and submit
          document.querySelector('#nonce').value = payload.nonce;
          form.submit();
        });
      },

      onCancel: function (data) {
        console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));
      },

      onError: function (err) {
        console.error('checkout.js error', err);
      }
    }, '#paypal-button').then(function () {
      // The PayPal button will be rendered in an html element with the id
      // `paypal-button`. This function will be called when the PayPal button
      // is set up and ready to be used.
    });

  });

});


    </script>
    <script src="javascript/demo.js"></script>
</body>
</html>
