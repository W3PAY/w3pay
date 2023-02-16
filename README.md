# W3PAY Web3 Crypto Payments Method

Add crypto payment acceptance for the website.
P2P payment acceptance is developed on the blockchain system and web3. Start accepting stablecoins USD or other tokens now.

## Use Resources

- Website: https://w3pay.dev/
- GitHub: https://github.com/w3pay
- Check out the ready plugins for  CMS. GitHub Website: https://w3pay.github.io/

## Installation. Manual Setup Instructions for website.

### Step 1: Download w3pay.

You can install the package via composer: 

```bash
composer require w3pay/w3pay
```

Or download the repository https://github.com/w3pay/w3pay and extract its files to the w3pay folder on the site.
- /w3pay/w3payBackend/
- /w3pay/w3payFrontend/

### Step 2: Displaying widgets on a website.

Copy the sample pages and paste outside of the w3pay directory.

#### Private pages for the administrator:

- [w3payFrontend/settings.php](/w3pay/w3payFrontend/settings.php) - Example page with settings form
- [w3payFrontend/transactions.php](/w3pay/w3payFrontend/transactions.php) - Transaction page example
- [w3payFrontend/load.php](/w3pay/w3payFrontend/load.php) - Request processing page example

#### Public pages for users:

- [w3payFrontend/payment.php](/w3pay/w3payFrontend/payment.php) - Payment page example
- [w3payFrontend/checkPayment.php](/w3pay/w3payFrontend/checkPayment.php) - Server check of payment example

#### Important. After copying the examples, you need to open the files and specify the paths to w3payFrontend and w3payBackend and remove exit at the beginning of the files.

After successfully implementing the plugin into your site, go to the settings and test the acceptance of crypto payments. https://w3pay.dev/settings