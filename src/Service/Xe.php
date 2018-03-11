<?php

/*
 * This file is part of Exchanger.
 *
 * (c) Florian Voutzinos <florian@voutzinos.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Exchanger\Service;

use Exchanger\Contract\ExchangeRateQuery;
use Exchanger\ExchangeRate;
use Exchanger\Contract\HistoricalExchangeRateQuery;
use Exchanger\StringUtil;
use Exchanger\Exception\UnsupportedCurrencyPairException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;

/**
 * Xe Service.
 *
 * @author Alberto Diaz
 */
class Xe extends HistoricalService
{

    const URL = 'https://xecdapi.xe.com/v1/convert_from.json/?from=%s&to=%s';
    const HISTORICAL_URL = 'https://xecdapi.xe.com/v1/historic_rate.json/?from=%s&date=%s&to=%s';

    /**
     * @param HttpClient|null     $httpClient
     * @param RequestFactory|null $requestFactory
     * @param array               $options
     */
    public function __construct($httpClient = null, RequestFactory $requestFactory = null, array $options = []) 
    {
        $this->processOptions($options);
        //var_dump($httpClient instanceof \Http\Discovery\Strategy\MockClientStrategy);exit;
        $client = new Client(
            [RequestOptions::AUTH=>[
            $options['account_id'],
            $options['api_key']]]
        );
        
        $this->httpClient = $client;
        $this->requestFactory = $requestFactory ? : MessageFactoryDiscovery::find();
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function processOptions(array &$options) 
    {
        if (!isset($options['api_key'])) {
            throw new \InvalidArgumentException('The "api_key" option must be provided.');
        }
        if (!isset($options['account_id'])) {
            throw new \InvalidArgumentException('The "account_id" option must be provided.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestExchangeRate(ExchangeRateQuery $exchangeQuery) 
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();
        
        $url = sprintf(
            self::URL,
            $currencyPair->getBaseCurrency(),
            $currencyPair->getQuoteCurrency()
        );
        
        $data = StringUtil::jsonToArray($this->request($url, []));

        if (isset($data['to'][0]['mid'])) {
            $date = (new \DateTime())->setTimestamp(strtotime($data['timestamp']));
            return new ExchangeRate($data['to'][0]['mid'], $date);
        }
        
        throw new UnsupportedCurrencyPairException($currencyPair, $this);
    }

    /**
     * {@inheritdoc}
     */
    protected function getHistoricalExchangeRate(HistoricalExchangeRateQuery $exchangeQuery) 
    {
        $currencyPair = $exchangeQuery->getCurrencyPair();
                
        $url = sprintf(
            self::HISTORICAL_URL, 
            $currencyPair->getBaseCurrency(), 
            $exchangeQuery->getDate()->format('Y-m-d'), 
            $currencyPair->getQuoteCurrency()
        );
                
        $data = StringUtil::jsonToArray($this->request($url, []));

        if (!isset($data['to'][0]['mid'])) {
            return null;
        }
        return new ExchangeRate($data['to'][0]['mid'], $exchangeQuery->getDate());
    }

    /**
     * {@inheritdoc}
     */
    public function supportQuery(ExchangeRateQuery $exchangeQuery) 
    {
        return true;
    }

    /**
     * Fetches the content of the given url.
     * 
     * @param type  $url
     * @param array $headers it will not be used
     * 
     * @return string
     */
    protected function request($url, array $headers = []) 
    {
        return $this->httpClient->request('GET', $url)->getBody()->__toString();
    }

}
