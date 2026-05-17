<?php $isEdit = !empty($editing); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Medicines &mdash; MediShop Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<?php require 'views/_admin_navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">&#128138; Manage Medicines</h1>
            <p class="page-sub">Add, edit, search and remove medicines</p>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php $msgs = ['added' => 'Medicine added successfully.',
                       'updated' => 'Medicine updated successfully.',
                       'deleted' => 'Medicine deleted successfully.']; ?>
        <?php if (!empty($msgs[$_GET['msg']])): ?>
            <div class="alert alert-success"><?= $msgs[$_GET['msg']] ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- ============ Add / Edit Form ============ -->
    <div class="card form-card">
        <h3 class="card-title">
            <?= $isEdit ? '&#9998; Edit Medicine (#' . intval($editing['id']) . ')' : '&#43; Add New Medicine' ?>
        </h3>
        <form method="POST"
              action="index.php?page=admin_medicines&action=<?= $isEdit ? 'update&id=' . intval($editing['id']) : 'add' ?>"
              enctype="multipart/form-data"
              class="form" novalidate>

            <div class="field-row">
                <div class="field">
                    <label for="name">Medicine Name *</label>
                    <input type="text" id="name" name="name"
                           value="<?= h($editing['name'] ?? '') ?>"
                           placeholder="e.g. Napa Extra 500mg" required>
                </div>
                <div class="field">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= (isset($editing['category_id']) && $editing['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= h($cat['name']) ?> (<?= h($cat['category_type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="vendor_name">Vendor Name *</label>
                    <input type="text" id="vendor_name" name="vendor_name"
                           value="<?= h($editing['vendor_name'] ?? '') ?>"
                           placeholder="e.g. Beximco Pharma" required>
                </div>
                <div class="field">
                    <label for="price">Price (&#2547;) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0"
                           value="<?= h($editing['price'] ?? '') ?>"
                           placeholder="0.00" required>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="availability">Stock Quantity *</label>
                    <input type="number" id="availability" name="availability" min="0"
                           value="<?= h($editing['availability'] ?? '') ?>"
                           placeholder="0" required>
                </div>
                <div class="field">
                    <label for="image">
                        Medicine Image <?= $isEdit ? '(leave empty to keep current)' : '' ?>
                    </label>
                    <input type="file" id="image" name="image"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <?php if ($isEdit && !empty($editing['image_path']) && file_exists($editing['image_path'])): ?>
                        <div style="margin-top:6px;">
                            <img src="<?= h($editing['image_path']) ?>" alt="current"
                                 style="height:48px;border-radius:6px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--text-muted);margin-left:8px;">Current image</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          placeholder="Brief description of the medicine..."><?= h($editing['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=admin_medicines" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Medicine</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Save Medicine</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ============ Medicines Table ============ -->
    <div class="card">
        <div class="card-toolbar">
            <div class="search-wrap">
                <span class="search-icon">&#128269;</span>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Search medicines by name, vendor...">
            </div>
            <span class="badge" id="resultCount"><?= count($medicines) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($medicines)): ?>
                        <tr><td colspan="8" class="empty">No medicines yet. Add one above.</td></tr>
                    <?php else: ?>
                        <?php foreach ($medicines as $i => $med): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="table-img">
                                    <?php if (!empty($med['image_path']) && file_exists($med['image_path'])): ?>
                                        <img src="<?= h($med['image_path']) ?>" alt="">
                                    <?php else: ?>
                                        &#128138;
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?= h($med['name']) ?></strong></td>
                            <td>
                                <?= h($med['category_name']) ?>
                                <span class="med-card-type type-<?= h($med['category_type']) ?>"
                                      style="display:inline-block;margin-left:4px;padding:2px 6px;">
                                    <?= ucfirst(h($med['category_type'])) ?>
                                </span>
                            </td>
                            <td><?= h($med['vendor_name']) ?></td>
                            <td class="text-right">&#2547; <?= number_format($med['price'], 2) ?></td>
                            <td class="text-right">
                                <?php if ($med['availability'] > 0): ?>
                                    <span style="color:var(--primary);font-weight:600;"><?= $med['availability'] ?></span>
                                <?php else: ?>
                                    <span class="out-of-stock">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a class="btn-sm btn-edit"
                                   href="index.php?page=admin_medicines&action=edit&id=<?= $med['id'] ?>">Edit</a>
                                <a class="btn-sm btn-delete"
                                   href="index.php?page=admin_medicines&action=delete&id=<?= $med['id'] ?>"
                                   onclick="return confirm('Delete this medicine?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop Admin Panel</footer>

<script>
(function () {
    var input   = document.getElementById('searchInput');
    var body    = document.getElementById('tableBody');
    var counter = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function render(rows) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="8" class="empty">No matching medicines.</td></tr>';
            counter.textContent = '0 results';
            return;
        }
        var html = '';
        rows.forEach(function (m, i) {
            var imgHtml = m.image_path
                ? '<img src="' + esc(m.image_path) + '" alt="" style="width:100%;height:100%;object-fit:cover;">'
                : '&#128138;';
            var stockHtml = parseInt(m.availability) > 0
                ? '<span style="color:var(--primary);font-weight:600;">' + esc(m.availability) + '</span>'
                : '<span class="out-of-stock">0</span>';
            var typeClass = m.category_type === 'liquid' ? 'type-liquid' : 'type-solid';
            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td><div class="table-img">' + imgHtml + '</div></td>' +
                '<td><strong>' + esc(m.name) + '</strong></td>' +
                '<td>' + esc(m.category_name) + ' <span class="med-card-type ' + typeClass + '" style="display:inline-block;margin-left:4px;padding:2px 6px;">' + (m.category_type === 'liquid' ? 'Liquid' : 'Solid') + '</span></td>' +
                '<td>' + esc(m.vendor_name) + '</td>' +
                '<td class="text-right">&#2547; ' + parseFloat(m.price).toFixed(2) + '</td>' +
                '<td class="text-right">' + stockHtml + '</td>' +
                '<td class="text-right">' +
                    '<a class="btn-sm btn-edit" href="index.php?page=admin_medicines&action=edit&id=' + m.id + '">Edit</a>' +
                    '<a class="btn-sm btn-delete" href="index.php?page=admin_medicines&action=delete&id=' + m.id + '" onclick="return confirm(\'Delete this medicine?\')">Delete</a>' +
                '</td></tr>';
        });
        body.innerHTML = html;
        counter.textContent = rows.length + (input.value.trim() ? ' results' : ' total');
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            fetch('index.php?page=ajax&type=medicines&q=' + encodeURIComponent(input.value.trim()),
                  { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        }, 220);
    });
})();
</script>

</body>
</html>
