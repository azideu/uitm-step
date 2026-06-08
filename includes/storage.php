<?php
// includes/storage.php
// Check if vendor/autoload exists, if not skip AWS SDK loading
$autoload_path = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

// Use statements are optional depending on AWS SDK availability
if (class_exists('Aws\S3\S3Client')) {
    // AWS SDK is available, we can use it
}

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
                if (class_exists('Aws\S3\S3Client')) {
                    self::$s3 = new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region'  => $region,
                        'endpoint' => "https://" . $endpoint,
                        'credentials' => [
                            'key'    => $key,
                            'secret' => $secret,
                        ],
                        'use_path_style_endpoint' => false,
                    ]);
                } else {
                    error_log("AWS SDK not installed. Falling back to local storage.");
                    self::$isSpacesConfigured = false;
                }
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
        
        if (self::$isSpacesConfigured && class_exists('Aws\S3\S3Client')) {
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
            } catch (Exception $e) {
                error_log("DigitalOcean Spaces Upload Error: " . $e->getMessage());
                // Fallback to local storage on error
                return self::uploadLocal($tmpFilePath, $destinationPath);
            }
        } else {
            // Local fallback
            return self::uploadLocal($tmpFilePath, $destinationPath);
        }
    }
    
    /**
     * Upload file to local storage
     * @param string $tmpFilePath
     * @param string $destinationPath
     * @return string|false
     */
    private static function uploadLocal($tmpFilePath, $destinationPath) {
        try {
            $fullPath = __DIR__ . '/../uploads/' . ltrim($destinationPath, '/');
            $dir = dirname($fullPath);
            
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    error_log("Failed to create directory: $dir");
                    return false;
                }
            }
            
            if (move_uploaded_file($tmpFilePath, $fullPath)) {
                return 'uploads/' . ltrim($destinationPath, '/');
            }
            
            error_log("Failed to move uploaded file from $tmpFilePath to $fullPath");
            return false;
        } catch (Exception $e) {
            error_log("Local upload error: " . $e->getMessage());
            return false;
        }
    }
}
?>
