<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-26
 * Time: 下午7:41
 */

namespace App\Utils\InstanceOSConfigure\Exception;


interface ErrorCode
{
    const INVALID_CONSTRUCTOR_ARGUMENT = 10001;

    const GUEST_AGENT_RESPONSE_DECODE_UNSUCCESSFULLY = 10002;

    const UNSUPPORTED_OS = 10003;
}