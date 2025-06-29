<?php
class VATValidator {
    
    /**
     * Validates Italian VAT number
     * @param string $vatNumber
     * @return array
     */
    public function validateItalianVAT($vatNumber) {
        // Store original VAT number
        $originalVat = $vatNumber;
        
        // First, check if it's a 11-digit number without IT prefix (needs correction)
        $digitsOnly = preg_replace('/[^0-9]/', '', $originalVat);
        if (strlen($digitsOnly) === 11 && !preg_match('/^IT/i', $originalVat)) {
            $correctedVat = 'IT' . $digitsOnly;
            return [
                'original' => $originalVat,
                'cleaned' => $originalVat,
                'corrected' => $correctedVat,
                'is_valid' => true,
                'status' => 'corrected',
                'message' => 'Added IT prefix to 11-digit number',
                'corrections' => [$correctedVat]
            ];
        }
        
        // Clean the VAT number for other validations
        $vatNumber = $this->cleanVATNumber($vatNumber);
        
        // Check if it's a valid Italian VAT format
        if (!$this->isValidItalianFormat($vatNumber)) {
            return [
                'original' => $originalVat,
                'cleaned' => $vatNumber,
                'is_valid' => false,
                'status' => 'incorrect',
                'message' => 'Invalid Italian VAT format',
                'corrections' => []
            ];
        }
        
        // If it passes format validation, it's acceptable
        return [
            'original' => $originalVat,
            'cleaned' => $vatNumber,
            'is_valid' => true,
            'status' => 'acceptable',
            'message' => 'Valid Italian VAT number',
            'corrections' => []
        ];
    }
    
    /**
     * Clean VAT number
     */
    private function cleanVATNumber($vatNumber) {
        // Remove spaces, dots, and other non-alphanumeric characters
        $vatNumber = preg_replace('/[^A-Z0-9]/', '', strtoupper($vatNumber));
        
        // Ensure it starts with IT
        if (!preg_match('/^IT/', $vatNumber)) {
            $vatNumber = 'IT' . $vatNumber;
        }
        
        return $vatNumber;
    }
    
    /**
     * Check if format is valid for Italian VAT
     */
    private function isValidItalianFormat($vatNumber) {
        // Italian VAT: IT + exactly 11 digits
        return preg_match('/^IT\d{11}$/', $vatNumber);
    }
}
?> 