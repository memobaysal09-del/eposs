<?php
// menu_yonetimi.php - Menü yönetimi
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Restaurant Sipariş Sistemi - Menü Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 15px 0;
            text-align: center;
            margin-bottom: 20px;
        }
        .delete-btn {
            cursor: pointer;
            color: #dc3545;
        }
        .edit-btn {
            cursor: pointer;
            color: #0d6efd;
        }
        .badge-custom {
            font-size: 0.85em;
        }
        .toplu-ekleme {
            border-left: 3px solid #0d6efd;
            padding-left: 15px;
            margin-top: 20px;
        }
        
        /* Added accordion styles */
        .accordion-category {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        
        .accordion-header {
            background-color: #f8f9fa;
            border: none;
            padding: 1rem;
            cursor: pointer;
            display: flex;
            justify-content: between;
            align-items: center;
            width: 100%;
            text-align: left;
            transition: background-color 0.15s ease-in-out;
        }
        
        .accordion-header:hover {
            background-color: #e9ecef;
        }
        
        .accordion-header.active {
            background-color: #dc3545;
            color: white;
        }
        
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background-color: white;
        }
        
        .accordion-content.active {
            max-height: 1000px;
            transition: max-height 0.3s ease-in;
        }
        
        .accordion-body {
            padding: 1rem;
        }
        
        .accordion-icon {
            transition: transform 0.3s ease;
        }
        
        .accordion-header.active .accordion-icon {
            transform: rotate(180deg);
        }
        
        .category-actions {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        
        .accordion-header:hover .category-actions,
        .accordion-header.active .category-actions {
            opacity: 1;
        }
        /* Sıra numarası için özel stil */
        .order-badge {
            background-color: #6c757d;
            font-size: 0.75rem;
            margin-left: 10px;
        }
        /* Sürükleme efekti için */
        .sortable-ghost {
            opacity: 0.5;
        }
        
        /* Added drag handle styles */
        .drag-handle {
            cursor: grab;
            color: #6c757d;
            margin-right: 10px;
            transition: color 0.2s ease;
        }
        
        .drag-handle:hover {
            color: #495057;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .accordion-category.sortable-chosen .drag-handle {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Menü Yönetimi</h1>
                
                <a href="opsiyon_yonetimi.php" class="btn btn-primary">
                    <i class="fas fa-utensils me-2"></i>Opsiyon Yönetimi
                </a>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-home me-2"></i>Masalara Dön
                </a>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Kategori Ekle</h5>
                        <button class="btn btn-sm btn-danger" id="deleteAllCategories">
                            <i class="fas fa-trash me-1"></i>Tüm Kategorileri ve Ürünleri Sil
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="categoryName" class="form-label">Kategori Adı</label>
                            <input type="text" class="form-control" id="categoryName">
                        </div>
                        <button class="btn btn-primary" id="addCategory">Kategori Ekle</button>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Ürün Ekle</h5>
                        <button class="btn btn-sm btn-danger" id="deleteAllProducts">
                            <i class="fas fa-trash me-1"></i>Tüm Ürünleri Sil
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Kategori</label>
                            <select class="form-select" id="productCategory">
                                <!-- Kategoriler JavaScript ile buraya eklenecek -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="productName" class="form-label">Ürün Adı</label>
                            <input type="text" class="form-control" id="productName">
                        </div>
                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Fiyat (£)</label>
                            <input type="number" step="0.01" class="form-control" id="productPrice">
                        </div>
                        <!-- Added content field for single product addition -->
                        <div class="mb-3">
                            <label for="productContent" class="form-label">İçerik (Opsiyonel)</label>
                            <textarea class="form-control" id="productContent" rows="3" placeholder="Ürün içeriğini buraya yazın..."></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="productAvailable" checked>
                            <label class="form-check-label" for="productAvailable">Mevcut</label>
                        </div>
                        <button class="btn btn-primary" id="addProduct">Ürün Ekle</button>
                    </div>
                </div>
<!-- Toplu Ekleme Bölümü -->
<div class="card mt-3">
    <div class="card-header">
        <h5>Toplu Ürün Ekleme</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="bulkData" class="form-label">Toplu Veri Girişi</label>
            <!-- Updated placeholder to show content format -->
            <textarea class="form-control" id="bulkData" rows="5" placeholder="Format: --Kategori Adı
Ürün Adı Fiyat -İçerik (opsiyonel)
Ürün Adı Fiyat -İçerik (opsiyonel)
--Başka Kategori
Ürün Adı Fiyat -İçerik (opsiyonel)
Örnek:
--Kahvaltı
Sucuklu Yumurta 45.00 -Sucuk, yumurta, domates
Peynir Tabağı 60.00 -Çeşitli peynirler
--Ana Yemekler
Kebap 120.00 -Et kebabı, pilav, salata
Lahmacun 40.00"></textarea>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="bulkProductAvailable" checked>
            <label class="form-check-label" for="bulkProductAvailable">Tüm ürünleri mevcut olarak işaretle</label>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="skipExistingCategories" checked>
            <label class="form-check-label" for="skipExistingCategories">Mevcut kategorileri atla (aynı isimde kategori varsa ürünleri mevcut kategoriye ekle)</label>
        </div>
        <button class="btn btn-success" id="addBulkProducts">Toplu Ekle</button>
    </div>
</div>
 </div>
            <div class="col-md-6">
                <h5>Mevcut Menü</h5>
                <div id="menuItems">
                    <!-- Menü öğeleri buraya eklenecek -->
                </div>
            </div>
        </div>
    </div>
    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Modal content will be added dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global değişkenler
        let categories = [];
        let products = [];
        let currentEditId = null;
        let currentEditType = null;
        let sortable = null;
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            document.getElementById('addCategory').addEventListener('click', addCategory);
            document.getElementById('addProduct').addEventListener('click', addProduct);
            document.getElementById('deleteAllCategories').addEventListener('click', confirmDeleteAllCategories);
            document.getElementById('deleteAllProducts').addEventListener('click', confirmDeleteAllProducts);
            document.getElementById('saveChanges').addEventListener('click', saveChanges);
            document.getElementById('addBulkProducts').addEventListener('click', addBulkProducts);
        });
        // Kategorileri yükle
        function loadCategories() {
            fetch('db.php?action=get_categories')
                .then(response => response.json())
                .then(data => {
                    categories = data;
                    updateCategoryDropdown();
                    renderMenuItems();
                    initSortable();
                })
                .catch(error => {
                    console.error('Kategoriler yüklenirken hata oluştu:', error);
                    alert('Kategoriler yüklenirken bir hata oluştu. Lütfen sayfayı yenileyin.');
                });
        }
        // Sortable'ı başlat
        function initSortable() {
            const menuItemsContainer = document.getElementById('menuItems');
            if (sortable) {
                sortable.destroy();
            }
            sortable = new Sortable(menuItemsContainer, {
                handle: '.drag-handle', // Added handle selector for drag grip
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen', // Added chosen class for visual feedback
                onEnd: function(evt) {
                    updateCategoryOrder();
                }
            });
        }
