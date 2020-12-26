<?php

namespace Lysice\Weather;

class WeatherServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Weather::class, function (){
            return new Weather(config('services.weather.key'));
        });
        $this->app->alias(Weather::class, 'weather');
    }
}
