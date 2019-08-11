<?php

namespace App\Http\Controllers;

use App\Constants\NoVNC;
use App\IssuedCertificate;
use App\MasterServer;
use App\Utils\Libvirt\LibvirtConnection;
use Illuminate\Http\Request;
use YunInternet\CCMSCommon\NOVNC\Authenticator;

class NOVNCController extends Controller
{
    public function authenticate(Request $request)
    {
        $serial = $request->serial;
        $certificate = IssuedCertificate::query()->where("serial_number", $serial)->firstOrFail();

        if (!Authenticator::verify($request->id, $request->salt, $request->expire_at, $request->serial,  $request->signature, $certificate->certificate, $message))
            return ["result" => false, "message" => $message];

        $domain = LibvirtConnection::getConnection()->domainLookupByName($request->id);
        return ["result" => true, "display" => $domain->vncDisplay()];
    }

    public function updateConfiguration(Request $request)
    {
        $oldUmask = umask(0027);
        if ($request->has("certificate")) {
            file_put_contents(NoVNC::CERTIFICATE_FILE_PATH, $request->get("certificate"));
        }
        if ($request->has("privateKey")) {
            file_put_contents(NoVNC::PRIVATE_KEY_FILE_PATH, $request->privateKey);
        }
        if ($request->has("port") && is_numeric($request->port)) {
            $port = $request->port;
            file_put_contents(NoVNC::CONSTANTS_FILE_PATH, <<<EOF
WS_PORT = "$port"
EOF
);
        } else {
            @unlink(NoVNC::CONSTANTS_FILE_PATH);
        }
        umask($oldUmask);
        exec("/usr/bin/sudo /usr/bin/supervisorctl restart ccms-slave-noVNC:websockify-automatic");
        return ["result" => true];
    }
}
