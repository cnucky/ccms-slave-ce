<?php
/**
 * Created by PhpStorm.
 * Date: 19-2-3
 * Time: 下午1:58
 */

namespace App\Utils\Master;


use YunInternet\CCMSCommon\Constants\Constants as CCMSConstants;
use YunInternet\CCMSCommon\Network\CommonHeader;
use YunInternet\CCMSCommon\Network\CommonOption;
use YunInternet\CCMSCommon\Network\Contract\APIRequestFactory;
use YunInternet\CCMSCommon\Network\CURLAPIRequest;
use YunInternet\CCMSCommon\Network\Utils\CURLCommon;

class MasterAPIRequestFactory implements APIRequestFactory
{
    use CommonHeader;

    use CommonOption;

    private $host;

    private $id;

    private $token;

    public function __construct($host, $id, $token)
    {
        $this->host = $host;

        $this->id = $id;

        $this->token = $token;

        $this
            ->addCommonHeader(CCMSConstants::CCMS_SLAVE_ID_HEADER_NAME, $this->id)
            ->addCommonHeader(CCMSConstants::CCMS_TOKEN_HEADER_NAME, $this->token)
            ->addCommonOption(CURLOPT_RETURNTRANSFER, true)
        ;
    }

    public function make($path, $data = null, $headers = null, $setOptions = null)
    {
        $url = $this->buildURL($path);

        // Build URL
        $ch = curl_init($url);

        // Set common options
        curl_setopt_array($ch, $this->commonOptions);

        // Set postfields
        $additionalHeaders = CURLCommon::setPostfieldsIfNeed($ch, $data);

        $cURLAPIRequest = new CURLAPIRequest($ch);
        // Add headers
        $cURLAPIRequest->setHeaderLists($this->commonHeaders, $additionalHeaders);

        return $cURLAPIRequest;
    }

    public static function makeDirectly($host, $id, $token, $path)
    {
        $factory = new self($host, $id, $token);
        return $factory->make($path);
    }

    private function buildURL($path)
    {
        $path = ltrim($path, "/");

        return sprintf("https://%s/api/slave/computeNodes/%s/%s", $this->host, $this->id, $path);
    }
}