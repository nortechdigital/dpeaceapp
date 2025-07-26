<?php
// pg/receipt.php
if (!isset($_SESSION['last_receipt'])) {
    header("Location: ../?page=dashboard");
    exit;
}

$receipt = $_SESSION['last_receipt'];
unset($_SESSION['last_receipt']);

// Map network IDs to names
$networkNames = [
    'mtn' => 'MTN',
    'airtel' => 'Airtel',
    'glo' => 'Glo',
    '9mobile' => '9Mobile'
];
$network = $networkNames[strtolower($receipt['network'])] ?? $receipt['network'];
?>

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Data Purchase Receipt</h3>
                <div>
                    <span class="badge bg-<?= $receipt['status'] == 'Success' ? 'success' : ($receipt['status'] == 'Pending' ? 'warning' : 'danger') ?>">
                        <?= htmlspecialchars($receipt['status']) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Transaction ID</h5>
                    <p class="text-muted"><?= htmlspecialchars($receipt['transaction_id']) ?></p>
                    
                    <h5 class="mt-3">Date/Time</h5>
                    <p class="text-muted"><?= htmlspecialchars($receipt['date']) ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Reference</h5>
                    <p class="text-muted"><?= htmlspecialchars($receipt['reference']) ?></p>
                </div>
            </div>
            
            <hr>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Customer</h5>
                    <p><?= htmlspecialchars($receipt['customer']) ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Service</h5>
                    <p><?= htmlspecialchars($receipt['service']) ?></p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Network</h5>
                    <p><?= htmlspecialchars($network) ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Phone Number</h5>
                    <p><?= htmlspecialchars($receipt['phone']) ?></p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Data Bundle</h5>
                    <p><?= htmlspecialchars($receipt['bundle']) ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Amount</h5>
                    <p>₦<?= htmlspecialchars($receipt['amount']) ?></p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Discount</h5>
                    <p>₦<?= htmlspecialchars($receipt['discount']) ?></p>
                </div>
            </div>
            
            <hr>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Balance Before</h5>
                    <p>₦<?= htmlspecialchars($receipt['balance_before']) ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Balance After</h5>
                    <p>₦<?= htmlspecialchars($receipt['balance_after']) ?></p>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button onclick="window.print()" class="btn btn-primary me-3">
                    <i class="bi bi-printer-fill"></i> Print Receipt
                </button>
                <a href="?page=data" class="btn btn-outline-primary me-3">
                    <i class="bi bi-arrow-repeat"></i> Buy Again
                </a>
                <a href="?page=dashboard" class="btn btn-outline-secondary">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </div>
        </div>
        <div class="card-footer text-center text-muted">
            <small>Thank you for your purchase</small>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
        }
        .no-print, .btn {
            display: none !important;
        }
    }
</style>