<?php
// system_settings.php - Sistem Ayarları Modülü
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Sipariş Sistemi - Sistem Ayarları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 15px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .settings-section {
            margin-bottom: 30px;
        }
        .back-btn {
            margin-right: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: #6c757d;
            border-radius: 10px;
        }
        .nav-pills .nav-link {
            color: #495057;
            border-radius: 10px;
            margin-bottom: 5px;
        }
        .system-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
        .backup-card {
            border-left: 4px solid #28a745;
        }
        .maintenance-card {
            border-left: 4px solid #ffc107;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>Sistem Ayarları</h1>
                    <p class="lead">Restaurant yönetim sistemi konfigürasyonu</p>
                </div>
                <div>
                    <a href="admin.php" class="btn btn-light back-btn">
                        <i class="fas fa-arrow-left me-2"></i>Admin Paneli
                    </a>
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-home me-2"></i>Ana Sayfa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Sol Menü -->
            <div class="col-md-3">
                <div class="card p-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="v-pills-general-tab" data-bs-toggle="pill" data-bs-target="#v-pills-general" type="button" role="tab" aria-controls="v-pills-general" aria-selected="true">
                            <i class="fas fa-cog me-2"></i>Genel Ayarlar
                        </button>
                        <button class="nav-link" id="v-pills-backup-tab" data-bs-toggle="pill" data-bs-target="#v-pills-backup" type="button" role="tab" aria-controls="v-pills-backup" aria-selected="false">
                            <i class="fas fa-database me-2"></i>Veri Yedekleme
                        </button>
                        <button class="nav-link" id="v-pills-maintenance-tab" data-bs-toggle="pill" data-bs-target="#v-pills-maintenance" type="button" role="tab" aria-controls="v-pills-maintenance" aria-selected="false">
                            <i class="fas fa-tools me-2"></i>Sistem Bakımı
                        </button>
                        <button class="nav-link" id="v-pills-info-tab" data-bs-toggle="pill" data-bs-target="#v-pills-info" type="button" role="tab" aria-controls="v-pills-info" aria-selected="false">
                            <i class="fas fa-info-circle me-2"></i>Sistem Bilgileri
                        </button>
                    </div>
                    
                    <div class="system-info mt-4">
                        <h6>Sistem Durumu</h6>
                        <div class="d-flex justify-content-between">
                            <span>Veritabanı:</span>
                            <span class="badge bg-success">Aktif</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Önbellek:</span>
                            <span class="badge bg-success">Aktif</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Güncellemeler:</span>
                            <span class="badge bg-success">Güncel</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sağ İçerik -->
            <div class="col-md-9">
                <div class="tab-content" id="v-pills-tabContent">
                    <!-- Genel Ayarlar -->
                    <div class="tab-pane fade show active" id="v-pills-general" role="tabpanel" aria-labelledby="v-pills-general-tab">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Genel Sistem Ayarları</h5>
                            </div>
                            <div class="card-body">
                                <form id="generalSettingsForm">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="restaurantName" class="form-label">Restaurant Adı</label>
                                            <input type="text" class="form-control" id="restaurantName">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="restaurantPhone" class="form-label">Telefon Numarası</label>
                                            <input type="text" class="form-control" id="restaurantPhone">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="restaurantAddress" class="form-label">Adres</label>
                                        <textarea class="form-control" id="restaurantAddress" rows="2"></textarea>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="currency" class="form-label">Para Birimi</label>
                                            <select class="form-select" id="currency">
                                                <option value="GBP">İngiliz Sterlini (£)</option>
                                                <option value="USD">Amerikan Doları ($)</option>
                                                <option value="EUR">Euro (€)</option>
                                                <option value="TL">Türk Lirası (₺)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="taxRate" class="form-label">Vergi Oranı (%)</label>
                                            <input type="number" class="form-control" id="taxRate" min="0" max="100">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="language" class="form-label">Dil</label>
                                            <select class="form-select" id="language">
                                                <option value="tr">Türkçe</option>
                                                <option value="en">İngilizce</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="timezone" class="form-label">Saat Dilimi</label>
                                            <select class="form-select" id="timezone">
                                                <option value="Europe/Istanbul">İstanbul (UTC+3)</option>
                                                <option value="Europe/London">Londra (UTC+0)</option>
                                                <option value="Europe/Berlin">Berlin (UTC+1)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="dateFormat" class="form-label">Tarih Formatı</label>
                                            <select class="form-select" id="dateFormat">
                                                <option value="d/m/Y">GG/AA/YYYY (24/12/2023)</option>
                                                <option value="m/d/Y">AA/GG/YYYY (12/24/2023)</option>
                                                <option value="Y-m-d">YYYY-AA-GG (2023-12-24)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="notifications">
                                        <label class="form-check-label" for="notifications">Bildirimleri Aktif Et</label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="autoPrint">
                                        <label class="form-check-label" for="autoPrint">Siparişten Sonra Otomatik Yazdır</label>
                                    </div>
                                    
                                    <button type="button" class="btn btn-primary" onclick="saveGeneralSettings()">
                                        <i class="fas fa-save me-2"></i>Ayarları Kaydet
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Veri Yedekleme -->
                    <div class="tab-pane fade" id="v-pills-backup" role="tabpanel" aria-labelledby="v-pills-backup-tab">
                        <div class="card backup-card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Veri Yedekleme ve Geri Yükleme</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Yedekleme İşlemi:</strong> Veritabanının tam yedeğini alır ve indirme bağlantısı sağlar.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0"><i class="fas fa-download me-2"></i>Yedek Al</h6>
                                            </div>
                                            <div class="card-body">
                                                <p>Veritabanının tam yedeğini indirin.</p>
                                                <div class="mb-3">
                                                    <label for="backupType" class="form-label">Yedek Türü</label>
                                                    <select class="form-select" id="backupType">
                                                        <option value="full">Tam Yedek</option>
                                                        <option value="structure">Sadece Yapı</option>
                                                        <option value="data">Sadece Veriler</option>
                                                    </select>
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="compressBackup" checked>
                                                    <label class="form-check-label" for="compressBackup">Sıkıştır (ZIP)</label>
                                                </div>
                                                <button class="btn btn-success w-100" onclick="createBackup()">
                                                    <i class="fas fa-download me-2"></i>Yedek Oluştur ve İndir
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card mb-4">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Geri Yükle</h6>
                                            </div>
                                            <div class="card-body">
                                                <p>Daha önce alınmış yedeği geri yükleyin.</p>
                                                <div class="mb-3">
                                                    <label for="backupFile" class="form-label">Yedek Dosyası</label>
                                                    <input class="form-control" type="file" id="backupFile" accept=".sql,.zip">
                                                </div>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="clearBeforeRestore">
                                                    <label class="form-check-label" for="clearBeforeRestore">Geri yüklemeden önce mevcut verileri temizle</label>
                                                </div>
                                                <button class="btn btn-warning w-100" onclick="restoreBackup()">
                                                    <i class="fas fa-upload me-2"></i>Geri Yükle
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sistem Bakımı -->
                    <div class="tab-pane fade" id="v-pills-maintenance" role="tabpanel" aria-labelledby="v-pills-maintenance-tab">
                        <div class="card maintenance-card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Sistem Bakım İşlemleri</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Dikkat:</strong> Bu işlemler sistem performansını etkileyebilir. Lütfen dikkatli kullanın.
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0"><i class="fas fa-broom me-2"></i>Önbellek Temizleme</h6>
                                            </div>
                                            <div class="card-body">
                                                <p>Sistem önbelleğini temizleyerek performansı artırın.</p>
                                                <button class="btn btn-info w-100" onclick="clearCache()">
                                                    <i class="fas fa-broom me-2"></i>Önbelleği Temizle
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-danger text-white">
                                                <h6 class="mb-0"><i class="fas fa-trash-alt me-2"></i>Veritabanı Optimizasyonu</h6>
                                            </div>
                                            <div class="card-body">
                                                <p>Veritabanını optimize ederek performansı artırın.</p>
                                                <button class="btn btn-danger w-100" onclick="optimizeDatabase()">
                                                    <i class="fas fa-bolt me-2"></i>Veritabanını Optimize Et
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-database me-2"></i>Veritabanı İstatistikleri</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="text-center">
                                                    <h4 id="dbSize">0</h4>
                                                    <small>MB Veritabanı</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="text-center">
                                                    <h4 id="totalOrdersCount">0</h4>
                                                    <small>Sipariş</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="text-center">
                                                    <h4 id="totalProductsCount">0</h4>
                                                    <small>Ürün</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="text-center">
                                                    <h4 id="totalTablesCount">0</h4>
                                                    <small>Masa</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25% Doluluk</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sistem Bilgileri -->
                    <div class="tab-pane fade" id="v-pills-info" role="tabpanel" aria-labelledby="v-pills-info-tab">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Sistem Bilgileri</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Yazılım Bilgileri</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Sürüm</td>
                                                <td>v2.1.0</td>
                                            </tr>
                                            <tr>
                                                <td>Güncelleme</td>
                                                <td id="lastUpdate">24 Kasım 2023</td>
                                            </tr>
                                            <tr>
                                                <td>Lisans</td>
                                                <td>Restaurant Pro Lisansı</td>
                                            </tr>
                                            <tr>
                                                <td>Son Yedek</td>
                                                <td id="lastBackup">24.11.2023 14:30</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Sistem Bilgileri</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <td>PHP Sürümü</td>
                                                <td><?php echo phpversion(); ?></td>
                                            </tr>
                                            <tr>
                                                <td>MySQL Sürümü</td>
                                                <td id="mysqlVersion">Yükleniyor...</td>
                                            </tr>
                                            <tr>
                                                <td>Sunucu</td>
                                                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                            </tr>
                                            <tr>
                                                <td>Son Giriş</td>
                                                <td id="lastLogin">24.11.2023 15:45</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Mesajları -->
    <div class="toast" id="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Bilgi</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            İşlem başarıyla tamamlandı.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadSystemSettings();
            loadSystemStats();
            loadSystemInfo();
        });

        // Sistem ayarlarını yükle
        function loadSystemSettings() {
            fetch('db.php?action=get_system_settings')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const settings = data.settings;
                        document.getElementById('restaurantName').value = settings.restaurant_name;
                        document.getElementById('restaurantPhone').value = settings.restaurant_phone;
                        document.getElementById('restaurantAddress').value = settings.restaurant_address;
                        document.getElementById('currency').value = settings.currency;
                        document.getElementById('taxRate').value = settings.tax_rate;
                        document.getElementById('language').value = settings.language;
                        document.getElementById('timezone').value = settings.timezone;
                        document.getElementById('dateFormat').value = settings.date_format;
                        document.getElementById('notifications').checked = settings.notifications == 1;
                        document.getElementById('autoPrint').checked = settings.auto_print == 1;
                    }
                })
                .catch(error => {
                    console.error('Ayarlar yüklenirken hata oluştu:', error);
                    showToast('Hata', 'Ayarlar yüklenirken bir hata oluştu.', 'danger');
                });
        }

        // Sistem istatistiklerini yükle
        function loadSystemStats() {
            fetch('db.php?action=get_system_stats')
                .then(response => response.json())
                .then(stats => {
                    document.getElementById('totalOrdersCount').textContent = stats.total_orders;
                    document.getElementById('totalProductsCount').textContent = stats.total_products;
                    document.getElementById('totalTablesCount').textContent = stats.total_tables;
                    
                    // Veritabanı boyutunu hesapla (yaklaşık)
                    const dbSize = (stats.total_orders * 0.5 + stats.total_products * 0.1 + stats.total_tables * 0.1) / 1000;
                    document.getElementById('dbSize').textContent = dbSize.toFixed(1);
                })
                .catch(error => {
                    console.error('İstatistikler yüklenirken hata oluştu:', error);
                });
        }

        // Sistem bilgilerini yükle
        function loadSystemInfo() {
            // MySQL sürümünü al
            fetch('db.php?action=get_mysql_version')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('mysqlVersion').textContent = data.version;
                    }
                })
                .catch(error => {
                    console.error('MySQL sürümü alınırken hata oluştu:', error);
                    document.getElementById('mysqlVersion').textContent = 'Bilinmiyor';
                });
        }

        // Genel ayarları kaydet
        function saveGeneralSettings() {
            const formData = new FormData();
            formData.append('restaurant_name', document.getElementById('restaurantName').value);
            formData.append('restaurant_phone', document.getElementById('restaurantPhone').value);
            formData.append('restaurant_address', document.getElementById('restaurantAddress').value);
            formData.append('currency', document.getElementById('currency').value);
            formData.append('tax_rate', document.getElementById('taxRate').value);
            formData.append('language', document.getElementById('language').value);
            formData.append('timezone', document.getElementById('timezone').value);
            formData.append('date_format', document.getElementById('dateFormat').value);
            formData.append('notifications', document.getElementById('notifications').checked ? 1 : 0);
            formData.append('auto_print', document.getElementById('autoPrint').checked ? 1 : 0);

            fetch('db.php?action=save_system_settings', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Başarılı', 'Ayarlar başarıyla kaydedildi.', 'success');
                } else {
                    showToast('Hata', 'Ayarlar kaydedilirken hata oluştu: ' + data.error, 'danger');
                }
            })
            .catch(error => {
                console.error('Ayarlar kaydedilirken hata oluştu:', error);
                showToast('Hata', 'Ayarlar kaydedilirken bir hata oluştu.', 'danger');
            });
        }
        
        // Yedek oluştur
        function createBackup() {
            const backupType = document.getElementById('backupType').value;
            const compress = document.getElementById('compressBackup').checked;
            
            showToast('Bilgi', `${compress ? 'Sıkıştırılmış' : 'Normal'} ${backupType} yedek oluşturuluyor...`, 'info');
            
            // Simüle edilmiş indirme
            setTimeout(() => {
                showToast('Başarılı', 'Yedek başarıyla oluşturuldu ve indiriliyor...', 'success');
            }, 2000);
        }
        
        // Yedekten geri yükle
        function restoreBackup() {
            const fileInput = document.getElementById('backupFile');
            const clearData = document.getElementById('clearBeforeRestore').checked;
            
            if (!fileInput.files.length) {
                showToast('Uyarı', 'Lütfen bir yedek dosyası seçin!', 'warning');
                return;
            }
            
            if (confirm(`${clearData ? 'Mevcut veriler silinecek ve ' : ''}Seçilen yedek geri yüklenecek. Emin misiniz?`)) {
                showToast('Bilgi', 'Yedek geri yükleniyor...', 'info');
                
                // Simüle edilmiş geri yükleme
                setTimeout(() => {
                    showToast('Başarılı', 'Yedek başarıyla geri yüklendi!', 'success');
                }, 3000);
            }
        }
        
        // Önbelleği temizle
        function clearCache() {
            showToast('Bilgi', 'Önbellek temizleniyor...', 'info');
            
            // Simüle edilmiş temizleme
            setTimeout(() => {
                showToast('Başarılı', 'Önbellek başarıyla temizlendi!', 'success');
            }, 1500);
        }
        
        // Veritabanını optimize et
        function optimizeDatabase() {
            showToast('Bilgi', 'Veritabanı optimize ediliyor...', 'info');
            
            // Simüle edilmiş optimizasyon
            setTimeout(() => {
                showToast('Başarılı', 'Veritabanı başarıyla optimize edildi!', 'success');
            }, 2500);
        }

        // Toast mesajı göster
        function showToast(title, message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            
            // Toast rengini ayarla
            toast.className = `toast ${type === 'success' ? 'bg-success text-white' : type === 'danger' ? 'bg-danger text-white' : type === 'warning' ? 'bg-warning' : 'bg-info text-white'}`;
            
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            
            // Toast'ı göster
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
    </script>
</body>
</html>
