<?php

namespace App\Exceptions;

use App\Utils\InstanceOSConfigure\Exception\ConfiguratorException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use YunInternet\CCMSCommon\Constants\ErrorSource;
use YunInternet\Libvirt\Exception\LibvirtException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (($errorSource = $this->detectSource($exception)) !== false) {
            return new JsonResponse(["result" => false, "source" => $errorSource, "errno" => $exception->getCode(), "message" => $exception->getMessage()], 500);
        } else {
            return parent::render($request, $exception);
        }
    }

    private function detectSource(\Throwable $throwable)
    {
        if ($throwable instanceof LibvirtException) {
            return ErrorSource::SOURCE_LIBVIRT;
        } else if ($throwable instanceof ConfiguratorException) {
            return ErrorSource::SOURCE_OS_CONFIGURATOR;
        }
        return false;
    }
}