// Sıralama değiştiğinde
function updateCategoryOrder() {
    const categoryElements = document.querySelectorAll('.accordion-category');
    const categoryOrders = [];
    
    categoryElements.forEach((element, index) => {
        const categoryId = element.querySelector('.accordion-header').getAttribute('onclick').match(/\d+/)[0];
        const orderIndex = index + 1; // 1'den başlayan sıra numarası
        
        categoryOrders.push({
            id: categoryId,
            order_index: orderIndex
        });
        
        // UI'daki sıra numarasını güncelle
        const orderBadge = element.querySelector('.order-badge');
        if (orderBadge) {
            orderBadge.textContent = `#${orderIndex}`;
        }
    });
    
    // Sunucuya yeni sırayı gönder
    const formData = new FormData();
    formData.append('category_orders', JSON.stringify(categoryOrders));
    
    fetch('db.php?action=update_category_order', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Başarılı - kategorileri yeniden yükle (order_index'leri güncellemek için)
            loadCategories();
            console.log('Sıralama güncellendi');
        } else {
            alert('Sıralama güncellenirken hata oluştu: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Sıralama güncellenirken hata oluştu:', error);
    });
}
        // Tüm ürünleri yükle
        function loadAllProducts() {
            fetch('db.php?action=get_all_products')
                .then(response => response.json())
                .then(data => {
                    products = data;
                })
                .catch(error => {
                    console.error('Ürünler yüklenirken hata oluştu:', error);
                });
        }
        // Kategori dropdown'ını güncelle
        function updateCategoryDropdown() {
            const productCategory = document.getElementById('productCategory');
            productCategory.innerHTML = '';
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                productCategory.appendChild(option);
            });
        }
