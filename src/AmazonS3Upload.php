<?php
namespace Vega6Dev;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
class AmazonS3Upload
{
    private $S3_Key;
    private $S3_Secret;
    private $S3_Bucket;
    private $S3_Region;
    private $S3_Bucket_Key;
    private $S3_Version = 'latest';
    private $S3_http_verify = false;

    private $S3_Client;

    private $ACL_Default = 'public-read';

    /**
     * S3Service constructor.
     * @param null $s3_key
     * @param null $s3_secret
     * @param null $s3_bucket
     * @param null $s3_region
     * @param null $s3_version
     * @param bool $s3_http_verify
     */
    function __construct($s3_key, $s3_secret, $s3_bucket = null, $s3_region = null, $s3_version = null, $s3_http_verify = false)
    {
        // set s3 key
        if (!empty($s3_key) || $s3_key !== null) {
            $this->setS3Key($s3_key);
        }

        // set s3 secret
        if (!empty($s3_secret) || $s3_secret !== null) {
            $this->setS3Secret($s3_secret);
        }

        // set s3 bucket
        if (!empty($s3_bucket) || $s3_bucket !== null) {
            $this->setS3Bucket($s3_bucket);
        }

        // set s3 region
        if (!empty($s3_region) || $s3_region !== null) {
            $this->setS3Region($s3_region);
        }

        // set s3 version
        if (!empty($s3_version) || $s3_version !== null) {
            $this->setS3Version($s3_version);
        }

        // set s3 http verify
        $this->setS3HttpVerify($s3_http_verify);
    }

    /**
     * **********************************
     *     S E T T E R S
     * **********************************
     */

    /**
     * @param mixed $S3_Key
     */
    public function setS3Key($S3_Key)
    {
        $this->S3_Key = $S3_Key;
    }

    /**
     * @param mixed $S3_Secret
     */
    public function setS3Secret($S3_Secret)
    {
        $this->S3_Secret = $S3_Secret;
    }

    /**
     * @param mixed $S3_Bucket
     */
    public function setS3Bucket($S3_Bucket)
    {
        $this->S3_Bucket = $S3_Bucket;
    }

    /**
     * @param mixed $S3_Region
     */
    public function setS3Region($S3_Region)
    {
        $this->S3_Region = $S3_Region;
    }

    /**
     * @param string $S3_Version
     */
    public function setS3Version($S3_Version)
    {
        $this->S3_Version = $S3_Version;
    }

    /**
     * @param bool $S3_http_verify
     */
    public function setS3HttpVerify($S3_http_verify)
    {
        $this->S3_http_verify = $S3_http_verify;
    }


    /**
     * *********************************
     *     G E T T E R S
     * *********************************
     */

    /**
     * @return mixed
     */
    public function getS3Key()
    {
        return $this->S3_Key;
    }

    /**
     * @return mixed
     */
    public function getS3Secret()
    {
        return $this->S3_Secret;
    }

    /**
     * @return mixed
     */
    public function getS3Bucket()
    {
        return $this->S3_Bucket;
    }

    /**
     * @return mixed
     */
    public function getS3Region()
    {
        return $this->S3_Region;
    }

    /**
     * @return string
     */
    public function getS3Version()
    {
        return $this->S3_Version;
    }

    /**
     * @return bool
     */
    public function isS3HttpVerify()
    {
        return $this->S3_http_verify;
    }

    /**
     * **************************************
     *     S 3  C L I E N T  O B J E C T
     * **************************************
     */


    /**
     * generate s3 client using client credentials
     */
    public function setS3Client()
    {
        $s3 = new S3Client(array(
            'version' => $this->getS3Version(),
            'region' => $this->getS3Region(),
            'http' => array(
                'verify' => $this->isS3HttpVerify()
            ),
            'credentials' => array(
                'key' => $this->getS3Key(),
                'secret' => $this->getS3Secret()
            )
        ));
        return $s3;
    }

    /**
     * move local file to s3 server
     *
     * @param $local_file
     * @param $key
     * @param array $acl
     * @return string
     * @throws Exception
     *
     * supported acl values are
     * $acl = [
     *     'private',
     *     'public-read',
     *     'public-read-write',
     *     'authenticated-read',
     *     'aws-exec-read',
     *     'bucket-owner-read',
     *     'bucket-owner-full-control'
     * ]
     */
    public function moveToS3($local_file, $key, $acl = array())
    {
        $S3_Client = $this->setS3Client();

        /**
         * if file does not exists throw exception
         */
        if (!file_exists($local_file)) {
            throw new Exception('File does not exists: '.$local_file);
        }

        /**
         * parse act array to generate string
         */
        if (!empty($acl) || $acl !== null) {

            $acl_string = '';

            foreach ($acl as $item) {
                $acl_string .= $item.'|';
            }

            // truncate last |
            $acl_string = rtrim($acl_string, "|");
        } else {
            $acl_string = $this->ACL_Default;
        }

        /**
         * upload file to s3
         */
        try {
            $result = $S3_Client->putObject(array(
                'Bucket' => $this->getS3Bucket(),
                'Key' => $key,
                'Body' => fopen($local_file, 'rb'),
                'ACL' => $acl_string
            ));

            return $result->toArray()['ObjectURL'];
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}