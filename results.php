<?php
require_once 'controllers/VATController.php';

session_start();
$controller = new VATController();

$results = [];
$message = '';
$messageType = '';

// Get results from session
if (isset($_SESSION['current_results'])) {
    $results = $_SESSION['current_results'];
    $message = $_SESSION['current_message'] ?? "Results have been processed successfully.";
    $messageType = $_SESSION['current_message_type'] ?? 'success';
    
    // Clear session data after retrieving
    unset($_SESSION['current_results']);
    unset($_SESSION['current_message']);
    unset($_SESSION['current_message_type']);
} else {
    $message = "No results found. Please upload a CSV file first.";
    $messageType = 'warning';
}

// Group results by status
$acceptableResults = $results['acceptable'] ?? [];
$correctedResults = $results['corrected'] ?? [];
$incorrectResults = $results['incorrect'] ?? [];

include 'Views/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-<?php echo $messageType; ?>">
            <h4><i class="bi bi-info-circle me-2"></i>VAT Validation Results</h4>
            <p class="mb-0"><?php echo $message; ?></p>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Validation Statistics</h5>
            </div>
            <div class="card-body">
                <?php
                $total = count($acceptableResults) + count($correctedResults) + count($incorrectResults);
                ?>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-success bg-opacity-10">
                            <h3 class="text-success"><?php echo count($acceptableResults); ?></h3>
                            <p class="mb-0">Acceptable VAT Numbers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-warning bg-opacity-10">
                            <h3 class="text-warning"><?php echo count($correctedResults); ?></h3>
                            <p class="mb-0">Corrected VAT Numbers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-danger bg-opacity-10">
                            <h3 class="text-danger"><?php echo count($incorrectResults); ?></h3>
                            <p class="mb-0">Incorrect VAT Numbers</p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted">Total processed: <?php echo $total; ?> VAT numbers</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acceptable VAT Numbers -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Acceptable VAT Numbers (<?php echo count($acceptableResults); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Original VAT</th>
                                <th>Cleaned VAT</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($acceptableResults)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="bi bi-inbox me-2"></i>No acceptable VAT numbers found
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($acceptableResults as $result): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($result['original']); ?></code></td>
                                    <td><code><?php echo htmlspecialchars($result['cleaned']); ?></code></td>
                                    <td><span class="badge bg-success">Acceptable</span></td>
                                    <td><?php echo htmlspecialchars($result['message']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Corrected VAT Numbers -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Corrected VAT Numbers (<?php echo count($correctedResults); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Original VAT</th>
                                <th>Cleaned VAT</th>
                                <th>Corrected VAT</th>
                                <th>What was modified</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($correctedResults)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="bi bi-inbox me-2"></i>No corrected VAT numbers found
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($correctedResults as $result): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($result['original']); ?></code></td>
                                    <td><code><?php echo htmlspecialchars($result['cleaned']); ?></code></td>
                                    <td><code class="text-success"><?php echo htmlspecialchars($result['corrected']); ?></code></td>
                                    <td><?php echo htmlspecialchars($result['message']); ?></td>
                                    <td><span class="badge bg-warning">Corrected</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incorrect VAT Numbers -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-x-circle me-2"></i>Incorrect VAT Numbers (<?php echo count($incorrectResults); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Original VAT</th>
                                <th>Cleaned VAT</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incorrectResults)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="bi bi-inbox me-2"></i>No incorrect VAT numbers found
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($incorrectResults, 0, 50) as $result): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($result['original']); ?></code></td>
                                    <td><code><?php echo htmlspecialchars($result['cleaned']); ?></code></td>
                                    <td><span class="badge bg-danger">Incorrect</span></td>
                                    <td><?php echo htmlspecialchars($result['message']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($incorrectResults) > 50): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="bi bi-three-dots me-2"></i>Showing first 50 of <?php echo count($incorrectResults); ?> incorrect VAT numbers
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 text-center">
        <a href="index.php" class="btn btn-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Forms
        </a>
    </div>
</div>

<?php include 'Views/footer.php'; ?> 