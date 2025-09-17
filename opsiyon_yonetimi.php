<?php
// opsiyon_yonetimi.php - Opsiyon yönetimi
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
     <title>Restaurant Sipariş Sistemi - Opsiyon Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            /* Changed header color from green to red to match menu management */
            background-color: #dc3545;
            color: white;
            padding: 15px 0;
            text-align: center;
            margin-bottom: 20px;
        }
        /* Added new styles to match menu management interface */
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
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-responsive {
            overflow-x: auto;
        }
        .badge {
            font-size: 0.85em;
        }
        .option-group-item {
            position: relative;
        }
        .option-item {
            position: relative;
        }
        .option-delete-btn {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
            color: #dc3545;
        }
        .product-option-item {
            position: relative;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .product-option-delete {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Opsiyon Yönetimi</h1>
                <!-- Updated navigation buttons to match menu management style -->
                <div>
                    <a href="menu_yonetimi.php" class="btn btn-primary">
                        <i class="fas fa-utensils me-2"></i>Menü Yönetimi
                    </a>
                    <a href="extra_yonetici.php" class="btn btn-primary">
                        <i class="fas fa-cog me-2"></i>Extra / Swaps 
                    </a>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-home me-2"></i>Masalara Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <!-- Updated card headers to match menu management style with delete all button -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Opsiyon Grubu Ekle</h5>
                        <button class="btn btn-sm btn-danger" id="deleteAllOptionGroups">
                            <i class="fas fa-trash me-1"></i>Tüm Opsiyon Gruplarını Sil
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="optionGroupName" class="form-label">Opsiyon Grubu Adı</label>
                            <input type="text" class="form-control" id="optionGroupName" placeholder="Opsiyon grubu adını girin">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="isRequired" checked>
                            <label class="form-check-label" for="isRequired">Zorunlu mu?</label>
                        </div>
                        <div class="mb-3">
                            <label for="minSelection" class="form-label">Minimum Seçim</label>
                            <input type="number" class="form-control" id="minSelection" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="maxSelection" class="form-label">Maksimum Seçim</label>
                            <input type="number" class="form-control" id="maxSelection" value="5" min="1">
                        </div>
                        <button class="btn btn-primary" id="addOptionGroup">
                            <i class="fas fa-plus me-1"></i>Opsiyon Grubu Ekle
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Opsiyon Ekle</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="optionGroup" class="form-label">Opsiyon Grubu</label>
                            <select class="form-select" id="optionGroup">
                                <option value="">Lütfen bir opsiyon grubu seçin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="optionName" class="form-label">Opsiyon Adı</label>
                            <input type="text" class="form-control" id="optionName" placeholder="Opsiyon adını girin">
                        </div>
                        <div class="mb-3">
                            <!-- Changed currency from TL to £ in label -->
                            <label for="optionPrice" class="form-label">Ek Fiyat (£)</label>
                            <input type="number" step="0.01" class="form-control" id="optionPrice" value="0" min="0">
                        </div>
                        <button class="btn btn-primary" id="addOption">
                            <i class="fas fa-plus me-1"></i>Opsiyon Ekle
                        </button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Ürünlere Opsiyon Grubu Ekle</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="productForOption" class="form-label">Ürünler</label>
                            <select class="form-select" id="productForOption" multiple size="5">
                                <option value="">Lütfen bir veya daha fazla ürün seçin</option>
                            </select>
                            <div class="form-text">Çoklu seçim için Ctrl (Windows) veya Command (Mac) tuşuna basılı tutun.</div>
                        </div>
                        <div class="mb-3">
                            <label for="optionGroupForProduct" class="form-label">Opsiyon Grubu</label>
                            <select class="form-select" id="optionGroupForProduct" multiple size="5">
                            </select>
                            <div class="form-text">Çoklu seçim için Ctrl (Windows) veya Command (Mac) tuşuna basılı tutun.</div>
                        </div>
                        <button class="btn btn-primary" id="addOptionToProduct">
                            <i class="fas fa-link me-1"></i>Seçili Ürünlere Opsiyon Ekle
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Kategoriye Opsiyon Grubu Ekle</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="categoryForOption" class="form-label">Kategori Seçin</label>
                            <select class="form-select" id="categoryForOption">
                                <option value="">Lütfen bir kategori seçin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="optionGroupForCategory" class="form-label">Opsiyon Grubu</label>
                            <select class="form-select" id="optionGroupForCategory" multiple size="5">
                            </select>
                            <div class="form-text">Çoklu seçim için Ctrl (Windows) veya Command (Mac) tuşuna basılı tutun.</div>
                        </div>
                        <button class="btn btn-primary" id="addOptionToCategory">
                            <i class="fas fa-link me-1"></i>Kategoriye Opsiyon Ekle
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Mevcut Opsiyon Grupları</h5>
                        <button class="btn btn-sm btn-light" id="refreshButton">
                            <i class="fas fa-sync-alt"></i> Yenile
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="optionGroups">
                            <p class="text-center">Opsiyon grupları yükleniyor...</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Ürünlere Atanmış Opsiyonlar</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="viewProductOptions" class="form-label">Ürün Seçin</label>
                            <select class="form-select" id="viewProductOptions">
                                <option value="">Lütfen bir ürün seçin</option>
                            </select>
                        </div>
                        <div id="productOptionsList">
                            <p class="text-center">Lütfen bir ürün seçin</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Kategorilere Atanmış Opsiyonlar</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="viewCategoryOptions" class="form-label">Kategori Seçin</label>
                            <select class="form-select" id="viewCategoryOptions">
                                <option value="">Lütfen bir kategori seçin</option>
                            </select>
                        </div>
                        <div id="categoryOptionsList">
                            <p class="text-center">Lütfen bir kategori seçin</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Added edit modal to match menu management functionality -->
    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Modal content will be added dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="saveChanges">Değişiklikleri Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Added confirmation modal for delete operations -->
    <!-- Silme Onay Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Silme Onayı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteMessage">Bu işlemi gerçekleştirmek istediğinizden emin misiniz?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Sil</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global değişkenler
        let optionGroups = [];
        let products = [];
        let categories = [];
        let currentDeleteType = null;
        let currentDeleteId = null;
        let currentDeleteProductId = null;
        let currentDeleteGroupId = null;
        let currentDeleteCategoryId = null;
        let currentEditId = null;
        let currentEditType = null;
        
        // Debounce fonksiyonu
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadOptionGroups();
            loadProducts();
            loadCategories();
            
            // Event listener'ları bir kere ekle
            document.getElementById('addOptionGroup').addEventListener('click', addOptionGroup);
            document.getElementById('addOption').addEventListener('click', addOption);
            document.getElementById('addOptionToProduct').addEventListener('click', addOptionToProduct);
            document.getElementById('addOptionToCategory').addEventListener('click', addOptionToCategory);
            document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
            document.getElementById('deleteAllOptionGroups').addEventListener('click', confirmDeleteAllOptionGroups);
            document.getElementById('saveChanges').addEventListener('click', saveChanges);
            
            // Debounce edilmiş refresh fonksiyonu
            const debouncedLoadOptionGroups = debounce(loadOptionGroups, 300);
            document.getElementById('refreshButton').addEventListener('click', debouncedLoadOptionGroups);
            
            // Ürün seçimini dinle
            document.getElementById('viewProductOptions').addEventListener('change', function() {
                const productId = this.value;
                if (productId) {
                    loadProductOptions(productId);
                } else {
                    document.getElementById('productOptionsList').innerHTML = '<p class="text-center">Lütfen bir ürün seçin</p>';
                }
            });
            
            // Kategori seçimini dinle
            document.getElementById('viewCategoryOptions').addEventListener('change', function() {
                const categoryId = this.value;
                if (categoryId) {
                    loadCategoryOptions(categoryId);
                } else {
                    document.getElementById('categoryOptionsList').innerHTML = '<p class="text-center">Lütfen bir kategori seçin</p>';
                }
            });
        });

        // Opsiyon gruplarını yükle
        function loadOptionGroups() {
            fetch('db.php?action=get_option_groups')
                .then(async (response) => {
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP ${response.status} – ${text?.slice(0,200)}`);
                }
                const text = await response.text();
                try {
                    return text ? JSON.parse(text) : { success: false, error: 'Boş yanıt' };
                } catch (e) {
                    throw new Error('Geçersiz JSON: ' + text.slice(0,200));
                }
            })
                .then(data => {
                    optionGroups = data;
                    updateOptionGroupDropdowns();
                    /* Updated renderOptionGroups function to use modern card layout with edit buttons */
                    renderOptionGroups();
                })
                .catch(error => {
                    console.error('Opsiyon grupları yüklenirken hata oluştu:', error);
                    alert('Opsiyon grupları yüklenirken bir hata oluştu. Lütfen sayfayı yenileyin.');
                });
        }

        // Ürünleri yükle
        function loadProducts() {
            fetch('db.php?action=get_all_products')
                .then(response => response.json())
                .then(data => {
                    products = data;
                    updateProductDropdown();
                    updateViewProductDropdown();
                })
                .catch(error => {
                    console.error('Ürünler yüklenirken hata oluştu:', error);
                    alert('Ürünler yüklenirken bir hata oluştu. Lütfen sayfayı yenileyin.');
                });
        }

        // Kategorileri yükle
        function loadCategories() {
            fetch('db.php?action=get_categories')
                .then(response => response.json())
                .then(data => {
                    categories = data;
                    updateCategoryDropdowns();
                })
                .catch(error => {
                    console.error('Kategoriler yüklenirken hata oluştu:', error);
                    alert('Kategoriler yüklenirken bir hata oluştu. Lütfen sayfayı yenileyin.');
                });
        }

        // Opsiyon grup dropdown'larını güncelle
        function updateOptionGroupDropdowns() {
            const optionGroup = document.getElementById('optionGroup');
            const optionGroupForProduct = document.getElementById('optionGroupForProduct');
            const optionGroupForCategory = document.getElementById('optionGroupForCategory');
            
            // İlk seçenekleri koru, diğerlerini temizle
            while (optionGroup.options.length > 1) {
                optionGroup.remove(1);
            }
            
            optionGroupForProduct.innerHTML = '';
            optionGroupForCategory.innerHTML = '';
            
            optionGroups.forEach(group => {
                // Tekli seçim dropdown'ı
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                optionGroup.appendChild(option);
                
                // Çoklu seçim dropdown'ı (ürünler için)
                const optionMulti = document.createElement('option');
                optionMulti.value = group.id;
                optionMulti.textContent = group.name;
                optionGroupForProduct.appendChild(optionMulti);
                
                // Çoklu seçim dropdown'ı (kategoriler için)
                const optionMultiCategory = document.createElement('option');
                optionMultiCategory.value = group.id;
                optionMultiCategory.textContent = group.name;
                optionGroupForCategory.appendChild(optionMultiCategory);
            });
        }

        // Ürün dropdown'ını güncelle (çoklu seçim için)
        function updateProductDropdown() {
            const productForOption = document.getElementById('productForOption');
            
            // İlk seçeneği koru, diğerlerini temizle
            while (productForOption.options.length > 1) {
                productForOption.remove(1);
            }
            
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.name + ' (' + product.category_name + ')';
                productForOption.appendChild(option);
            });
        }
        
        // Ürün görüntüleme dropdown'ını güncelle
        function updateViewProductDropdown() {
            const viewProductOptions = document.getElementById('viewProductOptions');
            
            if (!viewProductOptions) return;
            
            // İlk seçeneği koru, diğerlerini temizle
            while (viewProductOptions.options.length > 1) {
                viewProductOptions.remove(1);
            }
            
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.name + ' (' + product.category_name + ')';
                viewProductOptions.appendChild(option);
            });
        }

        // Kategori dropdown'larını güncelle
        function updateCategoryDropdowns() {
            const categoryForOption = document.getElementById('categoryForOption');
            const viewCategoryOptions = document.getElementById('viewCategoryOptions');
            
            // İlk seçenekleri koru, diğerlerini temizle
            while (categoryForOption.options.length > 1) {
                categoryForOption.remove(1);
            }
            
            if (viewCategoryOptions) {
                while (viewCategoryOptions.options.length > 1) {
                    viewCategoryOptions.remove(1);
                }
            }
            
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryForOption.appendChild(option);
                
                if (viewCategoryOptions) {
                    const viewOption = document.createElement('option');
                    viewOption.value = category.id;
                    viewOption.textContent = category.name;
                    viewCategoryOptions.appendChild(viewOption);
                }
            });
        }

        /* Updated renderOptionGroups function to use modern card layout with edit buttons */
        // Opsiyon gruplarını görüntüle
        function renderOptionGroups() {
            const optionGroupsContainer = document.getElementById('optionGroups');
            optionGroupsContainer.innerHTML = '';
            
            if (optionGroups.length === 0) {
                optionGroupsContainer.innerHTML = '<p class="text-center">Henüz hiç opsiyon grubu eklenmemiş.</p>';
                return;
            }
            
            optionGroups.forEach(group => {
                const groupCard = document.createElement('div');
                groupCard.className = 'card mb-3';
                groupCard.innerHTML = `
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6>${group.name}</h6>
                        <div>
                            <i class="fas fa-edit edit-btn me-2" onclick="editOptionGroup(${group.id}, '${group.name.replace(/'/g, "\\'")}', ${group.is_required}, ${group.min_selection}, ${group.max_selection})"></i>
                            <i class="fas fa-trash delete-btn" onclick="showDeleteConfirm('group', ${group.id})"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">
                                <span class="badge ${(group.is_required == 1 || group.is_required == '1') ? 'bg-danger' : 'bg-secondary'} badge-custom me-1">
                                    ${(group.is_required == 1 || group.is_required == '1') ? 'Zorunlu' : 'İsteğe Bağlı'}
                                </span>
                                Min: ${group.min_selection} | Max: ${group.max_selection}
                            </small>
                        </div>
                        <div id="options-${group.id}">
                            <p class="text-muted">Opsiyonlar yükleniyor...</p>
                        </div>
                    </div>
                `;
                
                optionGroupsContainer.appendChild(groupCard);
                
                // Bu gruba ait opsiyonları yükle
                loadGroupOptions(group.id);
            });
        }

        /* Updated loadGroupOptions to use modern list layout with edit buttons */
        // Grup opsiyonlarını yükle
        function loadGroupOptions(groupId) {
            fetch(`db.php?action=get_options&group_id=${groupId}`)
                .then(response => response.json())
                .then(data => {
                    const optionsContainer = document.getElementById(`options-${groupId}`);
                    optionsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        optionsContainer.innerHTML = '<p class="text-muted">Bu grupta henüz opsiyon yok.</p>';
                        return;
                    }
                    
                    const optionList = document.createElement('ul');
                    optionList.className = 'list-group';
                    
                    data.forEach(option => {
                        const optionItem = document.createElement('li');
                        optionItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                        optionItem.innerHTML = `
                            <div>
                                <h6 class="mb-1" id="option-name-${option.id}">${option.name}</h6>
                                <small class="text-muted" id="option-price-${option.id}">£${parseFloat(option.price).toFixed(2)}</small>
                            </div>
                            <div>
                                <i class="fas fa-edit edit-btn me-2" onclick="editOption(${option.id}, '${option.name.replace(/'/g, "\\'")}', ${option.price})"></i>
                                <i class="fas fa-trash delete-btn" onclick="showDeleteConfirm('option', ${option.id})"></i>
                            </div>
                        `;
                        
                        optionList.appendChild(optionItem);
                    });
                    
                    optionsContainer.appendChild(optionList);
                })
                .catch(error => {
                    console.error('Opsiyonlar yüklenirken hata oluştu:', error);
                    document.getElementById(`options-${groupId}`).innerHTML = 
                        '<p class="text-danger">Opsiyonlar yüklenirken hata oluştu.</p>';
                });
        }
        
 // Ürüne ait opsiyonları yükle
function loadProductOptions(productId) {
    fetch(`db.php?action=get_product_options&product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            const productOptionsContainer = document.getElementById('productOptionsList');
            if (!productOptionsContainer) return;
            
            // Container'ı tamamen temizle
            productOptionsContainer.innerHTML = '';
            
            if (!Array.isArray(data) || data.length === 0) {
                productOptionsContainer.innerHTML = '<p class="text-center text-muted">Bu ürüne ait opsiyon grubu bulunmamaktadır.</p>';
                return;
            }
            
            // Benzersiz grupları takip etmek için Map kullan
            const uniqueGroups = new Map();
            
            data.forEach(group => {
                // Grup zaten işlendiyse atla
                if (uniqueGroups.has(group.id)) {
                    console.log('Çift grup atlandı:', group.id, group.name);
                    return;
                }
                uniqueGroups.set(group.id, group);
                
                const groupItem = document.createElement('div');
                groupItem.className = 'product-option-item';
                groupItem.innerHTML = `
                    <h6>${group.name}</h6>
                    <small class="text-muted">${(group.is_required == 1 || group.is_required == '1') ? 'Zorunlu' : 'İsteğe Bağlı'} | Min: ${group.min_selection}, Max: ${group.max_selection}</small>
                    <span class="product-option-delete" onclick="deleteProductOption(${productId}, ${group.id})">
                        <i class="fas fa-times"></i>
                    </span>
                    <ul class="mt-2">
                        ${group.options && Array.isArray(group.options) ? group.options.map(option => {
                            const price = parseFloat(option.price) || 0;
                            // Changed currency from TL to £ in option list display
                            return `<li>${option.name} (+${price.toFixed(2)} £)</li>`;
                        }).join('') : ''}
                    </ul>
                `;
                
                productOptionsContainer.appendChild(groupItem);
            });
        })
        .catch(error => {
            console.error('Ürün opsiyonları yüklenirken hata oluştu:', error);
            const productOptionsContainer = document.getElementById('productOptionsList');
            if (productOptionsContainer) {
                productOptionsContainer.innerHTML = '<p class="text-center text-danger">Ürün opsiyonları yüklenirken hata oluştu.</p>';
            }
        });
}
// Kategoriye ait opsiyonları yükle
function loadCategoryOptions(categoryId) {
    fetch(`db.php?action=get_category_options&category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            const categoryOptionsContainer = document.getElementById('categoryOptionsList');
            if (!categoryOptionsContainer) return;
            
            // Container'ı tamamen temizle
            categoryOptionsContainer.innerHTML = '';
            
            if (!Array.isArray(data) || data.length === 0) {
                categoryOptionsContainer.innerHTML = '<p class="text-center text-muted">Bu kategoriye ait opsiyon grubu bulunmamaktadır.</p>';
                return;
            }
            
            // Benzersiz grupları takip etmek için Map kullan
            const uniqueGroups = new Map();
            
            data.forEach(group => {
                // Grup zaten işlendiyse atla
                if (uniqueGroups.has(group.id)) {
                    console.log('Çift grup atlandı:', group.id, group.name);
                    return;
                }
                uniqueGroups.set(group.id, group);
                
                const groupItem = document.createElement('div');
                groupItem.className = 'product-option-item';
                groupItem.innerHTML = `
                    <h6>${group.name}</h6>
                    <small class="text-muted">${group.is_required ? 'Zorunlu' : 'Opsiyonel'} | Min: ${group.min_selection}, Max: ${group.max_selection}</small>
                    <span class="product-option-delete" onclick="deleteCategoryOption(${categoryId}, ${group.id})">
                        <i class="fas fa-times"></i>
                    </span>
                    <ul class="mt-2">
                        ${group.options && Array.isArray(group.options) ? group.options.map(option => {
                            const price = parseFloat(option.price) || 0;
                            // Changed currency from TL to £ in option list display
                            return `<li>${option.name} (+${price.toFixed(2)} £)</li>`;
                        }).join('') : ''}
                    </ul>
                `;
                
                categoryOptionsContainer.appendChild(groupItem);
            });
        })
        .catch(error => {
            console.error('Kategori opsiyonları yüklenirken hata oluştu:', error);
            const categoryOptionsContainer = document.getElementById('categoryOptionsList');
            if (categoryOptionsContainer) {
                categoryOptionsContainer.innerHTML = '<p class="text-center text-danger">Kategori opsiyonları yüklenirken hata oluştu.</p>';
            }
        });
}

        // Opsiyon grubu ekle
        function addOptionGroup() {
            const groupName = document.getElementById('optionGroupName').value.trim();
            const isRequired = document.getElementById('isRequired').checked ? 1 : 0;
            const minSelection = parseInt(document.getElementById('minSelection').value);
            const maxSelection = parseInt(document.getElementById('maxSelection').value);
            
            if (!groupName) {
                alert('Lütfen bir opsiyon grubu adı girin.');
                return;
            }
            
            if (minSelection > maxSelection) {
                alert('Minimum seçim, maksimum seçimden büyük olamaz.');
                return;
            }

            // FormData oluştur
            const formData = new FormData();
            formData.append('name', groupName);
            formData.append('is_required', isRequired);
            formData.append('min_selection', minSelection);
            formData.append('max_selection', maxSelection);

            // AJAX isteği gönder
            fetch('db.php?action=add_option_group', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Opsiyon grubu başarıyla eklendi.');
                    
                    document.getElementById('optionGroupName').value = '';
                    document.getElementById('isRequired').checked = true; // Reset to default checked
                    document.getElementById('minSelection').value = 0;
                    document.getElementById('maxSelection').value = 5;
                    
                    loadOptionGroups(); // Opsiyon gruplarını yeniden yükle
                } else {
                    alert('Opsiyon grubu eklenirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }

        // Opsiyon ekle
        function addOption() {
            const groupId = parseInt(document.getElementById('optionGroup').value);
            const optionName = document.getElementById('optionName').value.trim();
            const optionPrice = parseFloat(document.getElementById('optionPrice').value);
            
            if (!optionName || isNaN(optionPrice)) {
                alert('Lütfen geçerli bir opsiyon adı ve fiyat girin.');
                return;
            }
            
            if (isNaN(groupId) || groupId <= 0) {
                alert('Lütfen geçerli bir opsiyon grubu seçin.');
                return;
            }
            
            // FormData oluştur
            const formData = new FormData();
            formData.append('group_id', groupId);
            formData.append('name', optionName);
            formData.append('price', optionPrice);
            
            // AJAX isteği gönder
            fetch('db.php?action=add_option', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Opsiyon başarıyla eklendi.');
                    
                    // Formu temizle
                    document.getElementById('optionName').value = '';
                    document.getElementById('optionPrice').value = '0';
                    
                    // İlgili gruptaki opsiyonları yeniden yükle
                    loadGroupOptions(groupId);
                } else {
                    alert('Opsiyon eklenirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Opsiyon eklenirken hata oluştu:', error);
                alert('Opsiyon eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }

        function addOptionToProduct() {
            const selectedProducts = Array.from(document.getElementById('productForOption').selectedOptions)
                .map(option => parseInt(option.value))
                .filter(id => !isNaN(id) && id > 0);
            
            const selectedGroups = Array.from(document.getElementById('optionGroupForProduct').selectedOptions)
                .map(option => parseInt(option.value))
                .filter(id => !isNaN(id) && id > 0);
            
            if (selectedProducts.length === 0) {
                alert('Lütfen en az bir ürün seçin.');
                return;
            }
            
            if (selectedGroups.length === 0) {
                alert('Lütfen en az bir opsiyon grubu seçin.');
                return;
            }
            
            const button = document.getElementById('addOptionToProduct');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Ekleniyor...';
            button.disabled = true;
            
            // FormData oluştur
            const formData = new FormData();
            formData.append('action', 'add_options_to_products');
            formData.append('product_ids', JSON.stringify(selectedProducts));
            formData.append('option_groups', JSON.stringify(selectedGroups));
            
            fetch('db.php?' + new URLSearchParams({ action: formData.get('action') || '' }), {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        alert(`Başarılı! ${data.processed} ürüne toplam ${data.added_count || 0} yeni opsiyon grubu eklendi.`);
                        
                        // Formu temizle
                        document.getElementById('productForOption').selectedIndex = -1;
                        document.getElementById('optionGroupForProduct').selectedIndex = -1;
                        
                        // Seçili ürünlerden birini görüntülüyorsak, onu yenile
                        const viewProductId = document.getElementById('viewProductOptions').value;
                        if (viewProductId && selectedProducts.includes(parseInt(viewProductId))) {
                            loadProductOptions(viewProductId);
                        }
                    } else {
                        alert('Hata: ' + (data.error || 'Bilinmeyen hata oluştu'));
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    alert('Sunucudan geçersiz yanıt alındı: ' + text);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Opsiyon grupları eklenirken bir hata oluştu: ' + error.message);
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

// Kategoriye opsiyon grubu ekle
function addOptionToCategory() {
    const categoryId = parseInt(document.getElementById('categoryForOption').value);
    const selectedGroups = Array.from(document.getElementById('optionGroupForCategory').selectedOptions)
        .map(option => parseInt(option.value))
        .filter(id => !isNaN(id) && id > 0);
    
    if (isNaN(categoryId) || categoryId <= 0) {
        alert('Lütfen geçerli bir kategori seçin.');
        return;
    }
    
    if (selectedGroups.length === 0) {
        alert('Lütfen en az bir opsiyon grubu seçin.');
        return;
    }
    
    // Kategori adını al
    const categorySelect = document.getElementById('categoryForOption');
    const categoryName = categorySelect.options[categorySelect.selectedIndex].text;
    
    // Onay iste
    if (!confirm(`${categoryName} kategorisine ve bu kategorideki TÜM ÜRÜNLERE ${selectedGroups.length} opsiyon grubu eklemek istediğinize emin misiniz?`)) {
        return;
    }
    
    const button = document.getElementById('addOptionToCategory');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Ekleniyor...';
    button.disabled = true;
    
    // FormData oluştur
    const formData = new FormData();
    formData.append('action', 'add_options_to_category_and_products');
    formData.append('category_id', categoryId);
    formData.append('option_groups', JSON.stringify(selectedGroups));
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${categoryName} kategorisine ve ${data.affected_products} ürüne ${data.processed} opsiyon grubu başarıyla eklendi.`);
            
            // Formu temizle
            document.getElementById('categoryForOption').selectedIndex = 0;
            document.getElementById('optionGroupForCategory').selectedIndex = -1;
        } else {
            alert('Opsiyon grupları eklenirken hata oluştu: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Opsiyon grupları eklenirken bir hata oluştu: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

        /* Updated edit functions to use modal instead of prompt */
        // Opsiyon grubu düzenleme modalını aç
        function editOptionGroup(groupId, name, isRequired, minSelection, maxSelection) {
            currentEditId = groupId;
            currentEditType = 'option_group';
            
            document.getElementById('editModalTitle').textContent = 'Opsiyon Grubu Düzenle';
            document.getElementById('editModalBody').innerHTML = `
                <div class="mb-3">
                    <label for="editOptionGroupName" class="form-label">Opsiyon Grubu Adı</label>
                    <input type="text" class="form-control" id="editOptionGroupName" value="${name}">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="editIsRequired" ${isRequired ? 'checked' : ''}>
                    <label class="form-check-label" for="editIsRequired">Zorunlu mu?</label>
                </div>
                <div class="mb-3">
                    <label for="editMinSelection" class="form-label">Minimum Seçim</label>
                    <input type="number" class="form-control" id="editMinSelection" value="${minSelection}" min="0">
                </div>
                <div class="mb-3">
                    <label for="editMaxSelection" class="form-label">Maksimum Seçim</label>
                    <input type="number" class="form-control" id="editMaxSelection" value="${maxSelection}" min="1">
                </div>
            `;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Opsiyon düzenleme modalını aç
        function editOption(optionId, name, price) {
            currentEditId = optionId;
            currentEditType = 'option';
            
            document.getElementById('editModalTitle').textContent = 'Opsiyon Düzenle';
            document.getElementById('editModalBody').innerHTML = `
                <div class="mb-3">
                    <label for="editOptionName" class="form-label">Opsiyon Adı</label>
                    <input type="text" class="form-control" id="editOptionName" value="${name}">
                </div>
                <div class="mb-3">
                    <label for="editOptionPrice" class="form-label">Ek Fiyat (£)</label>
                    <input type="number" step="0.01" class="form-control" id="editOptionPrice" value="${price}">
                </div>
            `;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        /* Added saveChanges function to handle modal-based editing */
        // Değişiklikleri kaydet
        function saveChanges() {
            if (currentEditType === 'option_group') {
                const newName = document.getElementById('editOptionGroupName').value.trim();
                const isRequired = document.getElementById('editIsRequired').checked ? 1 : 0;
                const minSelection = parseInt(document.getElementById('editMinSelection').value);
                const maxSelection = parseInt(document.getElementById('editMaxSelection').value);
                
                if (!newName) {
                    alert('Opsiyon grubu adı boş olamaz.');
                    return;
                }
                
                if (minSelection > maxSelection) {
                    alert('Minimum seçim, maksimum seçimden büyük olamaz.');
                    return;
                }

                const formData = new FormData();
                formData.append('group_id', currentEditId);
                formData.append('name', newName);
                formData.append('is_required', isRequired);
                formData.append('min_selection', minSelection);
                formData.append('max_selection', maxSelection);

                fetch('db.php?action=update_option_group', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Opsiyon grubu başarıyla güncellendi.');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                        modal.hide();
                        loadOptionGroups(); // Refresh the display to show updated values
                    } else {
                        alert('Güncelleme sırasında hata oluştu: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                });

            } else if (currentEditType === 'option') {
                const newName = document.getElementById('editOptionName').value.trim();
                const newPrice = parseFloat(document.getElementById('editOptionPrice').value);
                
                if (!newName || isNaN(newPrice)) {
                    alert('Lütfen geçerli bir opsiyon adı ve fiyat girin.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('option_id', currentEditId);
                formData.append('name', newName);
                formData.append('price', newPrice);
                
                fetch('db.php?action=update_option', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Opsiyon başarıyla güncellendi.');
                        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                        // Update the display immediately
                        document.getElementById(`option-name-${currentEditId}`).textContent = newName;
                        document.getElementById(`option-price-${currentEditId}`).textContent = `£${newPrice.toFixed(2)}`;
                    } else {
                        alert('Opsiyon güncellenirken hata oluştu: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Opsiyon güncellenirken hata oluştu:', error);
                    alert('Opsiyon güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
                });
            }
        }

        /* Added delete all functionality to match menu management */
        // Tüm opsiyon gruplarını silme onayı
        function confirmDeleteAllOptionGroups() {
            if (!confirm('TÜM opsiyon gruplarını silmek istediğinize emin misiniz? Bu işlem geri alınamaz ve tüm opsiyonlar da silinecektir!')) {
                return;
            }
            
            if (confirm('BU İŞLEM TÜM VERİLERİ SİLECEK. SON BİR KEZ ONAKLIYOR MUSUNUZ?')) {
                deleteAllOptionGroups();
            }
        }
// Ürün opsiyon grubunu silme fonksiyonu
function deleteProductOption(productId, groupId) {
    if (!confirm('Bu opsiyon grubunu üründen kaldırmak istediğinize emin misiniz?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'remove_option_group_from_product');
    formData.append('product_id', productId);
    formData.append('group_id', groupId);
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Opsiyon grubu üründen başarıyla kaldırıldı.');
            loadProductOptions(productId); // Ürün opsiyonlarını yeniden yükle
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Silme işlemi sırasında hata:', error);
        alert('Silme işlemi sırasında bir hata oluştu');
    });
}
        // Tüm opsiyon gruplarını sil
        function deleteAllOptionGroups() {
            fetch('db.php?action=delete_all_option_groups', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tüm opsiyon grupları ve opsiyonlar başarıyla silindi.');
                    loadOptionGroups();
                } else {
                    alert('Opsiyon grupları silinirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Opsiyon grupları silinirken hata oluştu:', error);
                alert('Opsiyon grupları silinirken bir hata oluştu. Lütfen tekrar deneyin.');
            });
        }

function showDeleteConfirm(type, id, productId = null, groupId = null, categoryId = null) {
    let message = '';
    let action = '';
    
    if (type === 'group') {
        message = 'Bu opsiyon grubunu silmek istediğinize emin misiniz?';
        action = 'delete_option_group';
    } else if (type === 'option') {
        message = 'Bu opsiyonu silmek istediğinize emin misiniz?';
        action = 'delete_option';
    } else if (type === 'product_option') {
        message = 'Bu opsiyon grubunu üründen kaldırmak istediğinize emin misiniz?';
        action = 'remove_option_group_from_product';
    } else if (type === 'category_option') {
        message = 'Bu opsiyon grubunu kategoriden kaldırmak istediğinize emin misiniz?';
        action = 'remove_option_group_from_category';
    }
    
    currentDeleteType = type;
    currentDeleteId = id;
    currentDeleteProductId = productId;
    currentDeleteCategoryId = categoryId;
    currentDeleteGroupId = groupId;
    
    document.getElementById('deleteMessage').textContent = message;
    const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    confirmDeleteModal.show();
}
// Kategori opsiyon grubunu silme fonksiyonu
function deleteCategoryOption(categoryId, groupId) {
    if (!confirm('Bu opsiyon grubunu kategoriden kaldırmak istediğinize emin misiniz?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'remove_option_group_from_category');
    formData.append('category_id', categoryId);
    formData.append('group_id', groupId);
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Opsiyon grubu kategoriden başarıyla kaldırıldı.');
            loadCategoryOptions(categoryId); // Kategori opsiyonlarını yeniden yükle
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Silme işlemi sırasında hata:', error);
        alert('Silme işlemi sırasında bir hata oluştu');
    });
}

        // Silme işlemini onayla
function confirmDelete() {
    console.log('Silme işlemi başlatılıyor:', {
        type: currentDeleteType,
        id: currentDeleteId,
        productId: currentDeleteProductId,
        categoryId: currentDeleteCategoryId,
        groupId: currentDeleteGroupId
    });
    
    let formData = new FormData();
    formData.append('action', '');
    
    if (currentDeleteType === 'group') {
        formData.append('action', 'delete_option_group');
        formData.append('group_id', currentDeleteId);
    } else if (currentDeleteType === 'option') {
        formData.append('action', 'delete_option');
        formData.append('option_id', currentDeleteId);
    } else if (currentDeleteType === 'product_option') {
        formData.append('action', 'remove_option_group_from_product');
        formData.append('product_id', currentDeleteProductId);
        formData.append('group_id', currentDeleteGroupId);
    } else if (currentDeleteType === 'category_option') {
        formData.append('action', 'remove_option_group_from_category');
        formData.append('category_id', currentDeleteCategoryId);
        formData.append('group_id', currentDeleteGroupId);
    }
    
    // Hata ayıklama için formData içeriğini göster
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Sunucu yanıtı:', data);
        if (data.success) {
            alert(data.message || 'İşlem başarıyla tamamlandı.');
            
            const confirmDeleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmDeleteModal.hide();
            
            if (currentDeleteType === 'group') {
                loadOptionGroups();
            } else if (currentDeleteType === 'option') {
                // Find the group ID that this option belongs to and reload it
                const groupId = optionGroups.find(group => group.options && group.options.find(option => option.id === currentDeleteId))?.id;
                if (groupId) {
                    loadGroupOptions(groupId);
                } else {
                    loadOptionGroups(); // Fallback to reloading all groups if group ID not found
                }
            } else if (currentDeleteType === 'product_option') {
                loadProductOptions(currentDeleteProductId);
            } else if (currentDeleteType === 'category_option') {
                loadCategoryOptions(currentDeleteCategoryId);
            }
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Silme işlemi sırasında hata:', error);
        alert('Silme işlemi sırasında bir hata oluştu');
    });
}
    </script>

<script>
(function () {
  var modal = document.getElementById('confirmDeleteModal');
  if (!modal) return;

  // Start hidden state with inert so it can't receive focus while hidden.
  if (!modal.hasAttribute('inert')) {
    modal.setAttribute('inert', '');
  }

  var lastFocus = null;

  modal.addEventListener('show.bs.modal', function () {
    lastFocus = document.activeElement;
    modal.removeAttribute('inert');
    // Ensure Bootstrap removes aria-hidden when showing (defensive)
    modal.removeAttribute('aria-hidden');
  });

  // Before Bootstrap toggles aria-hidden=true on hide, move focus out of the modal
  modal.addEventListener('hide.bs.modal', function () {
    if (modal.contains(document.activeElement)) {
      try { document.activeElement.blur(); } catch (e) {}
    }
  });

  modal.addEventListener('hidden.bs.modal', function () {
    // Now that it's fully hidden, make it inert again so it can't be focused
    modal.setAttribute('inert', '');
    modal.setAttribute('aria-hidden', 'true'); // allow AT hint while modal is inert (safe since no focus inside)
    // Try to restore focus to the element that opened the modal
    if (lastFocus && typeof lastFocus.focus === 'function') {
      try { lastFocus.focus(); } catch (e) {}
    }
  });
})();
</script>

</body>
</html>
