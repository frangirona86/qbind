# Italian VAT Number Validator

A PHP-based application for validating Italian VAT numbers using Object-Oriented Programming principles. The system processes CSV files containing VAT numbers, validates them, attempts corrections, and stores results in a MySQL database.

## Features

- **CSV File Upload**: Process multiple VAT numbers from uploaded CSV files
- **Single VAT Validation**: Test individual VAT numbers with detailed feedback
- **Automatic Correction**: Attempts to fix common formatting errors
- **Database Storage**: Stores all validation results with timestamps
- **Three-State Results Display**:
  - ✅ Acceptable VAT numbers
  - ⚠️ Corrected VAT numbers (with modification details)
  - ❌ Incorrect VAT numbers
- **Responsive UI**: Bootstrap-based interface for better user experience

## Requirements

- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

## Installation

1. **Clone or download the project** to your XAMPP htdocs folder:
   ```
   C:\xampp\htdocs\QBind\
   ```

2. **Start XAMPP**:
   - Start Apache and MySQL services
   - Open phpMyAdmin (http://localhost/phpmyadmin)

3. **Create Database**:
   - Import the `database/setup.sql` file in phpMyAdmin, OR
   - Run the SQL commands manually:
   ```sql
   CREATE DATABASE qbind_vat;
   USE qbind_vat;
   
   CREATE TABLE vat_validations (
       id INT AUTO_INCREMENT PRIMARY KEY,
       original_vat VARCHAR(20) NOT NULL,
       cleaned_vat VARCHAR(20) NOT NULL,
       corrected_vat VARCHAR(20) NULL,
       status ENUM('acceptable', 'corrected', 'incorrect') NOT NULL,
       message TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

4. **Configure Database Connection**:
   - Edit `config/database.php`
   - Update credentials if needed (default: root/root for XAMPP)

5. **Set Permissions**:
   - Ensure the `uploads/` directory is writable (will be created automatically)

## Usage

### CSV File Upload
1. Prepare a CSV file with VAT numbers in the first column
2. Navigate to the application: `http://localhost/QBind/`
3. Upload your CSV file using the "CSV File Upload" form
4. View results on the results page

### Single VAT Validation
1. Use the "Validate String" form
2. Enter a single VAT number
3. Get immediate feedback with validation status


**Validation Rules**:
- Must start with "IT"
- Must be followed by exactly 11 digits
- Checksum validation using Italian VAT algorithm

## Database Schema

### vat_validations Table
- `id`: Primary key
- `original_vat`: Original input VAT number
- `cleaned_vat`: VAT number after basic cleaning
- `corrected_vat`: Corrected VAT number (if applicable)
- `status`: Validation status (acceptable/corrected/incorrect)
- `message`: Detailed validation message
- `created_at`: Timestamp of validation


## Testing

Use the provided `sample_vat_numbers.csv` file to test the application:

```csv
vat_number
IT12345678901
IT98765432109
IT11111111111
98765432158
IT12345
123-hello
```