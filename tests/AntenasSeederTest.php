<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../db/seeds/AntenasSeeder.php';

final class AntenasSeederTest extends TestCase
{
    private AntenasSeeder $seeder;

    protected function setUp(): void
    {
        $this->seeder = new AntenasSeeder();
    }

    public function testGerarLatitudeRetornaValorDentroDoIntervalo(): void
    {
        for ($i = 0; $i < 1000; $i++) {
            $latitude = $this->seeder->gerarLatitude();
            $this->assertIsFloat($latitude, 'Latitude deve ser float');
            $this->assertGreaterThanOrEqual(-90.0, $latitude, 'Latitude menor que -90');
            $this->assertLessThanOrEqual(90.0, $latitude, 'Latitude maior que 90');
        }
    }

    public function testGerarLongitudeRetornaValorDentroDoIntervalo(): void
    {
        for ($i = 0; $i < 1000; $i++) {
            $longitude = $this->seeder->gerarLongitude();
            $this->assertIsFloat($longitude, 'Longitude deve ser float');
            $this->assertGreaterThanOrEqual(-180.0, $longitude, 'Longitude menor que -180');
            $this->assertLessThanOrEqual(180.0, $longitude, 'Longitude maior que 180');
        }
    }
}
