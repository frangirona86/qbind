<?php
require_once 'controllers/VATController.php';

session_start();
$controller = new VATController();
$message = '';
$messageType = '';
$validationResult = null;

// Handle CSV Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $result = $controller->handleCSVUpload($_FILES['csvFile']);
    
    if ($result['success'] && $result['redirect']) {
        // Store results in session for the results page
        $_SESSION['current_results'] = $result['results'];
        $_SESSION['current_message'] = $result['message'];
        $_SESSION['current_message_type'] = $result['messageType'];
        
        header("Location: " . $result['redirect']);
        exit;
    } else {
        $message = $result['message'];
        $messageType = $result['messageType'];
    }
}

// Handle Single VAT Validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stringInput'])) {
    $vatNumber = trim($_POST['stringInput']);
    $result = $controller->handleSingleVATValidation($vatNumber);
    
    $message = $result['message'];
    $messageType = $result['messageType'];
    $validationResult = $result['validationResult'];
}

include 'Views/header.php';
?>

<?php if (!empty($message)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i><?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Single VAT Validation Result -->
<?php if ($validationResult): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-search me-2"></i>Validation Result</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Original VAT:</strong></td>
                                <td><code><?php echo htmlspecialchars($validationResult['original']); ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Cleaned VAT:</strong></td>
                                <td><code><?php echo htmlspecialchars($validationResult['cleaned']); ?></code></td>
                            </tr>
                            <?php if (isset($validationResult['corrected'])): ?>
                            <tr>
                                <td><strong>Corrected VAT:</strong></td>
                                <td><code class="text-success"><?php echo htmlspecialchars($validationResult['corrected']); ?></code></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php 
                                    $statusClass = '';
                                    $statusText = '';
                                    switch($validationResult['status']) {
                                        case 'acceptable':
                                            $statusClass = 'bg-success';
                                            $statusText = 'Acceptable';
                                            break;
                                        case 'corrected':
                                            $statusClass = 'bg-warning';
                                            $statusText = 'Corrected';
                                            break;
                                        case 'incorrect':
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Incorrect';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Message:</strong></td>
                                <td><?php echo htmlspecialchars($validationResult['message']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-lightbulb me-2"></i>What this means:</h6>
                            <?php if ($validationResult['status'] === 'acceptable'): ?>
                                <p class="mb-0">This VAT number is valid and follows the correct Italian format (IT + 11 digits).</p>
                            <?php elseif ($validationResult['status'] === 'corrected'): ?>
                                <p class="mb-0">This VAT number was corrected by adding the IT prefix. The original number had 11 digits but was missing the country code.</p>
                            <?php else: ?>
                                <p class="mb-0">This VAT number is invalid. It doesn't follow the Italian VAT format requirements.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- CSV Upload Form -->
<div class="row mb-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>CSV File Upload</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Select CSV file:</label>
                        <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv" required>
                        <div class="form-text">
                            <strong>Required format:</strong> CSV file with exactly 2 columns: "id" and "vat_number". 
                            First row must be the header. Example: id,vat_number
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i>Upload File
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- String Validation Form -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Validate String</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="stringInput" class="form-label">Enter VAT number to validate:</label>
                        <input type="text" class="form-control" id="stringInput" name="stringInput" placeholder="e.g., IT12345678901 or 26828104042" required>
                        <div class="form-text">Enter a single Italian VAT number to validate individually.</div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-search me-2"></i>Validate VAT Number
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'Views/footer.php'; ?>
