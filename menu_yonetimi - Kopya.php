<?php
// menu_yonetimi.php - Menü yönetimi
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Sipariş Sistemi - Menü Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .bulk-section {
            background-color: #f0f8ff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .instructions {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
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
        <div class="bulk-section">
            <h4>Toplu Menü Ekleme</h4>
            <div class="instructions">
                <p><strong>Kullanım:</strong> Her satıra bir kategori veya ürün yazın. Kategori eklemek için satır başına "--" ekleyin. Ürün eklemek için "ürün adı fiyat" formatını kullanın.</p>
                <p><strong>Örnek:</strong><br>
                --Kahvaltı<br>
                Sucuklu Yumurta 45.00<br>
                Menemen 40.00<br>
                --İçecekler<br>
                Çay 15.00<br>
                Ayran 20.00</p>
            </div>
            <div class="mb-3">
                <label for="bulkMenuInput" class="form-label">Menü İçeriği</label>
                <textarea class="form-control" id="bulkMenuInput" rows="10" placeholder="Kategori ve ürünleri yukarıdaki formatta girin..."></textarea>
            </div>
            <button class="btn btn-success" id="addBulkMenu">
                <i class="fas fa-bulk me-1"></i>Toplu Menü Ekle
            </button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Kategori Ekle</h5>
                        <button class="btn btn-sm btn-danger" id="deleteAllCategories">
                            <i class="fas fa-trash me-1"></i>Tüm Kategorileri Sil
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
                            <label for="productPrice" class="form-label">Fiyat (TL)</label>
                            <input type="number" step="0.01" class="form-control" id="productPrice">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="productAvailable" checked>
                            <label class="form-check-label" for="productAvailable">Mevcut</label>
                        </div>
                        <button class="btn btn-primary" id="addProduct">Ürün Ekle</button>
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
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editModalBody">
                    <!-- Modal içeriği dinamik olarak eklenecek -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" id="saveChanges">Değişiklikleri Kaydet</button>
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

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            document.getElementById('addCategory').addEventListener('click', addCategory);
            document.getElementById('addProduct').addEventListener('click', addProduct);
            document.getElementById('deleteAllCategories').addEventListener('click', confirmDeleteAllCategories);
            document.getElementById('deleteAllProducts').addEventListener('click', confirmDeleteAllProducts);
            document.getElementById('saveChanges').addEventListener('click', saveChanges);
            document.getElementById('addBulkMenu').addEventListener('click', addBulkMenu);
        });

        // Kategorileri yükle
        function loadCategories() {
            fetch('get_data.php?action=get_categories')
                .then(response => response.json())
                .then(data => {
                    categories = data;
                    updateCategoryDropdown();
                    renderMenuItems();
                })
                .catch(error => {
                    console.error('Kategoriler yüklenirken hata oluştu:', error);
                    alert('Kategoriler yüklenirken bir hata oluştu. Lütfen sayfayı yenileyin.');
                });
        }

        // Tüm ürünleri yükle
        function loadAllProducts() {
            fetch('get_data.php?action=get_all_products')
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

        // Menü öğelerini görüntüle
        function renderMenuItems() {
            const menuItems = document.getElementById('menuItems');
            menuItems.innerHTML = '';
            
            if (categories.length === 0) {
                menuItems.innerHTML = '<p>Henüz hiç kategori eklenmemiş.</p>';
                return;
            }
            
            categories.forEach(category => {
                const categoryCard = document.createElement('div');
                categoryCard.className = 'card mb-3';
                categoryCard.innerHTML = `
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>${category.name}</h5>
                        <div>
                            <i class="fas fa-edit edit-btn me-2" onclick="editCategory(${category.id}, '${category.name.replace(/'/g, "\\'")}')"></i>
                            <i class="fas fa-trash delete-btn" onclick="deleteCategory(${category.id})"></i>
                        </div>
                    </div>
                    <div class="card-body" id="products-${category.id}">
                        <p>Ürünler yükleniyor...</p>
                    </div>
                `;
                
                menuItems.appendChild(categoryCard);
                
                // Bu kategoriye ait ürünleri yükle
                loadCategoryProducts(category.id);
            });
        }

        // Kategoriye ait ürünleri yükle
        function loadCategoryProducts(categoryId) {
            fetch(`get_data.php?action=get_products&category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    const productsContainer = document.getElementById(`products-${categoryId}`);
                    productsContainer.innerHTML = '';
                    
                    if (data.length === 0) {
                        productsContainer.innerHTML = '<p>Bu kategoride ürün bulunmamaktadır.</p>';
                        return;
                    }
                    
                    const productList = document.createElement('ul');
                    productList.className = 'list-group';
                    
                    data.forEach(product => {
                        const productItem = document.createElement('li');
                        productItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                        productItem.innerHTML = `
                            <div>
                                <h6 class="mb-1">${product.name}</h6>
                                <small class="text-muted">${product.price} TL</small>
                            </div>
                            <div>
                                <span class="badge ${product.available ? 'bg-success' : 'bg-danger'} badge-custom me-2">
                                    ${product.available ? 'Mevcut' : 'Stokta Yok'}
                                </span>
                                <i class="fas fa-edit edit-btn me-2" onclick="editProduct(${product.id}, '${product.name.replace(/'/g, "\\'")}', ${product.price}, ${product.available ? 1 : 0}, ${product.category_id})"></i>
                                <i class="fas fa-trash delete-btn" onclick="deleteProduct(${product.id})"></i>
                            </div>
                        `;
                        
                        productList.appendChild(productItem);
                    });
                    
                    productsContainer.appendChild(productList);
                })
                .catch(error => {
                    console.error('Ürünler yüklenirken hata oluştu:', error);
                    document.getElementById(`products-${categoryId}`).innerHTML = 
                        '<p>Ürünler yüklenirken hata oluştu.</p>';
                });
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
            fetch('get_data.php?action=add_category', {
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
        }

        // Ürün ekle
        function addProduct() {
            const categoryId = parseInt(document.getElementById('productCategory').value);
            const productName = document.getElementById('productName').value.trim();
            const productPrice = parseFloat(document.getElementById('productPrice').value);
            const productAvailable = document.getElementById('productAvailable').checked;
            
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
            
            // AJAX isteği gönder
            fetch('get_data.php?action=add_product', {
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

        // Toplu menü ekleme
        function addBulkMenu() {
            const bulkInput = document.getElementById('bulkMenuInput').value.trim();
            
            if (!bulkInput) {
                alert('Lütfen toplu menü içeriği girin.');
                return;
            }
            
            const lines = bulkInput.split('\n');
            let currentCategoryId = null;
            let processedCount = 0;
            let errorCount = 0;
            
            // İşlem sırasında kullanıcıyı bilgilendir
            alert('Toplu menü ekleme işlemi başlatılıyor. Bu işlem biraz zaman alabilir.');
            
            // Her satırı işle
            processLinesSequentially(lines, 0);
            
            function processLinesSequentially(lines, index) {
                if (index >= lines.length) {
                    // Tüm satırlar işlendi
                    alert(`Toplu menü ekleme tamamlandı.\nBaşarılı: ${processedCount}\nHata: ${errorCount}`);
                    loadCategories(); // Menüyü yeniden yükle
                    document.getElementById('bulkMenuInput').value = ''; // Textarea'yı temizle
                    return;
                }
                
                const line = lines[index].trim();
                
                if (!line) {
                    // Boş satır, bir sonrakine geç
                    processLinesSequentially(lines, index + 1);
                    return;
                }
                
                // Kategori satırı mı kontrol et (-- ile başlıyorsa)
                if (line.startsWith('--')) {
                    const categoryName = line.substring(2).trim();
                    
                    if (categoryName) {
                        // Kategori ekle
                        const formData = new FormData();
                        formData.append('name', categoryName);
                        
                        fetch('get_data.php?action=add_category', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                processedCount++;
                                // Yeni eklenen kategoriyi bul
                                fetch('get_data.php?action=get_categories')
                                    .then(response => response.json())
                                    .then(cats => {
                                        const newCategory = cats.find(cat => cat.name === categoryName);
                                        if (newCategory) {
                                            currentCategoryId = newCategory.id;
                                        }
                                        processLinesSequentially(lines, index + 1);
                                    });
                            } else {
                                errorCount++;
                                processLinesSequentially(lines, index + 1);
                            }
                        })
                        .catch(error => {
                            console.error('Kategori eklenirken hata oluştu:', error);
                            errorCount++;
                            processLinesSequentially(lines, index + 1);
                        });
                    } else {
                        processLinesSequentially(lines, index + 1);
                    }
                } else if (currentCategoryId) {
                    // Ürün satırı - "ürün adı fiyat" formatında
                    const parts = line.split(' ');
                    if (parts.length >= 2) {
                        // Son kısım fiyat olmalı
                        const priceStr = parts[parts.length - 1].replace(',', '.');
                        const price = parseFloat(priceStr);
                        
                        if (!isNaN(price) && price > 0) {
                            // Ürün adı fiyattan önceki tüm kısımlar
                            const productName = parts.slice(0, parts.length - 1).join(' ').trim();
                            
                            if (productName) {
                                // Ürün ekle
                                const formData = new FormData();
                                formData.append('category_id', currentCategoryId);
                                formData.append('name', productName);
                                formData.append('price', price);
                                formData.append('available', 1);
                                
                                fetch('get_data.php?action=add_product', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        processedCount++;
                                    } else {
                                        errorCount++;
                                    }
                                    processLinesSequentially(lines, index + 1);
                                })
                                .catch(error => {
                                    console.error('Ürün eklenirken hata oluştu:', error);
                                    errorCount++;
                                    processLinesSequentially(lines, index + 1);
                                });
                            } else {
                                processLinesSequentially(lines, index + 1);
                            }
                        } else {
                            // Geçersiz fiyat formatı
                            errorCount++;
                            processLinesSequentially(lines, index + 1);
                        }
                    } else {
                        // Geçersiz ürün formatı
                        errorCount++;
                        processLinesSequentially(lines, index + 1);
                    }
                } else {
                    // Kategori tanımlanmadan ürün eklenmeye çalışılıyor
                    errorCount++;
                    processLinesSequentially(lines, index + 1);
                }
            }
        }

        // Kategori düzenleme modalını aç
        function editCategory(id, name) {
            currentEditId = id;
            currentEditType = 'category';
            
            document.getElementById('editModalTitle').textContent = 'Kategori Düzenle';
            document.getElementById('editModalBody').innerHTML = `
                <div class="mb-3">
                    <label for="editCategoryName" class="form-label">Kategori Adı</label>
                    <input type="text" class="form-control" id="editCategoryName" value="${name}">
                </div>
            `;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Ürün düzenleme modalını aç
        function editProduct(id, name, price, available, categoryId) {
            currentEditId = id;
            currentEditType = 'product';
            
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
                    <label for="editProductPrice" class="form-label">Fiyat (TL)</label>
                    <input type="number" step="0.01" class="form-control" id="editProductPrice" value="${price}">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="editProductAvailable" ${available ? 'checked' : ''}>
                    <label class="form-check-label" for="editProductAvailable">Mevcut</label>
                </div>
            `;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }

        // Değişiklikleri kaydet
        function saveChanges() {
            if (currentEditType === 'category') {
                const newName = document.getElementById('editCategoryName').value.trim();
                
                if (!newName) {
                    alert('Kategori adı boş olamaz.');
                    return;
                }
                
                const formData = new FormData();
                formData.append('category_id', currentEditId);
                formData.append('name', newName);
                
                fetch('get_data.php?action=update_category', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Kategori başarıyla güncellendi.');
                        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                        loadCategories();
                    } else {
                        alert('Kategori güncellenirken hata oluştu: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Kategori güncellenirken hata oluştu:', error);
                    alert('Kategori güncellenirken bir hata oluştu. Lütfen tekrar deneyin.');
                });
                
            } else if (currentEditType === 'product') {
                const categoryId = parseInt(document.getElementById('editProductCategory').value);
                const name = document.getElementById('editProductName').value.trim();
                const price = parseFloat(document.getElementById('editProductPrice').value);
                const available = document.getElementById('editProductAvailable').checked;
                
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
                
                fetch('get_data.php?action=update_product', {
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
            
            fetch('get_data.php?action=delete_category', {
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
            
            fetch('get_data.php?action=delete_product', {
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
            fetch('get_data.php?action=delete_all_categories', {
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
            fetch('get_data.php?action=delete_all_products', {
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