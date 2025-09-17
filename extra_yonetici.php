<?php
// Disable db.php API dispatch when included from this page
if (!defined('DB_DISABLE_API')) { define('DB_DISABLE_API', true); }
// extra_yonetici.php — GLOBAL + KATEGORİ bazlı yönetim
require_once 'db.php';
session_start();

function table_exists(mysqli $conn, string $name): bool {
  $name_esc = $conn->real_escape_string($name);
  $res = $conn->query("SHOW TABLES LIKE '{$name_esc}'");
  return $res && $res->num_rows > 0;
}

$has_categories = table_exists($conn, 'categories');

// Kategoriler (opsiyonel)
$categories = [];
if ($has_categories) {
  $res = $conn->query("SELECT id, name FROM categories ORDER BY name");
  if ($res) { while ($row=$res->fetch_assoc()) $categories[]=$row; }
}

$active_tab = 'extrasTab'; // Default tab
if (isset($_GET['tab'])) {
    $active_tab = $_GET['tab'];
} elseif (isset($_POST['active_tab'])) {
    $active_tab = $_POST['active_tab'];
}

function handleForm($conn, $table, $has_categories) {
    global $active_tab;
    
    // Toplu ekleme işlemi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'bulk_advanced') {
            // Gelişmiş mod (-- ve - ile)
            $bulkData = trim($_POST['bulk_data']);
            $lines = explode("\n", $bulkData);
            $successCount = 0;
            $errorCount = 0;
            $currentTable = 'extras'; // Varsayılan tablo
            $currentCategory = null;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Tab değiştirme
                if (preg_match('/^--\s*(extras?|cold_drinks?|hot_drinks?|swapsin?|swapsout?|in|out|cold|hot)/i', $line, $matches)) {
                    $tableType = strtolower($matches[1]);
                    if (in_array($tableType, ['extra', 'extras'])) {
                        $currentTable = 'extras';
                    } else if (in_array($tableType, ['cold_drink', 'cold_drinks', 'cold'])) { // Added 'cold' shortcut
                        $currentTable = 'cold_drinks';
                    } else if (in_array($tableType, ['hot_drink', 'hot_drinks', 'hot'])) { // Added 'hot' shortcut
                        $currentTable = 'hot_drinks';
                    } else if (in_array($tableType, ['swapsin', 'in'])) {
                        $currentTable = 'swaps_in';
                    } else if (in_array($tableType, ['swapsout', 'out'])) {
                        $currentTable = 'swaps_out';
                    }
                    continue;
                }
                
                // Kategori değiştirme
                if (preg_match('/^-\s*(global|[\w\s]+)/i', $line, $matches)) {
                    $categoryName = trim($matches[1]);
                    if (strtolower($categoryName) === 'global') {
                        $currentCategory = null;
                    } else {
                        // Kategori adına göre ID bul
                        $currentCategory = null;
                        global $categories;
                        foreach ($categories as $cat) {
                            if (strcasecmp(trim($cat['name']), $categoryName) === 0) {
                                $currentCategory = $cat['id'];
                                break;
                            }
                        }
                    }
                    continue;
                }
                
                // Ürün ekleme formatı: "ürün_adi fiyat"
                if (preg_match('/^([^0-9-]+)\s+([0-9.]+)$/', $line, $matches)) {
                    $name = trim($matches[1]);
                    $price = floatval($matches[2]);
                    
                    if ($currentCategory === null || !$has_categories) {
                        $stmt = $conn->prepare("INSERT INTO $currentTable (category_id, name, price) VALUES (NULL, ?, ?)");
                        $stmt->bind_param("sd", $name, $price);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO $currentTable (category_id, name, price) VALUES (?, ?, ?)");
                        $stmt->bind_param("isd", $currentCategory, $name, $price);
                    }
                    
                    if ($stmt->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                    $stmt->close();
                } else {
                    $errorCount++;
                }
            }
            
            $_SESSION['bulk_message'] = "Toplu işlem tamamlandı: $successCount başarılı, $errorCount hatalı";
            header("Location: ".basename($_SERVER['PHP_SELF'])."?tab=bulkAdvancedTab"); 
            exit;
            
        } elseif ($_POST['action'] === 'bulk_simple') {
            // Basit mod (sadece ürün listesi)
            $bulkData = trim($_POST['bulk_data_simple']);
            $targetTable = $_POST['target_table'];
            $targetCategory = isset($_POST['bulk_category_id']) && $_POST['bulk_category_id'] !== '' ? intval($_POST['bulk_category_id']) : null;
            $lines = explode("\n", $bulkData);
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Ürün ekleme formatı: "ürün_adi fiyat"
                if (preg_match('/^([^0-9-]+)\s+([0-9.]+)$/', $line, $matches)) {
                    $name = trim($matches[1]);
                    $price = floatval($matches[2]);
                    
                    if ($targetCategory === null || !$has_categories) {
                        $stmt = $conn->prepare("INSERT INTO $targetTable (category_id, name, price) VALUES (NULL, ?, ?)");
                        $stmt->bind_param("sd", $name, $price);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO $targetTable (category_id, name, price) VALUES (?, ?, ?)");
                        $stmt->bind_param("isd", $targetCategory, $name, $price);
                    }
                    
                    if ($stmt->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                    $stmt->close();
                } else {
                    $errorCount++;
                }
            }
            
            $_SESSION['bulk_message'] = "Toplu işlem tamamlandı: $successCount başarılı, $errorCount hatalı";
            header("Location: ".basename($_SERVER['PHP_SELF'])."?tab=bulkSimpleTab"); 
            exit;
        }
    }
    
    // Güncelleme işlemi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_'.$table) {
        $id = intval($_POST['id']);
        $name  = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $cid   = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : null;

        if ($cid === null || !$has_categories) {
            $stmt = $conn->prepare("UPDATE $table SET category_id = NULL, name = ?, price = ? WHERE id = ?");
            $stmt->bind_param("sdi", $name, $price, $id);
        } else {
            $stmt = $conn->prepare("UPDATE $table SET category_id = ?, name = ?, price = ? WHERE id = ?");
            $stmt->bind_param("isdi", $cid, $name, $price, $id);
        }
        $stmt->execute();
        $stmt->close();
        $tab_map = [
            'extras' => 'extrasTab',
            'cold_drinks' => 'coldDrinksTab', 
            'hot_drinks' => 'hotDrinksTab',
            'swaps_out' => 'swapsOutTab',
            'swaps_in' => 'swapsInTab'
        ];
        $redirect_tab = isset($tab_map[$table]) ? $tab_map[$table] : 'extrasTab';
        header("Location: ".basename($_SERVER['PHP_SELF'])."?tab=".$redirect_tab); 
        exit;
    }
    
    // Ekleme işlemi
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === $table) {
        $name  = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $cid   = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : null;

        if ($cid === null || !$has_categories) {
            $stmt = $conn->prepare("INSERT INTO $table (category_id, name, price) VALUES (NULL, ?, ?)");
            $stmt->bind_param("sd", $name, $price);
        } else {
            $stmt = $conn->prepare("INSERT INTO $table (category_id, name, price) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $cid, $name, $price);
        }
        $stmt->execute();
        $stmt->close();
        $tab_map = [
            'extras' => 'extrasTab',
            'cold_drinks' => 'coldDrinksTab', 
            'hot_drinks' => 'hotDrinksTab',
            'swaps_out' => 'swapsOutTab',
            'swaps_in' => 'swapsInTab'
        ];
        $redirect_tab = isset($tab_map[$table]) ? $tab_map[$table] : 'extrasTab';
        header("Location: ".basename($_SERVER['PHP_SELF'])."?tab=".$redirect_tab); 
        exit;
    }
    
    // Silme işlemi
    if (isset($_GET['delete']) && isset($_GET['table']) && $_GET['table'] === $table) {
        $id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $tab_map = [
            'extras' => 'extrasTab',
            'cold_drinks' => 'coldDrinksTab', 
            'hot_drinks' => 'hotDrinksTab',
            'swaps_out' => 'swapsOutTab',
            'swaps_in' => 'swapsInTab'
        ];
        $redirect_tab = isset($tab_map[$table]) ? $tab_map[$table] : 'extrasTab';
        header("Location: ".basename($_SERVER['PHP_SELF'])."?tab=".$redirect_tab); 
        exit;
    }

    if ($has_categories) {
      $sql = "SELECT t.*, c.name AS category_name FROM $table t LEFT JOIN categories c ON c.id=t.category_id ORDER BY COALESCE(c.name,'GLOBAL'), t.name";
    } else {
      $sql = "SELECT t.*, NULL AS category_name FROM $table t ORDER BY t.name";
    }
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

$extras     = handleForm($conn, "extras", $has_categories);
$cold_drinks = handleForm($conn, "cold_drinks", $has_categories); // Added cold drinks handling
$hot_drinks = handleForm($conn, "hot_drinks", $has_categories); // Added hot drinks handling
$swaps_out  = handleForm($conn, "swaps_out", $has_categories);
$swaps_in   = handleForm($conn, "swaps_in", $has_categories);

// Toplu işlem mesajını göster
$bulk_message = '';
if (isset($_SESSION['bulk_message'])) {
    $bulk_message = $_SESSION['bulk_message'];
    unset($_SESSION['bulk_message']);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">  <title>Extra / Drinks / Swaps Yönetimi (Global + Kategori)</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-bottom-color: #fff;
            font-weight: 600;
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-header {
            background-color: #dc3545;
            color: white;
        }
        .bulk-help {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .bulk-example {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 10px;
            margin-top: 10px;
        }
  </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Extra / Drinks / Swaps Yönetimi</h1>
                <div>
                    <a href="menu_yonetimi.php" class="btn btn-primary">
                        <i class="fas fa-utensils me-2"></i>Menü Yönetimi
                    </a>
                    <a href="opsiyon_yonetimi.php" class="btn btn-info">
                        <i class="fas fa-cog me-2"></i>Opsiyon Yönetimi
                    </a>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-home me-2"></i>Masalara Dön
                    </a>
                </div>
            </div>
        </div>
    </div>

<div class="container">
  <h2 class="mb-4">Extra / Drinks / Swaps Yönetimi (Global + Kategori)</h2>

  <?php if (!empty($bulk_message)): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= $bulk_message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <ul class="nav nav-tabs" role="tablist">
    <!-- Added active class detection based on active_tab variable -->
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'extrasTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#extrasTab" aria-selected="<?= $active_tab === 'extrasTab' ? 'true' : 'false' ?>" role="tab">Extras</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'coldDrinksTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#coldDrinksTab" aria-selected="<?= $active_tab === 'coldDrinksTab' ? 'true' : 'false' ?>" role="tab" tabindex="-1">Cold Drinks</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'hotDrinksTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#hotDrinksTab" aria-selected="<?= $active_tab === 'hotDrinksTab' ? 'true' : 'false' ?>" role="tab" tabindex="-1">Hot Drinks</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'swapsOutTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#swapsOutTab" aria-selected="<?= $active_tab === 'swapsOutTab' ? 'true' : 'false' ?>" role="tab" tabindex="-1">Swaps Out</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'swapsInTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#swapsInTab" aria-selected="<?= $active_tab === 'swapsInTab' ? 'true' : 'false' ?>" role="tab" tabindex="-1">Swaps In</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'bulkSimpleTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#bulkSimpleTab" aria-selected="<?= $active_tab === 'bulkSimpleTab' ? 'true' : 'false' ?>" role="tab" tabindex="-1"><i class="fas fa-bolt me-1"></i>Hızlı Ekle 1</button></li>
    <li class="nav-item" role="presentation"><button class="nav-link <?= $active_tab === 'bulkAdvancedTab' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#bulkAdvancedTab" aria-selected="<?= $active_tab === 'bulkAdvancedTab' ? 'true' : 'false' ?>" role="tab" tabindex="-1"><i class="fas fa-bolt me-1"></i>Hızlı Ekle 2</button></li>
  </ul>

  <div class="tab-content mt-3">
    <!-- Extras -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'extrasTab' ? 'show active' : '' ?>" id="extrasTab">
      <form method="post" class="row g-2 mb-3" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
        <input type="hidden" name="action" value="extras">
        <div class="col-md-3">
          <?php if ($has_categories): ?>
            <select name="category_id" class="form-select">
              <option value="">Global (tüm ürünler)</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" class="form-control" value="Kategori tablosu yok" disabled>
          <?php endif; ?>
        </div>
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Ad" required></div>
        <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Fiyat" required></div>
        <div class="col-md-2"><button class="btn btn-success w-100"><i class="fas fa-plus me-1"></i> Ekle</button></div>
      </form>
      <div class="card">
        <div class="card-header">
            <h5>Mevcut Extras</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped align-middle">
            <thead><tr><th>ID</th><th>Kategori</th><th>Ad</th><th>Fiyat</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach($extras as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['category_name'] ? htmlspecialchars($r['category_name']) : '<span class="badge text-bg-secondary">GLOBAL</span>' ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>£<?= number_format((float)$r['price'],2) ?></td>
                <td>
                  <button class="btn btn-primary btn-sm me-1" onclick="editItem('extras', <?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>', <?= $r['price'] ?>, <?= $r['category_id'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Düzenle
                  </button>
                  <a href="?delete=<?= $r['id'] ?>&table=extras#extrasTab" class="btn btn-danger btn-sm" onclick="return confirm('Bu öğeyi silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Sil
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Added Cold Drinks Tab -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'coldDrinksTab' ? 'show active' : '' ?>" id="coldDrinksTab">
      <form method="post" class="row g-2 mb-3" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
        <input type="hidden" name="action" value="cold_drinks">
        <div class="col-md-3">
          <?php if ($has_categories): ?>
            <select name="category_id" class="form-select">
              <option value="">Global (tüm ürünler)</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" class="form-control" value="Kategori tablosu yok" disabled>
          <?php endif; ?>
        </div>
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Ad" required></div>
        <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Fiyat" required></div>
        <div class="col-md-2"><button class="btn btn-success w-100"><i class="fas fa-plus me-1"></i> Ekle</button></div>
      </form>
      <div class="card">
        <div class="card-header">
            <h5>Mevcut Cold Drinks</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped align-middle">
            <thead><tr><th>ID</th><th>Kategori</th><th>Ad</th><th>Fiyat</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach($cold_drinks as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['category_name'] ? htmlspecialchars($r['category_name']) : '<span class="badge text-bg-secondary">GLOBAL</span>' ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>£<?= number_format((float)$r['price'],2) ?></td>
                <td>
                  <button class="btn btn-primary btn-sm me-1" onclick="editItem('cold_drinks', <?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>', <?= $r['price'] ?>, <?= $r['category_id'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Düzenle
                  </button>
                  <a href="?delete=<?= $r['id'] ?>&table=cold_drinks#coldDrinksTab" class="btn btn-danger btn-sm" onclick="return confirm('Bu öğeyi silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Sil
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Added Hot Drinks Tab -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'hotDrinksTab' ? 'show active' : '' ?>" id="hotDrinksTab">
      <form method="post" class="row g-2 mb-3" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
        <input type="hidden" name="action" value="hot_drinks">
        <div class="col-md-3">
          <?php if ($has_categories): ?>
            <select name="category_id" class="form-select">
              <option value="">Global (tüm ürünler)</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" class="form-control" value="Kategori tablosu yok" disabled>
          <?php endif; ?>
        </div>
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Ad" required></div>
        <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Fiyat" required></div>
        <div class="col-md-2"><button class="btn btn-success w-100"><i class="fas fa-plus me-1"></i> Ekle</button></div>
      </form>
      <div class="card">
        <div class="card-header">
            <h5>Mevcut Hot Drinks</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped align-middle">
            <thead><tr><th>ID</th><th>Kategori</th><th>Ad</th><th>Fiyat</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach($hot_drinks as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['category_name'] ? htmlspecialchars($r['category_name']) : '<span class="badge text-bg-secondary">GLOBAL</span>' ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>£<?= number_format((float)$r['price'],2) ?></td>
                <td>
                  <button class="btn btn-primary btn-sm me-1" onclick="editItem('hot_drinks', <?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>', <?= $r['price'] ?>, <?= $r['category_id'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Düzenle
                  </button>
                  <a href="?delete=<?= $r['id'] ?>&table=hot_drinks#hotDrinksTab" class="btn btn-danger btn-sm" onclick="return confirm('Bu öğeyi silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Sil
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Swaps Out -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'swapsOutTab' ? 'show active' : '' ?>" id="swapsOutTab">
      <form method="post" class="row g-2 mb-3" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
        <input type="hidden" name="action" value="swaps_out">
        <div class="col-md-3">
          <?php if ($has_categories): ?>
            <select name="category_id" class="form-select">
              <option value="">Global (tüm ürünler)</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" class="form-control" value="Kategori tablosu yok" disabled>
          <?php endif; ?>
        </div>
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Ad" required></div>
        <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Fiyat" required></div>
        <div class="col-md-2"><button class="btn btn-success w-100"><i class="fas fa-plus me-1"></i> Ekle</button></div>
      </form>
      <div class="card">
        <div class="card-header">
            <h5>Mevcut Swaps Out</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped align-middle">
            <thead><tr><th>ID</th><th>Kategori</th><th>Ad</th><th>Fiyat</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach($swaps_out as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['category_name'] ? htmlspecialchars($r['category_name']) : '<span class="badge text-bg-secondary">GLOBAL</span>' ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>£<?= number_format((float)$r['price'],2) ?></td>
                <td>
                  <button class="btn btn-primary btn-sm me-1" onclick="editItem('swaps_out', <?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>', <?= $r['price'] ?>, <?= $r['category_id'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Düzenle
                  </button>
                  <a href="?delete=<?= $r['id'] ?>&table=swaps_out#swapsOutTab" class="btn btn-danger btn-sm" onclick="return confirm('Bu öğeyi silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Sil
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Swaps In -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'swapsInTab' ? 'show active' : '' ?>" id="swapsInTab">
      <form method="post" class="row g-2 mb-3" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
        <input type="hidden" name="action" value="swaps_in">
        <div class="col-md-3">
          <?php if ($has_categories): ?>
            <select name="category_id" class="form-select">
              <option value="">Global (tüm ürünler)</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" class="form-control" value="Kategori tablosu yok" disabled>
          <?php endif; ?>
        </div>
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Ad" required></div>
        <div class="col-md-3"><input type="number" step="0.01" name="price" class="form-control" placeholder="Fiyat" required></div>
        <div class="col-md-2"><button class="btn btn-success w-100"><i class="fas fa-plus me-1"></i> Ekle</button></div>
      </form>
      <div class="card">
        <div class="card-header">
            <h5>Mevcut Swaps In</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped align-middle">
            <thead><tr><th>ID</th><th>Kategori</th><th>Ad</th><th>Fiyat</th><th>İşlemler</th></tr></thead>
            <tbody>
            <?php foreach($swaps_in as $r): ?>
              <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['category_name'] ? htmlspecialchars($r['category_name']) : '<span class="badge text-bg-secondary">GLOBAL</span>' ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>£<?= number_format((float)$r['price'],2) ?></td>
                <td>
                  <button class="btn btn-primary btn-sm me-1" onclick="editItem('swaps_in', <?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['name'])) ?>', <?= $r['price'] ?>, <?= $r['category_id'] ?? 'null' ?>)">
                    <i class="fas fa-edit"></i> Düzenle
                  </button>
                  <a href="?delete=<?= $r['id'] ?>&table=swaps_in#swapsInTab" class="btn btn-danger btn-sm" onclick="return confirm('Bu öğeyi silmek istediğinize emin misiniz?')">
                    <i class="fas fa-trash"></i> Sil
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Hızlı Ekleme 1 (Gelişmiş) -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'bulkAdvancedTab' ? 'show active' : '' ?>" id="bulkAdvancedTab">
      <div class="card">
        <div class="card-header">
            <h5>Toplu Ürün Ekleme (Gelişmiş)</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <form method="post" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
                <input type="hidden" name="action" value="bulk_advanced">
                <div class="mb-3">
                  <label for="bulkData" class="form-label">Toplu Veri</label>
                  <textarea class="form-control" id="bulkData" name="bulk_data" rows="10" placeholder="Örnek format:
--extras
-global
Yumurta 1.50
Pastırma 2.00

--cold_drinks
-Kahvaltı
Portakal Suyu 2.50
Elma Suyu 2.25

--hot_drinks
-İçecekler
Türk Çayı 1.50
Kahve 2.50

--swaps_out
-İçecekler
Su 0.00
Çay 0.00

--swaps_in
-Kahvaltı
Beyaz Peynir 0.50
Zeytin 0.25"></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-bolt me-1"></i> Toplu Ekle
                </button>
              </form>
            </div>
            <div class="col-md-6">
              <div class="bulk-help">
                <h6>Toplu Ekleme Kılavuzu (Gelişmiş)</h6>
                <p>Aşağıdaki formatı kullanarak çoklu ürün ekleyebilirsiniz:</p>
                
                <div class="bulk-example">
                  <strong>Tab belirtmek için:</strong><br>
                  <code>--extras</code> veya <code>--extra</code><br>
                  <code>--cold_drinks</code> veya <code>--cold</code><br> <!-- Added cold shortcut -->
                  <code>--hot_drinks</code> veya <code>--hot</code><br> <!-- Added hot shortcut -->
                  <code>--swapsout</code> veya <code>--out</code><br>
                  <code>--swapsin</code> veya <code>--in</code><br><br>
                  
                  <strong>Kategori belirtmek için:</strong><br>
                  <code>-global</code> (Global kategori)<br>
                  <code>-KategoriAdı</code> (Özel kategori)<br><br>
                  
                  <strong>Ürün eklemek için:</strong><br>
                  <code>ÜrünAdı Fiyat</code><br>
                  Örnek: <code>Yumurta 1.50</code>
                </div>
                
                <div class="mt-3">
                  <strong>Örnek:</strong>
                  <pre class="bg-light p-2 mt-2">
--extras
-global
Yumurta 1.50
Pastırma 2.00

--cold_drinks
-İçecekler
Kola 2.50
Su 1.50

--hot_drinks
-İçecekler
Çay 1.50
Kahve 2.50

--swaps_out
-İçecekler
Su 0.00
Çay 0.00

--swaps_in
-Kahvaltı
Beyaz Peynir 0.50
Zeytin 0.25</pre>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hızlı Ekleme 2 (Basit) -->
    <!-- Added show active class detection based on active_tab variable -->
    <div class="tab-pane fade <?= $active_tab === 'bulkSimpleTab' ? 'show active' : '' ?>" id="bulkSimpleTab">
      <div class="card">
        <div class="card-header">
            <h5>Toplu Ürün Ekleme (Basit)</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <form method="post" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
                <input type="hidden" name="action" value="bulk_simple">
                <div class="mb-3">
                  <label for="targetTable" class="form-label">Hedef Tablo</label>
                  <select class="form-select" id="targetTable" name="target_table">
                    <option value="extras">Extras</option>
                    <!-- Added cold and hot drinks options -->
                    <option value="cold_drinks">Cold Drinks</option>
                    <option value="hot_drinks">Hot Drinks</option>
                    <option value="swaps_out">Swaps Out</option>
                    <option value="swaps_in">Swaps In</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="bulkCategory" class="form-label">Kategori</label>
                  <?php if ($has_categories): ?>
                    <select name="bulk_category_id" class="form-select" id="bulkCategory">
                      <option value="">Global (tüm ürünler)</option>
                      <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php else: ?>
                    <input type="text" class="form-control" value="Kategori tablosu yok" disabled>
                  <?php endif; ?>
                </div>
                <div class="mb-3">
                  <label for="bulkDataSimple" class="form-label">Toplu Veri</label>
                  <textarea class="form-control" id="bulkDataSimple" name="bulk_data_simple" rows="10" placeholder="Örnek format:
Yumurta 1.50
Pastırma 2.00
Beyaz Peynir 0.50
Zeytin 0.25"></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">
                  <i class="fas fa-bolt me-1"></i> Toplu Ekle
                </button>
              </form>
            </div>
            <div class="col-md-6">
              <div class="bulk-help">
                <h6>Toplu Ekleme Kılavuzu (Basit)</h6>
                <p>Aşağıdaki formatı kullanarak çoklu ürün ekleyebilirsiniz:</p>
                
                <div class="bulk-example">
                  <strong>Ürün eklemek için:</strong><br>
                  <code>ÜrünAdı Fiyat</code><br>
                  Örnek: <code>Yumurta 1.50</code>
                </div>
                
                <div class="mt-3">
                  <strong>Örnek:</strong>
                  <pre class="bg-light p-2 mt-2">
Yumurta 1.50
Pastırma 2.00
Beyaz Peynir 0.50
Zeytin 0.25</pre>
                </div>

                <div class="mt-3">
                  <strong>Not:</strong>
                  <ul>
                    <li>Üstteki "Hedef Tablo" seçeneğinden hangi tabloya eklemek istediğinizi seçin</li>
                    <li>"Kategori" seçeneğinden tüm ürünlerin hangi kategoride olacağını belirleyin</li>
                    <li>Her satıra bir ürün yazın</li>
                    <li>Boş satırları atlayacaktır</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Düzenleme Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Öğeyi Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
                <div class="modal-body">
                    <input type="hidden" name="action" id="editAction" value="">
                    <input type="hidden" name="id" id="editId" value="">
                    
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" id="editCategory">
                            <option value="">Global (tüm ürünler)</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editName" class="form-label">Ad</label>
                        <input type="text" class="form-control" id="editName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editPrice" class="form-label">Fiyat (£)</label>
                        <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editItem(table, id, name, price, categoryId) {
        document.getElementById('editAction').value = 'update_' + table;
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editPrice').value = price;
        
        // Kategori seçimini ayarla
        const categorySelect = document.getElementById('editCategory');
        if (categoryId) {
            for (let i = 0; i < categorySelect.options.length; i++) {
                if (categorySelect.options[i].value == categoryId) {
                    categorySelect.selectedIndex = i;
                    break;
                }
            }
        } else {
            categorySelect.selectedIndex = 0; // Global seçeneği
        }
        
        // Modal'ı göster
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        editModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Get current tab from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');
        
        if (activeTab) {
            // Activate the tab from URL parameter
            const tabElement = document.querySelector(`[data-bs-target="#${activeTab}"]`);
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
        }
        
        // Listen for tab changes and update URL
        const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabElements.forEach(function(tabElement) {
            tabElement.addEventListener('shown.bs.tab', function(event) {
                const targetTab = event.target.getAttribute('data-bs-target').substring(1); // Remove #
                const url = new URL(window.location);
                url.searchParams.set('tab', targetTab);
                window.history.replaceState({}, '', url);
            });
        });
    });
</script>
</body>
</html>
