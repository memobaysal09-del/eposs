<!DOCTYPE html>
<html lang="tr">
<head>

    <title>Restaurant Sipariş Sistemi - Yazıcı Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* isolate company name sizing in preview */ 
#receiptPreview #previewCompanyName { line-height:1.2; }
#receiptPreview *:not(#previewCompanyName) { font-size: inherit; } 
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
        .preview-container {
            border: 1px dashed #ccc;
            padding: 20px;
            min-height: 400px;
            background-color: white;
        }
        .logo-preview {
            max-width: 200px;
            max-height: 100px;
            margin-bottom: 15px;
        }
        .settings-card {
            margin-bottom: 20px;
        }
        .receipt-item {
            padding: 5px 0;
            border-bottom: 1px dotted #eee;
        }
        .receipt-total {
            font-weight: bold;
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .nav-tabs .nav-link.active {
            background-color: #f8f9fa;
            border-bottom-color: #f8f9fa;
            font-weight: bold;
        }
        .tab-content {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .connection-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .settings-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #fff;
        }
        .settings-section h4 {
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #dc3545;
        }
        .font-preview {
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-top: 10px;
            min-height: 50px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Yazıcı Yönetimi</h1>
                <div>
                    <a href="index.php" class="btn btn-light me-2">
                        <i class="fas fa-home me-2"></i>Masalara Dön
                    </a>
                    <a href="menu_yonetimi.php" class="btn btn-light">
                        <i class="fas fa-utensils me-2"></i>Menü Yönetimi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="loading" id="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
            <p class="mt-2">İşlem yapılıyor, lütfen bekleyin...</p>
        </div>

        <div class="row">
            <!-- Sol Taraf: Logo ve Fiş Ayarları -->
            <div class="col-md-6">
                <!-- Logo Ayarları -->
                <div class="settings-section">
                    <h4><i class="fas fa-image me-2"></i>Logo Ayarları</h4>
                    <div class="mb-3">
                        <label for="logoUpload" class="form-label">Logo Yükle</label>
                        <input class="form-control" type="file" id="logoUpload" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo Hizalaması</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="logoAlignment" id="logoLeft" value="left" autocomplete="off">
                            <label class="btn btn-outline-primary" for="logoLeft"><i class="fas fa-align-left"></i></label>

                            <input type="radio" class="btn-check" name="logoAlignment" id="logoCenter" value="center" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="logoCenter"><i class="fas fa-align-center"></i></label>

                            <input type="radio" class="btn-check" name="logoAlignment" id="logoRight" value="right" autocomplete="off">
                            <label class="btn btn-outline-primary" for="logoRight"><i class="fas fa-align-right"></i></label>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="logoWidth" class="form-label">Logo Genişliği (px)</label>
                            <input type="number" class="form-control" id="logoWidth" min="50" max="500" value="200">
                        </div>
                        <div class="col-md-6">
                            <label for="logoHeight" class="form-label">Logo Yüksekliği (px)</label>
                            <input type="number" class="form-control" id="logoHeight" min="50" max="500" value="100">
                        </div>
                    </div>
                    <button class="btn btn-success" id="saveLogoSettings">
                        <i class="fas fa-save me-2"></i>Logo Ayarlarını Kaydet
                    </button>
                </div>

                <!-- Fiş Ayarları -->
                <div class="settings-section">
                    <h4><i class="fas fa-receipt me-2"></i>Fiş Ayarları</h4>
                    <div class="mb-3">
                        <label for="companyName" class="form-label">İşletme Adı</label>
                        <input type="text" class="form-control" id="companyName">
                    </div>
                    
                    <!-- Yeni: İşletme Adı Yazı Tipi ve Boyutu -->
                    <div class="mb-3">
                        <label for="companyNameFont" class="form-label">İşletme Adı Yazı Tipi</label>
                        <select class="form-select" id="companyNameFont">
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="'Times New Roman', serif">Times New Roman</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="Verdana, Geneva, sans-serif">Verdana</option>
                            <option value="'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">Segoe UI</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                            <option value="Impact, Haettenschweiler, sans-serif">Impact</option>
                            <option value="'Comic Sans MS', cursive">Comic Sans MS</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="companyNameSize" class="form-label">İşletme Adı Yazı Boyutu</label>
                        <!-- Reduced font size range and improved alignment -->
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" class="form-range flex-grow-1" id="companyNameSize" min="12" max="30" value="14" step="1">
                            <span id="companyNameSizeValue" class="text-nowrap" style="min-width: 40px;">14px</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <!-- Adjusted preview font size -->
                        <div class="font-preview" id="companyNamePreview" style="font-size: 14px;">
                            İşletme Adı Önizleme
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="companyAddress" class="form-label">Adres</label>
                        <textarea class="form-control" id="companyAddress" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="companyPhone" class="form-label">Telefon</label>
                        <input type="text" class="form-control" id="companyPhone">
                    </div>
                    <div class="mb-3">
                        <label for="footerText" class="form-label">Alt Bilgi Metni</label>
                        <textarea class="form-control" id="footerText" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fiş Genişliği</label>
                        <select class="form-select" id="receiptWidth">
                         
                            <option value="80">80mm (Geniş)</option>
                        </select>
                    </div>
                    
                    <!-- Added order items font size setting -->
                    <div class="mb-3">
                        <label for="orderItemsFontSize" class="form-label">Sipariş Öğeleri Yazı Boyutu</label>
                        <div class="d-flex align-items-center">
                            <input type="range" class="form-range me-2" id="orderItemsFontSize" min="10" max="20" value="12" step="1">
                            <span id="orderItemsFontSizeValue">12px</span>
                        </div>
                    </div>
                    
                    <button class="btn btn-success" id="saveReceiptSettings">
                        <i class="fas fa-save me-2"></i>Fiş Ayarlarını Kaydet
                    </button>
                </div>
            </div>

            <!-- Sağ Taraf: Fiş Önizleme -->
            <div class="col-md-6">
                <div class="settings-section">
                    <h4><i class="fas fa-print me-2"></i>Fiş Önizleme</h4>
                    <div class="preview-container" id="receiptPreview">
                        <div class="text-center">
                            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSFBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxPR08gT0tVPC90ZXh0Pgo8L3N2Zz4=" id="previewLogo" class="logo-preview d-none">
                            <h4 id="previewCompanyName" style="font-family: Arial, sans-serif; font-size: 18px;">Restaurant Adı</h4>
                            <p id="previewAddress">Örnek Mah. Örnek Cad. No:123</p>
                            <p id="previewPhone">0 (212) 345 67 89</p>
                            <hr>
                            <p>Fiş No: #12345<br>Tarih: <?php echo date("d.m.Y H:i"); ?></p>
                            <hr>
                        </div>
                        
                        <div class="receipt-items">
                            <div class="receipt-item d-flex justify-content-between">
                                <span>2x Mercimek Çorbası</span>
                                <span>50.00₺</span>
                            </div>
                            <div class="receipt-item d-flex justify-content-between">
                                <span>1x Kebap</span>
                                <span>120.00₺</span>
                            </div>
                            <div class="receipt-item d-flex justify-content-between">
                                <span>2x Ayran</span>
                                <span>30.00₺</span>
                            </div>
                            <div class="receipt-item d-flex justify-content-between">
                                <span>1x Baklava</span>
                                <span>60.00₺</span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="receipt-total d-flex justify-content-between">
                            <span>TOPLAM:</span>
                            <span>260.00₺</span>
                        </div>
                        
                        <div class="text-center mt-3">
                            <hr>
                            <p id="previewFooter">Teşekkür Ederiz, Yine Bekleriz!</p>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary" id="testPrint">
                            <i class="fas fa-print me-2"></i>Test Yazdır
                        </button>
                        <a href="Print.php?test=true" target="_blank" class="btn btn-info">
                            <i class="fas fa-external-link-alt me-2"></i>Print.php'yi Görüntüle
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bağlantı Ayarları (Alt Kısımda) -->
        <div class="settings-section mt-4">
            <h4><i class="fas fa-wifi me-2"></i>Bağlantı Ayarları</h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-bluetooth fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Bluetooth</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input connection-toggle" type="checkbox" id="bluetoothToggle" data-connection="bluetooth">
                                <label class="form-check-label" for="bluetoothToggle">Aktif</label>
                            </div>
                            <div class="mb-3">
                                <label for="bluetoothMac" class="form-label">MAC Adresi</label>
                                <input type="text" class="form-control" id="bluetoothMac" placeholder="00:1A:7D:DA:71:13">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-wifi fa-3x text-success mb-3"></i>
                            <h5 class="card-title">WiFi</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input connection-toggle" type="checkbox" id="wifiToggle" data-connection="wifi" checked>
                                <label class="form-check-label" for="wifiToggle">Aktif</label>
                            </div>
                            <div class="mb-3">
                                <label for="printerIp" class="form-label">IP Adresi</label>
                                <input type="text" class="form-control" id="printerIp" placeholder="192.168.1.100">
                            </div>
                            <div class="mb-3">
                                <label for="printerPort" class="form-label">Port</label>
                                <input type="number" class="form-control" id="printerPort" value="9100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-network-wired fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Ethernet</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input connection-toggle" type="checkbox" id="ethernetToggle" data-connection="ethernet">
                                <label class="form-check-label" for="ethernetToggle">Aktif</label>
                            </div>
                            <div class="mb-3">
                                <label for="ethernetIp" class="form-label">IP Adresi</label>
                                <input type="text" class="form-control" id="ethernetIp" placeholder="192.168.1.101">
                            </div>
                            <div class="mb-3">
                                <label for="ethernetPort" class="form-label">Port</label>
                                <input type="number" class="form-control" id="ethernetPort" value="9100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-usb fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">USB</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input connection-toggle" type="checkbox" id="usbToggle" data-connection="usb">
                                <label class="form-check-label" for="usbToggle">Aktif</label>
                            </div>
                            <div class="mb-3">
                                <label for="usbPrinterName" class="form-label">Yazıcı Adı</label>
                                <input type="text" class="form-control" id="usbPrinterName" placeholder="Yazıcı adını girin...">
                                <small class="form-text text-muted">Örnek: Epson TM-T20</small>
                            </div>
                            <button class="btn btn-outline-secondary btn-sm" id="refreshPrinters">
                                <i class="fas fa-sync-alt me-1"></i>Yazıcıları Listele
                            </button>
                            <div id="printerList" class="mt-2" style="display: none;">
                                <select class="form-select form-select-sm" id="availablePrinters">
                                    <option value="">Listeden seçin...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button class="btn btn-info me-2" id="testConnection">
                    <i class="fas fa-wifi me-2"></i>Bağlantıyı Test Et
                </button>
                <button class="btn btn-success" id="saveConnectionSettings">
                    <i class="fas fa-save me-2"></i>Bağlantı Ayarlarını Kaydet
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global değişkenler
        let printerSettings = {};
        let currentLogoPath = '';

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            updateCompanyNamePreview();
// Ayarları yükle
            loadSettings();
            
            // Event listener'ları ekle
            document.getElementById('testConnection').addEventListener('click', testConnection);
            document.getElementById('saveConnectionSettings').addEventListener('click', saveConnectionSettings);
            document.getElementById('logoUpload').addEventListener('change', handleLogoUpload);
            document.getElementById('saveLogoSettings').addEventListener('click', saveLogoSettings);
            document.getElementById('saveReceiptSettings').addEventListener('click', saveReceiptSettings);
            document.getElementById('testPrint').addEventListener('click', testPrint);
            document.getElementById('refreshPrinters').addEventListener('click', refreshPrinterList);
            
            document.querySelectorAll('.connection-toggle').forEach(toggle => {
                toggle.addEventListener('change', handleConnectionToggle);
            });
            
            document.getElementById('availablePrinters').addEventListener('change', function() {
                if (this.value) {
                    document.getElementById('usbPrinterName').value = this.value;
                }
            });
            
            // Ayarlar değiştiğinde önizlemeyi güncelle
            document.getElementById('companyName').addEventListener('input', updatePreview);
            document.getElementById('companyAddress').addEventListener('input', updatePreview);
            document.getElementById('companyPhone').addEventListener('input', updatePreview);
            document.getElementById('footerText').addEventListener('input', updatePreview);
            document.getElementById('receiptWidth').addEventListener('change', updatePreview);
            document.getElementById('logoWidth').addEventListener('input', updatePreview);
            document.getElementById('logoHeight').addEventListener('input', updatePreview);
            
            // Logo hizalama değiştiğinde
            document.querySelectorAll('input[name="logoAlignment"]').forEach(radio => {
                radio.addEventListener('change', updatePreview);
            });
            
            // Yazı tipi ve boyutu değiştiğinde
            document.getElementById('companyNameFont').addEventListener('change', updateCompanyNamePreview);
            document.getElementById('companyNameSize').addEventListener('input', updateCompanyNamePreview);
            
            document.getElementById('orderItemsFontSize').addEventListener('input', function() {
                const size = this.value;
                document.getElementById('orderItemsFontSizeValue').textContent = size + 'px';
                updatePreview();
            });
        });

        function handleConnectionToggle(event) {
            const clickedToggle = event.target;
            const connectionType = clickedToggle.dataset.connection;
            
            if (clickedToggle.checked) {
                // Diğer tüm toggle'ları kapat
                document.querySelectorAll('.connection-toggle').forEach(toggle => {
                    if (toggle !== clickedToggle) {
                        toggle.checked = false;
                    }
                });
                
                // Aktif bağlantı tipini göster
                showActiveConnection(connectionType);
            } else {
                // Hiçbiri seçili değilse WiFi'yi varsayılan yap
                document.getElementById('wifiToggle').checked = true;
                showActiveConnection('wifi');
            }
        }
        
        function showActiveConnection(type) {
            // Tüm kartların border'ını temizle
            document.querySelectorAll('.settings-section .card').forEach(card => {
                card.style.border = '';
                card.style.boxShadow = '';
            });
            
            // Aktif kartı vurgula
            const activeCard = document.querySelector(`#${type}Toggle`).closest('.card');
            if (activeCard) {
                activeCard.style.border = '2px solid #28a745';
                activeCard.style.boxShadow = '0 0 10px rgba(40, 167, 69, 0.3)';
            }
        }

        function refreshPrinterList() {
            showLoading(true);
            
            fetch('get_printers.php')
                .then(response => response.json())
                .then(data => {
                    const printerListDiv = document.getElementById('printerList');
                    const select = document.getElementById('availablePrinters');
                    
                    if (Array.isArray(data) && data.length > 0) {
                        select.innerHTML = '<option value="">Listeden seçin...</option>';
                        data.forEach(printer => {
                            const option = document.createElement('option');
                            option.value = printer;
                            option.textContent = printer;
                            select.appendChild(option);
                        });
                        printerListDiv.style.display = 'block';
                        alert(`${data.length} yazıcı bulundu!`);
                    } else {
                        printerListDiv.style.display = 'none';
                        alert('Hiç yazıcı bulunamadı. Manuel olarak yazıcı adını girebilirsiniz.');
                    }
                })
                .catch(error => {
                    console.error('Yazıcı listesi alınamadı:', error);
                    document.getElementById('printerList').style.display = 'none';
                    alert('Yazıcı listesi alınamadı. Manuel olarak yazıcı adını girebilirsiniz.');
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        // İşletme adı yazı tipi önizlemesini güncelle
        function updateCompanyNamePreview() {
            const font = document.getElementById('companyNameFont').value;
            const size = document.getElementById('companyNameSize').value;
            
            // Önizleme kutusunu güncelle
            const previewBox = document.getElementById('companyNamePreview');
            previewBox.style.fontFamily = font;
            previewBox.style.fontSize = size + 'px';
            
            // Boyut değerini göster
            document.getElementById('companyNameSizeValue').textContent = size + 'px';
            
            // Fiş önizlemesini güncelle
            updatePreview();
        }

// Logo yükleme işlemini güncelle
function handleLogoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    showLoading(true);
    
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('action', 'upload_logo'); // action parametresi ekle
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentLogoPath = data.logo_path;
            const previewLogo = document.getElementById('previewLogo');
            previewLogo.src = data.logo_path + '?t=' + new Date().getTime(); // cache önleme
            
            // Logo boyutlarını güncelle
            const logoWidth = document.getElementById('logoWidth').value;
            const logoHeight = document.getElementById('logoHeight').value;
            previewLogo.style.width = logoWidth + 'px';
            previewLogo.style.height = logoHeight + 'px';
            
            previewLogo.classList.remove('d-none');
            updateLogoAlignment();
            
            alert('Logo başarıyla yüklendi! Şimdi "Logo Ayarlarını Kaydet" butonuna tıklayarak kaydedin.');
        } else {
            alert('Logo yüklenirken hata oluştu: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Hata:', error);
        alert('Logo yüklenirken bir hata oluştu.');
    })
    .finally(() => {
        showLoading(false);
    });
}
        // Logo hizalamasını güncelle
        function updateLogoAlignment() {
            const previewLogo = document.getElementById('previewLogo');
            const alignment = document.querySelector('input[name="logoAlignment"]:checked').value;
            
            // Logo konteynerını bul
            const logoContainer = previewLogo.parentElement;
            
            // Önce tüm hizalama class'larını kaldır
            logoContainer.classList.remove('text-start', 'text-center', 'text-end');
            
            // Yeni hizalamayı ekle
            if (alignment === 'left') {
                logoContainer.classList.add('text-start');
            } else if (alignment === 'center') {
                logoContainer.classList.add('text-center');
            } else if (alignment === 'right') {
                logoContainer.classList.add('text-end');
            }
        }

        // Önizlemeyi güncelle
        function updatePreview() {
            // Şirket bilgilerini güncelle
            const companyName = document.getElementById('companyName').value;
            document.getElementById('previewCompanyName').textContent = companyName;
            document.getElementById('previewAddress').textContent = document.getElementById('companyAddress').value;
            document.getElementById('previewPhone').textContent = document.getElementById('companyPhone').value;
            document.getElementById('previewFooter').textContent = document.getElementById('footerText').value;
            
            // İşletme adı yazı tipi ve boyutunu güncelle
            const font = document.getElementById('companyNameFont').value;
            const size = document.getElementById('companyNameSize').value;
            document.getElementById('previewCompanyName').style.fontFamily = font;
            document.getElementById('previewCompanyName').style.fontSize = size + 'px';
            
            // Logo boyutlarını güncelle
            const logoWidth = document.getElementById('logoWidth').value;
            const logoHeight = document.getElementById('logoHeight').value;
            const previewLogo = document.getElementById('previewLogo');
            
            if (previewLogo && !previewLogo.classList.contains('d-none')) {
                previewLogo.style.width = logoWidth + 'px';
                previewLogo.style.height = logoHeight + 'px';
            }
            
            // Logo hizalamasını güncelle
            updateLogoAlignment();
            
            // Fiş genişliğini güncelle
            const width = document.getElementById('receiptWidth').value;
            document.getElementById('receiptPreview').style.width = width + 'mm';
            
            const orderItemsSize = document.getElementById('orderItemsFontSize').value;
            const orderItemsElements = document.querySelectorAll('.receipt-item');
            orderItemsElements.forEach(element => {
                element.style.fontSize = orderItemsSize + 'px';
            });
        }

        // Bağlantıyı test et
        function testConnection() {
            const connectionParams = getConnectionParams();
            
            showLoading(true);
            
            fetch('Print.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'test_connection',
                    ...connectionParams
                })
            })
            .then(response => {
                // Önce içerik tipini kontrol et
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    alert('Bağlantı testi başarılı! ✅\n' + data.message);
                } else {
                    alert('Bağlantı testi başarısız: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bağlantı testi sırasında bir hata oluştu: ' + error.message);
            })
            .finally(() => {
                showLoading(false);
            });
        }

