<?php
// includes/storage.php
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Storage {
    private static $isSpacesConfigured = false;
    private static $s3 = null;
    
    public static function init() {
        $key = getenv('DO_SPACES_KEY') ?: '';
        $secret = getenv('DO_SPACES_SECRET') ?: '';
        $endpoint = getenv('DO_SPACES_ENDPOINT') ?: ''; // e.g. sgp1.digitaloceanspaces.com
        
        if ($key !== '' && $secret !== '' && $endpoint !== '') {
            self::$isSpacesConfigured = true;
            
            // Extract region (e.g., "sgp1" from "sgp1.digitaloceanspaces.com")
            $region = explode('.', $endpoint)[0];
            
            try {
                self::$s3 = new S3Client([
                    'version' => 'latest',
                    'region'  => $region,
                    'endpoint' => "https://" . $endpoint,
                    'credentials' => [
                        'key'    => $key,
                        'secret' => $secret,
                    ],
                    'use_path_style_endpoint' => false,
                ]);
            } catch (Exception $e) {
                error_log("Failed to initialize S3 Client: " . $e->getMessage());
                self::$isSpacesConfigured = false;
            }
        }
    }
    
    /**
     * Upload a file either to DO Spaces or locally.
     * @param string $tmpFilePath
     * @param string $destinationPath Relative path (e.g. 'gigs/image.jpg')
     * @param string $mimeType
     * @return string|false Return the URL (for Spaces) or relative path (for local)
     */
    public static function upload($tmpFilePath, $destinationPath, $mimeType = 'application/octet-stream') {
        self::init();
        
        if (self::$isSpacesConfigured) {
            $bucket = getenv('DO_SPACES_BUCKET') ?: '';
            
            try {
                $result = self::$s3->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $destinationPath,
                    'SourceFile' => $tmpFilePath,
                    'ACL'    => 'public-read',
                    'ContentType' => $mimeType,
                ]);
                
                return $result['ObjectURL'];
            } catch (AwsException $e) {
                error_log("DigitalOcean Spaces Upload Error: " . $e->getMessage());
                return false;
            }
        } else {
            // Local fallback
            $fullPath = __DIR__ . '/../uploads/' . ltrim($destinationPath, '/');
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if (move_uploaded_file($tmpFilePath, $fullPath)) {
                return 'uploads/' . ltrim($destinationPath, '/');
            }
            return false;
        }
    }
}
?>
