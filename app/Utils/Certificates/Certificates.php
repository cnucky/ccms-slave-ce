<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 上午1:36
 */

namespace App\Utils\Certificates;


use App\Constants\AvailableSystemConfigurations;
use App\SystemConfigurations;
use Illuminate\Support\Facades\Crypt;

class Certificates
{
    const PKI_PATH = __DIR__ . "/../../../resources/pki/";


    const CA_CERTIFICATE_FILE_PATH = self::PKI_PATH . "/CA/cacert.pem";

    const CA_CRL_FILE_PATH = self::PKI_PATH . "/CA/crl.pem";


    const SERVER_PRIVATE_KEY_FILE_PATH = self::PKI_PATH . "/private/serverkey.pem";

    const SERVER_CERTIFICATE_FILE_PATH = self::PKI_PATH . "/servercert.pem";

    const SERVER_FULL_CHAIN_CERTIFICATE_FILE_PATH = self::PKI_PATH . "/full-chain-servercert.pem";

    /**
     * Get decrypted CA private key from database
     * @return string|null
     */
    public static function getCAPrivateKey()
    {
        return Crypt::decryptString(SystemConfigurations::value(AvailableSystemConfigurations::CA_PRIVATE_KEY));
    }

    /**
     * Get CA certificate from database
     * @return string|null
     */
    public static function getCACertificate()
    {
        return SystemConfigurations::value(AvailableSystemConfigurations::CA_CERTIFICATE);
    }

    public static function writeCACertificate($certificate)
    {
        self::createParentDirectoryIfNotExists(self::CA_CERTIFICATE_FILE_PATH);
        file_put_contents(self::CA_CERTIFICATE_FILE_PATH, $certificate);
        chmod(self::CA_CERTIFICATE_FILE_PATH, 0644);
    }

    public static function writeCACRLFile($crl)
    {
        self::createParentDirectoryIfNotExists(self::CA_CRL_FILE_PATH);
        file_put_contents(self::CA_CRL_FILE_PATH, $crl);
        chmod(self::CA_CRL_FILE_PATH, 0644);
    }

    public static function writeServerPrivateKey($privateKey)
    {
        self::createParentDirectoryIfNotExists(self::SERVER_PRIVATE_KEY_FILE_PATH);
        file_put_contents(self::SERVER_PRIVATE_KEY_FILE_PATH, $privateKey);
        chmod(self::SERVER_PRIVATE_KEY_FILE_PATH, 0600);
    }

    public static function writeServerCertificateFile($certificate)
    {
        self::createParentDirectoryIfNotExists(self::SERVER_CERTIFICATE_FILE_PATH);
        file_put_contents(self::SERVER_CERTIFICATE_FILE_PATH, $certificate);
        chmod(self::SERVER_PRIVATE_KEY_FILE_PATH, 0644);
    }

    public static function writeFullChainServerCertificateFile($serverCertificate, ... $chainCertificates)
    {
        self::createParentDirectoryIfNotExists(self::SERVER_FULL_CHAIN_CERTIFICATE_FILE_PATH);
        $fp = fopen(self::SERVER_FULL_CHAIN_CERTIFICATE_FILE_PATH, "w+");
        fwrite($fp, $serverCertificate);
        foreach ($chainCertificates as $chainCertificate)
            fwrite($fp, $chainCertificate);
        fflush($fp);
        fclose($fp);
    }

    private static function createParentDirectoryIfNotExists($path)
    {
        $dirname = dirname($path);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755);
        }
    }
}