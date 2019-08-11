<?php

namespace App\Providers;

use App\ComputeInstance;
use App\ComputeInstanceResource;
use App\StorageVolumeResource;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use YunInternet\Libvirt\Exception\ErrorCode;
use YunInternet\Libvirt\Exception\LibvirtException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();

        Route::bind('computeInstance', function ($value) {
            $builder = ComputeInstance::query();
            if (is_numeric($value))
                $builder->where("id", $value);
            else
                $builder->where("unique_id", $value);
            return $builder->firstOrFail();
        });

        Route::bind('libvirtDomain', function ($value) {
            return LibvirtConnection::getConnection()->libvirt_domain_lookup_by_name($value);
        });

        Route::bind('computeInstanceResource', function ($value) {
            return new ComputeInstanceResource($value);
        });

        Route::bind('storageVolumeResource', function ($value) {
            return new StorageVolumeResource($value);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
