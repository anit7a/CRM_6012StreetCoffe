<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_login();
verify_csrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer = (int) ($_POST['customer_id'] ?? 0);
    $rawItems = $_POST['items'] ?? [];
    $lines = [];

    if (is_array($rawItems) && count($rawItems) <= 30) {
        $m = $pdo->prepare("SELECT name,price FROM menus WHERE id=? AND is_active=1");
        foreach ($rawItems as $it) {
            $qty = filter_var($it['qty'] ?? 0, FILTER_VALIDATE_INT);
            $choice = $it['menu'] ?? '';
            if (!$qty || $qty < 1) continue;
            if ($choice === '__custom') {
                $name = trim($it['custom'] ?? '');
                $price = filter_var($it['price'] ?? 0, FILTER_VALIDATE_INT);
                if ($name === '' || mb_strlen($name) > 100 || !$price || $price < 1) continue;
            } else {
                $m->execute([(int) $choice]);
                $row = $m->fetch();
                if (!$row) continue;
                $name = $row['name'];
                $price = (int) $row['price'];
            }
            $lines[] = ['name' => $name, 'qty' => $qty, 'price' => $price];
        }
    }
    if (!$lines || count($lines) !== count($rawItems)) {
        flash('Periksa kembali item pesanan (menu, jumlah, dan harga).', 'danger');
        redirect('transactions.php');
    }

    $subtotal = 0;
    $totalQty = 0;
    $parts = [];
    foreach ($lines as $l) {
        $subtotal += $l['price'] * $l['qty'];
        $totalQty += $l['qty'];
        $parts[] = $l['name'] . ' x' . $l['qty'];
    }
    $discType = $_POST['disc_type'] ?? 'none';
    $discVal = max(0, (float) ($_POST['disc_value'] ?? 0));
    $disc = 0;
    if ($discType === 'percent') $disc = (int) round($subtotal * min($discVal, 100) / 100);
    if ($discType === 'nominal') $disc = (int) min($discVal, $subtotal);
    $computed = max($subtotal - $disc, 1);
    $total = filter_var($_POST['total_amount'] ?? null, FILTER_VALIDATE_INT);
    if (!$total || $total < 1) $total = $computed;

    $summary = implode(', ', $parts);
    if (mb_strlen($summary) > 150) $summary = mb_substr($summary, 0, 147) . '...';

    if ($customer > 0) {
        $c = $pdo->prepare("SELECT id FROM customers WHERE id=?");
        $c->execute([$customer]);
        if (!$c->fetch()) {
            flash('Pelanggan tidak ditemukan.', 'danger');
            redirect('transactions.php');
        }
    }
    $points = (int) floor($total / 10000);
    try {
        $pdo->beginTransaction();
        $s = $pdo->prepare("INSERT INTO transactions(customer_id,menu_name,quantity,total_amount,points_awarded) VALUES(?,?,?,?,?)");
        $s->execute([$customer ?: null, $summary, $totalQty, $total, $points]);
        $tid = (int) $pdo->lastInsertId();
        $si = $pdo->prepare("INSERT INTO transaction_items(transaction_id,menu_name,quantity,unit_price) VALUES(?,?,?,?)");
        foreach ($lines as $l) {
            $si->execute([$tid, $l['name'], $l['qty'], $l['price']]);
        }
        if ($customer > 0 && $points > 0) {
            $u = $pdo->prepare("UPDATE customers SET points=points+? WHERE id=?");
            $u->execute([$points, $customer]);
        }
        $pdo->commit();
        flash("Transaksi tersimpan ({$totalQty} item). Poin yang diperoleh: {$points}.");
    } catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        flash('Transaksi gagal disimpan.', 'danger');
    }
    redirect('transactions.php');
}

