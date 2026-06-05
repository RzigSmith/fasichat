<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FasiChat Classroom', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php if (SessionManager::estConnecte()): ?>
<?php $currentUser = SessionManager::getUser(); ?>
<nav class="navbar">
    <div class="navbar-brand">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="28" height="28" rx="8" fill="#4F46E5"/>
            <path d="M7 9h14M7 14h10M7 19h7" stroke="white" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>FasiChat<strong>Classroom</strong></span>
    </div>
    <ul class="navbar-nav">
        <li><a href="<?= BASE_PATH ?>/chat" class="<?= str_starts_with($_SERVER['REQUEST_URI'], BASE_PATH . '/chat') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Messagerie
        </a></li>
        <li><a href="<?= BASE_PATH ?>/valve" class="<?= str_starts_with($_SERVER['REQUEST_URI'], BASE_PATH . '/valve') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Valve
        </a></li>
        <?php if (in_array($currentUser['role'], ['enseignant', 'assistant'], true)): ?>
        <li><a href="<?= BASE_PATH ?>/dashboard" class="<?= str_starts_with($_SERVER['REQUEST_URI'], BASE_PATH . '/dashboard') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Tableau de bord
        </a></li>
        <li><a href="<?= BASE_PATH ?>/convocations" class="<?= str_starts_with($_SERVER['REQUEST_URI'], BASE_PATH . '/convocations') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91"/></svg>
            Convocations
        </a></li>
        <?php endif; ?>
        <?php if (in_array($currentUser['role'], ['doyen', 'viceDoyen'], true)): ?>
        <li><a href="<?= BASE_PATH ?>/convocation/envoyer">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Convoquer
        </a></li>
        <li><a href="<?= BASE_PATH ?>/admin">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Administration
        </a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-user">
        <div class="user-avatar"><?= strtoupper(substr($currentUser['prenom'], 0, 1) . substr($currentUser['nom'], 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="role-badge role-<?= $currentUser['role'] ?>"><?= ROLES[$currentUser['role']] ?? $currentUser['role'] ?></span>
        </div>
        <a href="<?= BASE_PATH ?>/logout" class="btn-logout" title="Se déconnecter">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>
</nav>
<?php endif; ?>
<main class="main-content">
<?php
$flash = SessionManager::getFlash();
if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>">
    <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>
