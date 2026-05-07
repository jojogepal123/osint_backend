<?php

/**
 * Class HLRRESTClientResponseObject
 *
 * An instance of this class is returned by the get, post, put or delete methods in HLRLookupClient
 */

namespace HlrLookup;

class HLRRESTClientResponseObject
{
    /**
     * Contains the HTTP response in plain text. Usually this is a JSON string
     *
     * @var string
     */
    public $responseBody = null;

    /**
     * Contains the HTTP response headers in plain text
     *
     * @var string (headers are separated by \n\n)
     */
    public $responseHeaders = null;

    /**
     * The numeric HTTP status code, as given by the HLR Lookups server
     *
     * @var int
     */
    public $httpStatusCode = null;

    /**
     * Plain text curl error description, if any
     *
     * @var string
     */
    public $curlError = null;

    /**
     * The numeric curl error code, if any
     *
     * @var int
     */
    public $curlErrNo = null;

    /**
     * Contains an array with the entire curl information, as given by PHP's native curl_info().
     *
     * @var array
     */
    public $curlInfo = null;

    public function __construct($responseBody, $responseHeaders, $httpStatusCode, $curlError, $curlErrNo, $curlInfo)
    {
        foreach (get_defined_vars() as $key => $value) {
            $this->$key = $value;
        }
    }
}
