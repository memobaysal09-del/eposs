<?php
// admin.php - Yönetici Paneli
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>

    <title>Restaurant Sipariş Sistemi - Yönetici Paneli</title>
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
        .admin-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .quick-actions {
            border-left: 4px solid #0d6efd;
            padding-left: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Yönetici Paneli</h1>
            <p class="lead">Restaurant yönetim sistemi - Tüm kontroller burada</p>
        </div>
    </div>

    <div class="container">
        <!-- Hızlı İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-chair"></i>
                    </div>
                    <h3 id="totalTables">0</h3>
                    <p class="mb-0">Toplam Masa</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3 id="totalProducts">0</h3>
                    <p class="mb-0">Toplam Ürün</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <h3 id="totalCategories">0</h3>
                    <p class="mb-0">Toplam Kategori</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h3 id="totalOptions">0</h3>
                    <p class="mb-0">Toplam Opsiyon</p>
                </div>
            </div>
        </div>

        <!-- Hızlı Erişim Butonları -->
        <div class="quick-actions">
            <h4><i class="fas fa-bolt me-2"></i>Hızlı Erişim</h4>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-home me-2"></i>Ana Sayfa
                </a>
                <a href="masa_yonetimi.php" class="btn btn-outline-success">
                    <i class="fas fa-plus me-2"></i>Yeni Masa Ekle
                </a>
                <a href="menu_yonetimi.php" class="btn btn-outline-success">
                    <i class="fas fa-plus me-2"></i>Yeni Ürün Ekle
                </a>
                <a href="printer_yonetimi.php" class="btn btn-outline-info">
                    <i class="fas fa-print me-2"></i>Yazıcı Ayarları
                </a>
            </div>
        </div>

        <!-- Yönetim Modülleri -->
        <div class="row">
            <!-- Masa Yönetimi -->
            <div class="col-md-4 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chair me-2"></i>Masa Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Masaları oluşturun, düzenleyin ve yönetin. Masa durumlarını takip edin.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Masa ekle/düzenle/sil</li>
                            <li class="list-group-item">Masa durumlarını görüntüle</li>
                            <li class="list-group-item">Masa özelleştirme</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="masa_yonetimi.php" class="btn btn-primary w-100">
                            <i class="fas fa-external-link-alt me-2"></i>Masa Yönetimine Git
                        </a>
                    </div>
                </div>
            </div>

            <!-- Menü Yönetimi -->
            <div class="col-md-4 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-utensils me-2"></i>Menü Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Ürünleri ve kategorileri yönetin. Fiyat güncellemeleri yapın.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Kategori işlemleri</li>
                            <li class="list-group-item">Ürün ekle/düzenle/sil</li>
                            <li class="list-group-item">Toplu ürün ekleme</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="menu_yonetimi.php" class="btn btn-success w-100">
                            <i class="fas fa-external-link-alt me-2"></i>Menü Yönetimine Git
                        </a>
                    </div>
                </div>
            </div>

            <!-- Opsiyon Yönetimi -->
            <div class="col-md-4 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Opsiyon Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Ürün opsiyonlarını yönetin. Özelleştirme seçenekleri ekleyin.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Opsiyon grupları</li>
                            <li class="list-group-item">Opsiyon fiyatlandırması</li>
                            <li class="list-group-item">Ürün-opsiyon ilişkileri</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="opsiyon_yonetimi.php" class="btn btn-info w-100">
                            <i class="fas fa-external-link-alt me-2"></i>Opsiyon Yönetimine Git
                        </a>
                    </div>
                </div>
            </div>

            <!-- Yazıcı Yönetimi -->
            <div class="col-md-4 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-print me-2"></i>Yazıcı Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Fiş yazdırma ayarlarını yapılandırın. Şirket bilgilerini düzenleyin.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Fiş özelleştirme</li>
                            <li class="list-group-item">Logo ayarları</li>
                            <li class="list-group-item">Yazıcı seçenekleri</li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="printer_yonetimi.php" class="btn btn-warning w-100">
                            <i class="fas fa-external-link-alt me-2"></i>Yazıcı Yönetimine Git
                        </a>
                    </div>
                </div>
            </div>

            <!-- Raporlar -->
            <div class="col-md-4 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Raporlar ve İstatistikler</h5>
                    </div>
                    <div class="card-body">
                        <p>Sistem istatistiklerini görüntüleyin. Performans raporları alın.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Satış raporları</li>
                            <li class="list-group-item">Masa doluluk oranları</li>
                            <li class="list-group-item">Ürün performansı</li>
                        </ul>
                    </div>
                    <div class="card-footer">
					 <a href="reports.php" class="btn btn-dark w-100">
					  <i class="fas fa-chart-line me-2"></i>Raporları Görüntüle 
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sistem Ayarları -->
            <div class="col-md-4 mb-4">
                <div class="card admin-card h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Sistem Ayarları</h5>
                    </div>
                    <div class="card-body">
                        <p>Sistem genel ayarlarını yapılandırın. Yedekleme işlemleri yapın.</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Genel ayarlar</li>
                            <li class="list-group-item">Veri yedekleme</li>
                            <li class="list-group-item">Sistem bakımı</li>
                        </ul>
                    </div>
                    <div class="card-footer">
										 <a href="system_settings.php" class="btn btn-secondary w-100">
					 <i class="fas fa-cogs me-2"></i>Ayarları Aç
                        </a>
					

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });

        // İstatistikleri yükle
        function loadStatistics() {
            // Masaları yükle
            fetch('db.php?action=get_tables')
                .then(response => response.json())
                .then(tables => {
                    document.getElementById('totalTables').textContent = tables.length;
                });

            // Kategorileri yükle
            fetch('db.php?action=get_categories')
                .then(response => response.json())
                .then(categories => {
                    document.getElementById('totalCategories').textContent = categories.length;
                });

            // Tüm ürünleri yükle
            fetch('db.php?action=get_all_products')
                .then(response => response.json())
                .then(products => {
                    document.getElementById('totalProducts').textContent = products.length;
                });

            // Opsiyon gruplarını yükle
            fetch('db.php?action=get_option_groups')
                .then(response => response.json())
                .then(groups => {
                    // Toplam opsiyon sayısını hesapla
                    let totalOptions = 0;
                    const groupPromises = groups.map(group => {
                        return fetch(`db.php?action=get_options&group_id=${group.id}`)
                            .then(response => response.json())
                            .then(options => {
                                totalOptions += options.length;
                            });
                    });

                    Promise.all(groupPromises).then(() => {
                        document.getElementById('totalOptions').textContent = totalOptions;
                    });
                });
        }

        // Raporları göster (şimdilik basit bir alert)
        function showReports() {
            alert('Raporlar modülü yakında eklenecek! Şu an geliştirme aşamasındadır.');
        }

        // Ayarları göster (şimdilik basit bir alert)
        function showSettings() {
            alert('Sistem ayarları modülü yakında eklenecek! Şu an geliştirme aşamasındadır.');
        }

        // Sayfayı yenile
        function refreshStats() {
            loadStatistics();
            alert('İstatistikler yenilendi!');
        }
    </script>
</body>
</html>