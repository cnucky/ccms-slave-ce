<?php

namespace App\Console\Commands\Image\PublicImage;

use App\Constants\Image\ImageTypeCode;
use App\PublicImage;
use App\Utils\Image\PublicImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Scan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pimage:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan public images.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $images = PublicImages::scan();

        $imageRecords = [];

        foreach ($images as $image => $versions) {
            foreach ($versions as $version => $path) {
                $this->info(sprintf("%s:%s -> %s", $image, $version, $path));
                $imageRecords[] = [
                    "name" => $image,
                    "version" => $version,
                    "path" => $path,
                    "format" => "qcow2",
                    "type" => ImageTypeCode::TYPE_AUTO_DISCOVERED,
                ];
            }
        }

        DB::transaction(function () use (&$images, &$imageRecords) {
            PublicImage::query()->where("type", "=", ImageTypeCode::TYPE_AUTO_DISCOVERED)->delete();
            PublicImage::query()->insert($imageRecords);
        });

        return 0;
    }
}
