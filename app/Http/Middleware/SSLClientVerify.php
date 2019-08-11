<?php

namespace App\Http\Middleware;

use App\IssuedCertificate;
use App\SlaveAPIRequestLog;
use Closure;
use Illuminate\Http\Request;

class SSLClientVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->isVerifySuccess())
            return $this->makeResponse($request, "Unauthenticated");
        if (($serialNumber = $this->isSerialNumberValid($request)) === false)
            return $this->makeResponse($request, "Invalid serial number");

        SlaveAPIRequestLog::query()->create([
            "client_ip" => $request->ip(),
            "client_certificate_serial_number" => $serialNumber,
            "url" => $request->fullUrl(),
            "raw_request_body" => $request->getContent(),
        ]);

        return $next($request);
    }

    private function isVerifySuccess()
    {
        return @$_SERVER["SSL_CLIENT_VERIFY"] === "SUCCESS";
    }

    private function isSerialNumberValid(Request $request)
    {
        $serialNumber = hexdec(@$_SERVER["SSL_CLIENT_SERIAL"]);

        // Some certificate may not set serial number
        if ($serialNumber === 0)
            return false;

        $matchedIssuedCertificate = IssuedCertificate::query()
            ->where([
                "status" => IssuedCertificate::STATUS_NORMAL,
                "serial_number" => $serialNumber,
            ])
            ->first()
        ;

        if (is_null($matchedIssuedCertificate))
            return false;
        return $serialNumber;
    }

    private function makeResponse(Request $request, $message)
    {
        if ($request->expectsJson()) {
            return response(json_encode([
                "result" => false,
                "message" => $message,
            ]), 400);
        }

        return response($message, 400);
    }
}