$menuGroups = [];
foreach ($pdo->query("SELECT id,category,name,price FROM menus WHERE is_active=1 ORDER BY id")->fetchAll() as $m) {
    $menuGroups[$m['category']][] = $m;
}
$customers = $pdo->query("SELECT id,name,phone FROM customers ORDER BY name")->fetchAll();
$history = $pdo->query("SELECT t.*,c.name customer_name FROM transactions t LEFT JOIN customers c ON c.id=t.customer_id ORDER BY t.transaction_date DESC,t.id DESC LIMIT 100")->fetchAll();
$itemsByTx = [];
if ($history) {
    $in = implode(',', array_map('intval', array_column($history, 'id')));
    foreach ($pdo->query("SELECT transaction_id,menu_name,quantity FROM transaction_items WHERE transaction_id IN ($in) ORDER BY id")->fetchAll() as $i) {
        $itemsByTx[$i['transaction_id']][] = $i;
    }
}
$pageTitle = 'Transaksi & Poin';
require 'includes/header.php';
?>
<div class="section-heading">
    <div>
        <h3>Pencatatan transaksi</h3>
        <p>Setiap transaksi menghasilkan 1 poin per Rp10.000 pembayaran.</p>
    </div><span class="rule-chip">10.000 = 1 poin</span>
</div>
<div class="form-card mb-4">
    <h4>Tambah transaksi</h4>
    <form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="col-12"><label class="form-label">Pelanggan</label><select class="form-select" name="customer_id">
                <option value="0">Pelanggan umum / tidak terdaftar</option><?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name'] . ' — ' . $c['phone']) ?></option><?php endforeach; ?>
            </select></div>

        <div class="col-12">
            <label class="form-label">Item pesanan *</label>
            <div id="itemList"></div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="addItem">+ Tambah item</button>
        </div>

        <div class="col-md-3"><label class="form-label">Diskon</label>
            <select class="form-select" name="disc_type" id="discType">
                <option value="none">Tanpa diskon</option>
                <option value="percent">Persen (%)</option>
                <option value="nominal">Nominal (Rp)</option>
            </select></div>
        <div class="col-md-3"><label class="form-label">Nilai diskon</label>
            <input class="form-control" type="number" name="disc_value" id="discValue" min="0" step="1" value="0"
                disabled placeholder="Contoh: 10 atau 5000"></div>
        <div class="col-md-6"><label class="form-label">Subtotal (sebelum diskon)</label>
            <div class="points-preview" id="subtotalPreview">Rp0</div></div>
        <div class="col-md-8"><label class="form-label">Total pembayaran (Rp) *</label><input class="form-control"
                type="number" name="total_amount" id="totalAmount" min="1" step="1" required
                placeholder="Terisi otomatis dari item − diskon">
            <div class="form-text">Terisi otomatis, tetap bisa diubah manual (mis. promo khusus).</div></div>
        <div class="col-md-4"><label class="form-label">Estimasi poin</label>
            <div class="points-preview" id="pointsPreview">0 poin</div>
        </div>
        <div class="col-12"><button class="btn btn-coffee">Simpan transaksi</button></div>
    </form>
</div>

