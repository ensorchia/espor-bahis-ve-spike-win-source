<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Hata kontrolü
    $errors = [];

    if (empty($email) || empty($username) || empty($password) || empty($password_confirm)) {
        $errors[] = "Tüm alanları doldurunuz.";
    }

    if ($password != $password_confirm) {
        $errors[] = "Şifreler eşleşmiyor.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalıdır.";
    }

    // E-posta kontrolü
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Bu e-posta adresi zaten kullanımda.";
    }

    // Kullanıcı adı kontrolü
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Bu kullanıcı adı zaten kullanımda.";
    }

    if (empty($errors)) {
        // Şifreyi hashle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Kullanıcıyı veritabanına ekle
        $stmt = $db->prepare("INSERT INTO users (email, username, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$email, $username, $hashed_password]);

        // Başarılı kayıt sonrası giriş sayfasına yönlendir
        header("Location: giris.php?success=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>chiawin.gg - Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <span class="logo-text">chiawin</span><span class="logo-gg">.gg</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="valorant.html">Valorant</a></li>
                    <li class="nav-item"><a class="nav-link" href="lol.html">League of Legends</a></li>
                    <li class="nav-item"><a class="nav-link" href="tft.html">TFT</a></li>
                    <li class="nav-item"><a class="nav-link" href="hesap-al.html">Spike Win Hesabı Al</a></li>
                    <li class="nav-item"><a class="nav-link" href="hesap-sat.html">Spike Win Hesabı Sat</a></li>
                    <li class="nav-item"><a class="nav-link" href="turnuvalar.html">Turnuvalar</a></li>
                    <li class="nav-item"><a class="nav-link" href="canli-bahis.html">Canlı Bahis</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="giris.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="kayit.php">Kayıt Ol</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil.php"><i class="fas fa-user"></i></a></li>
                    <li class="nav-item"><a class="nav-link" href="bakiye.php">Bakiye: 0 CC</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1>Kayıt Ol</h1>
            <p>Yeni hesap oluşturun</p>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="game-card">
                    <div class="game-header">
                        <h3>Kayıt Formu</h3>
                        <span class="live-badge">GÜVENLİ</span>
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
                        
                        <form method="POST" class="register-form">
                            <div class="mb-3">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kullanıcı Adı</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Şifre Tekrar</label>
                                <input type="password" name="password_confirm" class="form-control" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    <a href="kullanim-kosullari.html">Kullanım Koşulları</a>'nı okudum ve kabul ediyorum
                                </label>
                            </div>
                            <button type="submit" class="btn-bet w-100">Kayıt Ol</button>
                        </form>
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
                        <li><a href="hakkimizda.html">Biz Kimiz?</a></li>
                        <li><a href="iletisim.html">İletişim</a></li>
                        <li><a href="blog.html">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Hesap İşlemleri</h5>
                    <ul>
                        <li><a href="hesap-al.html">Hesap Al</a></li>
                        <li><a href="hesap-sat.html">Hesap Sat</a></li>
                        <li><a href="hesap-gecmisi.html">İşlem Geçmişi</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Bahis</h5>
                    <ul>
                        <li><a href="canli-bahis.html">Canlı Bahis</a></li>
                        <li><a href="kuponlarim.html">Kuponlarım</a></li>
                        <li><a href="istatistikler.html">İstatistikler</a></li>
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