// Menü öğelerini accordion formatında görüntüle
function renderMenuItems() {
    const menuItems = document.getElementById('menuItems');
    menuItems.innerHTML = '';
    
    if (categories.length === 0) {
        menuItems.innerHTML = '<p>Henüz hiç kategori eklenmemiş.</p>';
        return;
    }
    
    // Kategorileri sıra numarasına göre sırala
    const sortedCategories = [...categories].sort((a, b) => {
        return (a.order_index || 0) - (b.order_index || 0);
    });
    
    sortedCategories.forEach((category, index) => {
        const categoryAccordion = document.createElement('div');
        categoryAccordion.className = 'accordion-category';
        categoryAccordion.innerHTML = `
            <button class="accordion-header" onclick="toggleAccordion(${category.id})">
                <div class="d-flex align-items-center flex-grow-1">
                    <i class="fas fa-grip-vertical drag-handle" onclick="event.stopPropagation();" title="Sürükleyerek sıralayın"></i>
                    <h5 class="mb-0 me-3">${category.name}</h5>
                    <span class="badge bg-secondary" id="product-count-${category.id}">0 ürün</span>
                    <span class="badge order-badge" title="Sıra Numarası">#${category.order_index || 0}</span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="category-actions me-3">
                        <i class="fas fa-edit edit-btn me-2" onclick="event.stopPropagation(); editCategory(${category.id}, '${category.name.replace(/'/g, "\\'")}', ${category.order_index || 0})"></i>
                        <i class="fas fa-trash delete-btn" onclick="event.stopPropagation(); deleteCategory(${category.id})"></i>
                    </div>
                    <i class="fas fa-chevron-down accordion-icon"></i>
                </div>
            </button>
            <div class="accordion-content" id="accordion-content-${category.id}">
                <div class="accordion-body" id="products-${category.id}">
                    <p>Ürünler yükleniyor...</p>
                </div>
            </div>
        `;
        
        menuItems.appendChild(categoryAccordion);
        
        // Bu kategoriye ait ürünleri yükle
        loadCategoryProducts(category.id);
    });
}
        // Accordion toggle functionality
        function toggleAccordion(categoryId) {
            const header = document.querySelector(`button[onclick="toggleAccordion(${categoryId})"]`);
            const content = document.getElementById(`accordion-content-${categoryId}`);
            
            // Toggle active class
            header.classList.toggle('active');
            content.classList.toggle('active');
        }
 // loadCategoryProducts fonksiyonunu güncelleyelim
