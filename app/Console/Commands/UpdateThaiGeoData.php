<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Downloads the full Thai province/district/subdistrict dataset from
 * kongvut/thai-province-data and rebuilds database/data/thai/thai-geo.json.
 *
 * Usage (on VPS):  php artisan thai:update-geo
 */
class UpdateThaiGeoData extends Command
{
    protected $signature   = 'thai:update-geo';
    protected $description = 'Download and rebuild Thai geography data (province/district/subdistrict/zip)';

    public function handle(): int
    {
        $url = 'https://raw.githubusercontent.com/kongvut/thai-province-data/master/api/v2/th/province_with_amphure_subdistrict.json';

        $this->info("Downloading Thai geography data from GitHub…");

        $raw = @file_get_contents($url);

        if ($raw === false) {
            $this->error("Download failed. Check internet connectivity on this server.");
            return self::FAILURE;
        }

        $source = json_decode($raw, true);

        if (! is_array($source)) {
            $this->error("Invalid JSON received.");
            return self::FAILURE;
        }

        $this->info("Processing " . count($source) . " provinces…");

        $provinces    = [];
        $districts    = [];
        $subdistricts = [];
        $zips         = [];

        foreach ($source as $province) {
            $pId = (string) $province['id'];
            $provinces[$pId] = $province['name_th'];

            foreach (($province['amphure'] ?? []) as $district) {
                $dId = (string) $district['id'];
                $districts[$pId][$dId] = $district['name_th'];

                foreach (($district['tambon'] ?? []) as $tambon) {
                    $tId = (string) $tambon['id'];
                    $subdistricts[$dId][$tId] = $tambon['name_th'];
                    $zips[$tId] = (string) ($tambon['zip_code'] ?? '');
                }
            }
        }

        // Sort provinces by name for readable dropdowns
        asort($provinces);

        $geo = compact('provinces', 'districts', 'subdistricts', 'zips');

        $dir  = base_path('database/data/thai');
        $file = $dir . '/thai-geo.json';

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($file, json_encode($geo, JSON_UNESCAPED_UNICODE));

        $subdistrictCount = array_sum(array_map('count', $subdistricts));
        $districtCount    = array_sum(array_map('count', $districts));

        $this->info("Done! Written to $file");
        $this->line("  Provinces:    " . count($provinces));
        $this->line("  Districts:    $districtCount");
        $this->line("  Subdistricts: $subdistrictCount");
        $this->line("  Zip entries:  " . count($zips));

        // Reset static cache so the new data is loaded on next request
        $reflection = new \ReflectionClass(\App\Support\ThaiGeography::class);
        $prop = $reflection->getProperty('data');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

        return self::SUCCESS;
    }
}
