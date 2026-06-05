<?php $pageTitle = 'Créer un compte — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container narrow">
    <div class="page-header">
        <h1>Créer un compte</h1>
        <a href="<?= BASE_PATH ?>/admin" class="btn btn-secondary">← Retour</a>
    </div>

    <?php if ($erreur ?? null): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($succes ?? null): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <div class="form-row">
            <div class="form-group">
                <label>Prénom *</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label>Nom *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" name="mot_de_passe" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirmer *</label>
                <input type="password" name="mot_de_passe_confirm" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rôle *</label>
                <select name="role" id="roleSelect" onchange="updateFields()" required>
                    <option value="">— Choisir —</option>
                    <?php foreach (ROLES as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($_POST['role'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="promoField">
                <label>Promotion</label>
                <select name="promotion_id">
                    <option value="">— Aucune —</option>
                    <?php foreach ($promos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom'] . ' (' . $p['annee'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group" id="coursField" style="display:none">
            <label>Cours affectés</label>
            <div class="checkbox-list">
                <?php foreach ($cours as $c): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="cours_ids[]" value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['titre'] . ' — ' . ($c['promotion_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-actions">
            <a href="<?= BASE_PATH ?>/admin" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Créer le compte</button>
        </div>
    </form>
</div>

<script>
function updateFields() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('coursField').style.display = ['enseignant','assistant'].includes(role) ? 'block' : 'none';
    document.getElementById('promoField').style.display = role === 'etudiant' ? 'block' : 'none';
}
updateFields();
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
