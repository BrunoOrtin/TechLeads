<?php

namespace App\DAO;

use App\Database\DBConnection;
use App\Model\Nota;
use InvalidArgumentException;
use PDO;

/**
 * @implements IDbAccess<Nota>
 */
class NotaDAO implements IDbAccess
{
    private const MODE = PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE;

    public function __construct(private DBConnection $connexio) {}

    public function findAll(): array
    {
        $pdo = $this->connexio->getPDO();

        $stmt = $pdo->query(
            'SELECT id, texto AS text, usuari_id, lead_id, data_creacio
             FROM notas
             ORDER BY data_creacio DESC, id DESC'
        );

        return $stmt->fetchAll(self::MODE, Nota::class);
    }

    public function findById(int $id): ?Nota
    {
        $pdo = $this->connexio->getPDO();

        $stmt = $pdo->prepare(
            'SELECT id, texto AS text, usuari_id, lead_id, data_creacio
             FROM notas
             WHERE id = :id'
        );

        $stmt->setFetchMode(self::MODE, Nota::class);
        $stmt->execute(['id' => $id]);

        $nota = $stmt->fetch();

        return $nota === false ? null : $nota;
    }

    public function findByLead(int $leadId): array
    {
        $pdo = $this->connexio->getPDO();

        $stmt = $pdo->prepare(
            'SELECT n.id,
                    n.texto AS text,
                    n.usuari_id,
                    n.lead_id,
                    n.data_creacio,
                    u.usuari AS nomUsuari
            FROM notas n
            JOIN usuaris u ON u.id = n.usuari_id
            WHERE n.lead_id = :lead_id
            ORDER BY n.data_creacio DESC, n.id DESC'
        );

        $stmt->setFetchMode(self::MODE, Nota::class);
        $stmt->execute(['lead_id' => $leadId]);

        return $stmt->fetchAll();
    }

    public function insert(object $nota): int
    {
        $nota = $this->comprovarNota($nota);
        $pdo = $this->connexio->getPDO();

        $stmt = $pdo->prepare(
            'INSERT INTO notas
                (texto, usuari_id, lead_id)
             VALUES
                (:texto, :usuari_id, :lead_id)'
        );

        $stmt->execute([
            'texto'      => $nota->getText(),
            'usuari_id'  => $nota->getUsuariId(),
            'lead_id'    => $nota->getLeadId()
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function update(object $nota): void
    {
        // No cal implementar-la perquè la lògica de negoci diu que no s'han de poder actualitzar.
    }

    public function delete(int $id): void
    {
        $pdo = $this->connexio->getPDO();

        $stmt = $pdo->prepare(
            'DELETE FROM notas WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
    }

    private function comprovarNota(object $entitat): Nota
    {
        if (!$entitat instanceof Nota) {
            throw new InvalidArgumentException(
                'NotaDAO només accepta objectes Nota.'
            );
        }

        return $entitat;
    }

    public function comptarPerLead(): array
    {
        $pdo = $this->connexio->getPDO();

        $stmt = $pdo->query(
            'SELECT lead_id AS id, COUNT(*) AS num_notes
             FROM notas
             GROUP BY lead_id'
        );

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
