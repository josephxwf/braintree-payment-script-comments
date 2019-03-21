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

                    <div ></div>
                    <div class="icon" id="venmo-button">
                    <img src="/images/blue_venmo_button_active_280x48.svg" alt="">
                    </div>

                </section>

                <input id="nonce" name="payment_method_nonce" type="hidden" />
                <button class="button" type="submit"><span>Test Transaction</span></button>

            </form>
        </div>
    </div>

    <script src="https://js.braintreegateway.com/web/3.40.0/js/client.min.js"></script>
  <script src="https://js.braintreegateway.com/web/3.40.0/js/venmo.min.js"></script>
  <script src="https://js.braintreegateway.com/web/3.40.0/js/data-collector.min.js"></script>


    <script>
        var form = document.querySelector('#payment-form');
        var client_token = "<?php echo($gateway->ClientToken()->generate()); ?>";

        // Create a client.
        var venmoButton = document.getElementById('venmo-button');

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

    braintree.dataCollector.create({
      client: clientInstance,
      paypal: true
    }, function (dataCollectorErr, dataCollectorInstance) {
      if (dataCollectorErr) {
        // Handle error in creation of data collector.
        return;
      }

      // At this point, you should access the deviceData value and provide it
      // to your server, e.g. by injecting it into your form as a hidden input.
      console.log('Got device data:', dataCollectorInstance.deviceData);
    });

    braintree.venmo.create({
      client: clientInstance,
      // Add allowNewBrowserTab: false if your checkout page does not support
      // relaunching in a new tab when returning from the Venmo app. This can
      // be omitted otherwise.
      allowNewBrowserTab: false
    }, function (venmoErr, venmoInstance) {
      if (venmoErr) {
        console.error('Error creating Venmo:', venmoErr);
        return;
      }

      // Verify browser support before proceeding.
      if (!venmoInstance.isBrowserSupported()) {
        console.log('Browser does not support Venmo');
        return;
      }

      displayVenmoButton(venmoInstance);

      // Check if tokenization results already exist. This occurs when your
      // checkout page is relaunched in a new tab. This step can be omitted
      // if allowNewBrowserTab is false.
      if (venmoInstance.hasTokenizationResult()) {
        venmoInstance.tokenize(function (tokenizeErr, payload) {
          if (err) {
            handleVenmoError(tokenizeErr);
          } else {
            handleVenmoSuccess(payload);
          }
        });
        return;
      }
    });
  });

  function displayVenmoButton(venmoInstance) {
    // Assumes that venmoButton is initially display: none.
    venmoButton.style.display = 'block';

    venmoButton.addEventListener('click', function () {
      venmoButton.disabled = true;

      venmoInstance.tokenize(function (tokenizeErr, payload) {
        venmoButton.removeAttribute('disabled');

        if (tokenizeErr) {
          handleVenmoError(tokenizeErr);
        } else {
          handleVenmoSuccess(payload);
        }
      });
    });
  }

  function handleVenmoError(err) {
    if (err.code === 'VENMO_CANCELED') {
      console.log('App is not available or user aborted payment flow');
    } else if (err.code === 'VENMO_APP_CANCELED') {
      console.log('User canceled payment flow');
    } else {
      console.error('An error occurred:', err.message);
    }
  }

  function handleVenmoSuccess(payload) {
    // Send the payment method nonce to your server, e.g. by injecting
    // it into your form as a hidden input.
    console.log('Got a payment method nonce:', payload.nonce);
    // Display the Venmo username in your checkout UI.
    console.log('Venmo user:', payload.details.username);

    document.querySelector('#nonce').value = payload.nonce;
    form.submit();
  }
    </script>
    <script src="javascript/demo.js"></script>
</body>
</html>
