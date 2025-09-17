<?php
header('Content-Type: text/html; charset=UTF-8');
// siparis.php - Order page
require_once 'db.php';

// Get table ID
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Get table information
$table = null;
if ($table_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->bind_param("i", $table_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $table = $result->fetch_assoc();
    $stmt->close();
}

// Redirect to main page if table not found
if (!$table) {
    header("Location: index.php");
    exit;
}
// siparis.php dosyasında, yazıcı ayarlarını alan kısmı bul ve GÜNCELLE:
$printer_settings = [];
$result = $conn->query("SELECT * FROM printer_settings ORDER BY id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $printer_settings = $result->fetch_assoc();
    
    // Bağlantı tipine göre doğru IP/Port bilgisini ayarla
    if ($printer_settings['connection_type'] === 'wifi') {
        $printer_ip = $printer_settings['printer_ip'] ?? '192.168.0.23';
        $printer_port = $printer_settings['printer_port'] ?? 9100;
    } 
    elseif ($printer_settings['connection_type'] === 'ethernet') {
        $printer_ip = $printer_settings['ethernet_ip'] ?? '192.168.0.23';
        $printer_port = $printer_settings['ethernet_port'] ?? 9100;
    }
    elseif ($printer_settings['connection_type'] === 'bluetooth') {
        $printer_ip = $printer_settings['bluetooth_mac'] ?? '';
        $printer_port = 1;
    }
    elseif ($printer_settings['connection_type'] === 'usb') {
        $printer_name = $printer_settings['printer_name'] ?? '';
    }
} else {
    // Varsayılan ayarlar
    $printer_settings = [
        'company_name' => 'Restaurant Name',
        'company_address' => '123 Main Street',
        'company_phone' => '(555) 123-4567',
        'footer_text' => 'Thank You, Come Again!',
        'receipt_width' => 58,
        'logo_alignment' => 'center',
        'logo_path' => null,
        'logo_width' => 200,
        'logo_height' => 100,
        'connection_type' => 'wifi',
        'printer_ip' => '192.168.0.23',
        'printer_port' => 9100
    ];
}
// Kullanıcının yazdırma tercihini kontrol et (cookie'den)
$print_preference = isset($_COOKIE['print_preference']) ? $_COOKIE['print_preference'] : 'ask';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  

    <title>Order - Table <?php echo $table['number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #2c3e50, #4a6580);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .category-list {
            list-style: none;
            padding: 0;
        }
        .category-item {
            padding: 12px 15px;
            margin-bottom: 5px;
            background-color: #e9ecef;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-align: left;
            width: 100%;
        }
        .category-item:hover, .category-item.active {
            background: linear-gradient(135deg, #2c3e50, #4a6580);
            color: white;
        }
        .product-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
            background: white;
        }
        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }
        .product-available {
            border-left: 4px solid #28a745;
        }
        .product-unavailable {
            border-left: 4px solid #dc3545;
            opacity: 0.6;
        }
        .order-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .order-header {
            background: linear-gradient(135deg, #2c3e50, #4a6580);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-list {
			/* max-height: 250px;*/         
            overflow-y: auto;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .order-item {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .quantity-btn {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .table-info {
            background: linear-gradient(135deg, #2c3e50, #4a6580);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .product-price {
            font-weight: bold;
            color: #2c3e50;
            font-size: 1.1em;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #dee2e6;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1.2em;
            font-weight: bold;
            border-top: 2px solid #2c3e50;
            margin-top: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2c3e50, #4a6580);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4a6580, #2c3e50);
        }
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #20c997, #28a745);
        }
        .numpad {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        .numpad-btn {
            padding: 12px;
            font-size: 18px;
            font-weight: bold;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .numpad-btn:hover {
            background: #f8f9fa;
            border-color: #2c3e50;
        }
        .money-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        .money-btn:hover {
            background: linear-gradient(135deg, #20c997, #28a745);
        }
        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .payment-btn {
            padding: 12px;
            text-align: center;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: bold;
        }
        .payment-btn.active {
            border-color: #2c3e50;
            background: #2c3e50;
            color: white;
        }
        .payment-btn:hover {
            border-color: #2c3e50;
        }
        .payment-icon {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .additional-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .option-btn {
            padding: 12px;
            text-align: center;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: bold;
        }
        .option-btn:hover {
            border-color: #2c3e50;
            background: #2c3e50;
            color: white;
        }
        .option-icon {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .payment-modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
        }
        .change-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
            font-size: 1.2em;
            font-weight: bold;
        }
        .print-options {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .print-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .print-modal-content {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
        }
        .product-option-btn {
            margin-top: 10px;
            padding: 5px 10px;
            font-size: 12px;
        }
        .order-item-details {
            flex-grow: 1;
        }
        .order-item-title {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .order-item-name {
            font-weight: bold;
        }
        .order-item-price {
            font-weight: bold;
            color: #2c3e50;
        }
        .order-item-options {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #e9ecef;
        }
        .option-item {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #6c757d;
        }
        .order-item-quantity {
            background: #4a6580;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 10px;
        }

/* Adding styles for category edit mode */
.category-item.dragging {
    opacity: 0.5;
}

#categoryList.edit-mode .category-item {
    border: 2px dashed #007bff;
    margin: 2px 0;
    transition: all 0.3s ease;
}

#categoryList.edit-mode .category-item:hover {
    background-color: #e3f2fd;
    transform: translateY(-2px);
}
    
        /* ==== Split Payment Modal ==== */
        .split-payment-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .split-payment-content {
            background: #fff;
            width: min(1100px, 95vw);
            max-height: 90vh;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            min-height: 420px;
        }
        .available-items, .customer-sections {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 12px;
            background: #f8f9fa;
            overflow: auto;
        }
        .split-item, .customer-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: grab;
        }
        .split-item:hover, .customer-item:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .customer-section { background:#fff; border:1px solid #dee2e6; border-radius:10px; padding:10px; margin-bottom:10px;}
        .customer-header { font-weight:700; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;}
        .customer-total { font-weight:700; margin-top:6px; }
        .customer-pay-btn { width:100%; margin-top:8px; }
        .split-controls { display:flex; justify-content:flex-end; gap:8px; margin-top:8px;}
        .drop-target { outline: 2px dashed #2c3e50; background:#eef3f8; }
        .qty-badge { font-size: 12px; padding: 2px 6px; border-radius: 12px; background:#eef; }
    
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Restaurant POS System</h1>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Tables
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Side - Menu -->
            <div class="col-md-8">
                <div class="table-info">
                     
                    <h4 class="mb-0"><i class="fas fa-table me-2"></i>Table <?php echo $table['number']; ?></h4>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Categories</h5>
                                    <button class="btn btn-sm btn-outline-light" id="editCategoriesBtn" onclick="toggleCategoryEditMode()">
                                        <i class="fas fa-edit me-1"></i>Edit Order
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <ul class="category-list" id="categoryList">
                                    <!-- Categories will be added here by JavaScript -->
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="products-container row g-3" id="productsContainer">
                            <!-- Products will be added here by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Order Summary -->
            <div class="col-md-4">
                <div class="order-container">
                    <div class="order-header">
                        <h3 class="mb-0 text-center"><i class="fas fa-receipt me-2"></i>ORDER SUMMARY</h3>
                    </div>
                    
                    <div class="order-list" id="orderItems">
                        <!-- Order items will be added here -->
                        <div class="text-center p-3 text-muted">No items in your order</div>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span id="subtotal">£0.00</span>
                        </div>
                        <div class="summary-total">
                            <span>TOTAL:</span>
                            <span id="orderTotal">£0.00</span>
                        </div>
                        
                        <!-- Print Options -->
                        <div class="print-options">
                            <label for="printPreference">Print:</label>
                            <select class="form-select form-select-sm" id="printPreference">
                                <option value="ask" <?php echo $print_preference == 'ask' ? 'selected' : ''; ?>>Ask every time</option>
                                <option value="always" <?php echo $print_preference == 'always' ? 'selected' : ''; ?>>Always print</option>
                                <option value="never" <?php echo $print_preference == 'never' ? 'selected' : ''; ?>>Never print</option>
                            </select>
                        </div>
                        
                        <!-- Additional Options -->
                        <div class="additional-options">
                            <div class="option-btn" onclick="orderPrint()">
                                <div class="option-icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div>ORDER PRINT</div>
                            </div>
                            <div class="option-btn" onclick="openTheTill()">
                                <div class="option-icon">
                                    <i class="fas fa-cash-register"></i>
                                </div>
                                <div>OPEN THE TILL</div>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="payment-methods">
                            <div class="payment-btn" id="cashBtn" onclick="showPaymentModal('cash')">
                                <div class="payment-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div>CASH</div>
                            </div>
                            <!-- Modified card payment to handle selected items removal -->
                            <div class="payment-btn" id="cardBtn" onclick="handleCardPayment()">
                                <div class="payment-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <div>CARD</div>
                            </div>
                        </div>
                                                <!-- Adding split payment button -->
                        <div class="d-grid gap-2 mb-2">
                            <button class="btn btn-warning" id="splitPaymentBtn" onclick="showSplitPaymentModal()">
                                <i class="fas fa-users me-2"></i>SPLIT PAYMENT
                            </button>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success btn-lg py-3" id="completeOrder" onclick="showPaymentModal('cash')">
                                <i class="fas fa-check-circle me-2"></i>PAYMENT
                            </button>
                            <button class="btn btn-outline-danger" id="clearOrder">
                                <i class="fas fa-trash me-2"></i>CLEAR ORDER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-modal-content">
            <h3 class="text-center mb-4">Cash Payment</h3>
            
            <div class="change-display">
                <div>Total: <span id="modalTotal">£0.00</span></div>
                <div>Amount Paid: <span id="amountPaid">£0.00</span></div>
                <div>Change: <span id="changeAmount">£0.00</span></div>
            </div>
            
            <div class="numpad">
                <button class="numpad-btn" onclick="appendToPaymentAmount('1')">1</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('2')">2</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('3')">3</button>
                <button class="numpad-btn money-btn" onclick="addMoneyAmount(5)">£5</button>
                
                <button class="numpad-btn" onclick="appendToPaymentAmount('4')">4</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('5')">5</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('6')">6</button>
                <button class="numpad-btn money-btn" onclick="addMoneyAmount(10)">£10</button>
                
                <button class="numpad-btn" onclick="appendToPaymentAmount('7')">7</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('8')">8</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('9')">9</button>
                <button class="numpad-btn money-btn" onclick="addMoneyAmount(20)">£20</button>
                
                <button class="numpad-btn" onclick="appendToPaymentAmount('.')">.</button>
                <button class="numpad-btn" onclick="appendToPaymentAmount('0')">0</button>
                <button class="numpad-btn" onclick="clearPaymentAmount()">
                    <i class="fas fa-backspace"></i>
                </button>
                <button class="numpad-btn money-btn" onclick="addMoneyAmount(50)">£50</button>
            </div>
            
            <div class="d-grid gap-2 mt-3">
			
                <button class="btn btn-success" onclick="completePayment('cash')">
                    COMPLETE PAYMENT
                </button>
                <button class="btn btn-secondary" onclick="hidePaymentModal()">
                    CANCEL
                </button>
            </div>
        </div>
    </div>

    <!-- Print Modal -->
    <div class="print-modal" id="printModal">
        <div class="print-modal-content">
            <h3 class="text-center mb-4">Print Receipt</h3>
            
            <p class="text-center">Do you want to print the receipt?</p>
            
            <div class="d-grid gap-2 mt-4">
                <button class="btn btn-success" onclick="handlePrintResponse(true)">
                    YES, PRINT
                </button>
                <button class="btn btn-secondary" onclick="handlePrintResponse(false)">
                    NO, THANKS
                </button>
                <button class="btn btn-outline-primary" onclick="setPrintPreference()">
                    REMEMBER MY CHOICE
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let categories = [];
        let products = [];
        let currentTableId = <?php echo $table_id; ?>;
        let currentOrder = [];
        let paymentAmount = '';
        let paymentMethod = '';
        let shouldPrint = false;
        let printPreference = '<?php echo $print_preference; ?>';
        let categoryEditMode = false;

// When page loads
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    document.getElementById('clearOrder').addEventListener('click', clearOrder);
    
    // Update print preference when changed
    document.getElementById('printPreference').addEventListener('change', function() {
        const preference = this.value;
        document.cookie = `print_preference=${preference}; expires=${new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString()}; path=/`;
        printPreference = preference;
    });
    
    // Listen for messages from option window
    window.addEventListener('message', function(event) {
        if (event.data.action === 'addProductWithOptions') {
            // Add product and options to cart
            addProductToOrder(
                event.data.product_id,
                event.data.product_name,
                event.data.base_price,
                event.data.options,
                event.data.total_price
            );
        }
    });
    
    // Load order from localStorage if exists

    // Listen for updates from the options popup
    window.addEventListener('message', function(e) {
        if (e && e.data && e.data.type === 'order-updated') {
            try {
                const key = `order_table_${currentTableId}`;
                const saved = localStorage.getItem(key);
                if (saved) {
                    currentOrder = JSON.parse(saved);
                    updateOrderDisplay();
                }
            } catch (err) {
                console.warn('order-updated handler failed:', err);
            }
        }
    });
        const savedOrder = localStorage.getItem(`order_table_${currentTableId}`);
    if (savedOrder) {
        currentOrder = JSON.parse(savedOrder);
        updateOrderDisplay();
    }
});
        // Load categories
        function loadCategories() {
            fetch('db.php?action=get_categories_ordered')
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        categories = Array.isArray(data) ? data.filter(c => (c.parent_id === undefined || c.parent_id === null || c.parent_id == 0)) : [];
                        renderCategories();
                    } else {
                        console.error('Invalid categories data received:', data);
                        // Fallback to regular categories if ordered version fails
                        return fetch('db.php?action=get_categories');
                    }
                })
                .then(response => {
                    if (response) {
                        return response.json();
                    }
                })
                .then(data => {
                    if (data && Array.isArray(data)) {
                        categories = Array.isArray(data) ? data.filter(c => (c.parent_id === undefined || c.parent_id === null || c.parent_id == 0)) : [];
                        renderCategories();
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    alert('Error loading categories. Please refresh the page.');
                });
        }

// In the loadProducts function, modify the SQL query to order by order_index
function loadProducts(categoryId) {
    fetch(`db.php?action=get_products&category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            // Sort products by order_index within the category
            products = data.sort((a, b) => {
                return (a.order_index || 0) - (b.order_index || 0);
            });
            showProducts(categoryId);
        })
        .catch(error => {
            console.error('Error loading products:', error);
            alert('Error loading products. Please refresh the page.');
        });
}

        // Display categories
        function renderCategories() {
            const categoryList = document.getElementById('categoryList');
            categoryList.innerHTML = '';
            
            categories.forEach(category => {
                const categoryItem = document.createElement('li');
                categoryItem.className = 'category-item';
                categoryItem.textContent = category.name;
                categoryItem.addEventListener('click', () => {
                    loadProducts(category.id);
                    
                    // Highlight active category
                    document.querySelectorAll('.category-item').forEach(item => {
                        item.classList.remove('active');
                    });
                    categoryItem.classList.add('active');
                });
                
                if (categories[0] && categories[0].id === category.id) {
                    categoryItem.classList.add('active');
                    loadProducts(category.id);
                }
                
                categoryList.appendChild(categoryItem);
            });
        }

        // Display products
        function showProducts(categoryId) {
            const productsContainer = document.getElementById('productsContainer');
            productsContainer.innerHTML = '';
            
            const categoryProducts = products.filter(product => product.category_id == categoryId);
            
            if (categoryProducts.length === 0) {
                productsContainer.innerHTML = '<div class="col-12"><p class="text-center p-3">Bu kategoride ürün yok.</p></div>';
                return;
            }
            
            categoryProducts.forEach(product => {
                const productCol = document.createElement('div');
                productCol.className = 'col-md-4 col-sm-6';
                
                productCol.innerHTML = `
                    <div class="product-card card ${product.available ? 'product-available' : 'product-unavailable'}" onclick="addToOrder(${product.id})" style="cursor: pointer;">
                        <div class="card-body text-center p-3">
                            <div class="mb-2">
                                <i class="fas fa-utensils fa-2x text-secondary"></i>
                            </div>
                            <h6 class="card-title mb-2">${product.name}</h6>
                            <p class="product-price mb-3">£${product.price}</p>
                            ${!product.available ? `<span class="badge bg-danger">Out of Stock</span>` : ''}
                            <div class="d-flex justify-content-center mt-2">
                                <button class="btn btn-sm btn-outline-primary product-option-btn" 
                                        data-product-id="${product.id}" 
                                        data-product-name="${product.name}"
                                        onclick="event.stopPropagation();">
                                    <i class="fas fa-cog"></i> Options
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                productsContainer.appendChild(productCol);
            });
            
            // Add click events to option buttons
            document.querySelectorAll('.product-option-btn').forEach(btn => {
                btn.addEventListener('click', function(event) {
                    event.stopPropagation(); // Prevent card click
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');
                    
                    // Open option window
                    window.open(`opsiyon.php?product_id=${productId}&table_id=${currentTableId}`, 
                               'opsiyonPenceresi', 
                               'width=1000,height=800,scrollbars=yes');
                });
            });
        }

        // Add product to order
        function addToOrder(productId) {
            const product = products.find(p => p.id === productId);
            if (!product || !product.available) return;
            
            // Check if product already in order
            const existingItem = currentOrder.find(item => item.product_id === productId && !item.hasOptions);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                currentOrder.push({
                    product_id: product.id,
                    name: product.name,
                    price: parseFloat(product.price),
                    quantity: 1,
                    hasOptions: false
                });
            }
            
            updateOrderDisplay();
        }

        // Add product with options to order
        function addProductToOrder(productId, productName, basePrice, options, totalPrice) {
            // Check if product with same options already in order
            const optionsKey = JSON.stringify(options);
            const existingItem = currentOrder.find(item => 
                item.product_id === productId && 
                item.hasOptions && 
                JSON.stringify(item.options) === optionsKey
            );
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                currentOrder.push({
                    product_id: productId,
                    name: productName,
                    price: parseFloat(totalPrice),
                    base_price: parseFloat(basePrice),
                    quantity: 1,
                    hasOptions: true,
                    options: options
                });
            }
            
            updateOrderDisplay();
        }

// Update order display
function updateOrderDisplay() {
    const orderItems = document.getElementById('orderItems');
    const orderTotal = document.getElementById('orderTotal');
    const subtotal = document.getElementById('subtotal');
    
    orderItems.innerHTML = '';
    let total = 0;
    
    // Save order to localStorage
    localStorage.setItem(`order_table_${currentTableId}`, JSON.stringify(currentOrder));
    
    // Masanın durumunu güncelle - YENİ EKLENDİ
    if (currentOrder.length > 0) {
        // Sipariş varsa masayı dolu yap
        updateTableStatus(currentTableId, 'occupied');
    } else {
        // Sipariş yoksa masayı boş yap
        updateTableStatus(currentTableId, 'available');
    }
    
    if (currentOrder.length === 0) {
        orderItems.innerHTML = '<p class="text-center p-3 text-muted">No items in your order</p>';
    } else {
        currentOrder.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            const orderItem = document.createElement('div');
            orderItem.className = 'order-item';
            
            let optionsHtml = '';
            if (item.hasOptions && item.options) {
                optionsHtml = '<div class="order-item-options">';
                item.options.forEach(option => {
                    let prefix = '';
                    if (option.type !== 'swap_in' && option.type !== 'swap_out') {
                        prefix = '+ ';
                    }
                    
                    if (option.price > 0) {
                        optionsHtml += `<div class="option-item">${prefix}${option.name} <span>£${option.price.toFixed(2)}</span></div>`;
                    } else {
                        optionsHtml += `<div class="option-item">${prefix}${option.name}</div>`;
                    }
                });
                optionsHtml += '</div>';
            }
            
            orderItem.innerHTML = `
                <div class="order-item-details">
                    <div class="order-item-title">
                        <span class="order-item-name">${item.name}</span>
                        <span class="order-item-price">£${item.price.toFixed(2)}</span>
                    </div>
                    ${optionsHtml}
                </div>
                <div class="quantity-controls">
                    <span class="order-item-quantity">${item.quantity}x</span>
                    <button class="btn btn-sm btn-outline-secondary quantity-btn decrease-item" data-index="${index}">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary quantity-btn increase-item" data-index="${index}">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="btn btn-sm btn-danger ms-2 remove-item" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            orderItems.appendChild(orderItem);
        });
        
        // Add event listeners to decrease buttons
        document.querySelectorAll('.decrease-item').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                decreaseOrderItem(index);
            });
        });
        
        // Add event listeners to increase buttons
        document.querySelectorAll('.increase-item').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                increaseOrderItem(index);
            });
        });
        
        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                removeOrderItem(index);
            });
        });
    }
    
    subtotal.textContent = '£' + total.toFixed(2);
    orderTotal.textContent = '£' + total.toFixed(2);
}
        // Decrease item quantity
        function decreaseOrderItem(index) {
            if (currentOrder[index].quantity > 1) {
                currentOrder[index].quantity -= 1;
            } else {
                currentOrder.splice(index, 1);
            }
            updateOrderDisplay();
        }

        // Increase item quantity
        function increaseOrderItem(index) {
            currentOrder[index].quantity += 1;
            updateOrderDisplay();
        }

        // Remove item from order
        function removeOrderItem(index) {
            currentOrder.splice(index, 1);
            updateOrderDisplay();
        }

// Clear order
function clearOrder() {
    if (confirm('Are you sure you want to clear the order?')) {
        currentOrder = [];
        // Remove from localStorage too
        localStorage.removeItem(`order_table_${currentTableId}`);
        updateOrderDisplay();
    }
}

        // Show payment modal
        function showPaymentModal(method) {
            if (currentOrder.length === 0) {
                alert('Your order is empty.');
                return;
            }
            
            paymentMethod = method;
            paymentAmount = '';
            
            const total = calculateTotal();
            document.getElementById('modalTotal').textContent = '£' + total.toFixed(2);
            document.getElementById('amountPaid').textContent = '£0.00';
            document.getElementById('changeAmount').textContent = '£0.00';
            
            document.getElementById('paymentModal').style.display = 'flex';
        }

        // Hide payment modal
        function hidePaymentModal() {

        }
// Siparişi tamamla ve ödemeyi yap
function completeOrder(paymentMethod) {
    if (cartItems.length === 0) {
        alert('Sepetiniz boş!');
        return;
    }
    
    const totalAmount = calculateTotal();
    const orderData = {
        table_id: currentTableId,
        total_amount: totalAmount,
        items: cartItems,
        payment_method: paymentMethod
    };
    
    // Cookie'den kayıt tercihini al
    const saveOption = getCookie('save_reports') || 'always';
    
    if (saveOption === 'never') {
        // Hiç kaydetme seçeneği
        if (confirm('Sipariş kaydedilmeyecek. Devam etmek istiyor musunuz?')) {
            resetOrder();
            alert('Sipariş tamamlandı (kaydedilmedi).');
        }
        return;
    }
    
    if (saveOption === 'ask') {
        // Her seferinde sor seçeneği
        if (!confirm('Bu siparişi veritabanına kaydetmek istiyor musunuz?')) {
            resetOrder();
            alert('Sipariş tamamlandı (kaydedilmedi).');
            return;
        } else {
            orderData.save_confirmed = true;
        }
    }
    
    // Siparişi kaydet
    fetch('db.php?action=save_order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Sipariş başarıyla tamamlandı ve kaydedildi!');
            resetOrder();
        } else if (data.needs_confirmation) {
            // Onay isteniyor
            if (confirm('Bu siparişi veritabanına kaydetmek istiyor musunuz?')) {
                orderData.save_confirmed = true;
                // Tekrar gönder
                return fetch('db.php?action=save_order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(orderData)
                }).then(response => response.json());
            } else {
                resetOrder();
                alert('Sipariş tamamlandı (kaydedilmedi).');
                return {success: true};
            }
        } else {
            alert('Sipariş kaydedilirken hata oluştu: ' + data.error);
        }
    })
    .then(data => {
        if (data && data.success) {
            if (window._splitContext && window._splitContext.active) {
                // In split mode: remove paid sub-order from original order, keep the rest, do not redirect.
                const paid = window._splitContext.subOrder;
                const original = window._splitContext.originalOrder;
                // Subtract quantities
                function subtractOrders(orig, paid){
                    const res = deepClone(orig);
                    paid.forEach(p => {
                        const idx = res.findIndex(o=>o.product_id===p.product_id && o.price===p.price && (o.name||o.product_name)===(p.name));
                        if (idx>-1){
                            res[idx].quantity -= p.quantity;
                            if (res[idx].quantity<=0){ res.splice(idx,1); }
                        }
                    });
                    return res;
                }
                const remaining = subtractOrders(original, paid);
                currentOrder = remaining;
                localStorage.setItem(`order_table_${currentTableId}`, JSON.stringify(currentOrder));
                updateOrderDisplay && updateOrderDisplay();
                hidePaymentModal && hidePaymentModal();
                alert('Customer payment successful!');
                // Clear context and reopen split if items remain
                const itemsRemain = currentOrder && currentOrder.length>0;
                window._splitContext = {active:false};
                if (itemsRemain){
                    // Reopen split to continue
                    showSplitPaymentModal();
                } else {
                    // If no items left, go back to index like usual
                    window.location.href = 'index.php';
                }
                return; // prevent running default success path
            }
        
            alert('Sipariş başarıyla tamamlandı ve kaydedildi!');
            resetOrder();
        }
    })
    .catch(error => {
        console.error('Hata:', error);
        alert('Sipariş işlemi sırasında bir hata oluştu.');
    });
}

// Cookie okuma fonksiyonu
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}
        // Append to payment amount
        function appendToPaymentAmount(value) {
            if (value === '.' && paymentAmount.includes('.')) {
                return; // Only one decimal point allowed
            }
            
            if (paymentAmount === '0' && value !== '.') {
                paymentAmount = value; // Replace initial zero
            } else {
                paymentAmount += value;
            }
            
            updatePaymentDisplay();
        }

        // Add money amount
        function addMoneyAmount(amount) {
            const currentAmount = paymentAmount ? parseFloat(paymentAmount) : 0;
            paymentAmount = (currentAmount + amount).toFixed(2);
            updatePaymentDisplay();
        }

        // Clear payment amount
        function clearPaymentAmount() {
            paymentAmount = '';
            updatePaymentDisplay();
        }

        // Update payment display
        function updatePaymentDisplay() {
            const amount = paymentAmount ? parseFloat(paymentAmount) : 0;
            const total = calculateTotal();
            const change = amount - total;
            
            document.getElementById('amountPaid').textContent = '£' + amount.toFixed(2);
            document.getElementById('changeAmount').textContent = '£' + (change > 0 ? change.toFixed(2) : '0.00');
        }

        // Calculate total
        function calculateTotal() {
            return currentOrder.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

// Complete payment
function completePayment(method) {
    // Önce ödeme miktarını ve toplamı kaydet
    const paidAmount = paymentMethod === 'cash' && paymentAmount ? parseFloat(paymentAmount) || 0 : 0;
    const totalAmount = calculateTotal();
    
    if (method === 'cash') {
        if (paidAmount < totalAmount) {
            alert('Amount paid is less than the total. Please enter a sufficient amount.');
            return;
        }
    }
    
    paymentMethod = method;
    
    // Create FormData
    const formData = new FormData();
    formData.append('table_id', currentTableId);
    formData.append('items', JSON.stringify(currentOrder));
    formData.append('payment_method', paymentMethod);
    formData.append('action', 'add_order');
    
    if (paymentMethod === 'cash') {
        formData.append('amount_paid', paidAmount.toFixed(2));
    }
    
    // Send AJAX request
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON:', text);
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        if (data && data.success) {
            // Update table status to available
            updateTableStatus(currentTableId, 'available');
            
            // Handle print based on preference
            handlePrintAfterPayment();
            
            // Clear order and close modal
            currentOrder = [];
            
            // Remove from localStorage after successful payment
            localStorage.removeItem(`order_table_${currentTableId}`);
            
            updateOrderDisplay();
            hidePaymentModal();
            
            // Show success message with correct change calculation
            if (paymentMethod === 'cash') {
                const change = paidAmount - totalAmount;
                
                // KAYDEDİLMİŞ değerleri kullan
                alert(`Payment successful! Change: £${change.toFixed(2)}`);
            } else {
                alert('Card payment successful!');
            }
            
            // Redirect to main page
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            const errorMsg = data && data.error ? data.error : 'Unknown error occurred';
            alert('Error saving order: ' + errorMsg);
        }
    })
    .catch(error => {
        console.error('Error sending order:', error);
        alert('Error sending order. Please try again: ' + error.message);
    });
}

// Hide payment modal - BU FONKSİYONU DEĞİŞTİRMEYİN
function hidePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    // paymentAmount = ''; // BU SATIRI KALDIRIN veya yorum yapın
}

// Handle print after payment
function handlePrintAfterPayment() {
    if (printPreference === 'always') {
        // Always print
        orderPrint();
    } else if (printPreference === 'never') {
        // Never print
        // Nothing to do
    } else {
        // Ask every time
        document.getElementById('printModal').style.display = 'flex';
    }
}

// Handle print response from modal
function handlePrintResponse(print) {
    document.getElementById('printModal').style.display = 'none';
    if (print) {
        orderPrint();
    }
}

// Set print preference permanently
function setPrintPreference() {
    const remember = confirm("Do you want to always use this choice?");
    if (remember) {
        const newPreference = shouldPrint ? 'always' : 'never';
        document.cookie = `print_preference=${newPreference}; expires=${new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString()}; path=/`;
        printPreference = newPreference;
        const printPreferenceElement = document.getElementById('printPreference');
        if (printPreferenceElement) {
            printPreferenceElement.value = newPreference;
        }
    }
    document.getElementById('printModal').style.display = 'none';
}

// Update table status
function updateTableStatus(tableId, status) {
    console.log('Updating table status:', tableId, status);
    
    const formData = new FormData();
    formData.append('table_id', tableId);
    formData.append('status', status);
    formData.append('action', 'update_table_status');
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                console.log('Table status updated successfully');
            } else {
                console.error('Error updating table status:', data.error);
            }
        } catch (e) {
            console.log('Table status update - Raw response:', text);
            // Even if we can't parse the response, assume it worked for UX
            console.log('Table status updated (assumed success)');
        }
    })
    .catch(error => {
        console.error('Network error updating table status:', error);
        // Even if there's an error, continue with the flow
        console.log('Table status updated (continued despite error)');
    });
}

// Safe fallback: update status text in the UI if elements exist.
function updateTableUI(tableId, status) {
    // Try a specific element for the current table page
    const elById = document.getElementById('table-status-text');
    if (elById) {
        elById.textContent = status;
    }
    // Try a generic selector if tables are listed
    const listEl = document.querySelector(`[data-table-id="${tableId}"] .table-status-text`);
    if (listEl) {
        listEl.textContent = status;
    }
}

// Order print
function orderPrint() {
    if (currentOrder.length === 0) {
        alert('No order to print.');
        return;
    }
    
    const orderData = {
        table: <?php echo $table_id; ?>,
        items: currentOrder,
        total: calculateTotal(),
        date: new Date().toLocaleString()
    };
    
    // Send to Print.php for proper ESC/POS formatting
    fetch('Print.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'print_order_auto',
            table_id: orderData.table,
            items: orderData.items,
            total: orderData.total,
            date: orderData.date
        })
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error('Non-JSON response: ' + text.substring(0, 100));
            });
        }
    })
    .then(data => {
        if (data.success) {
            console.log('Order printed successfully via', data.connection_type);
            alert('Order printed successfully via ' + data.connection_type + ' connection!');
        } else {
            console.error('Print failed:', data.error);
            alert('Print failed: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Print failed:', error);
        alert('Print failed: ' + error.message);
    });
}

// Fallback browser print function with proper printer settings
function fallbackBrowserPrint(orderData) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Order Receipt</title>
            <style>
                body { 
                    font-family: <?php echo isset($printer_settings['font_family']) ? $printer_settings['font_family'] : 'monospace'; ?>; 
                    margin: 0; 
                    padding: 10px;
                    width: <?php echo $printer_settings['receipt_width']; ?>mm;
                }
                .header { 
                    text-align: <?php echo $printer_settings['logo_alignment']; ?>; 
                    margin-bottom: 20px; 
                }
                .company-name { 
                    font-weight: bold; 
                    font-size: 18px; 
                    margin-bottom: 5px;
                }
                .company-info { 
                    font-size: 12px; 
                    margin-bottom: 2px;
                }
                .item { 
                    margin-bottom: 5px; 
                    font-size: 12px;
                }
                .total { 
                    font-weight: bold; 
                    margin-top: 10px; 
                    border-top: 1px dashed #000;
                    padding-top: 5px;
                }
                .footer { 
                    margin-top: 20px; 
                    text-align: center; 
                    font-size: 10px;
                    border-top: 1px dashed #000;
                    padding-top: 10px;
                }
                @media print {
                    body { margin: 0; }
                }
            
        /* ==== Split Payment Modal ==== */
        .split-payment-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .split-payment-content {
            background: #fff;
            width: min(1100px, 95vw);
            max-height: 90vh;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            min-height: 420px;
        }
        .available-items, .customer-sections {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 12px;
            background: #f8f9fa;
            overflow: auto;
        }
        .split-item, .customer-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: grab;
        }
        .split-item:hover, .customer-item:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .customer-section { background:#fff; border:1px solid #dee2e6; border-radius:10px; padding:10px; margin-bottom:10px;}
        .customer-header { font-weight:700; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;}
        .customer-total { font-weight:700; margin-top:6px; }
        .customer-pay-btn { width:100%; margin-top:8px; }
        .split-controls { display:flex; justify-content:flex-end; gap:8px; margin-top:8px;}
        .drop-target { outline: 2px dashed #2c3e50; background:#eef3f8; }
        .qty-badge { font-size: 12px; padding: 2px 6px; border-radius: 12px; background:#eef; }
    
    </style>
        </head>
        <body>
            <div class="header">
                <?php if (!empty($printer_settings['logo_path'])): ?>
                <img src="<?php echo $printer_settings['logo_path']; ?>" 
                     style="max-width: <?php echo $printer_settings['logo_width']; ?>px; height: auto; margin-bottom: 10px;">
                <?php endif; ?>
                <div class="company-name"><?php echo $printer_settings['company_name']; ?></div>
                <div class="company-info"><?php echo $printer_settings['company_address']; ?></div>
                <div class="company-info"><?php echo $printer_settings['company_phone']; ?></div>
                <div style="margin-top: 10px; font-size: 14px;">ORDER RECEIPT</div>
                <div style="font-size: 12px;">Table: ${orderData.table}</div>
                <div style="font-size: 12px;">${orderData.date}</div>
            </div>
            <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
            <div class="items">
                ${orderData.items.map(item => `
                    <div class="item">
                        ${item.quantity}x ${item.name}
                        <div style="float: right;">£${(item.price * item.quantity).toFixed(2)}</div>
                        <div style="clear: both;"></div>
                        ${item.hasOptions ? `<div style="font-size: 10px; margin-left: 10px;">${formatOptions(item.options)}</div>` : ''}
                    </div>
                `).join('')}
            </div>
            <div class="total">
                <div style="text-align: right;">TOTAL: £${orderData.total.toFixed(2)}</div>
            </div>
            <div class="footer">
                <p><?php echo $printer_settings['footer_text']; ?></p>
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() { window.close(); }, 500);
                }
            <\/script>
        
    <!-- Split Payment Modal -->
    <div class="split-payment-modal" id="splitPaymentModal" style="display:none;">
        <div class="split-payment-content">
            <h3 class="text-center mb-2">
                <i class="fas fa-users me-2"></i>Split Payment - Assign Items to Customers
            </h3>
            <div class="split-container">
                <div class="available-items">
                    <h5 class="text-center mb-2">Available Items (Click or Drag to Assign)</h5>
                    <div id="availableItemsList"></div>
                </div>
                <div class="customer-sections" id="customerSections"></div>
            </div>
            <div class="split-controls">
                <button class="btn btn-secondary" onclick="hideSplitPaymentModal()">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button class="btn btn-primary" onclick="addCustomer()">
                    <i class="fas fa-user-plus me-1"></i>Add Customer
                </button>
                <button class="btn btn-danger" onclick="resetSplit()">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
    
</body>
        </html>
    `);
    printWindow.document.close();
}

// Format options for display
function formatOptions(options) {
    return options.map(option => {
        let prefix = '';
        if (option.type !== 'swap_in' && option.type !== 'swap_out') {
            prefix = '+';
        }
        
        // Build a string with conditional prefix and the option name.
        // Only include the option value if it is defined and not 'undefined'.
        const valuePart = (option.value !== undefined && option.value !== null && option.value !== '' && option.value !== 'undefined')
            ? `: ${option.value}`
            : '';
        // Include price information if there is an additional cost.
        const pricePart = option.price > 0 ? ` (+£${option.price})` : '';
        return `${prefix}${option.name}${valuePart}${pricePart}`;
    }).join('<br>');
}

// Open the till
function openTheTill() {
    alert('Till opened for cash transaction.');
}

// Toggle category edit mode
function toggleCategoryEditMode() {
    categoryEditMode = !categoryEditMode;
    const editBtn = document.getElementById('editCategoriesBtn');
    const categoryList = document.getElementById('categoryList');
    
    if (categoryEditMode) {
        editBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Order';
        editBtn.className = 'btn btn-sm btn-success';
        categoryList.classList.add('edit-mode');
        enableCategoryDragDrop();
    } else {
        editBtn.innerHTML = '<i class="fas fa-edit me-1"></i>Edit Order';
        editBtn.className = 'btn btn-sm btn-outline-light';
        categoryList.classList.remove('edit-mode');
        disableCategoryDragDrop();
        saveCategoryOrder();
    }
}

// Enable drag and drop for categories
function enableCategoryDragDrop() {
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.draggable = true;
        item.style.cursor = 'move';
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragend', handleDragEnd);
    });
}

