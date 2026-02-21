<?php
/**
 * Upload Helper CEPUOK
 * File: helpers/UploadHelper.php
 */

class UploadHelper {
    private $uploadPath;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $maxSize = 2097152; // 2MB

    public function __construct() {
        $this->uploadPath = ROOT_PATH . '/uploads/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload file
     */
    public function uploadFile($file, $subfolder = '') {
        try {
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception($this->getUploadErrorMessage($file['error']));
            }

            // Validate file type
            if (!in_array($file['type'], $this->allowedTypes)) {
                throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF');
            }

            // Validate file size
            if ($file['size'] > $this->maxSize) {
                throw new Exception('File too large. Max size: 2MB');
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;

            // Determine upload path
            $uploadDir = $this->uploadPath;
            if (!empty($subfolder)) {
                $uploadDir .= $subfolder . '/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
            }

            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    'success' => true,
                    'filepath' => ($subfolder ? $subfolder . '/' : '') . $filename,
                    'filename' => $filename
                ];
            } else {
                throw new Exception('Failed to move uploaded file');
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete file
     */
    public function deleteFile($filepath) {
        $fullPath = $this->uploadPath . $filepath;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($error) {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive in HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
}