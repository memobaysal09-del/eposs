<?php
// masa_yonetimi.php - Masa Yönetimi
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <title>Restaurant Sipariş Sistemi - Masa Yönetimi</title>
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
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .table-info {
            flex-grow: 1;
        }
        .table-name {
            font-weight: bold;
            font-size: 16px;
        }
        .table-details {
            font-size: 14px;
            color: #666;
        }
        .table-actions {
            display: flex;
            gap: 8px;
        }
        .status-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            cursor: pointer;
        }
        .status-available {
            background-color: #28a745;
            color: white;
        }
        .status-occupied {
            background-color: #dc3545;
            color: white;
        }
        .edit-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .edit-input {
            flex: 1;
        }
        .status-select {
            width: 100%;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .danger-zone {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Masa Yönetimi</h1>
                <div>
                    <a href="index.php" class="btn btn-light me-2">
                        <i class="fas fa-home me-2"></i>Masalara Dön
                    </a>
                    <button class="btn btn-warning" onclick="loadTables()">
                        <i class="fas fa-refresh me-2"></i>Yenile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-plus-circle me-2"></i>Masa Ekle</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tableName" class="form-label">Masa Adı *</label>
                            <input type="text" class="form-control" id="tableName" placeholder="Örn: İç Masa 1, Dış Masa, Köşe Masa" required>
                            <div class="form-text">Masanın tanımlayıcı adını girin</div>
                        </div>
                        <div class="mb-3">
                            <label for="tableNumber" class="form-label">Masa Numarası *</label>
                            <input type="number" class="form-control" id="tableNumber" placeholder="Masa numarası girin" min="1" required>
                            <div class="form-text">Her masa numarası benzersiz olmalıdır</div>
                        </div>
                        <div class="mb-3">
                            <label for="tableStatus" class="form-label">Durum *</label>
                            <select class="form-control" id="tableStatus" required>
                                <option value="available">Boş</option>
                                <option value="occupied">Dolu</option>
                            </select>
                        </div>
                        <button class="btn btn-success w-100" id="addTableBtn">
                            <i class="fas fa-plus me-2"></i>Masa Ekle
                        </button>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-info-circle me-2"></i>Toplu İşlemler</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">1'den 20'ye kadar tüm masaları otomatik ekle:</p>
                        <button class="btn btn-outline-primary w-100 mb-2" onclick="addAllTables()">
                            <i class="fas fa-bolt me-2"></i>1-20 Arası Tüm Masaları Ekle
                        </button>
                        
                        <hr>
                        
                        <div class="danger-zone p-3 bg-light rounded">
                            <p class="text-danger"><strong><i class="fas fa-exclamation-triangle me-1"></i>Tehlikeli İşlem</strong></p>
                            <p class="text-muted small">Tüm masaları kalıcı olarak silmek için aşağıdaki butonu kullanın:</p>
                            <button class="btn btn-outline-danger w-100" onclick="deleteAllTables()">
                                <i class="fas fa-trash-alt me-2"></i>Tüm Masaları Sil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5><i class="fas fa-list me-2"></i>Mevcut Masalar</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Toplam: <span id="totalTables">0</span> masa</span>
                            <span class="text-muted" id="tablesSummary">Boş: 0 | Dolu: 0</span>
                        </div>
                        <div id="tablesList">
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>Masalar yükleniyor...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadTables();
            document.getElementById('addTableBtn').addEventListener('click', addTable);
            
            // Enter tuşu ile masa ekleme
            document.getElementById('tableNumber').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    addTable();
                }
            });
        });

        function loadTables() {
            document.getElementById('tablesList').innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin me-2"></i>Masalar yükleniyor...
                </div>
            `;
            
            fetch('db.php?action=get_tables')
                .then(response => response.json())
                .then(data => {
                    renderTables(data);
                    updateSummary(data);
                })
                .catch(error => {
                    console.error('Masalar yüklenirken hata oluştu:', error);
                    document.getElementById('tablesList').innerHTML = `
                        <div class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Masalar yüklenirken hata oluştu.
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="loadTables()">
                                <i class="fas fa-refresh me-1"></i>Tekrar Dene
                            </button>
                        </div>
                    `;
                });
        }

        function renderTables(tables) {
            const tablesList = document.getElementById('tablesList');
            
            if (tables.length === 0) {
                tablesList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chair me-2"></i>
                        Henüz hiç masa eklenmemiş.
                    </div>
                `;
                return;
            }
            
            const list = document.createElement('div');
            tables.forEach(table => {
                const tableItem = document.createElement('div');
                tableItem.className = 'table-item';
                tableItem.id = `table-${table.id}`;
                
                // Masa adı ve numarasını yan yana göster
                const displayName = table.name ? `${table.name} ${table.number}` : `Masa ${table.number}`;
                
                tableItem.innerHTML = `
                    <div class="table-info">
                        <div class="table-name">${displayName}</div>
                        <div class="table-details">
                            Durum: <span class="status-badge ${table.status === 'available' ? 'status-available' : 'status-occupied'}" 
                                onclick="editTableStatus(${table.id}, '${table.name || ''}', ${table.number}, '${table.status}')">
                                ${table.status === 'available' ? 'Boş' : 'Dolu'}
                            </span>
                        </div>
                    </div>
                    <div class="table-actions">
                        <button class="btn btn-primary btn-sm" onclick="editTable(${table.id}, '${table.name || ''}', ${table.number}, '${table.status}')" title="Masa Düzenle">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteTable(${table.id}, '${displayName}')" title="Masa Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                list.appendChild(tableItem);
            });
            tablesList.innerHTML = '';
            tablesList.appendChild(list);
        }

        function updateSummary(tables) {
            document.getElementById('totalTables').textContent = tables.length;
            
            const available = tables.filter(table => table.status === 'available').length;
            const occupied = tables.filter(table => table.status === 'occupied').length;
            
            document.getElementById('tablesSummary').textContent = `Boş: ${available} | Dolu: ${occupied}`;
        }

        function addTable() {
            const tableName = document.getElementById('tableName').value.trim();
            const tableNumber = document.getElementById('tableNumber').value;
            const tableStatus = document.getElementById('tableStatus').value;
            
            if (!tableName) {
                alert('Lütfen masa adı girin.');
                document.getElementById('tableName').focus();
                return;
            }
            
            if (!tableNumber || tableNumber <= 0) {
                alert('Lütfen geçerli bir masa numarası girin.');
                document.getElementById('tableNumber').focus();
                return;
            }
            
            const addBtn = document.getElementById('addTableBtn');
            const originalText = addBtn.innerHTML;
            addBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Ekleniyor...';
            addBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('name', tableName);
            formData.append('number', tableNumber);
            formData.append('status', tableStatus);
            
            fetch('db.php?action=add_table', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Masa başarıyla eklendi!');
                    document.getElementById('tableName').value = '';
                    document.getElementById('tableNumber').value = '';
                    document.getElementById('tableName').focus();
                    loadTables();
                } else {
                    alert('✗ Masa eklenirken hata: ' + (data.error || 'Bilinmeyen hata'));
                    console.error('Masa ekleme hatası:', data.error);
                }
            })
            .catch(error => {
                console.error('Masa eklenirken hata oluştu:', error);
                alert('✗ Masa eklenirken bir hata oluştu. Lütfen tekrar deneyin.');
            })
            .finally(() => {
                addBtn.innerHTML = originalText;
                addBtn.disabled = false;
            });
        }

        function editTableStatus(tableId, name, number, currentStatus) {
            const newStatus = currentStatus === 'available' ? 'occupied' : 'available';
            const formData = new FormData();
            formData.append('table_id', tableId);
            formData.append('name', name);
            formData.append('number', number);
            formData.append('status', newStatus);
            
            fetch('update_table.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTables();
                } else {
                    alert('✗ Durum güncellenirken hata: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Durum güncelleme hatası:', error);
                alert('✗ Durum güncellenirken bir hata oluştu.');
            });
        }

        function editTable(tableId, currentName, currentNumber, currentStatus) {
            const tableElement = document.getElementById(`table-${tableId}`);
            
            tableElement.innerHTML = `
                <div class="edit-form">
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control edit-input" id="editName-${tableId}" 
                               value="${currentName || ''}" placeholder="Masa Adı">
                        <input type="number" class="form-control edit-input" id="editNumber-${tableId}" 
                               value="${currentNumber}" placeholder="Masa No" min="1">
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-control status-select" id="editStatus-${tableId}">
                            <option value="available" ${currentStatus === 'available' ? 'selected' : ''}>Boş</option>
                            <option value="occupied" ${currentStatus === 'occupied' ? 'selected' : ''}>Dolu</option>
                        </select>
                        <button class="btn btn-success btn-sm" onclick="saveTableEdit(${tableId})" title="Kaydet">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="cancelEdit(${tableId})" title="İptal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        function cancelEdit(tableId) {
            loadTables(); // Sayfayı yeniden yükle
        }

        function saveTableEdit(tableId) {
            const newName = document.getElementById(`editName-${tableId}`).value.trim();
            const newNumber = document.getElementById(`editNumber-${tableId}`).value;
            const newStatus = document.getElementById(`editStatus-${tableId}`).value;
            
            if (!newNumber || newNumber <= 0) {
                alert('Lütfen geçerli bir masa numarası girin.');
                return;
            }
            
            // AJAX ile güncelleme yap
            const formData = new FormData();
            formData.append('table_id', tableId);
            formData.append('name', newName);
            formData.append('number', newNumber);
            formData.append('status', newStatus);
            
            fetch('update_table.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Masa başarıyla güncellendi!');
                    loadTables();
                } else {
                    alert('✗ Masa güncellenirken hata: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Masa güncelleme hatası:', error);
                alert('✗ Masa güncellenirken bir hata oluştu.');
            });
        }

        function deleteTable(tableId, tableName) {
            if (confirm(`"${tableName}" masasını silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz ve masaya ait tüm sipariş geçmişi silinir!`)) {
                fetch(`db.php?action=delete_table&id=${tableId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✓ Masa başarıyla silindi!');
                        loadTables();
                    } else {
                        alert('✗ Masa silinirken hata: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Masa silinirken hata oluştu:', error);
                    alert('✗ Masa silinirken bir hata oluştu. Lütfen tekrar deneyin.');
                });
            }
        }

        function addAllTables() {
            if (confirm('1\'den 20\'ye kadar tüm masaları eklemek istediğinizden emin misiniz?\n\nBu işlem sadece mevcut olmayan masaları ekleyecektir.')) {
                fetch('add_all_tables.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✓ ${data.added} masa başarıyla eklendi!\n${data.existing} masa zaten mevcuttu.`);
                        loadTables();
                    } else {
                        alert('✗ Masalar eklenirken hata: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Toplu masa ekleme hatası:', error);
                    alert('✗ Masalar eklenirken bir hata oluştu.');
                });
            }
        }

        // Tüm masaları silme fonksiyonu
        function deleteAllTables() {
            if (confirm('TÜM masaları silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz ve tüm masa verileri kalıcı olarak silinecektir!')) {
                fetch('db.php?action=delete_all_tables')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✓ Tüm masalar başarıyla silindi!`);
                        loadTables();
                    } else {
                        alert('✗ Masalar silinirken hata: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Tüm masalar silinirken hata oluştu:', error);
                    alert('✗ Tüm masalar silinirken bir hata oluştu. Lütfen tekrar deneyin.');
                });
            }
        }
    </script>
</body>
</html>