// Disable drag and drop for categories
function disableCategoryDragDrop() {
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.draggable = false;
        item.style.cursor = 'pointer';
        item.removeEventListener('dragstart', handleDragStart);
        item.removeEventListener('dragover', handleDragOver);
        item.removeEventListener('drop', handleDrop);
        item.removeEventListener('dragend', handleDragEnd);
    });
}

// ===== Split Payment Logic =====
let splitState = {
    customers: [], // [{items:[{product_id,name,price,quantity}]}]
    available: []  // same shape
};
window._splitContext = { active:false };

function deepClone(obj){ return JSON.parse(JSON.stringify(obj)); }

function rebuildAvailableFromOrder(){
    // Build from currentOrder
    const map = new Map();
    currentOrder.forEach(it => {
        const key = it.product_id + '|' + (it.name || it.product_name || '') + '|' + it.price;
        const prev = map.get(key) || { product_id: it.product_id, name: (it.name||it.product_name||('Item '+it.product_id)), price: it.price, quantity: 0 };
        prev.quantity += it.quantity;
        map.set(key, prev);
    });
    splitState.available = Array.from(map.values());
}

function showSplitPaymentModal(){
    rebuildAvailableFromOrder();
    splitState.customers = [{items:[]},{items:[]}]; // default two
    renderSplitUI();
    document.getElementById('splitPaymentModal').style.display = 'flex';
}

