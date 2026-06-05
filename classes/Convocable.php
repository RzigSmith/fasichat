<?php
/**
 * Interface Convocable
 * Doit être implémentée par les acteurs ayant le droit de convoquer une réunion.
 * (Doyen, Vice-Doyen)
 */
interface Convocable
{
    /**
     * Envoie une convocation de réunion collective à tous les enseignants et assistants.
     *
     * @param string $objet   Objet de la réunion
     * @param string $date    Date et heure de la réunion (format Y-m-d H:i)
     * @param string $lieu    Lieu ou lien de réunion
     * @param string $message Message explicatif facultatif
     * @return int            ID de la convocation créée
     */
    public function convoquer(string $objet, string $date, string $lieu, string $message = ''): int;
}
