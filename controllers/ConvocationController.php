<?php
/**
 * Contrôleur des convocations
 * Gère l'envoi (Doyen/Vice-Doyen) et la réception (Enseignants/Assistants) des convocations.
 */
class ConvocationController
{
    /**
     * Affiche la liste des convocations reçues.
     */
    public function index(): void
    {
        SessionManager::exigerConnexion();
        SessionManager::exigerRole(['enseignant', 'assistant', 'doyen', 'viceDoyen']);

        $user         = SessionManager::getUser();
        $convocations = [];
        $canConvoquer = in_array($user['role'], ['doyen', 'viceDoyen'], true);

        if (in_array($user['role'], ['enseignant', 'assistant'], true)) {
            $convocations = Convocation::recuperer(['destinataire_id' => $user['id']]);
        }

        include __DIR__ . '/../views/convocations.php';
    }

    /**
     * Affiche le formulaire de convocation et traite la soumission.
     */
    public function envoyer(): void
    {
        SessionManager::exigerRole(['doyen', 'viceDoyen']);

        $user   = SessionManager::getUser();
        $erreur = null;
        $succes = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $objet   = trim($_POST['objet'] ?? '');
            $date    = trim($_POST['date_reunion'] ?? '');
            $lieu    = trim($_POST['lieu'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (!$objet || !$date || !$lieu) {
                $erreur = 'L\'objet, la date et le lieu sont obligatoires.';
            } else {
                try {
                    $utilisateur = Utilisateur::factory(
                        BaseDeDonnees::getInstance()->query('SELECT * FROM utilisateurs WHERE id = ?', [$user['id']])->fetch()
                    );

                    /** @var Convocable $utilisateur */
                    $id = $utilisateur->convoquer(
                        htmlspecialchars($objet, ENT_QUOTES, 'UTF-8'),
                        $date,
                        htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
                    );

                    $succes = "Convocation #$id envoyée à tous les enseignants et assistants.";
                } catch (Exception $e) {
                    $erreur = 'Erreur : ' . $e->getMessage();
                }
            }
        }

        include __DIR__ . '/../views/form_convocation.php';
    }

    /**
     * Marque une convocation comme lue.
     */
    public function marquerLu(): void
    {
        SessionManager::exigerConnexion();
        header('Content-Type: application/json');

        $convId = (int)($_POST['convocation_id'] ?? 0);
        $userId = SessionManager::getUserId();

        if ($convId) {
            Convocation::marquerLu($convId, $userId);
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
