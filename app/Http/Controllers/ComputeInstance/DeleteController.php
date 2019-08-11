<?php

namespace App\Http\Controllers\ComputeInstance;

use App\ComputeInstance;
use App\ComputeInstanceResource;
use App\Utils\ComputeInstanceUtils;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeleteController extends Controller
{
    public function __invoke(Request $request, ComputeInstanceResource $computeInstanceResource)
    {
        $util = $computeInstanceResource->getComputeInstanceUtils();
        $util->delete($request->deleteAttachedVolumes);

        try {
            $computeInstanceResource->getComputeInstanceModel()->delete();
        } catch (ModelNotFoundException $e) {
        }

        return ["result" => true];
    }
}