function hideSplitPaymentModal(){
    document.getElementById('splitPaymentModal').style.display = 'none';
}

function resetSplit(){
    rebuildAvailableFromOrder();
    splitState.customers.forEach(c => c.items = []);
    renderSplitUI();
}

function addCustomer(){
    splitState.customers.push({items:[]});
    renderSplitUI();
}

function renderSplitUI(){
    const avail = document.getElementById('availableItemsList');
    avail.innerHTML = '';
    splitState.available.forEach((it, idx) => {
        const div = document.createElement('div');
        div.className = 'split-item';
        div.draggable = true;
        div.dataset.index = idx;
        div.innerHTML = `<div><strong>${it.name}</strong><br><small>Quantity: <span class="qty">${it.quantity}</span></small></div><div>£${it.price.toFixed(2)}</div>`;
        div.addEventListener('click', ()=>promptAssign(idx));
        div.addEventListener('dragstart', (e)=>{
            e.dataTransfer.setData('text/plain', JSON.stringify({type:'avail', index: idx}));
        });
        avail.appendChild(div);
    });
    const customerSections = document.getElementById('customerSections');
    customerSections.innerHTML = '';
    splitState.customers.forEach((c, ci)=>{
        const sec = document.createElement('div'); sec.className='customer-section';
        const total = c.items.reduce((s,it)=>s+it.price*it.quantity,0);
        sec.innerHTML = `
            <div class="customer-header">
                <span>Customer ${ci+1}</span>
                <button class="btn btn-sm btn-outline-danger" onclick="removeCustomer(${ci})" ${splitState.customers.length<=1?'disabled':''}>Sil</button>
            </div>
            <div class="customer-items" id="custItems_${ci}">${c.items.length? '' : '<div class="text-center text-muted p-3">No items assigned</div>'}</div>
            <div class="customer-total">Total: £${total.toFixed(2)}</div>
            <div class="customer-payment-buttons">
                <button class="btn btn-success customer-pay-btn" onclick="payForCustomer(${ci})" ${c.items.length? '' : 'disabled'} style="margin-right: 10px;">
                    <i class="fas fa-money-bill-wave me-1"></i>Cash £${total.toFixed(2)}
                </button>
                <button class="btn btn-primary customer-pay-btn" onclick="handleCardPaymentFromSplit(${ci})" ${c.items.length? '' : 'disabled'}>
                    <i class="fas fa-credit-card me-1"></i>Card £${total.toFixed(2)}
                </button>
            </div>
        `;
        // Make droppable
        sec.addEventListener('dragover', (e)=>{ e.preventDefault(); sec.classList.add('drop-target'); });
        sec.addEventListener('dragleave', ()=>sec.classList.remove('drop-target'));
        sec.addEventListener('drop', (e)=>{
            e.preventDefault(); sec.classList.remove('drop-target');
            try{
                const data = JSON.parse(e.dataTransfer.getData('text/plain')||'{}');
                if(data.type==='avail'){ promptAssign(data.index, ci); }
            }catch(_){}
        });
        customerSections.appendChild(sec);
        const itemsWrap = sec.querySelector(`#custItems_${ci}`);
        if (c.items.length){
            c.items.forEach((it,iidx)=>{
                const row = document.createElement('div');
                row.className='customer-item';
                row.innerHTML = `<div><strong>${it.name}</strong> <span class="qty-badge">x${it.quantity}</span></div><div>£${(it.price*it.quantity).toFixed(2)}</div>`;
                // option to return back to available on click
                row.addEventListener('click', ()=>returnToAvailable(ci, iidx));
                itemsWrap.appendChild(row);
            });
        }
    });
}

