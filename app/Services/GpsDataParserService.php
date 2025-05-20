<?php

namespace App\Services;

use Carbon\Carbon;

class GpsDataParserService
{
    public function parse(string $data): array
    {
        $parts = explode(',', $data);

        if (count($parts) !== 10) {
            throw new \InvalidArgumentException('Invalid GPS data format');
        }

        return [
            'latitude' => $this->convertNmeaToDecimal($parts[1]),
            'longitude' => $this->convertNmeaToDecimal($parts[2]),
            'date' => $this->parseDate($parts[4]),
            'time' => $this->convertUtcToGmt($parts[5]),
            'speed' => (float) $parts[6],
            'status' => $parts[8] === '1' ? 'on' : 'off',
            'imei' => $parts[9]
        ];
    }

    private function convertNmeaToDecimal(string $nmea): float
    {
        // NMEA format is ddmm.mmmm
        $degrees = (float) substr($nmea, 0, 2);
        $minutes = (float) substr($nmea, 2);
        return $degrees + ($minutes / 60);
    }

    private function parseDate(string $date): string
    {
        // Input format: ddmmyy
        $day = substr($date, 0, 2);
        $month = substr($date, 2, 2);
        $year = '20' . substr($date, 4, 2);

        return sprintf('%s-%s-%s', $year, $month, $day);
    }

    private function convertUtcToGmt(string $time): string
    {
        // Input format: hhmmss
        $hour = substr($time, 0, 2);
        $minute = substr($time, 2, 2);
        $second = substr($time, 4, 2);

        $utcTime = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            date('Y-m-d') . " {$hour}:{$minute}:{$second}",
            'UTC'
        );

        return $utcTime->setTimezone('GMT')->format('H:i:s');
    }
}
