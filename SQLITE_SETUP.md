# Migration vers SQLite

## Configuration

Votre projet est maintenant configuré pour utiliser **SQLite** au lieu de MySQL.

### Fichiers modifiés:
- `config/config.php` - `DB_DRIVER` changé de `'mysql'` à `'sqlite'`

### Avantages de SQLite:
✓ Pas de serveur MySQL nécessaire  
✓ Base de données stockée dans un simple fichier (`database/fasichat.db`)  
✓ Plus facile à déployer  
✓ Performance suffisante pour une application pédagogique  

## Initialisation

### 1ère fois - Créer la base de données:

```bash
php init_sqlite.php
```

Cela créera le fichier `database/fasichat.db` avec toutes les tables.

## Structure SQLite

Le fichier de base de données sera créé à:
```
database/fasichat.db
```

**Important**: Ce fichier doit avoir des permissions en écriture:
```bash
chmod 666 database/fasichat.db
chmod 755 database/
```

## Utilisation

L'application utilise maintenant SQLite automatiquement. Aucun changement dans le code n'est nécessaire - la classe `BaseDeDonnees` gère déjà les deux bases.

## Sauvegarde

Pour sauvegarder votre base de données, copiez simplement le fichier:
```bash
cp database/fasichat.db database/fasichat_backup.db
```

## Retour à MySQL (optionnel)

Si vous voulez revenir à MySQL, il suffit de changer dans `config/config.php`:
```php
define('DB_DRIVER', 'mysql');
```