function getConnectionParams() {
    const params = {};
    
    // Sadece aktif bağlantının verilerini gönder
    if (document.getElementById('usbToggle').checked) {
        params.connection_type = 'usb';
        params.printer_name = document.getElementById('usbPrinterName').value.trim();
    }
    else if (document.getElementById('bluetoothToggle').checked) {
        params.connection_type = 'bluetooth';
        params.bluetooth_mac = document.getElementById('bluetoothMac').value.trim();
    }
    else if (document.getElementById('wifiToggle').checked) {
        params.connection_type = 'wifi';
        params.printer_ip = document.getElementById('printerIp').value.trim();
        params.printer_port = document.getElementById('printerPort').value || '9100';
    }
    else if (document.getElementById('ethernetToggle').checked) {
        params.connection_type = 'ethernet';
        params.ethernet_ip = document.getElementById('ethernetIp').value.trim();
        params.ethernet_port = document.getElementById('ethernetPort').value || '9100';
    }
    else {
        // Varsayılan olarak WiFi
        params.connection_type = 'wifi';
        params.printer_ip = document.getElementById('printerIp').value.trim();
        params.printer_port = document.getElementById('printerPort').value || '9100';
    }
    
    return params;
}

        // Bağlantı ayarlarını kaydet
        function saveConnectionSettings() {
            const settings = getConnectionParams();
            
            showLoading(true);
            
            fetch('save_printer_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(settings)
            })
            .then(response => {
                console.log('Response status:', response.status);
                
                // Önce içerik tipini kontrol et
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);
                
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        console.log('JSON Response:', data);
                        return data;
                    });
                } else {
                    return response.text().then(text => {
                        console.log('Non-JSON Response:', text.substring(0, 500));
                        
                        // Daha detaylı hata analizi
                        let errorMessage = 'Sunucu beklenmeyen bir yanıt döndü';
                        
                        if (text.includes('Parse error') || text.includes('syntax error')) {
                            errorMessage = 'PHP syntax hatası: ' + text.match(/(Parse error|syntax error)[\s\S]{1,200}/)?.[0] || 'Bilinmeyen syntax hatası';
                        } else if (text.includes('Fatal error')) {
                            errorMessage = 'PHP fatal hatası: ' + text.match(/Fatal error[\s\S]{1,200}/)?.[0] || 'Bilinmeyen fatal hata';
                        } else if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                            errorMessage = 'Sunucu HTML hatası döndü. PHP hatası olabilir.';
                        } else if (response.status === 500) {
                            errorMessage = 'Sunucu iç hatası (500). Logları kontrol edin.';
                        }
                        
                        throw new Error(errorMessage + ' (Status: ' + response.status + ')');
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    alert('Bağlantı ayarları başarıyla kaydedildi!');
                } else {
                    alert('Bağlantı ayarları kaydedilirken hata oluştu: ' + (data.error || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Fetch hatası:', error);
                alert('Bağlantı ayarları kaydedilirken bir hata oluştu: ' + error.message);
            })
            .finally(() => {
                showLoading(false);
            });
        }

        // Logo ayarlarını kaydet
        function saveLogoSettings() {
            const logoAlignment = document.querySelector('input[name="logoAlignment"]:checked').value;
            const logoWidth = document.getElementById('logoWidth').value;
            const logoHeight = document.getElementById('logoHeight').value;
            
            if (!currentLogoPath) {
                alert('Lütfen önce bir logo yükleyin.');
                return;
            }
            
            saveSettings({
                logo_alignment: logoAlignment,
                logo_path: currentLogoPath,
                logo_width: logoWidth,
                logo_height: logoHeight
            });
        }

        // Fiş ayarlarını kaydet
        function saveReceiptSettings() {
            const logoAlignment = document.querySelector('input[name="logoAlignment"]:checked').value;
            const logoWidth = document.getElementById('logoWidth').value;
            const logoHeight = document.getElementById('logoHeight').value;
            
            const settings = {
                company_name: document.getElementById('companyName').value,
                company_name_font: document.getElementById('companyNameFont').value,
                company_name_size: document.getElementById('companyNameSize').value,
                company_address: document.getElementById('companyAddress').value,
                company_phone: document.getElementById('companyPhone').value,
                footer_text: document.getElementById('footerText').value,
                receipt_width: document.getElementById('receiptWidth').value,
                logo_alignment: logoAlignment,
                logo_width: logoWidth,
                logo_height: logoHeight
            };
            
            if (currentLogoPath) {
                settings.logo_path = currentLogoPath;
            }
            
            saveSettings(settings);
        }

// Test yazdırma fonksiyonunu güncelle
function testPrint() {
    const connectionParams = getConnectionParams();
    
    if (connectionParams.connection_type === 'usb') {
        // USB yazdırma
        showLoading(true);
        
        fetch('Print.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'print_usb',
                printer_name: connectionParams.printer_name,
                // Test verileri
                table_id: 1,
                order_id: Math.floor(Math.random() * 10000),
                date: new Date().toLocaleString('tr-TR'),
                items: [
                    {
                        quantity: 2,
                        name: 'Test Ürünü',
                        price: 25.00,
                        hasOptions: false
                    }
                ],
                total: 50.00
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Test yazdırma başarılı!');
            } else {
                alert('Test yazdırma başarısız: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            alert('Test yazdırma sırasında hata oluştu: ' + error.message);
        })
        .finally(() => {
            showLoading(false);
        });
    } else {
        // Diğer yazdırma yöntemleri için mevcut kod
        const printWindow = window.open('Print.php?test=true', '_blank');
        
        if (!printWindow || printWindow.closed || typeof printWindow.closed == 'undefined') {
            alert('Yazdırma penceresi açılamadı. Lütfen pop-up engelleyicinizi kontrol edin.');
        }
    }
}

 // Ayarları kaydet fonksiyonunu güncelle
function saveSettings(settings) {
    showLoading(true);
    
    // Eksik alanları ekle
    settings.order_items_font_size = parseInt(document.getElementById('orderItemsFontSize').value);
    settings.company_name_font = document.getElementById('companyNameFont').value;
    settings.company_name_size = parseInt(document.getElementById('companyNameSize').value);
    
    // Bağlantı ayarlarını ekle
    const connectionParams = getConnectionParams();
    Object.assign(settings, connectionParams);
    
    fetch('save_printer_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(settings)
    })
    .then(response => {
        // Önce içerik tipini kontrol et
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
            });
        }
    })
    .then(data => {
        if (data.success) {
            alert('Ayarlar başarıyla kaydedildi!');
            // Sayfayı yenile
            location.reload();
        } else {
            alert('Ayarlar kaydedilirken hata oluştu: ' + (data.message || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Hata:', error);
        alert('Ayarlar kaydedilirken bir hata oluştu: ' + error.message);
    })
    .finally(() => {
        showLoading(false);
    });
}

// Logo yükleme işlemini düzelt
function handleLogoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Dosya tipi kontrolü
    if (!file.type.match('image.*')) {
        alert('Sadece resim dosyaları yükleyebilirsiniz!');
        return;
    }
    
    showLoading(true);
    
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('action', 'upload_logo');
    
    fetch('db.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentLogoPath = data.logo_path;
            const previewLogo = document.getElementById('previewLogo');
            
            // Logo yolu doğruysa göster
            if (currentLogoPath && currentLogoPath !== 'null') {
                previewLogo.src = currentLogoPath + '?t=' + new Date().getTime();
                previewLogo.classList.remove('d-none');
                
                // Logo boyutlarını güncelle
                const logoWidth = document.getElementById('logoWidth').value;
                const logoHeight = document.getElementById('logoHeight').value;
                previewLogo.style.width = logoWidth + 'px';
                previewLogo.style.height = logoHeight + 'px';
                
                updateLogoAlignment();
                
                // Logo ayarlarını kaydet
                const settings = {
                    logo_path: currentLogoPath,
                    logo_width: parseInt(logoWidth),
                    logo_height: parseInt(logoHeight),
                    logo_alignment: document.querySelector('input[name="logoAlignment"]:checked').value
                };
                
                saveSettings(settings);
            }
            
            alert('Logo başarıyla yüklendi ve kaydedildi!');
        } else {
            alert('Logo yüklenirken hata oluştu: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Hata:', error);
        alert('Logo yüklenirken bir hata oluştu: ' + error.message);
    })
    .finally(() => {
        showLoading(false);
    });
}
// printer_yonetimi.php'deki JavaScript'e bu fonksiyonları ekleyin
function refreshPrinterList() {
    showLoading(true);
    
    fetch('get_printers.php')
        .then(response => response.json())
        .then(printers => {
            const select = document.getElementById('usbPrinterName');
            select.innerHTML = '<option value="">Yazıcı seçin...</option>';
            
            printers.forEach(printer => {
                const option = document.createElement('option');
                option.value = printer;
                option.textContent = printer;
                select.appendChild(option);
            });
            
            // Kayıtlı yazıcı varsa seç
            if (printerSettings.printer_name) {
                select.value = printerSettings.printer_name;
            }
        })
        .catch(error => {
            console.error('Yazıcı listesi alınamadı:', error);
            alert('Yazıcı listesi alınamadı. Manuel olarak girebilirsiniz.');
        })
        .finally(() => {
            showLoading(false);
        });
}

