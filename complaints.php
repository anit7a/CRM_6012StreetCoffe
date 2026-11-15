<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_login();
verify_csrf();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $customer = (int) ($_POST['customer_id'] ?? 0);
        $category = $_POST['category'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $pic = trim($_POST['pic'] ?? '');
        $status = $_POST['status'] ?? 'Baru';
        $response = trim($_POST['response'] ?? '');
        if (!in_array($category, ['Keluhan', 'Saran'], true) || $description === '' || !in_array($status, ['Baru', 'Diproses', 'Selesai'], true)) {
            flash('Periksa kembali kategori, deskripsi, dan status.', 'danger');
            redirect('complaints.php');
        }
        // Status Selesai wajib disertai respon dari PIC
        if ($status === 'Selesai' && $response === '') {
            flash('Respon PIC wajib diisi jika status Selesai.', 'danger');
            redirect('complaints.php');
        }
        $s = $pdo->prepare("INSERT INTO complaints(customer_id,category,description,pic,status,response,responded_at) VALUES(?,?,?,?,?,?,?)");
        $s->execute([
            $customer ?: null, $category, $description, $pic ?: null, $status,
            $response ?: null, $response !== '' ? date('Y-m-d H:i:s') : null
        ]);
        flash('Keluhan atau saran berhasil dicatat.');
        redirect('complaints.php');
    }
    if ($action === 'status') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $response = trim($_POST['response'] ?? '');
        if (!in_array($status, ['Baru', 'Diproses', 'Selesai'], true)) {
            redirect('complaints.php');
        }
        if ($status === 'Selesai' && $response === '') {
            flash('Respon PIC wajib diisi sebelum status diubah menjadi Selesai.', 'danger');
            redirect('complaints.php');
        }
        $s = $pdo->prepare("UPDATE complaints SET status=?, response=?, responded_at=IF(?='', responded_at, NOW()) WHERE id=?");
        $s->execute([$status, $response ?: null, $response, $id]);
        flash('Status dan respon penanganan diperbarui.');
        redirect('complaints.php');
    }
}
$customers = $pdo->query("SELECT id,name FROM customers ORDER BY name")->fetchAll();
$rows = $pdo->query("SELECT x.*,c.name customer_name FROM complaints x LEFT JOIN customers c ON c.id=x.customer_id ORDER BY x.complaint_date DESC,x.id DESC")->fetchAll();
$pageTitle = 'Keluhan & Saran';
require 'includes/header.php';
?>
<div class="section-heading">
    <div>
        <h3>Suara pelanggan</h3>
        <p>Catat masukan dan pantau tindak lanjutnya.</p>
    </div>
</div>
<div class="form-card mb-4">
    <h4>Catat keluhan atau saran</h4>
    <form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input
            type="hidden" name="action" value="save">
        <div class="col-md-6"><label class="form-label">Pelanggan</label><select class="form-select" name="customer_id">
                <option value="0">Tidak diketahui / anonim</option><?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-3"><label class="form-label">Kategori</label><select class="form-select" name="category">
                <option>Keluhan</option>
                <option>Saran</option>
            </select></div>
        <div class="col-md-3"><label class="form-label">Status awal</label><select class="form-select" name="status"
                id="newStatus">
                <option>Baru</option>
                <option>Diproses</option>
                <option>Selesai</option>
            </select></div>
        <div class="col-md-8"><label class="form-label">Deskripsi *</label><textarea class="form-control"
                name="description" rows="2" required maxlength="2000"></textarea></div>
        <div class="col-md-4"><label class="form-label">Penanggung jawab (PIC)</label><input class="form-control"
                name="pic" maxlength="100" placeholder="Nama petugas"></div>
        <div class="col-12"><label class="form-label">Respon PIC <span id="newRespReq"
                    class="text-danger d-none">*</span></label><textarea class="form-control" name="response"
                id="newResponse" rows="2" maxlength="2000"
                placeholder="Wajib diisi jika status Selesai"></textarea></div>
        <div class="col-12"><button class="btn btn-coffee">Simpan masukan</button></div>
    </form>
</div>
<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID / Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Kategori & Deskripsi</th>
                    <th>PIC</th>
                    <th>Respon PIC</th>
                    <th>Status penanganan</th>
                </tr>
            </thead>
            <tbody><?php if (!$rows): ?>
                    <tr>
                        <td colspan="6" class="empty-state">Belum ada keluhan atau saran.</td>
                    </tr><?php endif; ?><?php foreach ($rows as $r): ?>
                    <tr>
                        <td>#<?= $r['id'] ?><small
                                class="d-block text-muted"><?= date('d M Y', strtotime($r['complaint_date'])) ?></small></td>
                        <td><?= e($r['customer_name'] ?? 'Anonim') ?></td>
                        <td><span class="status-pill pill-gray"><?= e($r['category']) ?></span>
                            <div class="description-cell"><?= e($r['description']) ?></div>
                        </td>
                        <td><?= e($r['pic'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($r['response'])): ?>
                                <div class="description-cell"><?= nl2br(e($r['response'])) ?></div>
                                <?php if (!empty($r['responded_at'])): ?>
                                    <small class="d-block text-muted">Direspon <?= date('d M Y H:i', strtotime($r['responded_at'])) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Belum ada respon</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" class="status-form">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <select class="form-select form-select-sm mb-2 status-select" name="status">
                                    <?php foreach (['Baru', 'Diproses', 'Selesai'] as $st): ?>
                                        <option <?= $r['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <textarea class="form-control form-control-sm mb-2 resp-input" name="response" rows="2"
                                    maxlength="2000"
                                    placeholder="Respon PIC (wajib jika Selesai)"><?= e($r['response'] ?? '') ?></textarea>
                                <button class="btn btn-sm btn-coffee">Simpan</button>
                            </form>
                        </td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    
function bindRequired(selectEl, textEl, starEl) {
    function sync() {
        var need = selectEl.value === 'Selesai';
        textEl.required = need;
        if (starEl) starEl.classList.toggle('d-none', !need);
    }
    selectEl.addEventListener('change', sync);
    sync();
}
bindRequired(document.getElementById('newStatus'), document.getElementById('newResponse'),
    document.getElementById('newRespReq'));
document.querySelectorAll('.status-form').forEach(function (f) {
    bindRequired(f.querySelector('.status-select'), f.querySelector('.resp-input'), null);
});
</script>
<?php require 'includes/footer.php'; ?>