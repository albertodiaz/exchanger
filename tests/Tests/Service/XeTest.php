<?php

namespace Exchanger\Tests\Service;

use Exchanger\HistoricalExchangeRateQuery;
use Exchanger\CurrencyPair;
use Exchanger\ExchangeRateQuery;
use Exchanger\Service\Xe;

class XeTest extends ServiceTestCase
{
    /**
     * @test
     */   
    public function it_supports_all_queries()
    {
        $service = new Xe($this->getMock('Http\Client\HttpClient'), null, ['api_key' => 'secret','account_id' => 'secret']);

        $this->assertTrue($service->supportQuery(new ExchangeRateQuery(CurrencyPair::createFromString('USD/EUR'))));
        $this->assertTrue($service->supportQuery(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/USD'), new \DateTime())));
    }


    /**
     * @test
     * @expectedException \Exchanger\Exception\Exception
     */
    /*
    public function it_throws_an_exception_when_rate_not_supported()
    {
        $url = 'https://xecdapi.xe.com/v1/convert_from.json/?from=EUR&to=ZZZ';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/Xe/error.json');
        $service = new Xe($this->getHttpAdapterMock($url, $content), null, ['api_key' => 'secret','account_id' => 'secret']);

        $service->getLatestExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/ZZZ')));        
    }

    /**
     * @test
     */
    /*
    public function it_fetches_a_rate()
    {
        $url = 'https://xecdapi.xe.com/v1/convert_from.json/?from=EUR&to=GBP';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/Xe/latest.json');
        $service = new Xe($this->getHttpAdapterMock($url, $content), null, ['api_key' => 'secret','account_id' => 'secret']);

        $rate = $service->getExchangeRate(new ExchangeRateQuery(CurrencyPair::createFromString('EUR/GBP')));

        $this->assertSame('0.8914036347', $rate->getValue());
        $this->assertTrue('2018-03-08' == $rate->getDate()->format('Y-m-d'));
    }
    
    /**
     * @test
     */
    /*
    public function it_fetches_a_historical_rate()
    {
        $uri = 'https://xecdapi.xe.com/v1/historic_rate.json/?from=EUR&date=2010-03-10&to=GBP';
        $content = file_get_contents(__DIR__.'/../../Fixtures/Service/Xe/historical.json');
        $date = new \DateTime('2010-03-10');

        $service = new Xe($this->getHttpAdapterMock($uri, $content));
        $rate = $service->getExchangeRate(new HistoricalExchangeRateQuery(CurrencyPair::createFromString('EUR/GBP'), $date));

        $this->assertEquals('0.9061699263', $rate->getValue());
        $this->assertEquals($date, $rate->getDate());
    }
     * 
     */   
}
