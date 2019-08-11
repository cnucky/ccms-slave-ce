<?php

namespace App\Http\Controllers\ComputeInstance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use YunInternet\CCMSCommon\Constants\Constants;

trait Validator
{
    protected static $__unit32_validate_rule = "nullable|integer|min:0|max:" . 4294967295;

    protected function storeValidate(Request $request)
    {
        $this->validate($request, [
            "unique_id" => "required|max:64",
            "hostname" => [
                "nullable",
                "regex:/^[a-zA-Z\-\.\d]{2,64}$/",
            ],
            "vCPU" => "required|integer|min:1",
            "memory" => "required|integer|min:1",
            "inbound_bandwidth" => self::$__unit32_validate_rule,
            "outbound_bandwidth" => self::$__unit32_validate_rule,
            "io_weight" => "nullable|integer|min:10|max:1000",
            "read_bytes_sec" => self::$__unit32_validate_rule,
            "write_bytes_sec" => self::$__unit32_validate_rule,
            "read_iops_sec" => self::$__unit32_validate_rule,
            "write_iops_sec" => self::$__unit32_validate_rule,
        ]);
    }

    protected function imageValidate(Request $request)
    {
        $this->validate($request, [
            "image_type" => [
                Rule::in(["0", "2"])
            ]
        ]);

        if ($request->image_type == "0") {
            $this->validate($request, [
                "image" => ["nullable", function ($attribute, $value, $fail) {
                    if (!is_dir(Constants::PUBLIC_IMAGE_DIRECTORY . "/" . $value))
                        $fail("image not exists");
                }]
            ]);
        }
    }

    protected function passwordValidate(Request $request)
    {
        $this->validate($request, [
            "password" => "nullable|max:255",
        ]);
    }

    protected function vncPasswordValidate(Request $request)
    {
        $this->validate($request, [
            "vnc_password" => "nullable|max:8",
        ]);
    }

    protected function retrieveValues(Request $request)
    {
        return [
            "unique_id" => $request->unique_id,
            "hostname" => $request->hostname ? $request->hostname : $request->unique_id,
            "vCPU" => $request->vCPU,
            "memory" => $request->memory,
            "inbound_bandwidth" => $request->inbound_bandwidth,
            "outbound_bandwidth" => $request->outbound_bandwidth,
            "io_weight" => $request->io_weight,
            "read_bytes_sec" => $request->read_bytes_sec,
            "write_bytes_sec" => $request->write_bytes_sec,
            "read_iops_sec" => $request->read_iops_sec,
            "write_iops_sec" => $request->write_iops_sec,
            "no_clean_traffic" => intval(boolval($request->no_clean_traffic)),
        ];
    }

    protected function retrievePassword(Request $request, &$values = [])
    {
        $values["password"] = $request->password;
        return $request->password;
    }

    protected function retrieveVNCPassword(Request $request, &$values = [])
    {
        $vncPassword = $request->vnc_password ? $request->vnc_password : mt_rand(10000000, 99999999);
        $values["vnc_password"] = $vncPassword;
        return $vncPassword;
    }

    protected function retrieveImage(Request $request, &$values = [])
    {
        $values["image_type"] = $request->image_type;
        $values["image"] = $request->image;

        return [
            "image_type" => $request->image_type,
            "image" => $request->image,
        ];
    }
}