// Sayfa yüklendiğinde yazıcı listesini yenile
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('refreshPrinters').addEventListener('click', refreshPrinterList);
    // Sayfa açıldığında da yazıcı listesini yükle
    refreshPrinterList();
});
        function loadSettings() {
            showLoading(true);
            
            fetch('db.php?action=get_printer_settings')
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            throw new Error('Server returned non-JSON response: ' + text.substring(0, 100));
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        printerSettings = data.settings;
                        
                        const connectionType = printerSettings.connection_type || 'wifi';
                        
                        // Önce tüm toggle'ları kapat
                        document.querySelectorAll('.connection-toggle').forEach(toggle => {
                            toggle.checked = false;
                        });
                        
                        // Bluetooth ayarlarını yükle
                        if (printerSettings.bluetooth_mac) {
                            document.getElementById('bluetoothMac').value = printerSettings.bluetooth_mac;
                        }
                        
                        // WiFi ayarlarını yükle
                        if (printerSettings.printer_ip) {
                            document.getElementById('printerIp').value = printerSettings.printer_ip;
                        }
                        if (printerSettings.printer_port) {
                            document.getElementById('printerPort').value = printerSettings.printer_port;
                        }
                        
                        // Ethernet ayarlarını yükle
                        if (printerSettings.ethernet_ip) {
                            document.getElementById('ethernetIp').value = printerSettings.ethernet_ip;
                        }
                        if (printerSettings.ethernet_port) {
                            document.getElementById('ethernetPort').value = printerSettings.ethernet_port;
                        }
                        
                        // USB ayarlarını yükle
                        if (printerSettings.printer_name) {
                            document.getElementById('usbPrinterName').value = printerSettings.printer_name;
                        }
                        
                        // Aktif bağlantı tipini seç
                        if (connectionType === 'bluetooth') {
                            document.getElementById('bluetoothToggle').checked = true;
                        } else if (connectionType === 'wifi') {
                            document.getElementById('wifiToggle').checked = true;
                        } else if (connectionType === 'ethernet') {
                            document.getElementById('ethernetToggle').checked = true;
                        } else if (connectionType === 'usb') {
                            document.getElementById('usbToggle').checked = true;
                        }
                        
                        showActiveConnection(connectionType);
                        // Font ayarlarını kaydet
function saveFontSettings() {
    const settings = {
        company_name_font: document.getElementById('companyNameFont').value,
        company_name_size: parseInt(document.getElementById('companyNameSize').value),
        order_items_font_size: parseInt(document.getElementById('orderItemsFontSize').value)
    };
    
    saveSettings(settings);
}

// Sayfa yüklendiğinde bu fonksiyonu çağır
document.addEventListener('DOMContentLoaded', function() {
    // Font değişikliklerini dinle
    document.getElementById('companyNameFont').addEventListener('change', saveFontSettings);
    document.getElementById('companyNameSize').addEventListener('change', saveFontSettings);
    document.getElementById('orderItemsFontSize').addEventListener('change', saveFontSettings);
});
                        // Logo ayarlarını yükle
// Logo ayarlarını yükle - BASİT VERSİYON
if (printerSettings.logo_path) {
    currentLogoPath = printerSettings.logo_path;
    const previewLogo = document.getElementById('previewLogo');
    
    // Logoyu göster
    previewLogo.src = printerSettings.logo_path + '?t=' + new Date().getTime();
    previewLogo.classList.remove('d-none');
}
                        
                        if (printerSettings.logo_alignment) {
                            document.querySelector(`input[name="logoAlignment"][value="${printerSettings.logo_alignment}"]`).checked = true;
                        }
                        
                        if (printerSettings.logo_width) {
                            document.getElementById('logoWidth').value = printerSettings.logo_width;
                        }
                        
                        if (printerSettings.logo_height) {
                            document.getElementById('logoHeight').value = printerSettings.logo_height;
                        }
                        
                        // Fiş ayarlarını yükle
                        if (printerSettings.company_name) {
                            document.getElementById('companyName').value = printerSettings.company_name;
                        }
                        
                        if (printerSettings.company_name_font) {
                            document.getElementById('companyNameFont').value = printerSettings.company_name_font;
                        }
                        
                        if (printerSettings.company_name_size) {
                            document.getElementById('companyNameSize').value = printerSettings.company_name_size;
                            document.getElementById('companyNameSizeValue').textContent = printerSettings.company_name_size + 'px';
                        }
                        
                        if (printerSettings.company_address) {
                            document.getElementById('companyAddress').value = printerSettings.company_address;
                        }
                        
                        if (printerSettings.company_phone) {
                            document.getElementById('companyPhone').value = printerSettings.company_phone;
                        }
                        
                        if (printerSettings.footer_text) {
                            document.getElementById('footerText').value = printerSettings.footer_text;
                        }
                        
                        if (printerSettings.receipt_width) {
                            document.getElementById('receiptWidth').value = printerSettings.receipt_width;
                        }
                        
                        if (printerSettings.order_items_font_size) {
                            document.getElementById('orderItemsFontSize').value = printerSettings.order_items_font_size;
                            document.getElementById('orderItemsFontSizeValue').textContent = printerSettings.order_items_font_size + 'px';
                        }
                        
                        // Önizlemeyi güncelle
                        updatePreview();
                        updateCompanyNamePreview();
                    } else {
                        console.error('Ayarlar yüklenirken hata oluştu:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Ayarlar yüklenirken bir hata oluştu: ' + error.message);
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        // Yükleme ekranını göster/gizle
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }
    </script>
</body>
</html>
