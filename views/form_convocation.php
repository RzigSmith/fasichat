<?php $pageTitle = 'Envoyer une convocation — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="page-container narrow">
    <div class="page-header">
        <h1>📨 Convoquer une réunion</h1>
        <p>La convocation sera envoyée à l'ensemble des enseignants et assistants.</p>
    </div>

    <?php if ($erreur ?? null): ?>
    <div class="alert alert-error"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($succes ?? null): ?>
    <div class="alert alert-success"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <div class="form-group">
            <label>Objet de la réunion *</label>
            <input type="text" name="objet" value="<?= htmlspecialchars($_POST['objet'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="Ex : Réunion pédagogique de fin de semestre">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Date et heure *</label>
                <input type="datetime-local" name="date_reunion" value="<?= htmlspecialchars($_POST['date_reunion'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="form-group">
                <label>Lieu / Lien de réunion *</label>
                <input type="text" name="lieu" value="<?= htmlspecialchars($_POST['lieu'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required placeholder="Salle A101 ou https://meet.example.com/...">
            </div>
        </div>
        <div class="form-group">
            <label>Message explicatif (optionnel)</label>
            <textarea name="message" rows="4" placeholder="Informations complémentaires sur la réunion…"><?= htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="alert alert-info">
            <strong>Note :</strong> Cette convocation sera envoyée automatiquement à <strong>tous les enseignants et assistants</strong> enregistrés sur la plateforme.
        </div>
        <div class="form-actions">
            <a href="<?= BASE_PATH ?>/convocations" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Envoyer la convocation</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
