<?php
/**
 * Created by PhpStorm.
 * Date: 19-3-29
 * Time: 下午1:29
 */

namespace App\Constants;


interface UploadStatus
{
    const STATUS_READY_FOR_UPLOADING = 0;

    const STATUS_UPLOADING = 1;

    const STATUS_UPLOADED = 2;
}