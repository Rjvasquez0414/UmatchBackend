<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    protected $apiKey;
    protected $lat;
    protected $lon;
    protected $baseUrl = 'https://atlas.microsoft.com/weather';

    public function __construct()
    {
        $this->apiKey = config('services.azure_maps.api_key');
        $this->lat = config('services.azure_maps.weather_lat');
        $this->lon = config('services.azure_maps.weather_lon');
    }

    /**
     * Obtener el clima actual con caché de 15 minutos
     */
    public function getCurrentWeather()
    {
        try {
            return Cache::remember('weather_current', now()->addMinutes(15), function () {
                $response = Http::get("{$this->baseUrl}/currentConditions/json", [
                    'api-version' => '1.0',
                    'subscription-key' => $this->apiKey,
                    'query' => "{$this->lat},{$this->lon}",
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['results'][0])) {
                        $weather = $data['results'][0];

                        return [
                            'temperature' => $weather['temperature']['value'] ?? null,
                            'temperature_unit' => $weather['temperature']['unit'] ?? 'C',
                            'feels_like' => $weather['realFeelTemperature']['value'] ?? null,
                            'description' => $weather['phrase'] ?? 'Desconocido',
                            'icon_code' => $weather['iconCode'] ?? 1,
                            'humidity' => $weather['relativeHumidity'] ?? null,
                            'wind_speed' => $weather['wind']['speed']['value'] ?? null,
                            'wind_unit' => $weather['wind']['speed']['unit'] ?? 'km/h',
                            'precipitation' => $weather['hasPrecipitation'] ?? false,
                            'uv_index' => $weather['uvIndex'] ?? null,
                            'uv_description' => $weather['uvIndexPhrase'] ?? null,
                            'cloud_cover' => $weather['cloudCover'] ?? null,
                            'timestamp' => now()->toIso8601String(),
                        ];
                    }
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error fetching weather: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener pronóstico por horas (próximas 12 horas)
     */
    public function getHourlyForecast($hours = 12)
    {
        try {
            return Cache::remember("weather_hourly_{$hours}", now()->addHours(1), function () use ($hours) {
                $response = Http::get("{$this->baseUrl}/forecast/hourly/json", [
                    'api-version' => '1.0',
                    'subscription-key' => $this->apiKey,
                    'query' => "{$this->lat},{$this->lon}",
                    'duration' => $hours,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['forecasts'])) {
                        return collect($data['forecasts'])->map(function ($forecast) {
                            return [
                                'datetime' => $forecast['date'] ?? null,
                                'temperature' => $forecast['temperature']['value'] ?? null,
                                'description' => $forecast['iconPhrase'] ?? 'Desconocido',
                                'icon_code' => $forecast['iconCode'] ?? 1,
                                'precipitation_probability' => $forecast['precipitationProbability'] ?? 0,
                                'rain_probability' => $forecast['rainProbability'] ?? 0,
                            ];
                        })->toArray();
                    }
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error fetching hourly forecast: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener pronóstico diario (próximos 5 días)
     */
    public function getDailyForecast($days = 5)
    {
        try {
            return Cache::remember("weather_daily_{$days}", now()->addHours(6), function () use ($days) {
                $response = Http::get("{$this->baseUrl}/forecast/daily/json", [
                    'api-version' => '1.0',
                    'subscription-key' => $this->apiKey,
                    'query' => "{$this->lat},{$this->lon}",
                    'duration' => $days,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['forecasts'])) {
                        return collect($data['forecasts'])->map(function ($forecast) {
                            return [
                                'date' => $forecast['date'] ?? null,
                                'temp_max' => $forecast['temperature']['maximum']['value'] ?? null,
                                'temp_min' => $forecast['temperature']['minimum']['value'] ?? null,
                                'day_description' => $forecast['day']['iconPhrase'] ?? 'Desconocido',
                                'day_icon_code' => $forecast['day']['iconCode'] ?? 1,
                                'precipitation_probability' => $forecast['day']['precipitationProbability'] ?? 0,
                                'rain_probability' => $forecast['day']['rainProbability'] ?? 0,
                            ];
                        })->toArray();
                    }
                }

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Error fetching daily forecast: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Determinar si el clima es apropiado para eventos al aire libre
     */
    public function isOutdoorFriendly()
    {
        $weather = $this->getCurrentWeather();

        if (!$weather) {
            return [
                'friendly' => true,
                'warning' => null,
                'reason' => 'No se pudo obtener información del clima',
            ];
        }

        // Temperatura extrema
        if ($weather['temperature'] > 35) {
            return [
                'friendly' => false,
                'warning' => 'extreme_heat',
                'reason' => 'Temperatura muy alta: ' . $weather['temperature'] . '°C',
            ];
        }

        if ($weather['temperature'] < 10) {
            return [
                'friendly' => false,
                'warning' => 'cold',
                'reason' => 'Temperatura muy baja: ' . $weather['temperature'] . '°C',
            ];
        }

        // Precipitación
        if ($weather['precipitation']) {
            return [
                'friendly' => false,
                'warning' => 'rain',
                'reason' => 'Precipitaciones actuales',
            ];
        }

        // UV index muy alto
        if ($weather['uv_index'] && $weather['uv_index'] >= 8) {
            return [
                'friendly' => true,
                'warning' => 'high_uv',
                'reason' => 'Índice UV muy alto: ' . $weather['uv_index'] . '. Se recomienda protector solar.',
            ];
        }

        // Clima favorable
        return [
            'friendly' => true,
            'warning' => null,
            'reason' => 'Clima favorable para actividades al aire libre',
        ];
    }

    /**
     * Obtener el ícono del clima según el código
     */
    public function getWeatherIcon($iconCode)
    {
        // Mapeo básico de códigos a emojis
        $iconMap = [
            1 => '☀️', // Sunny
            2 => '🌤️', // Mostly Sunny
            3 => '🌤️', // Partly Sunny
            4 => '🌥️', // Intermittent Clouds
            5 => '🌥️', // Hazy Sunshine
            6 => '☁️', // Mostly Cloudy
            7 => '☁️', // Cloudy
            8 => '☁️', // Overcast
            11 => '🌫️', // Fog
            12 => '🌧️', // Showers
            13 => '🌦️', // Mostly Cloudy w/ Showers
            14 => '🌦️', // Partly Sunny w/ Showers
            15 => '⛈️', // T-Storms
            16 => '⛈️', // Mostly Cloudy w/ T-Storms
            17 => '⛈️', // Partly Sunny w/ T-Storms
            18 => '🌧️', // Rain
        ];

        return $iconMap[$iconCode] ?? '🌤️';
    }
}
