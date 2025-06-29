-- Create database
CREATE DATABASE IF NOT EXISTS qbind_vat;
USE qbind_vat;

-- Create VAT validations table
CREATE TABLE IF NOT EXISTS vat_validations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_vat VARCHAR(20) NOT NULL,
    cleaned_vat VARCHAR(20) NOT NULL,
    corrected_vat VARCHAR(20) NULL,
    status ENUM('acceptable', 'corrected', 'incorrect') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_status ON vat_validations(status);
CREATE INDEX idx_created_at ON vat_validations(created_at);
CREATE INDEX idx_original_vat ON vat_validations(original_vat); 