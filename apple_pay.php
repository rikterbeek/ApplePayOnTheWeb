<html>
<head><title>Apple Pay For The Web Example</title></head>

<body>
<?php if($this->hasApplePayEnabled()): ?>
    <div id="adyen-apple-pay-button" class="apple-pay-button" style="display: none;"></div>


    <script type="text/javascript">

        var applePayButton = document.getElementById("adyen-apple-pay-button");
        applePayButton.observe('click', applePayButtonClicked);


        // Only show button for browsers that suppoprt ApplePaySession
        document.addEventListener("DOMContentLoaded", function () {
            if(window.ApplePaySession) {
                var promise = ApplePaySession.canMakePaymentsWithActiveCard("merchant.com.canine-clothing");
                promise.then(function (canMakePayments) {
                    if (canMakePayments) {
                        $('adyen-apple-pay-button').show();
                    }
                });
            }
        });

        // do request
        function applePayButtonClicked(event)
        {
            var paymentRequest = {
                currencyCode: "USD",
                countryCode: "US",
                total: {
                    label: "Canine Clothing",
                    amount: "19.99"
                },
                supportedNetworks: ["amex","discover","masterCard","visa"],
                merchantCapabilities: ["supports3DS"],
                //requiredShippingAddressFields:["postalAddress"]
            };

            var session = new ApplePaySession(1, paymentRequest);

            session.onvalidatemerchant = function(event) {
                var promise = performValidation(event.validationURL);

                promise.then(function (merchantSession) {
                    session.completeMerchantValidation(merchantSession);
                });

            }

            session.onpaymentauthorized = function(event) {

                var promise = sendPayment(event.payment.token);

                promise.then(function (success) {
                    var status;
                    if(success)
                        status = ApplePaySession.STATUS_SUCCESS;
                    else
                        status = ApplePaySession.STATUS_FAILURE;

                    session.completePayment(status);
                });


            }

            session.begin();
        }

        function performValidation(validationURL)
        {
            // Return a new promise.
            return new Promise(function(resolve, reject) {

                var url = '<?php echo $this->getUrl('adyen/applePay/requestMerchantSession', array('_secure'=>true));?>';

                ajaxReq = new Ajax.Request(url, {
                    parameters: {validationURL: validationURL, domainName: location.host, isAjax: 1, method: 'POST'},
                    onSuccess: function (response) {
                        var data = JSON.parse(response.responseText);

                        if(data) {
                            resolve(data);
                        } else {
                            reject(Error(response.responseText));
                        }

                    },
                    onFailure: function() {
                        reject(Error("Network Error"));
                    }
                });

            });
        }




        function sendPayment(payment) {

            // Return a new promise.
            return new Promise(function(resolve, reject) {
                var url = '<?php echo $this->getUrl('adyen/applePay/sendPayment', array('_secure'=>true));?>';

                ajaxReq = new Ajax.Request(url, {
                    parameters: {payment: JSON.stringify(payment), isAjax: 1, method: 'POST'},
                    onSuccess: function (response) {

                        if (response.responseText && response.responseText.length >= 0) {
                            var data = JSON.parse(response.responseText);
                        }

                        var success = true; // TODO

                        if(success) {
                            resolve(success);
                        } else {
                            reject(Error(response.responseText));
                        }
                    },
                    onFailure: function() {
                        reject(Error("Network Error"));
                    }
                });
            });
        }



    </script>


<?php endif; ?>

</body>

</html>