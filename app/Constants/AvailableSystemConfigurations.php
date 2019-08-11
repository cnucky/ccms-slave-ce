<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 上午12:20
 */

namespace App\Constants;


interface AvailableSystemConfigurations
{
    const CA_PRIVATE_KEY = "CAPrivateKey";
    const CA_CERTIFICATE = "CACertificate";
    const CA_CRL = "CACRL";

    const SERVER_PRIVATE_KEY = "ServerPrivateKey";
    const SERVER_CERTIFICATE = "ServerCertificate";

    const LIBVIRTD_LISTEN_TLS = "LibvirtdListenTLS";
    const LIBVIRTD_LISTEN_TCP = "LIbvirtdListenTCP";

    const LIBVIRTD_TLS_PORT = "LibvirtdTLSPort";
    const LIBVIRTD_TCP_PORT = "LibvirtdTCPPort";

    const LIBVIRTD_LISTEN_ADDRESS = "LibvirtdListenAddress";

    const LIBVIRTD_LOG_LEVEL = "LibvirtdLogLevel";
    const LIBVIRTD_LOG_OUTPUTS = "LibvirtdLogOutputs";

    const LIBVIRTD_ADDITIONAL_CONFIGURATION = "LibvirtdAdditionalConfiguration";

    const PUBLIC_IMAGE_SECRET = "PublicImageSecret";

    const IMAGE_SERVER = "ImageServer";

    const CCMS_SLAVE_UUID = "CCMSSlaveUUID";
}