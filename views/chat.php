<?php $pageTitle = 'Messagerie — FasiChat Classroom'; ?>
<?php include __DIR__ . '/layout/header.php'; ?>

<div class="chat-layout">
    <!-- Liste des contacts -->
    <aside class="contacts-panel">
        <div class="contacts-header">
            <h2>Conversations</h2>
            <span class="badge"><?= count($contacts) ?></span>
        </div>
        <div class="contacts-search">
            <input type="text" id="searchContact" placeholder="Rechercher…" onkeyup="filterContacts(this.value)">
        </div>
        <ul class="contacts-list" id="contactsList">
            <?php if (empty($contacts)): ?>
            <li class="no-contact">Aucun contact disponible selon votre rôle.</li>
            <?php else: ?>
            <?php foreach ($contacts as $contact): ?>
            <li class="contact-item <?= $selectedId === (int)$contact['id'] ? 'active' : '' ?>"
                onclick="location.href='<?= BASE_PATH ?>/chat?avec=<?= $contact['id'] ?>'">
                <div class="contact-avatar role-<?= $contact['role'] ?>">
                    <?= strtoupper(substr($contact['prenom'], 0, 1) . substr($contact['nom'], 0, 1)) ?>
                </div>
                <div class="contact-info">
                    <span class="contact-name"><?= htmlspecialchars($contact['prenom'] . ' ' . $contact['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="contact-role"><?= ROLES[$contact['role']] ?? $contact['role'] ?></span>
                </div>
            </li>
            <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <?php if (!empty($messagesPublics)): ?>
        <div class="public-wall">
            <div class="contacts-header">
                <h2>Mur public de la promo</h2>
            </div>
            <div class="public-messages">
                <?php foreach ($messagesPublics as $mp): ?>
                <div class="public-msg">
                    <div class="msg-avatar role-<?= $mp['exp_role'] ?>">
                        <?= strtoupper(substr($mp['exp_prenom'], 0, 1) . substr($mp['exp_nom'], 0, 1)) ?>
                    </div>
                    <div class="msg-body">
                        <span class="msg-author"><?= htmlspecialchars($mp['exp_prenom'] . ' ' . $mp['exp_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                        <p><?= nl2br(htmlspecialchars($mp['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <span class="msg-time"><?= date('d/m H:i', strtotime($mp['date_envoi'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Zone de chat -->
    <section class="chat-area">
        <?php if ($selectedUser): ?>
        <div class="chat-header">
            <div class="contact-avatar role-<?= $selectedUser['role'] ?>" style="width:40px;height:40px;font-size:14px">
                <?= strtoupper(substr($selectedUser['prenom'], 0, 1) . substr($selectedUser['nom'], 0, 1)) ?>
            </div>
            <div>
                <h3><?= htmlspecialchars($selectedUser['prenom'] . ' ' . $selectedUser['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                <span class="role-badge role-<?= $selectedUser['role'] ?>"><?= ROLES[$selectedUser['role']] ?? '' ?></span>
            </div>
            <?php
            $isPublic = ($currentUser['role'] === 'etudiant' && in_array($selectedUser['role'], ['enseignant','assistant'], true))
                     || (in_array($currentUser['role'], ['enseignant','assistant'], true) && $selectedUser['role'] === 'etudiant');
            if ($isPublic): ?>
            <span class="visibility-badge public">👁 Visible par la promotion</span>
            <?php else: ?>
            <span class="visibility-badge private">🔒 Message privé</span>
            <?php endif; ?>
        </div>

        <div class="messages-container" id="messagesContainer">
            <?php if (empty($messages)): ?>
            <div class="empty-chat">
                <svg width="64" height="64" fill="none" stroke="#CBD5E0" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <p>Commencez la conversation…</p>
            </div>
            <?php endif; ?>

            <?php foreach ($messages as $msg): ?>
            <?php $isMine = (int)$msg['expediteur_id'] === $currentUser['id']; ?>
            <div class="message <?= $isMine ? 'message-mine' : 'message-other' ?>" data-id="<?= $msg['id'] ?>">
                <?php if (!$isMine): ?>
                <div class="msg-avatar-small role-<?= $msg['exp_role'] ?>">
                    <?= strtoupper(substr($msg['exp_prenom'], 0, 1) . substr($msg['exp_nom'], 0, 1)) ?>
                </div>
                <?php endif; ?>
                <div class="message-bubble">
                    <?php if (!$isMine): ?>
                    <span class="msg-sender"><?= htmlspecialchars($msg['exp_prenom'] . ' ' . $msg['exp_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if (!empty($msg['contenu'])): ?>
                    <p><?= nl2br(htmlspecialchars($msg['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($msg['fichier_chemin'])): ?>
                    <div class="message-file">
                        <?php if (str_starts_with($msg['fichier_type'] ?? '', 'image/')): ?>
                        <img src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($msg['fichier_chemin'], ENT_QUOTES, 'UTF-8') ?>" alt="Image" class="msg-image">
                        <?php elseif (str_starts_with($msg['fichier_type'] ?? '', 'audio/')): ?>
                        <audio controls src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($msg['fichier_chemin'], ENT_QUOTES, 'UTF-8') ?>"></audio>
                        <?php elseif (str_starts_with($msg['fichier_type'] ?? '', 'video/')): ?>
                        <video controls src="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($msg['fichier_chemin'], ENT_QUOTES, 'UTF-8') ?>" class="msg-video"></video>
                        <?php else: ?>
                        <a href="<?= BASE_PATH ?>/uploads/<?= htmlspecialchars($msg['fichier_chemin'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="file-link">
                            📎 <?= htmlspecialchars($msg['fichier_nom'] ?? 'Fichier', ENT_QUOTES, 'UTF-8') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <span class="msg-time"><?= date('H:i', strtotime($msg['date_envoi'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulaire d'envoi -->
        <form id="messageForm" class="message-form" enctype="multipart/form-data">
            <input type="hidden" name="destinataire_id" value="<?= $selectedUser['id'] ?>">
            <input type="hidden" id="isPublicMsg" value="<?= $isPublic ? '1' : '0' ?>">
            <input type="hidden" id="promotionId" value="<?= $currentUser['promotion_id'] ?? '' ?>">
            <div class="message-input-area">
                <label class="file-upload-btn" title="Joindre un fichier">
                    <input type="file" name="fichier" id="fichierInput" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                </label>
                <textarea name="contenu" id="msgContenu" placeholder="Votre message…" rows="1"
                          onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"></textarea>
                <button type="button" class="btn-send" onclick="sendMessage()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </div>
            <div id="filePreview" class="file-preview" style="display:none"></div>
        </form>

        <script>
        let lastMessageId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
        const destId = <?= (int)$selectedUser['id'] ?>;
        const isPublic = <?= $isPublic ? 'true' : 'false' ?>;
        const promotionId = <?= (int)($currentUser['promotion_id'] ?? 0) ?>;
        const basePath = '<?= BASE_PATH ?>';

        // Scroll automatique vers le bas
        const container = document.getElementById('messagesContainer');
        container.scrollTop = container.scrollHeight;

        // Polling des nouveaux messages toutes les 3 secondes
        setInterval(pollMessages, 3000);

        function pollMessages() {
            fetch(`${basePath}/chat/messages?avec=${destId}&depuis=${lastMessageId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(appendMessage);
                        lastMessageId = data.messages[data.messages.length - 1].id;
                        container.scrollTop = container.scrollHeight;
                    }
                }).catch(() => {});
        }

        function appendMessage(msg) {
            const myId = <?= (int)$currentUser['id'] ?>;
            const isMine = parseInt(msg.expediteur_id) === myId;
            const initials = (msg.exp_prenom?.[0] ?? '').toUpperCase() + (msg.exp_nom?.[0] ?? '').toUpperCase();

            const div = document.createElement('div');
            div.className = `message ${isMine ? 'message-mine' : 'message-other'}`;
            div.dataset.id = msg.id;

            let fileHtml = '';
            if (msg.fichier_chemin) {
                const type = msg.fichier_type || '';
                if (type.startsWith('image/')) {
                    fileHtml = `<img src="${basePath}/uploads/${msg.fichier_chemin}" class="msg-image" alt="Image">`;
                } else if (type.startsWith('audio/')) {
                    fileHtml = `<audio controls src="${basePath}/uploads/${msg.fichier_chemin}"></audio>`;
                } else if (type.startsWith('video/')) {
                    fileHtml = `<video controls src="${basePath}/uploads/${msg.fichier_chemin}" class="msg-video"></video>`;
                } else {
                    fileHtml = `<a href="${basePath}/uploads/${msg.fichier_chemin}" target="_blank" class="file-link">📎 ${escHtml(msg.fichier_nom || 'Fichier')}</a>`;
                }
            }

            div.innerHTML = `
                ${!isMine ? `<div class="msg-avatar-small role-${msg.exp_role}">${initials}</div>` : ''}
                <div class="message-bubble">
                    ${!isMine ? `<span class="msg-sender">${escHtml(msg.exp_prenom + ' ' + msg.exp_nom)}</span>` : ''}
                    ${msg.contenu ? `<p>${escHtml(msg.contenu).replace(/\n/g,'<br>')}</p>` : ''}
                    ${fileHtml ? `<div class="message-file">${fileHtml}</div>` : ''}
                    <span class="msg-time">${msg.date_envoi.substring(11,16)}</span>
                </div>`;
            container.appendChild(div);
        }

        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function sendMessage() {
            const contenu = document.getElementById('msgContenu').value.trim();
            const fichier = document.getElementById('fichierInput').files[0];
            if (!contenu && !fichier) return;

            const form = document.getElementById('messageForm');
            const fd   = new FormData(form);
            const url  = isPublic ? `${basePath}/chat/envoyer-public` : `${basePath}/chat/envoyer-prive`;

            if (isPublic) fd.append('promotion_id', promotionId);

            fetch(url, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        appendMessage(data.message);
                        lastMessageId = data.message.id;
                        document.getElementById('msgContenu').value = '';
                        document.getElementById('fichierInput').value = '';
                        document.getElementById('filePreview').style.display = 'none';
                        container.scrollTop = container.scrollHeight;
                    } else {
                        alert(data.error || 'Erreur lors de l\'envoi.');
                    }
                }).catch(() => alert('Erreur réseau.'));
        }

        // Prévisualisation fichier
        document.getElementById('fichierInput').addEventListener('change', function() {
            const preview = document.getElementById('filePreview');
            if (this.files[0]) {
                preview.textContent = '📎 ' + this.files[0].name;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });

        function filterContacts(val) {
            document.querySelectorAll('#contactsList .contact-item').forEach(el => {
                el.style.display = el.textContent.toLowerCase().includes(val.toLowerCase()) ? '' : 'none';
            });
        }
        </script>

        <?php else: ?>
        <div class="chat-empty-state">
            <svg width="80" height="80" fill="none" stroke="#CBD5E0" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <h3>Sélectionnez une conversation</h3>
            <p>Choisissez un contact dans la liste pour commencer à discuter.</p>
        </div>
        <?php endif; ?>
    </section>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
