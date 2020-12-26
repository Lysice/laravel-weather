<?php

namespace Lysice\Weather\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Lysice\Weather\Exceptions\HttpException;
use Lysice\Weather\Exceptions\InvalidArgumentException;
use Lysice\Weather\Weather;
use Mockery\Matcher\AnyArgs;
use PHPUnit\Framework\TestCase;

class WeatherTest extends TestCase
{
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid type value(base/all):foo");
        $w->getWeather("济南", "foo");
        $this->fail("Failed to assert getWeather throw exception with invalid argument.");
    }

    // 检查 $format 参数
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');

        // 断言会抛出此异常类
        $this->expectException(InvalidArgumentException::class);

        // 断言异常消息为 'Invalid response format: array'
        $this->expectExceptionMessage('Invalid response format:array');

        // 因为支持的格式为 xml/json，所以传入 array 会抛出异常
        $w->getWeather('济南', 'base', 'array');

        // 如果没有抛出异常，就会运行到这行，标记当前测试没成功
        $this->fail('Failed to assert getWeather throw exception with invalid argument.');
    }

    public function testGetWeather()
    {
        $response = new Response(200, [], '{"success": true}');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '济南',
                'output' =>'json',
                'extensions' => 'base'
            ]
        ])->andReturn($response);
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getWeather('济南'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('request timeout'));
        $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');
        $w->getWeather('济南');
    }

    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');
        $this->assertInstanceOf( ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');
        // 设置参数前 timeout 为null
        $this->assertNull($w->getHttpClient()->getConfig('timeout'));
        $w->setGuzzleOptions(['timeout' => 5000]);
        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }
}
