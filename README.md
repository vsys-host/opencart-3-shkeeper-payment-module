# opencart-3-shkeeper-payment-module
SHKeeper payment gateway module for OpenCart 3

*Module has been tested on CMS OpenCart Version 3.0.3.9*

## Installation
### Upload via Extension Installer

Download from [Github releases page](https://github.com/vsys-host/opencart-3-shkeeper-payment-module/releases) the latest module archive `opencart-3-shkeeper-payment-module.ocmod.zip`
* Upload `opencart-3-shkeeper-payment-module.ocmod.zip` to your OpenCart or ocStore installation using the administrator menu _Extensions_ -> _Extension Installer_
* Activate the module in payment extensions (_Extensions_ -> _Payments_)

Detailed instruction can be found official OpenCart [site](https://docs.opencart.com/en-gb/extension/installer/)
### Manual Plugin Installation

In rare cases, you may need to install a plugin by manually transferring the files onto the server. This is recommended only when absolutely necessary, for example when your server is not configured to allow automatic installations.

This procedure requires you to be familiar with the process of transferring files using an SFTP client. It is recommended for advanced users and developers.

## Configuration

After successful installation you should configure plugin.
1. Press _Edit_ button beside the _Install_ in payment extensions (_Extensions_ -> _Payments_).
2. Here, enter the api key, api url, instructions for your customers and set Status _Enabled_.
    * Instruction – Contains the explanation on how to pay by SHKeeper.
    * Api key - Authorization and identification SHKeeper key. You can generate it in SHKeeper admin panel for any crypto wallet.
    * Api url - SHKeeper server api entry point.
    * Sort Order – The position of the payment method in the store front when listed among all the available payment methods.
    * Confirmed Order Status – Order statuses for successfully processed payment
    * Status – Disable or Enable module.
3. Once done save the changes.

 Detailed common instruction can be found on official OpenCart [site](https://docs.opencart.com/en-gb/extension/payment/)