<template id="itemTpl">
    <div class="col-md-6">
        <select class="form-select menu-select" name="items[__i__][menu]" required>
            <option value="" disabled selected>— Pilih menu —</option>
            <?php foreach ($menuGroups as $group => $items): ?>
                <optgroup label="<?= e($group) ?>">
                    <?php foreach ($items as $m): ?>
                        <option value="<?= (int) $m['id'] ?>" data-price="<?= (int) $m['price'] ?>"><?= e($m['name']) ?> — <?= rupiah($m['price']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
            <option value="__custom">Lainnya (ketik manual)…</option>
        </select>
        <div class="custom-box d-none mt-2">
            <input class="form-control mb-1 custom-name" name="items[__i__][custom]" maxlength="100" placeholder="Nama menu">
            <input class="form-control custom-price" type="number" min="1" name="items[__i__][price]" placeholder="Harga satuan (Rp)">
        </div>
    </div>
    <div class="col-4 col-md-2"><input class="form-control item-qty" type="number" min="1" value="1"
            name="items[__i__][qty]" required aria-label="Jumlah"></div>
    <div class="col-6 col-md-3 d-flex align-items-center"><b class="line-total">Rp0</b></div>
    <div class="col-2 col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-item"
            title="Hapus item">&times;</button></div>
</template>

<div class="section-heading">
    <div>
        <h3>Riwayat transaksi</h3>
        <p>100 transaksi terbaru.</p>
    </div>
</div>
<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Menu</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                    <th>Poin</th>
                </tr>
            </thead>
            <tbody><?php if (!$history): ?>
                    <tr>
                        <td colspan="6" class="empty-state">Belum ada transaksi tercatat.</td>
                    </tr><?php endif; ?><?php foreach ($history as $t): ?>
                    <tr>
                        <td><?= date('d M Y H:i', strtotime($t['transaction_date'])) ?></td>
                        <td><?= e($t['customer_name'] ?? 'Pelanggan umum') ?></td>
                        <td>
                            <?php if (!empty($itemsByTx[$t['id']])): ?>
                                <?php foreach ($itemsByTx[$t['id']] as $i): ?>
                                    <div><?= e($i['menu_name']) ?> <small class="text-muted">×<?= (int) $i['quantity'] ?></small></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?= e($t['menu_name']) ?>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $t['quantity'] ?></td>
                        <td><b><?= rupiah($t['total_amount']) ?></b></td>
                        <td><span class="points-tag">+<?= (int) $t['points_awarded'] ?> pts</span></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
(function () {
    var list = document.getElementById('itemList');
    var tpl = document.getElementById('itemTpl').innerHTML;
    var total = document.getElementById('totalAmount');
    var dType = document.getElementById('discType');
    var dValue = document.getElementById('discValue');
    var sub = document.getElementById('subtotalPreview');
    var idx = 0;

    function rp(n) { return 'Rp' + n.toLocaleString('id-ID'); }

    function recalc() {
        var subtotal = 0;
        list.querySelectorAll('.item-row').forEach(function (row) {
            var sel = row.querySelector('.menu-select');
            var price = 0;
            if (sel.value === '__custom') {
                price = parseInt(row.querySelector('.custom-price').value, 10) || 0;
            } else if (sel.selectedIndex > 0) {
                price = parseInt(sel.options[sel.selectedIndex].dataset.price, 10) || 0;
            }
            var q = parseInt(row.querySelector('.item-qty').value, 10) || 0;
            var line = price * q;
            row.querySelector('.line-total').textContent = rp(line);
            subtotal += line;
        });
        var d = parseFloat(dValue.value) || 0;
        var disc = 0;
        if (dType.value === 'percent') disc = Math.round(subtotal * Math.min(d, 100) / 100);
        if (dType.value === 'nominal') disc = Math.min(d, subtotal);
        sub.textContent = rp(subtotal);
        if (subtotal > 0) total.value = Math.max(subtotal - disc, 1);
        else total.value = '';
        total.dispatchEvent(new Event('input', { bubbles: true })); // update estimasi poin
    }

    function addRow() {
        var row = document.createElement('div');
        row.className = 'row g-2 item-row mb-2';
        row.innerHTML = tpl.replace(/__i__/g, idx++);
        list.appendChild(row);
        recalc();
    }

    list.addEventListener('change', function (e) {
        if (e.target.classList.contains('menu-select')) {
            var row = e.target.closest('.item-row');
            var isCustom = e.target.value === '__custom';
            row.querySelector('.custom-box').classList.toggle('d-none', !isCustom);
            row.querySelector('.custom-name').required = isCustom;
            row.querySelector('.custom-price').required = isCustom;
        }
        recalc();
    });
    list.addEventListener('input', recalc);
    list.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            if (list.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
                recalc();
            }
        }
    });
    document.getElementById('addItem').addEventListener('click', addRow);
    dType.addEventListener('change', function () {
        dValue.disabled = dType.value === 'none';
        if (dType.value === 'none') dValue.value = 0;
        dValue.max = dType.value === 'percent' ? 100 : '';
        recalc();
    });
    dValue.addEventListener('input', recalc);

    addRow(); 
})();
</script>
<?php require 'includes/footer.php'; ?>