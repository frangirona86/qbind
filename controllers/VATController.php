<?php
require_once 'includes/VATProcessor.php';

class VATController {
    private $processor;
    private $uploadDir = 'uploads/';
    
    public function __construct() {
        $this->processor = new VATProcessor();
        
        // Create uploads directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
        
        // Ensure database table exists
        $this->processor->createTable();
    }
    
    /**
     * Handle CSV file upload and processing
     */
    public function handleCSVUpload($file) {
        $result = [
            'success' => false,
            'message' => '',
            'messageType' => 'danger',
            'redirect' => null,
            'results' => null
        ];
        
        // Check if file was uploaded successfully
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['message'] = $this->getUploadErrorMessage($file['error']);
            return $result;
        }
        
        $uploadFile = $this->uploadDir . basename($file['name']);
        
        if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
            try {
                $results = $this->processor->processCSVFile($uploadFile);
                $totalProcessed = count($results['acceptable']) + count($results['corrected']) + count($results['incorrect']);
                
                $result['success'] = true;
                $result['message'] = "CSV file processed successfully! $totalProcessed VAT numbers validated.";
                $result['messageType'] = 'success';
                $result['results'] = $results;
                $result['redirect'] = "results.php?session_id=" . time();
                
            } catch (Exception $e) {
                $result['message'] = "Error processing CSV file: " . $e->getMessage();
                
                // Clean up uploaded file if processing failed
                if (file_exists($uploadFile)) {
                    unlink($uploadFile);
                }
            }
        } else {
            $result['message'] = "Error uploading file. Please try again.";
        }
        
        return $result;
    }
    
    /**
     * Handle single VAT number validation
     */
    public function handleSingleVATValidation($vatNumber) {
        $result = [
            'success' => false,
            'message' => '',
            'messageType' => 'danger',
            'validationResult' => null
        ];
        
        if (!empty($vatNumber)) {
            try {
                $validationResult = $this->processor->validateSingleVAT($vatNumber);
                $result['success'] = true;
                $result['message'] = "VAT validation completed.";
                $result['messageType'] = 'info';
                $result['validationResult'] = $validationResult;
            } catch (Exception $e) {
                $result['message'] = "Error validating VAT number: " . $e->getMessage();
            }
        } else {
            $result['message'] = "Please enter a VAT number to validate.";
        }
        
        return $result;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "File is too large. Maximum file size allowed is " . ini_get('upload_max_filesize');
            case UPLOAD_ERR_PARTIAL:
                return "File was only partially uploaded.";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded.";
            default:
                return "Upload error occurred.";
        }
    }
    
    /**
     * Get validation statistics
     */
    public function getStatistics() {
        return $this->processor->getStatistics();
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->processor->getConnection();
    }
}
?> 