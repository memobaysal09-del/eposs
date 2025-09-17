<?php
// opsiyon.php - Product option selection page

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

// Debug
error_log("URL Parameters - product_id: $product_id, table_id: $table_id");

if ($product_id <= 0) {
    die("Invalid product ID");
}

if ($table_id <= 0) {
    die("Invalid table ID");
}

if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

$product = null;
$product_category_id = 0;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.id as category_id FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    
    if (!$stmt->execute()) {
        die("Query error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if (!$product) {
        die("Product not found");
    }
    
    $product_category_id = $product['category_id'];
    error_log("Product found: " . $product['name'] . ", Category ID: " . $product_category_id);
}

$table = null;
if ($table_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->bind_param("i", $table_id);
    
    if (!$stmt->execute()) {
        die("Query error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $table = $result->fetch_assoc();
    $stmt->close();
    
    if (!$table) {
        die("Table not found");
    }
    
    error_log("Table found: " . $table['number']);
}

// Çift kayıtları temizle
$cleanup_stmt = $conn->prepare("DELETE t1 FROM product_option_groups t1 INNER JOIN product_option_groups t2 WHERE t1.id > t2.id AND t1.product_id = t2.product_id AND t1.option_group_id = t2.option_group_id");

if ($cleanup_stmt->execute()) {
    error_log("Duplicate records cleaned: " . $cleanup_stmt->affected_rows);
} else {
    error_log("Cleanup error: " . $cleanup_stmt->error);
}

$cleanup_stmt->close();

// Hem ürün hem de kategori opsiyonlarını getir
$optionGroups = [];
$stmt = $conn->prepare("SELECT DISTINCT og.id, og.name, og.is_required, og.min_selection, og.max_selection FROM option_groups og WHERE og.id IN (SELECT DISTINCT pog.option_group_id FROM product_option_groups pog WHERE pog.product_id = ? UNION SELECT DISTINCT cog.option_group_id FROM category_option_groups cog WHERE cog.category_id = ?) ORDER BY og.name");

if (!$stmt) {
    die("Query preparation error: " . $conn->error);
}

$stmt->bind_param("ii", $product_id, $product_category_id);

if (!$stmt->execute()) {
    die("Query execution error: " . $stmt->error);
}

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $optionGroups[] = $row;
}
$stmt->close();

error_log("Product ID: $product_id, Category ID: $product_category_id has " . count($optionGroups) . " option groups (product + category)");

foreach ($optionGroups as $key => $group) {
    $stmt = $conn->prepare("SELECT * FROM options WHERE group_id = ? ORDER BY name");
    $stmt->bind_param("i", $group['id']);
    
    if (!$stmt->execute()) {
        error_log("Options query error for group {$group['id']}: " . $stmt->error);
        continue;
    }
    
    $result = $stmt->get_result();
    $options = [];
    
    while ($row = $result->fetch_assoc()) {
        $options[] = $row;
    }
    
    $stmt->close();
    
    $optionGroups[$key]['options'] = $options;
    error_log("Option Group: " . $group['name'] . " (ID: " . $group['id'] . ") with " . count($options) . " options");
}

/* === GLOBAL EXTRAS / SWAPS / DRINKS QUERIES (product_id FİLTRESİ YOK) === */
if (!isset($__extras_loaded__)) {
    $__extras_loaded__ = true;

    // Extras - category aware (global + this category)
    $extras_stmt = $conn->prepare("SELECT id, name, price FROM extras WHERE (category_id IS NULL OR category_id = ?) ORDER BY name");
    $extras_stmt->bind_param("i", $product_category_id);
    $extras_stmt->execute();
    $extras_result = $extras_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $extras = !empty($extras_result) ? $extras_result : [];
    $extras_stmt->close();

    // Cold Drinks - category aware (global + this category)
    $cold_drinks_stmt = $conn->prepare("SELECT id, name, price FROM cold_drinks WHERE (category_id IS NULL OR category_id = ?) ORDER BY name");
    $cold_drinks_stmt->bind_param("i", $product_category_id);
    $cold_drinks_stmt->execute();
    $cold_drinks_result = $cold_drinks_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $cold_drinks = !empty($cold_drinks_result) ? $cold_drinks_result : [];
    $cold_drinks_stmt->close();

    // Hot Drinks - category aware (global + this category)
    $hot_drinks_stmt = $conn->prepare("SELECT id, name, price FROM hot_drinks WHERE (category_id IS NULL OR category_id = ?) ORDER BY name");
    $hot_drinks_stmt->bind_param("i", $product_category_id);
    $hot_drinks_stmt->execute();
    $hot_drinks_result = $hot_drinks_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $hot_drinks = !empty($hot_drinks_result) ? $hot_drinks_result : [];
    $hot_drinks_stmt->close();

    // Swaps Out - category aware (global + this category)
    $swaps_out_stmt = $conn->prepare("SELECT id, name, price FROM swaps_out WHERE (category_id IS NULL OR category_id = ?) ORDER BY name");
    $swaps_out_stmt->bind_param("i", $product_category_id);
    $swaps_out_stmt->execute();
    $swaps_out_result = $swaps_out_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $swaps_out = !empty($swaps_out_result) ? $swaps_out_result : [];
    $swaps_out_stmt->close();

    // Swaps In - category aware (global + this category)
    $swaps_in_stmt = $conn->prepare("SELECT id, name, price FROM swaps_in WHERE (category_id IS NULL OR category_id = ?) ORDER BY name");
    $swaps_in_stmt->bind_param("i", $product_category_id);
    $swaps_in_stmt->execute();
    $swaps_in_result = $swaps_in_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $swaps_in = !empty($swaps_in_result) ? $swaps_in_result : [];
    $swaps_in_stmt->close();

    $extras_json = json_encode($extras);
    $cold_drinks_json = json_encode($cold_drinks);
    $hot_drinks_json = json_encode($hot_drinks);
    $swaps_out_json = json_encode($swaps_out);
    $swaps_in_json = json_encode($swaps_in);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Option Selection - <?php echo $product['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        .product-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .option-group {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: white;
        }
        .option-item {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .option-item:hover {
            background-color: #f8f9fa;
            border-color: #28a745;
        }
        .option-item.selected {
            background-color: #d4edda;
            border-color: #28a745;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quantity-btn:hover {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .quantity-display {
            min-width: 30px;
            text-align: center;
            font-weight: bold;
        }
        .summary-card {
            position: sticky;
            top: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9em;
            display: none;
        }
        .option-content {
            display: none;
        }
        .option-content.active {
            display: block;
        }
        .list-group-item.selected {
            background-color: #d4edda;
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Option Selection</h1>
                <!-- Changed back button to redirect instead of closing window -->
<button onclick="closeWindow()" class="btn btn-light">
    <i class="fas fa-arrow-left me-2"></i>Back
</button>

<script>
function closeWindow() {
    // Önce geçmişte geri gitmeyi dene
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Eğer geçmiş yoksa pencereyi kapatmayı dene
        window.close();
    }
    return false;
}
</script>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="product-card card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <i class="fas fa-utensils fa-3x text-secondary"></i>
                            </div>
                            <div class="col-md-6">
                                <h3 class="card-title"><?php echo $product['name']; ?></h3>
                                <p class="card-text text-muted"><?php echo $product['category_name']; ?></p>
                                <!-- Added product content display -->
                                <?php if (!empty($product['icerik'])): ?>
                                    <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($product['icerik']); ?></small></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-end">
                                <h4 class="text-primary">£<?php echo number_format($product['price'], 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="optionsForm">
                    <div id="extrasSwapsArea" class="alert alert-secondary text-center mt-3">Please select an option type from the right panel.</div>
                    
                    <div id="optionGroupsContent" class="option-content">
                        <?php if (count($optionGroups) > 0): ?>
                            <?php foreach ($optionGroups as $index => $group): ?>
                                <div class="option-group">
                                    <h4>
                                        <?php echo $group['name']; ?>
                                        <?php if ($group['is_required']): ?>
                                            <span class="badge bg-danger">Required</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Optional</span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="text-muted">
                                        <?php if ($group['is_required']): ?>
                                            This group is required. You must select at least <?php echo $group['min_selection']; ?> option(s). No maximum limit.
                                        <?php else: ?>
                                            This group is optional. You may select <?php echo $group['min_selection']; ?> or more options. No maximum limit.
                                        <?php endif; ?>
                                    </p>
                                    
                                    <div class="options-container">
                                        <?php foreach ($group['options'] as $option): ?>
                                            <div class="option-item" data-group="<?php echo $group['id']; ?>" data-option="<?php echo $option['id']; ?>" data-price="<?php echo $option['price']; ?>">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="form-check flex-grow-1">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="option_group_<?php echo $group['id']; ?>" 
                                                               id="option_<?php echo $group['id']; ?>_<?php echo $option['id']; ?>" 
                                                               value="<?php echo $option['id']; ?>"
                                                               data-price="<?php echo $option['price']; ?>"
                                                               style="display: none;">
                                                        <label class="form-check-label d-flex justify-content-between w-100" for="option_<?php echo $group['id']; ?>_<?php echo $option['id']; ?>">
                                                            <span><?php echo $option['name']; ?></span>
                                                            <span class="text-primary">+£<?php echo number_format($option['price'], 2); ?></span>
                                                        </label>
                                                    </div>
                                                    <div class="quantity-controls">
                                                        <button type="button" class="quantity-btn" onclick="decreaseQuantity(<?php echo $group['id']; ?>, <?php echo $option['id']; ?>)">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <span class="quantity-display" id="qty_<?php echo $group['id']; ?>_<?php echo $option['id']; ?>">0</span>
                                                        <button type="button" class="quantity-btn" onclick="increaseQuantity(<?php echo $group['id']; ?>, <?php echo $option['id']; ?>)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="error-message mt-2" id="error_<?php echo $group['id']; ?>">
                                        <?php if ($group['is_required']): ?>
                                            Please make a valid selection for this required group.
                                        <?php else: ?>
                                            Please make a valid selection for this group.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No options are defined for this product.
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="col-md-4">
                <div class="summary-card card">
                    <div class="card-header bg-primary text-white">
                       <center> <h4 class="mb-0">Order Summary</h4></center>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Product:</strong> <?php echo $product['name']; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Base Price:</strong> £<?php echo number_format($product['price'], 2); ?>
                        </div>
                        
                        <div id="selectedOptions">
                            <strong>Selected Options:</strong>
                            <div id="selectedOptionsList" class="mt-2"></div>
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="totalPrice">£<?php echo number_format($product['price'], 2); ?></strong>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="button" class="btn btn-success btn-lg" id="addToOrder">
                                <i class="fas fa-cart-plus me-2"></i>Add to Basket
                            </button>
                        </div>
                    </div>
                </div> <br>
<div class="summary-card card">
    <div class="card-header bg-primary text-white">
        <center><h4 class="mb-0">Extra or Swaps</h4></center>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-success mb-2" id="btnOpsiyon">
                <i class="fas fa-cog me-2"></i>Opsiyon
            </button>
            <button type="button" class="btn btn-outline-success mb-2" id="btnExtra">
                <i class="fas fa-plus-circle me-2"></i>Extra
            </button>
            <!-- Added cold drinks button -->
            <button type="button" class="btn btn-outline-success mb-2" id="btnColdDrinks">
                <i class="fas fa-snowflake me-2"></i>Cold Drinks
            </button>
            <!-- Added hot drinks button -->
            <button type="button" class="btn btn-outline-success mb-2" id="btnHotDrinks">
                <i class="fas fa-fire me-2"></i>Hot Drinks
            </button>
            <button type="button" class="btn btn-outline-success mb-2" id="btnSwapOut">
                <i class="fas fa-exchange-alt me-2"></i>Swap Out
            </button>
            <button type="button" class="btn btn-outline-success mb-2" id="btnSwapIn">
                <i class="fas fa-exchange-alt me-2"></i>Swap In
            </button>
        </div>
    </div>
</div>              
            </div>
            
        </div>
    </div>

    <!-- Debug information -->
    <div style="display: none; position: fixed; bottom: 10px; right: 10px; background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; z-index: 1000;">
        <strong>Debug Info:</strong><br>
        Product ID: <?php echo $product_id; ?><br>
        Category ID: <?php echo $product_category_id; ?><br>
        Option Groups: <?php echo count($optionGroups); ?><br>
        <?php foreach ($optionGroups as $group): ?>
            Group: <?php echo $group['name']; ?> (<?php echo count($group['options']); ?> options)<br>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const basePrice = <?php echo $product['price']; ?>;
        let selectedOptions = {};
        let optionQuantities = {};
        let totalPrice = basePrice;
        let currentView = null;
        let selectedExtras = {};
        let selectedColdDrinks = {}; // Added cold drinks selection
        let selectedHotDrinks = {}; // Added hot drinks selection
        let selectedSwapsOut = {};
        let selectedSwapsIn = {};
        
        const extrasData = <?php echo $extras_json; ?>;
        const coldDrinksData = <?php echo $cold_drinks_json; ?>; // Added cold drinks data
        const hotDrinksData = <?php echo $hot_drinks_json; ?>; // Added hot drinks data
        const swapsOutData = <?php echo $swaps_out_json; ?>;
        const swapsInData = <?php echo $swaps_in_json; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            updateTotalPrice();
            document.getElementById('addToOrder').addEventListener('click', addToOrder);
            
            showView('optionGroups');
            setActiveButton('btnOpsiyon');
            
            // Set up button click handlers
            document.getElementById('btnOpsiyon').addEventListener('click', function() {
                showView('optionGroups');
                setActiveButton('btnOpsiyon');
            });
            
            document.getElementById('btnExtra').addEventListener('click', function() {
                showView('extras');
                setActiveButton('btnExtra');
            });
            
            document.getElementById('btnColdDrinks').addEventListener('click', function() {
                showView('coldDrinks');
                setActiveButton('btnColdDrinks');
            });
            
            document.getElementById('btnHotDrinks').addEventListener('click', function() {
                showView('hotDrinks');
                setActiveButton('btnHotDrinks');
            });
            
            document.getElementById('btnSwapOut').addEventListener('click', function() {
                showView('swapsOut');
                setActiveButton('btnSwapOut');
            });
            
            document.getElementById('btnSwapIn').addEventListener('click', function() {
                showView('swapsIn');
                setActiveButton('btnSwapIn');
            });
        });
        
function setActiveButton(activeId) {
    // Remove active class from all buttons
    ['btnOpsiyon', 'btnExtra', 'btnColdDrinks', 'btnHotDrinks', 'btnSwapOut', 'btnSwapIn'].forEach(id => { // Added new button IDs
        const btn = document.getElementById(id);
        if (btn) {
            btn.classList.remove('btn-primary');
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        }
    });
    
    // Set active button
    const activeBtn = document.getElementById(activeId);
    if (activeBtn) {
        activeBtn.classList.remove('btn-outline-success');
        activeBtn.classList.add('btn-success');
    }
}
        
        function showView(viewName) {
            // Hide all content areas
            document.querySelectorAll('.option-content').forEach(el => {
                el.classList.remove('active');
            });
            
            // Show the selected view
            if (viewName === 'optionGroups') {
                document.getElementById('optionGroupsContent').classList.add('active');
                document.getElementById('extrasSwapsArea').style.display = 'none';
            } else {
                document.getElementById('extrasSwapsArea').style.display = 'block';
                document.getElementById('optionGroupsContent').classList.remove('active');
                
                loadExtrasSwapsContent(viewName);
            }
            
            currentView = viewName;
        }
        
        function loadExtrasSwapsContent(viewName) {
            const area = document.getElementById('extrasSwapsArea');
            let data;
            let badgeClass;
            let typeName;
            
            switch(viewName) {
                case 'extras':
                    data = extrasData;
                    badgeClass = 'bg-success';
                    typeName = 'Extra';
                    break;
                case 'coldDrinks':
                    data = coldDrinksData;
                    badgeClass = 'bg-primary';
                    typeName = 'Cold Drink';
                    break;
                case 'hotDrinks':
                    data = hotDrinksData;
                    badgeClass = 'bg-danger';
                    typeName = 'Hot Drink';
                    break;
                case 'swapsOut':
                    data = swapsOutData;
                    badgeClass = 'bg-warning text-dark';
                    typeName = 'Swap Out';
                    break;
                case 'swapsIn':
                    data = swapsInData;
                    badgeClass = 'bg-info';
                    typeName = 'Swap In';
                    break;
            }
            
            if (data && data.length > 0) {
                let html = '<ul class="list-group mb-2">';
                data.forEach((item, index) => {
                    const isSelected = getSelectedItemsForView(viewName)[index];
                    const quantity = isSelected ? isSelected.quantity : 0;
                    
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center ${quantity > 0 ? 'selected' : ''}">
                            <div class="w-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>${item.name}</span>
                                    <span class="badge ${badgeClass}">£${parseFloat(item.price).toFixed(2)}</span>
                                </div>
                                <div class="quantity-controls mt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">${typeName}</small>
                                        <div class="quantity-controls d-flex align-items-center gap-2">
                                            <button type="button" class="quantity-btn" onclick="decreaseExtraQuantity('${viewName}', ${index})">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <span class="quantity-display" id="qty_${viewName}_${index}">${quantity}</span>
                                            <button type="button" class="quantity-btn" onclick="increaseExtraQuantity('${viewName}', ${index}, '${item.name}', ${item.price})">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                area.innerHTML = html;
            } else {
                area.innerHTML = `<p class="text-muted mb-0 text-center">No ${typeName.toLowerCase()}s are defined.</p>`;
            }
        }
        
        function getSelectedItemsForView(viewName) {
            switch(viewName) {
                case 'extras': return selectedExtras;
                case 'coldDrinks': return selectedColdDrinks; // Added cold drinks case
                case 'hotDrinks': return selectedHotDrinks; // Added hot drinks case
                case 'swapsOut': return selectedSwapsOut;
                case 'swapsIn': return selectedSwapsIn;
                default: return {};
            }
        }
        
        function increaseExtraQuantity(viewName, index, name, price) {
            const selectedItems = getSelectedItemsForView(viewName);
            
            if (!selectedItems[index]) {
                selectedItems[index] = { name, price, quantity: 0 };
            }
            
            selectedItems[index].quantity++;
            document.getElementById(`qty_${viewName}_${index}`).textContent = selectedItems[index].quantity;
            
            // Add visual feedback
            const item = document.querySelectorAll('#extrasSwapsArea .list-group-item')[index];
            if (item) item.classList.add('selected');
            
            updateSelectedOptions();
            updateTotalPrice();
        }
        
        function decreaseExtraQuantity(viewName, index) {
            const selectedItems = getSelectedItemsForView(viewName);
            
            if (!selectedItems[index] || selectedItems[index].quantity <= 0) return;
            
            selectedItems[index].quantity--;
            document.getElementById(`qty_${viewName}_${index}`).textContent = selectedItems[index].quantity;
            
            if (selectedItems[index].quantity === 0) {
                delete selectedItems[index];
                const item = document.querySelectorAll('#extrasSwapsArea .list-group-item')[index];
                if (item) item.classList.remove('selected');
            }
            
            updateSelectedOptions();
            updateTotalPrice();
        }

        function increaseQuantity(groupId, optionId) {
            const group = <?php echo json_encode($optionGroups); ?>.find(g => g.id == groupId);
            if (!group) return;
            
            if (!optionQuantities[groupId]) {
                optionQuantities[groupId] = {};
            }
            
            if (!optionQuantities[groupId][optionId]) {
                optionQuantities[groupId][optionId] = 0;
            }
            
            optionQuantities[groupId][optionId]++;
            updateQuantityDisplay(groupId, optionId);
            updateSelectedOptions();
            validateSelections(groupId);
            updateTotalPrice();
        }
        
        function decreaseQuantity(groupId, optionId) {
            if (!optionQuantities[groupId] || !optionQuantities[groupId][optionId] || optionQuantities[groupId][optionId] <= 0) {
                return;
            }
            
            optionQuantities[groupId][optionId]--;
            updateQuantityDisplay(groupId, optionId);
            updateSelectedOptions();
            validateSelections(groupId);
            updateTotalPrice();
        }
        
        function updateQuantityDisplay(groupId, optionId) {
            const quantity = optionQuantities[groupId] && optionQuantities[groupId][optionId] ? optionQuantities[groupId][optionId] : 0;
            document.getElementById(`qty_${groupId}_${optionId}`).textContent = quantity;
            
            const optionItem = document.querySelector(`[data-group="${groupId}"][data-option="${optionId}"]`);
            if (quantity > 0) {
                optionItem.classList.add('selected');
            } else {
                optionItem.classList.remove('selected');
            }
        }
        
        function validateSelections(groupId) {
            const group = <?php echo json_encode($optionGroups); ?>.find(g => g.id == groupId);
            if (!group) return;
            
            const selectedCount = Object.values(optionQuantities[groupId] || {}).reduce((sum, qty) => sum + qty, 0);
            const errorElement = document.getElementById(`error_${groupId}`);
            
            if (group.is_required && selectedCount < group.min_selection) {
                errorElement.style.display = 'block';
            } else {
                errorElement.style.display = 'none';
            }
        }
        
        function updateSelectedOptions() {
            const container = document.getElementById('selectedOptionsList');
            container.innerHTML = '';
            
            let hasSelection = false;
            
            for (const groupId in optionQuantities) {
                for (const optionId in optionQuantities[groupId]) {
                    const quantity = optionQuantities[groupId][optionId];
                    if (quantity > 0) {
                        const optionItem = document.querySelector(`[data-group="${groupId}"][data-option="${optionId}"]`);
                        const optionName = optionItem.querySelector('label span:first-child').textContent;
                        const optionPrice = parseFloat(optionItem.getAttribute('data-price'));
                        
                        const div = document.createElement('div');
                        div.className = 'd-flex justify-content-between mb-1 p-1 bg-light rounded';
                        div.innerHTML = `
                            <span><strong>${optionName}</strong> x${quantity}</span>
                            <span class="text-primary">+£${(optionPrice * quantity).toFixed(2)}</span>
                        `;
                        container.appendChild(div);
                        hasSelection = true;
                    }
                }
            }
            
            const extraTypes = [
                { items: selectedExtras, type: 'Extra' },
                { items: selectedColdDrinks, type: 'Cold Drink' },
                { items: selectedHotDrinks, type: 'Hot Drink' },
                { items: selectedSwapsOut, type: 'Swap Out' },
                { items: selectedSwapsIn, type: 'Swap In' }
            ];
            
            extraTypes.forEach(({ items, type }) => {
                for (const index in items) {
                    const item = items[index];
                    if (item.quantity > 0) {
                        const div = document.createElement('div');
                        div.className = 'd-flex justify-content-between mb-1 p-1 bg-light rounded';
                        div.innerHTML = `
                            <span><strong>${item.name}</strong> x${item.quantity} <small class="text-muted">(${type})</small></span>
                            <span class="text-primary">+£${(item.price * item.quantity).toFixed(2)}</span>
                        `;
                        container.appendChild(div);
                        hasSelection = true;
                    }
                }
            });
            
            if (!hasSelection) {
                container.innerHTML = '<span class="text-muted">No options selected yet</span>';
            }
        }
        
        function updateTotalPrice() {
            totalPrice = basePrice;
            
            for (const groupId in optionQuantities) {
                for (const optionId in optionQuantities[groupId]) {
                    const quantity = optionQuantities[groupId][optionId];
                    if (quantity > 0) {
                        const optionItem = document.querySelector(`[data-group="${groupId}"][data-option="${optionId}"]`);
                        const optionPrice = parseFloat(optionItem.getAttribute('data-price'));
                        totalPrice += optionPrice * quantity;
                    }
                }
            }
            
            const extraTypes = [
                selectedExtras,
                selectedColdDrinks,
                selectedHotDrinks,
                selectedSwapsOut,
                selectedSwapsIn
            ];
            
            extraTypes.forEach(selectedItems => {
                for (const index in selectedItems) {
                    const item = selectedItems[index];
                    if (item.quantity > 0) {
                        totalPrice += item.price * item.quantity;
                    }
                }
            });
            
            document.getElementById('totalPrice').textContent = '£' + totalPrice.toFixed(2);
        }
        
        function validateAllSelections() {
            const groups = <?php echo json_encode($optionGroups); ?>;
            let isValid = true;
            
            for (const group of groups) {
                const selectedCount = Object.values(optionQuantities[group.id] || {}).reduce((sum, qty) => sum + qty, 0);
                
                if (group.is_required && selectedCount < group.min_selection) {
                    document.getElementById(`error_${group.id}`).style.display = 'block';
                    isValid = false;
                    
                    if (isValid === false) {
                        document.getElementById(`error_${group.id}`).scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } else {
                    document.getElementById(`error_${group.id}`).style.display = 'none';
                }
            }
            
            return isValid;
        }
        
        function addToOrder() {
            if (!validateAllSelections()) {
                alert('Please complete all required fields correctly.');
                return;
            }
            
            const optionsData = [];
            
            for (const groupId in optionQuantities) {
                for (const optionId in optionQuantities[groupId]) {
                    const quantity = optionQuantities[groupId][optionId];
                    if (quantity > 0) {
                        const optionItem = document.querySelector(`[data-group="${groupId}"][data-option="${optionId}"]`);
                        const optionName = optionItem.querySelector('label span:first-child').textContent;
                        const optionPrice = parseFloat(optionItem.getAttribute('data-price'));
                        
                        for (let i = 0; i < quantity; i++) {
                            optionsData.push({
                                option_id: optionId,
                                name: optionName,
                                price: optionPrice,
                                type: 'option'
                            });
                        }
                    }
                }
            }
            
            const extraTypes = [
                { items: selectedExtras, type: 'extra' },
                { items: selectedColdDrinks, type: 'cold_drink' },
                { items: selectedHotDrinks, type: 'hot_drink' },
                { items: selectedSwapsOut, type: 'swap_out' },
                { items: selectedSwapsIn, type: 'swap_in' }
            ];
            
            extraTypes.forEach(({ items, type }) => {
                for (const index in items) {
                    const item = items[index];
                    if (item.quantity > 0) {
                        for (let i = 0; i < item.quantity; i++) {
                            optionsData.push({
                                option_id: `${type}_${index}`,
                                name: item.name,
                                price: item.price,
                                type: type
                            });
                        }
                    }
                }
            });
            
            window.opener.postMessage({
                action: 'addProductWithOptions',
                product_id: <?php echo $product_id; ?>,
                product_name: '<?php echo $product['name']; ?>',
                base_price: basePrice,
                options: optionsData,
                total_price: totalPrice,
                table_id: <?php echo $table_id; ?>
            }, '*');
            
            window.close();
        }

    </script>
</body>
</html>