function removeCustomer(ci){
    splitState.customers.splice(ci,1);
    renderSplitUI();
}

function promptAssign(availIndex, toCustomerIndex=null){
    const it = splitState.available[availIndex];
    if(!it) return;
    let qty = 1;
    if (it.quantity>1){
        const val = prompt(`Kaç adet "${it.name}" atansın? (1 - ${it.quantity})`, '1');
        qty = Math.max(1, Math.min(it.quantity, parseInt(val||'1', 10)));
    }
    // pick customer if not provided
    let cidx = toCustomerIndex;
    if (cidx===null){
        cidx = 0; // default to first
    }
    assignQtyToCustomer(availIndex, cidx, qty);
}

function assignQtyToCustomer(availIndex, customerIndex, qty){
    const list = splitState.available;
    const src = list[availIndex];
    if(!src) return;
    // reduce available
    src.quantity -= qty;
    if (src.quantity<=0){ list.splice(availIndex,1); }
    // add to customer
    const c = splitState.customers[customerIndex];
    const existing = c.items.find(x=>x.product_id===src.product_id && x.price===src.price && x.name===src.name);
    if (existing){ existing.quantity += qty; } else { c.items.push({product_id:src.product_id, name:src.name, price:src.price, quantity: qty}); }
    renderSplitUI();
}

