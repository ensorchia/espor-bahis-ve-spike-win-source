<?php
require_once 'auth.php';
requireLogin();

$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $type = $_POST['type'];

    if ($amount < 10) {
        $errors[] = "Minimum çekim tutarı 10 CC'dir.";
    }

    if ($amount > 5000) {
        $errors[] = "Maksimum çekim tutarı 5000 CC'dir.";
    }

    if ($amount > $user['balance']) {
        $errors[] = "Yetersiz bakiye.";
    }

    if (empty($errors)) {
        if (updateBalance($user['id'], -$amount, 'withdraw')) {
            $success = true;
            $user = getCurrentUser(); // Bakiye bilgilerini güncelle
        } else {
            $errors[] = "İşlem sırasında bir hata oluştu.";
        }
    }
}

// İşlem geçmişini al
$stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>chiawin.gg - Bakiye</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="logo-text">chiawin</span><span class="logo-gg">.gg</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="valorant.php">Valorant</a></li>
                    <li class="nav-item"><a class="nav-link" href="lol.php">League of Legends</a></li>
                    <li class="nav-item"><a class="nav-link" href="tft.php">TFT</a></li>
                    <li class="nav-item"><a class="nav-link" href="hesap-al.php">Spike Win Hesabı Al</a></li>
                    <li class="nav-item"><a class="nav-link" href="hesap-sat.php">Spike Win Hesabı Sat</a></li>
                    <li class="nav-item"><a class="nav-link" href="turnuvalar.php">Turnuvalar</a></li>
                    <li class="nav-item"><a class="nav-link" href="canli-bahis.php">Canlı Bahis</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="profil.php"><?php echo htmlspecialchars($user['username']); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="bakiye.php">Bakiye: <?php echo number_format($user['balance'], 2); ?> CC</a></li>
                    <li class="nav-item"><a class="nav-link" href="cikis.php">Çıkış Yap</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1>Bakiye İşlemleri</h1>
            <p>Bakiyenizi yönetin ve işlemlerinizi takip edin</p>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <div class="game-card">
                    <div class="game-header">
                        <h3>Bakiye Bilgileri</h3>
                        <span class="live-badge">GÜNCEL</span>
                    </div>
                    <div class="match-card">
                        <div class="balance-info">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Mevcut Bakiye:</span>
                                <span class="balance-amount"><?php echo number_format($user['balance'], 2); ?> CC</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Toplam Kazanç:</span>
                                <span><?php echo number_format($user['balance'], 2); ?> CC</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Toplam Harcama:</span>
                                <span>0 CC</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="game-card mt-4">
                    <div class="game-header">
                        <h3>Bakiye Çekme</h3>
                        <span class="upcoming-badge">HIZLI</span>
                    </div>
                    <div class="match-card">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                İşlem başarıyla tamamlandı.
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="balance-form">
                            <div class="mb-3">
                                <label class="form-label">Çekilecek Miktar (CC)</label>
                                <input type="number" name="amount" class="form-control" min="1" required>
                                <small class="text-muted">1 CC = 1 TL</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Çekim Yöntemi</label>
                                <select name="type" class="form-select" required>
                                    <option value="">Seçiniz</option>
                                    <option value="bank">Banka Havalesi</option>
                                    <option value="card">Kredi Kartı</option>
                                    <option value="crypto">Kripto Para</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-bet w-100">Bakiye Çek</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="game-card">
                    <div class="game-header">
                        <h3>İşlem Geçmişi</h3>
                        <span class="stock-badge">SON 10</span>
                    </div>
                    <div class="match-card">
                        <div class="table-responsive">
                            <table class="table table-dark">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>İşlem</th>
                                        <th>Tutar</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Henüz işlem bulunmuyor</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo date('d.m.Y H:i', strtotime($transaction['created_at'])); ?></td>
                                                <td><?php echo $transaction['type']; ?></td>
                                                <td><?php echo number_format($transaction['amount'], 2); ?> CC</td>
                                                <td><?php echo $transaction['status']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="game-card mt-4">
                    <div class="game-header">
                        <h3>Bakiye Kuralları</h3>
                        <span class="stock-badge">ÖNEMLİ</span>
                    </div>
                    <div class="match-card">
                        <div class="rules-content">
                            <ul>
                                <li>1 CC = 1 TL</li>
                                <li>Minimum çekim tutarı: 10 CC</li>
                                <li>Maksimum çekim tutarı: 5000 CC</li>
                                <li>Çekim işlemleri 3 iş günü içinde tamamlanır</li>
                                <li>7/24 destek hizmeti</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>Hakkımızda</h5>
                    <ul>
                        <li><a href="hakkimizda.php">Biz Kimiz?</a></li>
                        <li><a href="iletisim.php">İletişim</a></li>
                        <li><a href="blog.php">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Hesap İşlemleri</h5>
                    <ul>
                        <li><a href="hesap-al.php">Hesap Al</a></li>
                        <li><a href="hesap-sat.php">Hesap Sat</a></li>
                        <li><a href="hesap-gecmisi.php">İşlem Geçmişi</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Bahis</h5>
                    <ul>
                        <li><a href="canli-bahis.php">Canlı Bahis</a></li>
                        <li><a href="kuponlarim.php">Kuponlarım</a></li>
                        <li><a href="istatistikler.php">İstatistikler</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Sosyal Medya</h5>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 chiawin.gg - Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
</body>
</html> 