<?php
// reports.php - Raporlar ve İstatistikler Modülü
require_once 'db.php';

// Rapor silme işlemi
if (isset($_GET['delete_report'])) {
    $report_id = intval($_GET['delete_report']);
    $stmt = $conn->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $stmt->close();
    header("Location: reports.php?deleted=1");
    exit;
}

// Rapor kaydetme tercihini kontrol et (cookie'den)
$save_reports = isset($_COOKIE['save_reports']) ? $_COOKIE['save_reports'] : 'always';
?>
<!DOCTYPE html>
<html lang="tr">
<head>

    <title>Restaurant Sipariş Sistemi - Raporlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .date-filter {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        .report-table {
            font-size: 14px;
        }
        .report-table th {
            background-color: #f8f9fa;
        }
        .back-btn {
            margin-right: 10px;
        }
        .save-options {
            background-color: #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .report-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1>Raporlar ve İstatistikler</h1>
                    <p class="lead">Restaurant performans analizleri ve satış raporları</p>
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
        <!-- Tarih Filtreleme -->
        <div class="row">
            <div class="col-12">
                <div class="date-filter">
                    <h4><i class="fas fa-filter me-2"></i>Tarih Aralığı Seçin</h4>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label for="startDate">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" id="startDate">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="endDate">Bitiş Tarihi</label>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="reportType">Rapor Türü</label>
                            <select class="form-select" id="reportType">
                                <option value="daily">Günlük</option>
                                <option value="weekly">Haftalık</option>
                                <option value="monthly">Aylık</option>
                                <option value="yearly">Yıllık</option>
                                <option value="custom">Özel Aralık</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="loadReports()">
                                <i class="fas fa-sync-alt me-2"></i>Raporu Yükle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kayıt Seçenekleri -->
        <div class="row">
            <div class="col-12">
                <div class="save-options">
                    <h4><i class="fas fa-database me-2"></i>Rapor Kayıt Seçenekleri</h4>
                    <p class="text-muted mb-3">Bu ayar sipariş tamamlandığında verilerin veritabanına kaydedilip kaydedilmeyeceğini belirler. Raporların doğru çalışması için verilerin kaydedilmesi gerekir.</p>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="saveOption" id="saveAlways" value="always" <?php echo $save_reports == 'always' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="saveAlways">
                            <strong>Her Zaman Kaydet</strong><br>
                            <small class="text-muted">Tüm siparişler otomatik olarak kaydedilir (Önerilen)</small>
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="saveOption" id="saveNever" value="never" <?php echo $save_reports == 'never' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="saveNever">
                            <strong>Hiç Kaydetme</strong><br>
                            <small class="text-muted">Siparişler sadece geçici olarak saklanır, raporlar çalışmaz</small>
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="saveOption" id="saveAsk" value="ask" <?php echo $save_reports == 'ask' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="saveAsk">
                            <strong>Her Seferinde Sor</strong><br>
                            <small class="text-muted">Her sipariş için kaydetme onayı istenecek.</small>
                        </label>
                    </div>
                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="savePreference()">
                        <i class="fas fa-save me-1"></i>Tercihi Kaydet
                    </button>
                </div>
            </div>
        </div>

        <!-- Rapor İşlemleri -->
        <div class="row">
            <div class="col-12">
                <div class="report-actions">
                    <button class="btn btn-success" onclick="saveReport()">
                        <i class="fas fa-save me-2"></i>Raporu Kaydet
                    </button>
                    <button class="btn btn-info" onclick="printReport()">
                        <i class="fas fa-print me-2"></i>Yazdır
                    </button>
                    <button class="btn btn-warning" onclick="exportReport()">
                        <i class="fas fa-download me-2"></i>Dışa Aktar
                    </button>
                    <div class="ms-auto">
                        <button class="btn btn-danger" onclick="clearOldReports()">
                            <i class="fas fa-trash me-2"></i>Eski Raporları Temizle
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- İstatistik Kartları -->
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-pound-sign"></i>
                    </div>
                    <h3 id="totalRevenue">0£</h3>
                    <p class="mb-0">Toplam Gelir</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3 id="totalOrders">0</h3>
                    <p class="mb-0">Toplam Sipariş</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3 id="totalProductsSold">0</h3>
                    <p class="mb-0">Satılan Ürün</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center p-3">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 id="avgOrderValue">0£</h3>
                    <p class="mb-0">Ort. Sipariş Değeri</p>
                </div>
            </div>
        </div>

        <!-- Ödeme Yöntemleri -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Ödeme Yöntemleri</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-cash-register me-2"></i>Kasa İşlemleri</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="cashDrawerChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafikler -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Aylık Gelir Grafiği</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Kategori Dağılımı</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Masa Doluluk Oranları -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chair me-2"></i>Masa Doluluk Oranları</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="tableOccupancyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ürün Performans Tablosu -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Ürün Performans Raporu</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover report-table">
                                <thead>
                                    <tr>
                                        <th>Ürün Adı</th>
                                        <th>Satış Adedi</th>
                                        <th>Toplam Gelir</th>
                                        <th>Ortalama Puan</th>
                                        <th>Popülerlik</th>
                                    </tr>
                                </thead>
                                <tbody id="productPerformanceTable">
                                    <tr>
                                        <td colspan="5" class="text-center">Rapor yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Satış Raporları Tablosu -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Detaylı Satış Raporları</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover report-table">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Sipariş Sayısı</th>
                                        <th>Toplam Gelir</th>
                                        <th>Ort. Sipariş Değeri</th>
                                        <th>Nakit</th>
                                        <th>Kart</th>
                                        <th>En Çok Satan Ürün</th>
                                    </tr>
                                </thead>
                                <tbody id="salesReportTable">
                                    <tr>
                                        <td colspan="7" class="text-center">Rapor yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kayıtlı Raporlar -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Kayıtlı Raporlar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover report-table">
                                <thead>
                                    <tr>
                                        <th>Rapor Adı</th>
                                        <th>Tür</th>
                                        <th>Tarih Aralığı</th>
                                        <th>Oluşturulma Tarihi</th>
                                        <th>Toplam Gelir</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody id="savedReportsTable">
                                    <tr>
                                        <td colspan="6" class="text-center">Raporlar yükleniyor...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kaydetme Onay Modal -->
    <div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Raporu Kaydet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reportName" class="form-label">Rapor Adı</label>
                        <input type="text" class="form-control" id="reportName" placeholder="Rapor için bir isim girin">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="savePreferenceCheck">
                        <label class="form-check-label" for="savePreferenceCheck">
                            Bu seçimi hatırla
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="confirmSaveReport()">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Grafik nesneleri
        let revenueChart = null;
        let categoryChart = null;
        let tableOccupancyChart = null;
        let paymentMethodChart = null;
        let cashDrawerChart = null;

        // Rapor verileri
        let currentReportData = null;

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            // Varsayılan tarih aralığını ayarla (son 30 gün)
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            
            document.getElementById('startDate').valueAsDate = startDate;
            document.getElementById('endDate').valueAsDate = endDate;
            
            // Raporları yükle
            loadReports();
            loadSavedReports();
        });

        // Raporları yükle
        function loadReports() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const reportType = document.getElementById('reportType').value;
            
            // Yükleme durumunu göster
            document.getElementById('productPerformanceTable').innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Rapor yükleniyor...</td></tr>';
            document.getElementById('salesReportTable').innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Rapor yükleniyor...</td></tr>';
            
            console.log('[v0] Loading reports with params:', {startDate, endDate, reportType});
            
            fetch(`db.php?action=get_reports&start_date=${startDate}&end_date=${endDate}&report_type=${reportType}`)
                .then(response => {
                    console.log('[v0] Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Sunucu hatası: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('[v0] Received data:', data);
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    currentReportData = data;
                    
                    // İstatistikleri yükle
                    if (data.stats) {
                        loadStats(data.stats);
                    } else {
                        console.log('[v0] No stats data received');
                        // Set default values
                        loadStats({
                            totalRevenue: '0.00',
                            totalOrders: 0,
                            totalProductsSold: 0,
                            avgOrderValue: '0.00'
                        });
                    }
                    
                    // Grafikleri yükle
                    if (data.charts) {
                        loadCharts(data.charts, reportType);
                    } else {
                        console.log('[v0] No charts data received');
                    }
                    
                    // Tabloları yükle
                    if (data.tables) {
                        loadTables(data.tables);
                    } else {
                        console.log('[v0] No tables data received');
                        // Show empty state
                        document.getElementById('productPerformanceTable').innerHTML = '<tr><td colspan="5" class="text-center text-muted">Seçilen tarih aralığında veri bulunamadı</td></tr>';
                        document.getElementById('salesReportTable').innerHTML = '<tr><td colspan="7" class="text-center text-muted">Seçilen tarih aralığında veri bulunamadı</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('[v0] Report loading error:', error);
                    
                    // Show error message
                    const errorMsg = `<tr><td colspan="5" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Rapor yüklenirken hata oluştu: ${error.message}</td></tr>`;
                    const errorMsg7 = `<tr><td colspan="7" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Rapor yüklenirken hata oluştu: ${error.message}</td></tr>`;
                    
                    document.getElementById('productPerformanceTable').innerHTML = errorMsg;
                    document.getElementById('salesReportTable').innerHTML = errorMsg7;
                    
                    // Reset stats to zero
                    loadStats({
                        totalRevenue: '0.00',
                        totalOrders: 0,
                        totalProductsSold: 0,
                        avgOrderValue: '0.00'
                    });
                });
        }

        // İstatistikleri yükle
        function loadStats(stats) {
            document.getElementById('totalRevenue').textContent = '£' + stats.totalRevenue;
            document.getElementById('totalOrders').textContent = stats.totalOrders;
            document.getElementById('totalProductsSold').textContent = stats.totalProductsSold;
            document.getElementById('avgOrderValue').textContent = '£' + stats.avgOrderValue;
        }

        // Grafikleri yükle
        function loadCharts(charts, reportType) {
            // Gelir grafiği
            if (revenueChart) revenueChart.destroy();
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: charts.revenue.labels,
                    datasets: [{
                        label: 'Gelir (£)',
                        data: charts.revenue.data,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Gelir Trendi'
                        }
                    }
                }
            });

            // Kategori dağılım grafiği
            if (categoryChart) categoryChart.destroy();
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: charts.categories.labels,
                    datasets: [{
                        data: charts.categories.data,
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1',
                            '#6610f2', '#e83e8c', '#fd7e14', '#20c997', '#17a2b8'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Masa doluluk grafiği
            if (tableOccupancyChart) tableOccupancyChart.destroy();
            const tableCtx = document.getElementById('tableOccupancyChart').getContext('2d');
            tableOccupancyChart = new Chart(tableCtx, {
                type: 'bar',
                data: {
                    labels: charts.tableOccupancy.labels,
                    datasets: [
                        {
                            label: 'Dolu Masalar',
                            data: charts.tableOccupancy.occupied,
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'Boş Masalar',
                            data: charts.tableOccupancy.available,
                            backgroundColor: '#dc3545'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Masa Sayısı'
                            }
                        }
                    }
                }
            });

            // Ödeme yöntemleri grafiği
            if (paymentMethodChart) paymentMethodChart.destroy();
            const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
            paymentMethodChart = new Chart(paymentCtx, {
                type: 'pie',
                data: {
                    labels: charts.paymentMethods.labels,
                    datasets: [{
                        data: charts.paymentMethods.data,
                        backgroundColor: [
                            '#28a745', '#007bff', '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: 'Ödeme Yöntemleri Dağılımı'
                        }
                    }
                }
            });

            // Kasa işlemleri grafiği
            if (cashDrawerChart) cashDrawerChart.destroy();
            const cashCtx = document.getElementById('cashDrawerChart').getContext('2d');
            cashDrawerChart = new Chart(cashCtx, {
                type: 'bar',
                data: {
                    labels: charts.cashDrawer.labels,
                    datasets: [{
                        label: 'Kasa Açılma Sayısı',
                        data: charts.cashDrawer.data,
                        backgroundColor: '#17a2b8'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Günlük Kasa Açılma Sayısı'
                        }
                    }
                }
            });
        }

        // Tabloları yükle
        function loadTables(tables) {
            // Ürün performans tablosu
            let productHtml = '';
            tables.products.forEach(product => {
                let popularity = '';
                let badgeClass = '';
                
                if (product.popularity === 'Çok Yüksek') {
                    badgeClass = 'bg-danger';
                } else if (product.popularity === 'Yüksek') {
                    badgeClass = 'bg-success';
                } else if (product.popularity === 'Orta') {
                    badgeClass = 'bg-warning';
                } else {
                    badgeClass = 'bg-secondary';
                }
                
                productHtml += `
                    <tr>
                        <td>${product.name}</td>
                        <td>${product.sales}</td>
                        <td>${product.revenue}£</td>
                        <td>${product.rating}</td>
                        <td><span class="badge ${badgeClass}">${product.popularity}</span></td>
                    </tr>
                `;
            });
            document.getElementById('productPerformanceTable').innerHTML = productHtml;
            
            // Satış raporları tablosu
            let salesHtml = '';
            tables.sales.forEach(sale => {
                salesHtml += `
                    <tr>
                        <td>${sale.date}</td>
                        <td>${sale.orders}</td>
                        <td>${sale.revenue}£</td>
                        <td>${sale.avgOrder}£</td>
                        <td>${sale.cash}£</td>
                        <td>${sale.card}£</td>
                        <td>${sale.topProduct}</td>
                    </tr>
                `;
            });
            document.getElementById('salesReportTable').innerHTML = salesHtml;
        }

        // Kayıtlı raporları yükle
        function loadSavedReports() {
            fetch('db.php?action=get_saved_reports')
                .then(response => response.json())
                .then(data => {
                    let reportsHtml = '';
                    
                    if (data.length === 0) {
                        reportsHtml = '<tr><td colspan="6" class="text-center">Kayıtlı rapor bulunamadı</td></tr>';
                    } else {
                        data.forEach(report => {
                            reportsHtml += `
                                <tr>
                                    <td>${report.name}</td>
                                    <td>${report.type}</td>
                                    <td>${report.date_range}</td>
                                    <td>${report.created_at}</td>
                                    <td>${report.total_revenue}£</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewReport(${report.id})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="exportReport(${report.id})">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteReport(${report.id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    
                    document.getElementById('savedReportsTable').innerHTML = reportsHtml;
                })
                .catch(error => {
                    console.error('Kayıtlı raporlar yüklenirken hata:', error);
                });
        }

        // Raporu kaydet
        function saveReport() {
            const saveOption = document.querySelector('input[name="saveOption"]:checked').value;
            
            if (saveOption === 'never') {
                alert('Rapor kaydetme özelliği devre dışı bırakılmış.');
                return;
            }
            
            if (saveOption === 'ask') {
                // Modal göster
                const modal = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
                modal.show();
            } else {
                // Doğrudan kaydet
                confirmSaveReport();
            }
        }

        // Rapor kaydetmeyi onayla
        function confirmSaveReport() {
            const reportName = document.getElementById('reportName').value || 'İsimsiz Rapor';
            const rememberPreference = document.getElementById('savePreferenceCheck').checked;
            
            if (rememberPreference) {
                const saveOption = document.querySelector('input[name="saveOption"]:checked').value;
                const expiryDate = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString();
                document.cookie = `save_reports=${saveOption}; expires=${expiryDate}; path=/`;
            }
            
            // Raporu veritabanına kaydet
            const formData = new FormData();
            formData.append('name', reportName);
            formData.append('type', document.getElementById('reportType').value);
            formData.append('start_date', document.getElementById('startDate').value);
            formData.append('end_date', document.getElementById('endDate').value);
            formData.append('data', JSON.stringify(currentReportData));
            
            fetch('db.php?action=save_report', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Rapor başarıyla kaydedildi.');
                    loadSavedReports();
                    
                    // Modal'ı kapat
                    const modal = bootstrap.Modal.getInstance(document.getElementById('saveConfirmModal'));
                    modal.hide();
                } else {
                    alert('Rapor kaydedilirken hata oluştu: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Rapor kaydetme hatası:', error);
                alert('Rapor kaydedilirken hata oluştu.');
            });
        }

        // Tercihi kaydet
        function savePreference() {
            const saveOption = document.querySelector('input[name="saveOption"]:checked').value;
            const expiryDate = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString();
            document.cookie = `save_reports=${saveOption}; expires=${expiryDate}; path=/`;
            
            let message = 'Tercihiniz kaydedildi. ';
            if (saveOption === 'always') {
                message += 'Artık tüm siparişler otomatik olarak kaydedilecek.';
            } else if (saveOption === 'never') {
                message += 'Siparişler kaydedilmeyecek, raporlar çalışmayacak.';
            } else {
                message += 'Her sipariş için kaydetme onayı istenecek.';
            }
            
            alert(message);
        }

        // Raporu görüntüle
        function viewReport(reportId) {
            window.open(`view_report.php?id=${reportId}`, '_blank');
        }

        // Raporu sil
        function deleteReport(reportId) {
            if (confirm('Bu raporu silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                window.location.href = `reports.php?delete_report=${reportId}`;
            }
        }

        // Eski raporları temizle
        function clearOldReports() {
            if (confirm('30 günden eski tüm raporları silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                fetch('db.php?action=clear_old_reports', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`${data.deleted_count} rapor silindi.`);
                        loadSavedReports();
                    } else {
                        alert('Raporlar silinirken hata oluştu: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Rapor silme hatası:', error);
                    alert('Raporlar silinirken hata oluştu.');
                });
            }
        }

        // Raporu yazdır
        function printReport() {
            window.print();
        }

        // Raporu dışa aktar
        function exportReport(reportId = null) {
            if (reportId) {
                // Belirli bir raporu dışa aktar
                window.open(`export_report.php?id=${reportId}`, '_blank');
            } else {
                // Mevcut raporu dışa aktar
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const reportType = document.getElementById('reportType').value;
                
                window.open(`export_report.php?start_date=${startDate}&end_date=${endDate}&report_type=${reportType}`, '_blank');
            }
        }
    </script>
</body>
</html>
