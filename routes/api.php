<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(\App\Http\Middleware\SSLClientVerify::class)->group(function () {
    Route::get('/phpinfo', "PHPInformation");

    Route::post('/masterServers/register', "MasterServer\\Register");

    Route::any('/ping', "PingController");

    Route::get("/ccms/slave/uuid", "CCMSSlaveController@nodeUUID");

    Route::post('/computeInstances/setupRequests', 'ComputeInstance\\ComputeInstanceController@setup');
    Route::post('/computeInstances/{computeInstanceResource}/reconfigure', 'ComputeInstance\\ComputeInstanceController@reconfigure');
    Route::post('/computeInstances/{computeInstanceResource}/changeHostname', 'ComputeInstance\\ComputeInstanceController@changeHostname');
    Route::post('/computeInstances/{computeInstanceResource}/changeOSPassword', 'ComputeInstance\\ComputeInstanceController@changeOSPassword');
    Route::post('/computeInstances/{computeInstanceResource}/reconfigureOSNetwork', 'ComputeInstance\\ComputeInstanceController@reconfigureOSNetwork');
    Route::post('/computeInstances/{computeInstanceResource}/networkInterfaces/updateIPAddresses', "ComputeInstance\\NetworkInterfaceController@updateIPAddresses");
    Route::post('/computeInstances/{computeInstanceResource}/networkInterfaces/changeModel', "ComputeInstance\\NetworkInterfaceController@changeModel");


    Route::any('/computeInstances/{computeInstanceResource}/delete', 'ComputeInstance\\DeleteController');

    Route::any('/computeInstances/{computeInstanceResource}/power/on', 'ComputeInstance\\PowerController@on');
    Route::any('/computeInstances/{computeInstanceResource}/power/off', 'ComputeInstance\\PowerController@off');
    Route::any('/computeInstances/{computeInstanceResource}/power/reset', 'ComputeInstance\\PowerController@reset');

    Route::any('/computeInstances/{computeInstanceResource}/volumes/{volumeUniqueId}/detach', 'StorageVolume\\StorageVolumeController@detach');
    Route::any('/computeInstances/{computeInstanceResource}/volumes/{volumeUniqueId}/attach', 'StorageVolume\\StorageVolumeController@attach');

    Route::any('/computeInstances/{computeInstanceResource}/media/{diskDeviceCode}/{deviceIndex}/{mediaInternalName?}', 'ComputeInstance\\MediaController@changeMedia');

    Route::post("/storageVolumes/new", "StorageVolume\\StorageVolumeController@newVolume");

    Route::post("/storageVolumes/{storageVolumeResource}/resize", "StorageVolume\\StorageVolumeController@resize");
    Route::post("/storageVolumes/{storageVolumeResource}/recreate", "StorageVolume\\StorageVolumeController@recreate");
    Route::post("/storageVolumes/{storageVolumeResource}/release", "StorageVolume\\StorageVolumeController@release");

    Route::post("/noVNC/configurations", "NOVNCController@updateConfiguration");
});

Route::middleware(\App\Http\Middleware\LocalAPIAuthenticate::class)->group(function () {
    Route::prefix("localOnly")->group(function () {
        Route::get("/noVNC/authenticate", "NOVNCController@authenticate");
        Route::post("/guest/fromHostOnly","GuestAgentController@fromHostOnly");
    });
});
