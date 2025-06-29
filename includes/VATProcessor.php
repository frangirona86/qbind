<?php
require_once 'config/database.php';
require_once 'includes/VATValidator.php';

class VATProcessor {
    private $db;
    private $validator;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->validator = new VATValidator();
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->db;
    }
    
    /**
     * Process CSV file with VAT numbers
     */
    public function processCSVFile($filePath) {
        $results = [
            'acceptable' => [],
            'corrected' => [],
            'incorrect' => []
        ];
        
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $row = 1;
            
            // Read header row and validate format
            $header = fgetcsv($handle, 1000, ",");
            if (!$this->validateCSVFormat($header)) {
                fclose($handle);
                throw new Exception("Invalid CSV format. File must contain exactly two columns: 'id' and 'vat_number'");
            }
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                
                // Check if row has enough columns
                if (count($data) < 2) {
                    fclose($handle);
                    throw new Exception("Row $row has insufficient columns. Expected 2 columns, found " . count($data));
                }
                
                $vatNumber = isset($data[1]) ? trim($data[1]) : '';
                if (!empty($vatNumber)) {
                    $validation = $this->validator->validateItalianVAT($vatNumber);
                    $this->saveToDatabase($validation);
                    
                    switch ($validation['status']) {
                        case 'acceptable':
                            $results['acceptable'][] = $validation;
                            break;
                        case 'corrected':
                            $results['corrected'][] = $validation;
                            break;
                        case 'incorrect':
                            $results['incorrect'][] = $validation;
                            break;
                    }
                }
            }
            fclose($handle);
        }
        
        return $results;
    }
    
    /**
     * Validate CSV file format
     */
    private function validateCSVFormat($header) {
        // Check if header has exactly 2 columns
        if (count($header) !== 2) {
            return false;
        }
        
        // Check if column names are correct (case insensitive)
        $expectedColumns = ['id', 'vat_number'];
        $headerLower = array_map('strtolower', array_map('trim', $header));
        
        return $headerLower === $expectedColumns;
    }
    
    /**
     * Validate single VAT number
     */
    public function validateSingleVAT($vatNumber) {
        $validation = $this->validator->validateItalianVAT($vatNumber);
        $this->saveToDatabase($validation);
        return $validation;
    }
    
    /**
     * Save validation result to database
     */
    private function saveToDatabase($validation) {
        $query = "INSERT INTO vat_validations 
                  (original_vat, cleaned_vat, corrected_vat, status, message, created_at) 
                  VALUES (:original, :cleaned, :corrected, :status, :message, NOW())";
        
        try {
            $stmt = $this->db->prepare($query);
            
            // Use bindValue instead of bindParam to avoid reference issues
            $original = $validation['original'];
            $cleaned = $validation['cleaned'];
            $corrected = isset($validation['corrected']) ? $validation['corrected'] : null;
            $status = $validation['status'];
            $message = $validation['message'];
            
            $stmt->bindValue(':original', $original);
            $stmt->bindValue(':cleaned', $cleaned);
            $stmt->bindValue(':corrected', $corrected);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':message', $message);
            
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get validation statistics
     */
    public function getStatistics() {
        $query = "SELECT status, COUNT(*) as count FROM vat_validations GROUP BY status";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * Create database table if not exists
     */
    public function createTable() {
        $query = "CREATE TABLE IF NOT EXISTS vat_validations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            original_vat VARCHAR(20) NOT NULL,
            cleaned_vat VARCHAR(20) NOT NULL,
            corrected_vat VARCHAR(20) NULL,
            status ENUM('acceptable', 'corrected', 'incorrect') NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->db->exec($query);
        } catch (PDOException $e) {
            error_log("Table creation error: " . $e->getMessage());
        }
    }
}
?> 