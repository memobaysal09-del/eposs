<?php
// siparis_qz.php
// Basit demo sipariş sayfası + QZ Tray entegrasyonu

// Burada normalde DB'den sipariş bilgilerini çekeceksin.
// Ben örnek sipariş verisini PHP -> JS içine gömeceğim:
$order = [
    "id"     => "SIP-2025-0001",
    "date"   => date("d.m.Y H:i"),
    "tableNo"=> "A3",
    "items"  => [
        ["name" => "Hamburger", "qty" => 2, "price" => 120.00],
        ["name" => "Patates",   "qty" => 1, "price" => 45.00],
        ["name" => "Ayran",     "qty" => 2, "price" => 25.00]
    ],
    "total"  => 335.00,
    "paid"   => 350.00
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Sipariş Yazdırma</title>
</head>
<body>
  <h1>Sipariş #<?php echo $order["id"]; ?></h1>

  <button id="btnPrint">Yazdır</button>
  <button id="btnDiag">Yazıcı Test</button>

  <script src="https://cdn.jsdelivr.net/npm/qz-tray/qz-tray.js"></script>
  <script>
  /* ====== PHP'den gelen sipariş verisi ====== */
  const CURRENT_ORDER = <?php echo json_encode($order, JSON_UNESCAPED_UNICODE); ?>;

  /* ====== QZ TRAY BAĞLANTI & GÜVENLİK ====== */
  qz.security.setCertificatePromise(function(resolve, reject) {
    // Geliştirmede:
    resolve();  // QZ Tray'de "Allow unsigned requests" açık olmalı
  });
  qz.security.setSignaturePromise(function(toSign) {
    return Promise.resolve(null); // Geliştirmede imzasız
  });

  async function ensureQZ() {
    if (!qz.websocket.isActive()) {
      await qz.websocket.connect();
    }
  }

  /* ====== Yazıcı ayarları (örnek) ====== */
  async function loadPrinterSettings() {
    // Normalde AJAX ile DB'den çekilir
    return {
      printer_name: "", // Boşsa IP/port kullanılacak
      ip: "192.168.1.50",
      port: 9100,
      mode: "escpos",
      logo_url: "",
      header_text: "İŞLETME ADI",
      footer_text: "Yine bekleriz",
      width: 42
    };
  }

  function buildPrinterTarget(settings) {
    if (settings.printer_name && settings.printer_name.trim() !== "") {
      return settings.printer_name.trim();
    }
    return `socket://${settings.ip}:${settings.port || 9100}`;
  }

  /* ====== ESC/POS veri üretici ====== */
  function buildEscPosData(order, settings) {
    const ESC = '\x1B';
    const GS  = '\x1D';

    const init        = ESC + '@';
    const alignLeft   = ESC + 'a' + '\x00';
    const alignCenter = ESC + 'a' + '\x01';
    const boldOn      = ESC + 'E' + '\x01';
    const boldOff     = ESC + 'E' + '\x00';
    const dblOn       = ESC + '!' + '\x30';
    const dblOff      = ESC + '!' + '\x00';
    const cutFull     = GS  + 'V' + '\x00';
    const feed3       = ESC + 'd' + '\x03';

    const wrap = (txt) => {
      const w = Number(settings.width || 42);
      const out = [];
      let s = String(txt);
      while (s.length > w) { out.push(s.slice(0, w)); s = s.slice(w); }
      out.push(s);
      return out.join('\n') + '\n';
    };

    const itemsTxt = order.items.map(it => {
      const left = `${it.name} x${it.qty}`;
      const right = (it.price * it.qty).toFixed(2) + '₺';
      const w = Number(settings.width || 42);
      const space = Math.max(1, w - left.length - right.length);
      return left + ' '.repeat(space) + right;
    }).join('\n') + '\n';

    let receipt = '';
    receipt += init;

    receipt += alignCenter + boldOn + dblOn + wrap(settings.header_text || 'FİŞ');
    receipt += dblOff + boldOff;

    if (order.tableNo) {
      receipt += alignCenter + boldOn + wrap(`Masa: ${order.tableNo}`) + boldOff;
    }

    receipt += alignLeft + wrap(`Fiş No: ${order.id}`);
    receipt += wrap(`Tarih : ${order.date}`);
    receipt += '-'.repeat(settings.width || 42) + '\n';
    receipt += itemsTxt;
    receipt += '-'.repeat(settings.width || 42) + '\n';

    const totalLineLeft = 'TOPLAM';
    const totalLineRight = (order.total).toFixed(2) + '₺';
    const w = Number(settings.width || 42);
    const sp = Math.max(1, w - totalLineLeft.length - totalLineRight.length);
    receipt += boldOn + totalLineLeft + ' '.repeat(sp) + totalLineRight + '\n' + boldOff;

    if (order.paid != null) {
      const paidLeft = 'ÖDENEN';
      const paidRight = (order.paid).toFixed(2) + '₺';
      const sp2 = Math.max(1, w - paidLeft.length - paidRight.length);
      receipt += paidLeft + ' '.repeat(sp2) + paidRight + '\n';
      const change = (order.paid - order.total);
      if (!isNaN(change)) {
        const chLeft = 'Para Üstü';
        const chRight = change.toFixed(2) + '₺';
        const sp3 = Math.max(1, w - chLeft.length - chRight.length);
        receipt += chLeft + ' '.repeat(sp3) + chRight + '\n';
      }
    }

    receipt += '\n' + alignCenter + wrap(settings.footer_text || 'Teşekkür ederiz');
    receipt += feed3 + cutFull;

    return [receipt];
  }

  async function printOrder(order) {
    try {
      await ensureQZ();
      const settings = await loadPrinterSettings();
      const printerTarget = buildPrinterTarget(settings);
      const cfg = qz.configs.create(printerTarget, { encoding: 'UTF-8', rasterize: false });
      const data = buildEscPosData(order, settings);
      await qz.print(cfg, data);
      alert('Fiş yazdırma isteği gönderildi.');
    } catch (err) {
      console.error(err);
      alert('Yazdırma hatası: ' + (err.message || err));
    }
  }

  document.getElementById('btnPrint').addEventListener('click', function() {
    printOrder(CURRENT_ORDER);
  });

  document.getElementById('btnDiag').addEventListener('click', async () => {
    try {
      await ensureQZ();
      const printers = await qz.printers.find();
      console.log('Bulunan yazıcılar:', printers);
      alert("QZ Tray çalışıyor. Konsolda yazıcı listesi var.");
    } catch (e) {
      alert('QZ Tray bağlantı hatası: ' + (e.message || e));
    }
  });
  </script>
</body>
</html>
