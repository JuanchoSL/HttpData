<?php

namespace JuanchoSL\HttpData\Tests\Integration;

use JuanchoSL\CurlClient\Wrappers\PsrCurlClient;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpHeaders\Constants\Types\Extensions;
use JuanchoSL\HttpHeaders\Constants\Types\MimeTypes;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PsrCurlClientTest extends TestCase
{

    public function testGetApiLyrics()
    {

        $request = (new RequestFactory)->createRequest('GET', 'http://api.chartlyrics.com')->withRequestTarget('/apiv1.asmx/SearchLyric?artist=rihanna&song=umbrella');
        $response = (new PsrCurlClient)->sendRequest($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsStringIgnoringCase("/" . Extensions::XML, $response->getHeaderLine('Content-type'));

        $xml = simplexml_load_string($response->getBody(), "SimpleXMLElement", LIBXML_NOCDATA);
        $body = json_decode(json_encode($xml), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasProperty('SearchLyricResult', $body);
        $this->assertIsArray($body->SearchLyricResult);
        $this->assertNotEmpty($body->SearchLyricResult);
        $body = current($body->SearchLyricResult);
        $this->assertObjectHasProperty('Artist', $body);
        $this->assertStringContainsStringIgnoringCase('rihanna', $body->Artist);
        $this->assertObjectHasProperty('Song', $body);
        $this->assertStringContainsStringIgnoringCase('umbrella', $body->Song);
    }
/*
    public function testGetApiBitcoinPrice()
    {
        $request = (new RequestFactory)->createRequest('GET', 'https://api.coindesk.com/v1/bpi/currentprice.json');
        $response = (new PsrCurlClient)->sendRequest($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringStartsWith(MimeTypes::JSON, $response->getHeaderLine('Content-type'));
        
        $body = json_decode($response->getBody(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertIsObject($body);
        $this->assertObjectHasProperty('chartName', $body);
        $this->assertEqualsIgnoringCase('bitcoin', $body->chartName);
    }
*/   
    public function testGetExchangeRatesApi()
    {
        $request = (new RequestFactory)->createRequest('GET', 'https://api.coingecko.com/api/v3/exchange_rates');
        $response = (new PsrCurlClient)->sendRequest($request);
        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringStartsWith(MimeTypes::JSON, $response->getHeaderLine('content-type'));

        $body = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('rates', $body);
        $this->assertIsArray($body['rates']);
        $this->assertArrayHasKey('eur', $body['rates']);
        $this->assertArrayHasKey('name', $body['rates']['eur']);
        $this->assertArrayHasKey('unit', $body['rates']['eur']);
        $this->assertArrayHasKey('value', $body['rates']['eur']);
        $this->assertArrayHasKey('type', $body['rates']['eur']);
        $this->assertEqualsIgnoringCase('fiat', $body['rates']['eur']['type']);
        $this->assertEqualsIgnoringCase('euro', $body['rates']['eur']['name']);
    }

}