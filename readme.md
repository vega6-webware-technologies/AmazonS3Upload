# AmazonS3Upload
Class to upload any files in a given bucket in Amazon S3

## Installation
```
composer require vega6-dev/amazon-s3-upload
```

## Initialize
```
$s3Service = new S3Service(S3_KEY, S3_SECRET, S3_BUCKET);
```
### set region
```
$s3Service->setS3Region('us-west-1');
```
### set version
```
$s3Service->setS3Version('latest');
```
### set HTTP Verfity
```
$s3Service->setS3HttpVerify(false);
```

# upload
```
try {

    $s3_url = $s3Service->moveToS3($local_file, $key, $acl);
    return $s3_url;
} catch (Exception $e) {
    print($e->getMessage();
}
```
