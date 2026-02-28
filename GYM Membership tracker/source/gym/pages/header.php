<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Misafir';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
?>
<div class="top-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">ESOĞÜ Spor Salonu Yönetim Sistemi</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="../index.php" class="btn btn-danger btn-sm" style="text-decoration: none;">
                <i class="fas fa-sign-out-alt me-1"></i>Çıkış Yap
            </a>
            <div class="text-end">
                <strong><?php echo htmlspecialchars($username); ?></strong>
                <br>
                <small class="text-muted"><?php echo ucfirst($role); ?></small>
            </div>
            <div class="user-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
        </div>
    </div>
</div>