function loadCategoryProducts(categoryId) {
    fetch(`db.php?action=get_products&category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            const productsContainer = document.getElementById(`products-${categoryId}`);
            const productCountBadge = document.getElementById(`product-count-${categoryId}`);
            
            productsContainer.innerHTML = '';
            
            productCountBadge.textContent = `${data.length} ürün`;
            
            if (data.length === 0) {
                productsContainer.innerHTML = '<p class="text-muted">Bu kategoride henüz ürün yok.</p>';
                return;
            }
            
            const productList = document.createElement('ul');
            productList.className = 'list-group list-group-flush';
            productList.id = `product-list-${categoryId}`;
            
            // order_index'e göre sırala
            data.sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
            
            data.forEach((product, index) => {
                const productItem = document.createElement('li');
                productItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                productItem.setAttribute('data-product-id', product.id);
                productItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="fas fa-grip-vertical drag-handle me-2" style="cursor: grab;" title="Sürükleyerek sıralayın"></i>
                        <div>
                            <h6 class="mb-1">${product.name}</h6>
                            <small class="text-muted">£${product.price}</small>
                        </div>
                    </div>
                    <div>
                        <span class="badge ${product.available == 1 ? 'bg-success' : 'bg-danger'} badge-custom me-2">
                            ${product.available == 1 ? 'Mevcut' : 'Tükendi'}
                        </span>
                        <i class="fas fa-edit edit-btn me-2" onclick="editProduct(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, ${product.available}, ${product.category_id}, ${product.order_index || 0})"></i>
                        <i class="fas fa-trash delete-btn" onclick="deleteProduct(${product.id})"></i>
                    </div>
                `;
                
                productList.appendChild(productItem);
            });
            
            productsContainer.appendChild(productList);
            
            // Bu kategori için Sortable'ı başlat
            initProductSortable(categoryId);
        })
        .catch(error => {
            console.error('Error loading products:', error);
            document.getElementById(`products-${categoryId}`).innerHTML = 
                '<p class="text-danger">Ürünler yüklenirken hata oluştu.</p>';
        });
}
// editProduct fonksiyonunu güncelleyelim
function editProduct(id, name, price, available, categoryId, orderIndex) {
    currentEditId = id;
    currentEditType = 'product';
    
    // ... mevcut kod (ürün içeriğini alan kısım) ...
    
    let categoryOptions = '';
    categories.forEach(category => {
        categoryOptions += `<option value="${category.id}" ${category.id == categoryId ? 'selected' : ''}>${category.name}</option>`;
    });
    
    document.getElementById('editModalTitle').textContent = 'Ürün Düzenle';
    document.getElementById('editModalBody').innerHTML = `
        <div class="mb-3">
            <label for="editProductCategory" class="form-label">Kategori</label>
            <select class="form-select" id="editProductCategory">
                ${categoryOptions}
            </select>
        </div>
        <div class="mb-3">
            <label for="editProductName" class="form-label">Ürün Adı</label>
            <input type="text" class="form-control" id="editProductName" value="${name}">
        </div>
        <div class="mb-3">
            <label for="editProductPrice" class="form-label">Fiyat (£)</label>
            <input type="number" step="0.01" class="form-control" id="editProductPrice" value="${price}">
        </div>
        <div class="mb-3">
            <label for="editProductContent" class="form-label">İçerik (Opsiyonel)</label>
            <textarea class="form-control" id="editProductContent" rows="3">${productContent}</textarea>
        </div>
        <div class="mb-3">
            <label for="editProductOrder" class="form-label">Sıra Numarası</label>
            <input type="number" class="form-control" id="editProductOrder" value="${orderIndex || 0}" min="0">
            <div class="form-text">Düşük numara daha önce gösterilir</div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="editProductAvailable" ${available ? 'checked' : ''}>
            <label class="form-check-label" for="editProductAvailable">Mevcut</label>
        </div>
    `;
    
    // ... mevcut kod (modal'ı göster) ...
}
// Ürün sırasını güncelle
function updateProductOrder(categoryId) {
    const productList = document.getElementById(`product-list-${categoryId}`);
    const productItems = productList.querySelectorAll('li[data-product-id]');
    const productOrders = [];
    
    productItems.forEach((item, index) => {
        const productId = item.getAttribute('data-product-id');
        const orderIndex = index + 1;
        
        productOrders.push({
            id: productId,
            order_index: orderIndex
        });
    });
    
    // Sunucuya yeni sırayı gönder
    const formData = new FormData();
    formData.append('product_orders', JSON.stringify(productOrders));
    
    fetch('db.php?action=update_product_order', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Ürün sıralaması güncellendi');
        } else {
            alert('Ürün sıralaması güncellenirken hata oluştu: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Ürün sıralaması güncellenirken hata oluştu:', error);
    });
}
// Ürünler için Sortable başlatma fonksiyonu
function initProductSortable(categoryId) {
    const productList = document.getElementById(`product-list-${categoryId}`);
    if (productList) {
        new Sortable(productList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function(evt) {
                updateProductOrder(categoryId);
            }
        });
    }
}
// Kategori ekle
function addCategory() {
    const categoryName = document.getElementById('categoryName').value.trim();
    
    if (!categoryName) {
        alert('Lütfen bir kategori adı girin.');
        return;
    }
    
    // FormData oluştur
    const formData = new FormData();
    formData.append('name', categoryName);
    
    // AJAX isteği gönder
    fetch('db.php?action=add_category', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Kategori başarıyla eklendi.');
            document.getElementById('categoryName').value = '';
            loadCategories(); // Kategorileri yeniden yükle
        } else {
            alert('Kategori eklenirken hata oluştu: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Kategori eklenirken hata oluştu:', error);
        alert('Kategori eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
    });
}      // Ürün ekle
        function addProduct() {
            const categoryId = parseInt(document.getElementById('productCategory').value);
            const productName = document.getElementById('productName').value.trim();
            const productPrice = parseFloat(document.getElementById('productPrice').value);
            const productAvailable = document.getElementById('productAvailable').checked;
            const productContent = document.getElementById('productContent').value.trim();
            
            if (!productName || isNaN(productPrice) || productPrice <= 0) {
                alert('Lütfen geçerli bir ürün adı ve fiyat girin.');
                return;
            }
            
            if (isNaN(categoryId) || categoryId <= 0) {
                alert('Lütfen geçerli bir kategori seçin.');
                return;
            }
            
            // FormData oluştur
            const formData = new FormData();
            formData.append('category_id', categoryId);
            formData.append('name', productName);
            formData.append('price', productPrice);
            formData.append('available', productAvailable);
            formData.append('icerik', productContent);
            
            // AJAX isteği gönder
            fetch('db.php?action=add_product', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ürün başarıyla eklendi.');
                    
                    // Formu temizle
                    document.getElementById('productName').value = '';
                    document.getElementById('productPrice').value = '';
                    document.getElementById('productContent').value = '';
                    document.getElementById('productAvailable').checked = true;
                    
                    // İlgili kategorideki ürünleri yeniden yükle
                    loadCategoryProducts(categoryId);
                } else {
                    alert('Ürün eklenirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Ürün eklenirken hata oluştu:', error);
                alert('Ürün eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }
// Toplu ürün ekle
function addBulkProducts() {
    const bulkData = document.getElementById('bulkData').value.trim();
    const productAvailable = document.getElementById('bulkProductAvailable').checked ? 1 : 0;
    const skipExistingCategories = document.getElementById('skipExistingCategories').checked;
    
    if (!bulkData) {
        alert('Lütfen toplu ekleme için veri girin.');
        return;
    }
    
    // Önce tüm kategorileri yükle
    fetch('db.php?action=get_categories')
        .then(response => response.json())
        .then(existingCategories => {
            const lines = bulkData.split('\n');
            let currentCategoryId = null;
            let currentCategoryName = null;
            let processedCount = 0;
            let errorCount = 0;
            let addedCategories = 0;
            let addedProducts = 0;
            let skippedCategories = 0;
            let errorMessages = []; // Hata mesajlarını saklamak için dizi
            
            // İşlem sırasında kullanıcıyı bilgilendir
            alert('Toplu ürün ekleme işlemi başlatılıyor. Bu işlem biraz zaman alabilir.');
            
            // Her satırı işle
            processLinesSequentially(lines, 0);
            
            function processLinesSequentially(lines, index) {
                if (index >= lines.length) {
                    // Tüm satırlar işlendi
                    let resultMessage = `Toplu ürün ekleme tamamlandı.\nEklenen Kategoriler: ${addedCategories}\nAtlanan Kategoriler: ${skippedCategories}\nEklenen Ürünler: ${addedProducts}\nToplam Hata: ${errorCount}`;
                    
                    // Hata mesajları varsa ekle
                    if (errorMessages.length > 0) {
                        resultMessage += "\n\nHata Detayları:\n" + errorMessages.join('\n');
                    }
                    
                    alert(resultMessage);
                    loadCategories(); // Menüyü yeniden yükle
                    document.getElementById('bulkData').value = ''; // Textarea'yı temizle
                    return;
                }
                
                const line = lines[index].trim();
                const lineNumber = index + 1; // Satır numarası (1'den başlar)
                
                if (!line) {
                    // Boş satır, bir sonrakine geç
                    processLinesSequentially(lines, index + 1);
                    return;
                }
                
                // Kategori satırı mı kontrol et (-- ile başlıyorsa)
                if (line.startsWith('--')) {
                    const categoryName = line.substring(2).trim();
                    
                    if (categoryName) {
                        currentCategoryName = categoryName;
                        
                        // Bu kategori zaten var mı kontrol et
                        const existingCategory = existingCategories.find(cat => cat.name.toLowerCase() === categoryName.toLowerCase());
                        
                        if (existingCategory) {
                            // Kategori zaten varsa
                            if (skipExistingCategories) {
                                // Mevcut kategorileri atla seçeneği işaretliyse
                                currentCategoryId = existingCategory.id;
                                skippedCategories++;
                                errorMessages.push(`Satır ${lineNumber}: "${categoryName}" kategorisi zaten mevcut, atlandı`);
                                processLinesSequentially(lines, index + 1);
                            } else {
                                // Mevcut kategorileri atla seçeneği işaretli değilse yine de mevcut kategoriyi kullan
                                currentCategoryId = existingCategory.id;
                                processLinesSequentially(lines, index + 1);
                            }
                        } else {
                            // Kategori yoksa, yeni kategori ekle
                            const formData = new FormData();
                            formData.append('name', categoryName);
                            formData.append('order_index', 0); // Varsayılan sıra numarası
                            
                            fetch('db.php?action=add_category', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    processedCount++;
                                    addedCategories++;
                                    // Yeni eklenen kategoriyi bul
                                    fetch('db.php?action=get_categories')
                                        .then(response => response.json())
                                        .then(cats => {
                                            const newCategory = cats.find(cat => cat.name === categoryName);
                                            if (newCategory) {
                                                currentCategoryId = newCategory.id;
                                                // Mevcut kategoriler listesini güncelle
                                                existingCategories.push(newCategory);
                                            }
                                            processLinesSequentially(lines, index + 1);
                                        });
                                } else {
                                    errorCount++;
                                    errorMessages.push(`Satır ${lineNumber}: "${categoryName}" kategorisi eklenemedi - ${data.error}`);
                                    processLinesSequentially(lines, index + 1);
                                }
                            })
                            .catch(error => {
                                console.error('Kategori eklenirken hata oluştu:', error);
                                errorCount++;
                                errorMessages.push(`Satır ${lineNumber}: "${categoryName}" kategorisi eklenirken hata oluştu`);
                                processLinesSequentially(lines, index + 1);
                            });
                        }
                    } else {
                        errorMessages.push(`Satır ${lineNumber}: Geçersiz kategori formatı`);
                        processLinesSequentially(lines, index + 1);
                    }
                } else if (currentCategoryId && currentCategoryName) {
                    // Ürün satırı - "ürün adı fiyat -İçerik" formatında
                    // GÜNCELLENMİŞ KISIM: Fiyatı bulmak için daha akıllı yöntem
                    const dashIndex = line.indexOf('-');
                    
                    if (dashIndex === -1) {
                        // Eğer "-" yoksa, son kelime fiyat olmalı
                        const parts = line.split(' ');
                        if (parts.length < 2) {
                            errorCount++;
                            errorMessages.push(`Satır ${lineNumber}: "${line}" - Geçersiz ürün formatı (en az 2 kelime gerekli)`);
                            processLinesSequentially(lines, index + 1);
                            return;
                        }
                        
                        const lastPart = parts[parts.length - 1];
                        const price = parseFloat(lastPart);
                        
                        if (isNaN(price)) {
                            errorCount++;
                            errorMessages.push(`Satır ${lineNumber}: "${line}" - Geçersiz fiyat formatı`);
                            processLinesSequentially(lines, index + 1);
                            return;
                        }
                        
                        const productName = parts.slice(0, parts.length - 1).join(' ').trim();
                        const content = '';
                        
                        addProductToCategory(productName, price, content, lineNumber);
                    } else {
                        // "-" varsa, fiyat ondan önceki son sayı olmalı
                        const beforeDash = line.substring(0, dashIndex).trim();
                        const afterDash = line.substring(dashIndex + 1).trim();
                        
                        const parts = beforeDash.split(' ');
                        if (parts.length < 2) {
                            errorCount++;
                            errorMessages.push(`Satır ${lineNumber}: "${line}" - Geçersiz ürün formatı (en az 2 kelime gerekli)`);
                            processLinesSequentially(lines, index + 1);
                            return;
                        }
                        
                        // Son kısımdaki fiyatı bul
                        let price = 0;
                        let priceFound = false;
                        let productNameParts = [];
                        
                        for (let i = parts.length - 1; i >= 0; i--) {
                            const part = parts[i];
                            const potentialPrice = parseFloat(part);
                            
                            if (!isNaN(potentialPrice) && !priceFound) {
                                price = potentialPrice;
                                priceFound = true;
                            } else {
                                productNameParts.unshift(part);
                            }
                        }
                        
                        if (!priceFound) {
                            errorCount++;
                            errorMessages.push(`Satır ${lineNumber}: "${line}" - Fiyat bulunamadı`);
                            processLinesSequentially(lines, index + 1);
                            return;
                        }
                        
                        const productName = productNameParts.join(' ').trim();
                        const content = afterDash;
                        
                        addProductToCategory(productName, price, content, lineNumber);
                    }
                } else {
                    // Kategori tanımlanmadan ürün eklenmeye çalışılıyor
                    errorCount++;
                    errorMessages.push(`Satır ${lineNumber}: "${line}" - Önce bir kategori tanımlanmalı (--KategoriAdı)`);
                    processLinesSequentially(lines, index + 1);
                }
                
                function addProductToCategory(productName, price, content, lineNumber) {
                    if (!productName) {
                        errorCount++;
                        errorMessages.push(`Satır ${lineNumber}: Ürün adı belirtilmemiş`);
                        processLinesSequentially(lines, index + 1);
                        return;
                    }
                    
                    // Ürünü ekle
                    const formData = new FormData();
                    formData.append('category_id', currentCategoryId);
                    formData.append('name', productName);
                    formData.append('price', price);
                    formData.append('available', productAvailable);
                    formData.append('icerik', content);
                    
                    fetch('db.php?action=add_product', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            processedCount++;
                            addedProducts++;
                        } else {
                            errorCount++;
                            errorMessages.push(`Satır ${lineNumber}: "${productName}" eklenemedi - ${data.error}`);
                        }
                        processLinesSequentially(lines, index + 1);
                    })
                    .catch(error => {
                        console.error('Ürün eklenirken hata oluştu:', error);
                        errorCount++;
                        errorMessages.push(`Satır ${lineNumber}: "${productName}" eklenirken hata oluştu`);
                        processLinesSequentially(lines, index + 1);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Kategoriler yüklenirken hata oluştu:', error);
            alert('Kategoriler yüklenirken bir hata oluştu. Lütfen tekrar deneyin.');
        });
}
 // Kategori düzenleme modalını aç
        function editCategory(id, name, orderIndex) {
            currentEditId = id;
            currentEditType = 'category';
            
            document.getElementById('editModalTitle').textContent = 'Kategori Düzenle';
            document.getElementById('editModalBody').innerHTML = `
                <div class="mb-3">
                    <label for="editCategoryName" class="form-label">Kategori Adı</label>
                    <input type="text" class="form-control" id="editCategoryName" value="${name}">
                </div>
                <div class="mb-3">
                    <label for="editCategoryOrder" class="form-label">Sıra Numarası</label>
                    <input type="number" class="form-control" id="editCategoryOrder" value="${orderIndex}" min="0">
                    <div class="form-text">Düşük numara daha önce gösterilir</div>
                </div>
            `;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
            
            // Remove aria-hidden when modal is shown to fix accessibility issue
            document.getElementById('editModal').removeAttribute('aria-hidden');
        }
        // Ürün düzenleme modalını aç
function editProduct(id, name, price, available, categoryId) {
    currentEditId = id;
    currentEditType = 'product';
    
    // Önce ürünün içeriğini almak için AJAX isteği yap
    fetch(`db.php?action=get_product_content&product_id=${id}`)
        .then(response => response.json())
        .then(data => {
            let productContent = data.content || '';
            
            let categoryOptions = '';
            categories.forEach(category => {
                categoryOptions += `<option value="${category.id}" ${category.id == categoryId ? 'selected' : ''}>${category.name}</option>`;
            });
            
            document.getElementById('editModalTitle').textContent = 'Ürün Düzenle';
            document.getElementById('editModalBody').innerHTML = `
                <div class="mb-3">
                    <label for="editProductCategory" class="form-label">Kategori</label>
                    <select class="form-select" id="editProductCategory">
                        ${categoryOptions}
                    </select>
                </div>
                <div class="mb-3">
                    <label for="editProductName" class="form-label">Ürün Adı</label>
                    <input type="text" class="form-control" id="editProductName" value="${name}">
                </div>
                <div class="mb-3">
                    <label for="editProductPrice" class="form-label">Fiyat (£)</label>
                    <input type="number" step="0.01" class="form-control" id="editProductPrice" value="${price}">
                </div>
                <div class="mb-3">
                    <label for="editProductContent" class="form-label">İçerik (Opsiyonel)</label>
                    <textarea class="form-control" id="editProductContent" rows="3">${productContent}</textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="editProductAvailable" ${available ? 'checked' : ''}>
                    <label class="form-check-label" for="editProductAvailable">Mevcut</label>
                </div>
            `;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Ürün içeriği yüklenirken hata oluştu:', error);
        });
} 
 // Değişiklikleri kaydet
 function saveChanges() {
    if (currentEditType === 'product') {
        const categoryId = parseInt(document.getElementById('editProductCategory').value);
        const name = document.getElementById('editProductName').value.trim();
        const price = parseFloat(document.getElementById('editProductPrice').value);
        const available = document.getElementById('editProductAvailable').checked;
        const icerik = document.getElementById('editProductContent').value.trim(); // İçerik alanını ekle
        
        if (!name || isNaN(price) || price <= 0) {
            alert('Lütfen geçerli bir ürün adı ve fiyat girin.');
            return;
        }
        
        if (isNaN(categoryId) || categoryId <= 0) {
            alert('Lütfen geçerli bir kategori seçin.');
            return;
        }
        
        const formData = new FormData();
        formData.append('product_id', currentEditId);
        formData.append('category_id', categoryId);
        formData.append('name', name);
        formData.append('price', price);
        formData.append('available', available);
        formData.append('icerik', icerik); // İçerik alanını ekle
        
        fetch('db.php?action=update_product', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ürün başarıyla güncellendi.');
                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                loadCategoryProducts(categoryId);
            } else {
                alert('Ürün güncellenirken hata oluştu: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Ürün güncellenirken hata oluştu:', error);
            alert('Ürün güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
        });
    }

}
        // Kategori silme
        function deleteCategory(id) {
            if (!confirm('Bu kategoriyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('category_id', id);
            
            fetch('db.php?action=delete_category', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Kategori başarıyla silindi.');
                    loadCategories();
                } else {
                    alert('Kategori silinirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Kategori silinirken hata oluştu:', error);
                alert('Kategori silinirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }
        // Ürün silme
        function deleteProduct(id) {
            if (!confirm('Bu ürünü silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('product_id', id);
            
            fetch('db.php?action=delete_product', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ürün başarıyla silindi.');
                    // Sayfayı yeniden yükle
                    location.reload();
                } else {
                    alert('Ürün silinirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Ürün silinirken hata oluştu:', error);
                alert('Ürün silinirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }
        // Tüm kategorileri silme onayı
        function confirmDeleteAllCategories() {
            if (!confirm('TÜM kategorileri silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve tüm ürünler de silinecektir!')) {
                return;
            }
            
            if (confirm('BU İŞLEM TÜM VERİLERİ SİLECEK. SON BİR KEZ ONAKLIYOR MUSUNUZ?')) {
                deleteAllCategories();
            }
        }
        // Tüm kategorileri sil
        function deleteAllCategories() {
            fetch('db.php?action=delete_all_categories', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tüm kategoriler ve ürünler başarıyla silindi.');
                    loadCategories();
                } else {
                    alert('Kategoriler silinirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Kategoriler silinirken hata oluştu:', error);
                alert('Kategoriler silinirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }
        // Tüm ürünleri silme onayı
        function confirmDeleteAllProducts() {
            if (!confirm('TÜM ürünleri silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
                return;
            }
            
            if (confirm('BU İŞLEM TÜM ÜRÜNLERİ SİLECEK. SON BİR KEZ ONAKLIYOR MUSUNUZ?')) {
                deleteAllProducts();
            }
        }
        // Tüm ürünleri sil
        function deleteAllProducts() {
            fetch('db.php?action=delete_all_products', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tüm ürünler başarıyla silindi.');
                    // Tüm kategorileri yeniden yükle (ürün listelerini güncellemek için)
                    categories.forEach(category => {
                        loadCategoryProducts(category.id);
                    });
                } else {
                    alert('Ürünler silinirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Ürünler silinirken hata oluştu:', error);
                alert('Ürünler silinirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }
    </script>
</body>
</html>