function returnToAvailable(customerIndex, itemIndex){
    const c = splitState.customers[customerIndex];
    const it = c.items[itemIndex];
    if(!it) return;
    // return all qty to available (merge if exists)
    const existing = splitState.available.find(x=>x.product_id===it.product_id && x.price===it.price && x.name===it.name);
    if (existing){ existing.quantity += it.quantity; } else { splitState.available.push(deepClone(it)); }
    c.items.splice(itemIndex,1);
    renderSplitUI();
}

function payForCustomer(customerIndex){
    const c = splitState.customers[customerIndex];
    if (!c || !c.items.length) return;
    // Build sub-order from items
    const subOrder = c.items.map(it=>({product_id: it.product_id, name: it.name, price: it.price, quantity: it.quantity, hasOptions:false}));
    // Save context
    window._splitContext = {
        active: true,
        customerIndex: customerIndex,
        originalOrder: deepClone(currentOrder),
        subOrder: deepClone(subOrder),
        splitModalOpen: true // Track that split modal should stay open
    };
    // Set current order to sub-order and open cash payment modal
    currentOrder = deepClone(subOrder);
    updateOrderDisplay && updateOrderDisplay();
    showCashPaymentWindow();
}

function handleCardPaymentFromSplit(customerIndex) {
    const c = splitState.customers[customerIndex];
    if (!c || !c.items.length) return;
    
    // Remove items from original order
    const itemsToRemove = c.items;
    itemsToRemove.forEach(removeItem => {
        const orderIndex = currentOrder.findIndex(orderItem => 
            orderItem.product_id === removeItem.product_id && 
            orderItem.price === removeItem.price
        );
        if (orderIndex !== -1) {
            if (currentOrder[orderIndex].quantity > removeItem.quantity) {
                currentOrder[orderIndex].quantity -= removeItem.quantity;
            } else {
                currentOrder.splice(orderIndex, 1);
            }
        }
    });
    
    // Remove customer from split
    splitState.customers.splice(customerIndex, 1);
    
    // Update displays
    updateOrderDisplay();
    renderSplitUI();
    
    alert('Card payment successful for selected items!');
}

