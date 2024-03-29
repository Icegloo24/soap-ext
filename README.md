# soap-ext

soap-ext is a library written in PHP that seeks to replace the PHP-Native SoapClient with a SoapClient consisting of a handfull of Middleware Components, which are replaceable.

The whole Client runs on a Middleware that is defined by Interfaces to enable the user to write New and replace specific Middleware-Components at will.

# Requirements

7.0++ due to phpunit

## How to Install

Install with [`composer.phar`](http://getcomposer.org).

```sh
composer require icegloo24/soap-ext
```

# How to
## Simple use case:
```php
use SoapExt\SoapClient;

$wsdl = "any existing wsdl";
$options = [];

$client = new SoapClient($wsdl, $options);

$input = new \SoapVar("foo", XSD_STRING, null, null, "bar", "https://foo.bar");

$client->__call($function, $input);

$result = $client->__getLastResponse();
```

## Extended use case:
```php
use SoapExt\SoapClient;
use SoapExt\Middleware\Native\Cache;
use SoapExt\Middleware\Native\Curl;
use SoapExt\Middleware\Native\RequestAdjustment;
use SoapExt\Middleware\Native\RequestBuilder;
use SoapExt\Middleware\Native\SignatureMaker;
use SoapExt\Middleware\Native\WsdlLoader;

$wsdl = "any existing wsdl";
$options = [];
$middleware = [
  new Cache(),
  new Curl($options),
  new RequestAdjustment(),
  new RequestBuilder(),
  new SignatureMaker($loc_certificate, $loc_privkey),
  new WsdlLoader()
];

$client = new SoapClient($wsdl, $options, $middleware);

$input = new \SoapVar("foo", XSD_STRING, null, null, "bar", "https://foo.bar");

$client->__call($function, $input);

$result = $client->__getLastResponse();
```

## Minimum configuration:
This case will yield the created Request as Response! No loaded Wsdl, no Validation, no Signature and no Curl-calls.
Just to check out what Request has been built and if it builds.
```php
use SoapExt\SoapClient;
use SoapExt\Middleware\Native\RequestBuilder;

$wsdl = "any existing wsdl";
$options = [];
$middleware = [
  new RequestBuilder()
];

$client = new SoapClient($wsdl, $options, $middleware);

$input = new \SoapVar("foo", XSD_STRING, null, null, "bar", "https://foo.bar");

$client->__call($function, $input);

$result = $client->__getLastResponse();
```
## Use your own Middleware
Just implement the *Interface* for the Middleware-Component you seek to write and append that to the Client.
## Middleware
#### CacheInterface
- Handles the Caching of the Wsdl!
#### CurlInterface
- Contains all handling of curl actions on the SoapClient.
#### RequestBuilderInterface
- Required to convert the array-structure handed into the __call() into a fully functional XML.
#### WsdlLoaderInterface
- Download, Load and Save the Wsdl into the Cache.
#### RequestAdjustmentInterface
- Placed before Validation and Signature it is perfectly located to adjust some changes to the XML that can not be resolved by SoapVar's.
#### ValidatorInterface
- Validates the Request versus the Wsdl.
#### SignatureMakerInterface
- Required to apply a Signature to the Header of the SoapRequest.

## Native Middleware
#### Cache
- Places cached Data in the usual /tmp Filesystem.
- Cache can be cleared via function.
#### Curl
- Takes several Arguments and uses the native curl_lib to execute.
#### RequestBuilder
- Is creating the XML File with the use of DOMDocument.
#### WsdlLoader
- Can download the wsdl or load from cache.
#### RequestAdjustment
- This actually just prints the Request to Console/Browser.
#### Validator
- Can validate ComplexTypes of Requests and the loose structure against the wsdl as well as Enumerations(SimpleTypes). Also included some Error handling!
#### SignatureMaker
- Create a Signature with the use of [RobRichards XMLSecLibs](https://github.com/robrichards/xmlseclibs).

## Todo:
- Soap Encoding - *(SOAP_ENC_ARRAY, XSD_...) only SOAP_ENC_OBJECT and XSD_STRING is supported yet*
- Improve Namespace Naming in RequestBuilder
- Replace $options-array with a SoapClientOptions-Class with setters and Documentation for further knowledge!

There are still many more things i need to complete for this library. Feel free to Start Issues and/or make Pull Request if you like to contribute.
Mail me at c.moesker@web.de if you have questions etc. I try not to be lazy with that.

## Versions