function handleCardPayment() {
    if (window._splitContext && window._splitContext.active) {
        // Handle card payment from split context
        completePayment('card');
    } else {
        // Regular card payment
        completePayment('card');
    }
}

function showCashPaymentWindow() {
    // Create cash payment modal if it doesn't exist
    let cashModal = document.getElementById('cashPaymentWindow');
    if (!cashModal) {
        cashModal = document.createElement('div');
        cashModal.id = 'cashPaymentWindow';
        cashModal.className = 'cash-payment-window';
        cashModal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 10000;
            width: 400px;
            padding: 20px;
            display: none;
        `;
        
        const total = calculateTotal();
        cashModal.innerHTML = `
            <h3 class="text-center mb-4">Cash Payment</h3>
            
            <div class="change-display" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div>Total: <span id="cashModalTotal">£${total.toFixed(2)}</span></div>
                <div>Amount Paid: <span id="cashAmountPaid">£0.00</span></div>
                <div>Change: <span id="cashChangeAmount">£0.00</span></div>
            </div>
            
            <div class="numpad" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px;">
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('1')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">1</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('2')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">2</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('3')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">3</button>
                <button class="numpad-btn money-btn" onclick="addCashMoneyAmount(5)" style="padding: 15px; border: 1px solid #28a745; background: #28a745; color: white; border-radius: 5px;">£5</button>
                
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('4')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">4</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('5')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">5</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('6')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">6</button>
                <button class="numpad-btn money-btn" onclick="addCashMoneyAmount(10)" style="padding: 15px; border: 1px solid #28a745; background: #28a745; color: white; border-radius: 5px;">£10</button>
                
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('7')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">7</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('8')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">8</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('9')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">9</button>
                <button class="numpad-btn money-btn" onclick="addCashMoneyAmount(20)" style="padding: 15px; border: 1px solid #28a745; background: #28a745; color: white; border-radius: 5px;">£20</button>
                
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('.')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">.</button>
                <button class="numpad-btn" onclick="appendToCashPaymentAmount('0')" style="padding: 15px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 5px;">0</button>
                <button class="numpad-btn" onclick="clearCashPaymentAmount()" style="padding: 15px; border: 1px solid #dc3545; background: #dc3545; color: white; border-radius: 5px;">
                    <i class="fas fa-backspace"></i>
                </button>
                <button class="numpad-btn money-btn" onclick="addCashMoneyAmount(50)" style="padding: 15px; border: 1px solid #28a745; background: #28a745; color: white; border-radius: 5px;">£50</button>
            </div>
            
            <div class="d-grid gap-2">
                <button class="btn btn-success" onclick="completeCashPayment()" style="padding: 15px; background: #28a745; border: none; color: white; border-radius: 5px; font-weight: bold;">
                    COMPLETE PAYMENT
                </button>
                <button class="btn btn-secondary" onclick="hideCashPaymentWindow()" style="padding: 10px; background: #6c757d; border: none; color: white; border-radius: 5px;">
                    CANCEL
                </button>
            </div>
        `;
        
        document.body.appendChild(cashModal);
    }
    
    // Update total and show modal
    const total = calculateTotal();
    document.getElementById('cashModalTotal').textContent = `£${total.toFixed(2)}`;
    cashModal.style.display = 'block';
    
    // Reset payment amount
    window.cashPaymentAmount = '';
    updateCashPaymentDisplay();
}

window.cashPaymentAmount = '';

function appendToCashPaymentAmount(digit) {
    window.cashPaymentAmount += digit;
    updateCashPaymentDisplay();
}

function clearCashPaymentAmount() {
    window.cashPaymentAmount = window.cashPaymentAmount.slice(0, -1);
    updateCashPaymentDisplay();
}

function addCashMoneyAmount(amount) {
    const current = parseFloat(window.cashPaymentAmount) || 0;
    window.cashPaymentAmount = (current + amount).toFixed(2);
    updateCashPaymentDisplay();
}

function updateCashPaymentDisplay() {
    const paidAmount = parseFloat(window.cashPaymentAmount) || 0;
    const totalAmount = calculateTotal();
    const change = Math.max(0, paidAmount - totalAmount);
    
    document.getElementById('cashAmountPaid').textContent = `£${paidAmount.toFixed(2)}`;
    document.getElementById('cashChangeAmount').textContent = `£${change.toFixed(2)}`;
}

function hideCashPaymentWindow() {
    const cashModal = document.getElementById('cashPaymentWindow');
    if (cashModal) {
        cashModal.style.display = 'none';
    }
    
    // If split context is active, restore original order and reopen split modal
    if (window._splitContext && window._splitContext.active && window._splitContext.splitModalOpen) {
        currentOrder = deepClone(window._splitContext.originalOrder);
        updateOrderDisplay();
        // Split modal should still be open, just refresh it
        renderSplitUI();
    }
}

function completeCashPayment() {
    const paidAmount = parseFloat(window.cashPaymentAmount) || 0;
    const totalAmount = calculateTotal();
    
    if (paidAmount < totalAmount) {
        alert('Amount paid is less than the total. Please enter a sufficient amount.');
        return;
    }
    
    // If in split context, handle split payment completion
    if (window._splitContext && window._splitContext.active) {
        // Remove items from original order
        const customerItems = window._splitContext.subOrder;
        customerItems.forEach(removeItem => {
            const orderIndex = window._splitContext.originalOrder.findIndex(orderItem => 
                orderItem.product_id === removeItem.product_id && 
                orderItem.price === removeItem.price
            );
            if (orderIndex !== -1) {
                if (window._splitContext.originalOrder[orderIndex].quantity > removeItem.quantity) {
                    window._splitContext.originalOrder[orderIndex].quantity -= removeItem.quantity;
                } else {
                    window._splitContext.originalOrder.splice(orderIndex, 1);
                }
            }
        });
        
        // Remove customer from split
        const customerIndex = window._splitContext.customerIndex;
        splitState.customers.splice(customerIndex, 1);
        
        // Update current order to remaining items
        currentOrder = deepClone(window._splitContext.originalOrder);
        
        // Update available items in split state
        rebuildAvailableFromOrder();
        
        // Update displays
        updateOrderDisplay();
        renderSplitUI();
        
        // Hide cash payment window
        hideCashPaymentWindow();
        
        // Show success message
        const change = paidAmount - totalAmount;
        alert(`Cash payment successful! Change: £${change.toFixed(2)}`);
        
        // Reset split context but keep modal open if there are more customers
        window._splitContext.active = false;
        
    } else {
        // Regular cash payment - complete entire order
        completePayment('cash');
        hideCashPaymentWindow();
    }
}

let draggedElement = null;

function handleDragStart(e) {
    draggedElement = this;
    this.style.opacity = '0.5';
}

function handleDragOver(e) {
    e.preventDefault();
}

function handleDrop(e) {
    e.preventDefault();
    if (this !== draggedElement) {
        const categoryList = document.getElementById('categoryList');
        const allItems = Array.from(categoryList.children);
        const draggedIndex = allItems.indexOf(draggedElement);
        const targetIndex = allItems.indexOf(this);
        
        if (draggedIndex < targetIndex) {
            categoryList.insertBefore(draggedElement, this.nextSibling);
        } else {
            categoryList.insertBefore(draggedElement, this);
        }
        
        // Update categories array order
        const draggedCategory = categories[draggedIndex];
        categories.splice(draggedIndex, 1);
        const newTargetIndex = draggedIndex < targetIndex ? targetIndex - 1 : targetIndex;
        categories.splice(newTargetIndex, 0, draggedCategory);
    }
}

function handleDragEnd(e) {
    this.style.opacity = '1';
    draggedElement = null;
}

// Save category order to database
function saveCategoryOrder() {
    const categoryOrders = categories.map(cat => cat.id);
    
    fetch('db.php?action=update_category_order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'category_orders=' + encodeURIComponent(JSON.stringify(categoryOrders))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Category order saved successfully');
        } else {
            console.error('Error saving category order:', data.error);
        }
    })
    .catch(error => {
        console.error('Error saving category order:', error);
    });
}
    </script>

    <!-- Split Payment Modal -->
    <div class="split-payment-modal" id="splitPaymentModal" style="display:none;">
        <div class="split-payment-content">
            <h3 class="text-center mb-2">
                <i class="fas fa-users me-2"></i>Split Payment - Assign Items to Customers
            </h3>
            <div class="split-container">
                <div class="available-items">
                    <h5 class="text-center mb-2">Available Items (Click or Drag to Assign)</h5>
                    <div id="availableItemsList"></div>
                </div>
                <div class="customer-sections" id="customerSections"></div>
            </div>
            <div class="split-controls">
                <button class="btn btn-secondary" onclick="hideSplitPaymentModal()">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button class="btn btn-primary" onclick="addCustomer()">
                    <i class="fas fa-user-plus me-1"></i>Add Customer
                </button>
                <button class="btn btn-danger" onclick="resetSplit()">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
    
</body>
</html>
