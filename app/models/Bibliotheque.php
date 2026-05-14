<?php

require_once __DIR__ . '/Emprunt.php';
require_once __DIR__ . '/Livre.php';

class Bibliotheque extends Model
{
    private ?int $id = null;
    private string $nom = '';
    private string $adresse = '';
    private string $ville = '';
    private string $telephone = '';
    private string $description = '';
    private float $latitude = 0.0;
    private float $longitude = 0.0;
    private int $bookCount = 0;
    private int $currentBorrowingsCount = 0;

    public function __construct(array $data = [])
    {
        parent::__construct();
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
        $this->nom = $data['nom'] ?? '';
        $this->adresse = $data['adresse'] ?? '';
        $this->ville = $data['ville'] ?? '';
        $this->telephone = $data['telephone'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->latitude = isset($data['latitude']) ? (float) $data['latitude'] : 0.0;
        $this->longitude = isset($data['longitude']) ? (float) $data['longitude'] : 0.0;
        $this->bookCount = (int) ($data['book_count'] ?? 0);
        $this->currentBorrowingsCount = (int) ($data['current_borrowings_count'] ?? 0);
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getVille(): string { return $this->ville; }
    public function getTelephone(): string { return $this->telephone; }
    public function getDescription(): string { return $this->description; }
    public function getLatitude(): float { return $this->latitude; }
    public function getLongitude(): float { return $this->longitude; }
    public function getBookCount(): int { return $this->bookCount; }
    public function getCurrentBorrowingsCount(): int { return $this->currentBorrowingsCount; }

    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function setVille(string $ville): void { $this->ville = $ville; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setLatitude(float $latitude): void { $this->latitude = $latitude; }
    public function setLongitude(float $longitude): void { $this->longitude = $longitude; }

    protected function hydrateRow(array $row): Bibliotheque
    {
        return new Bibliotheque($row);
    }

    public function all(): array
    {
        $rows = $this->fetchAll(
            'SELECT b.*,
                    (SELECT COUNT(*) FROM bibliotheque_livres bl WHERE bl.bibliotheque_id = b.id) AS book_count,
                    (SELECT COUNT(*) FROM emprunts e WHERE e.bibliotheque_id = b.id AND e.status IN (\'pending\', \'confirmed\')) AS current_borrowings_count
              FROM bibliotheques b
              ORDER BY b.created_at DESC'
        );

        return $this->hydrateRows($rows);
    }

    public function allWithBookCounts(): array
    {
        return $this->fetchAll(
            'SELECT b.*,
                    (SELECT COUNT(*) FROM bibliotheque_livres bl WHERE bl.bibliotheque_id = b.id) AS book_count,
                    (SELECT COUNT(*) FROM emprunts e WHERE e.bibliotheque_id = b.id AND e.status IN (\'pending\', \'confirmed\')) AS current_borrowings_count
              FROM bibliotheques b
              ORDER BY b.nom ASC'
        );
    }

    public function find(int $id): ?Bibliotheque
    {
        $row = $this->fetchOne(
            'SELECT b.*,
                    (SELECT COUNT(*) FROM bibliotheque_livres bl WHERE bl.bibliotheque_id = b.id) AS book_count,
                    (SELECT COUNT(*) FROM emprunts e WHERE e.bibliotheque_id = b.id AND e.status IN (\'pending\', \'confirmed\')) AS current_borrowings_count
              FROM bibliotheques b
              WHERE b.id = :id
              LIMIT 1',
            ['id' => $id]
        );
        return $row ? $this->hydrateRow($row) : null;
    }

    public function booksById(int $id): array
    {
        return (new Livre())->findByBranch($id);
    }

    public function currentBorrowingsById(int $id): array
    {
        $rows = $this->fetchAll(
            'SELECT e.*, u.full_name AS user_name, u.status AS user_status
             FROM emprunts e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.bibliotheque_id = :id AND e.status IN (\'pending\', \'confirmed\')
             ORDER BY e.created_at DESC',
            ['id' => $id]
        );

        $items = [];

        foreach ($rows as $row) {
            $items[] = new Emprunt($row);
        }

        return $items;
    }

    public function create(array $data): bool
    {
        return $this->execute(
            'INSERT INTO bibliotheques (nom, adresse, ville, telephone, description, latitude, longitude, created_at, updated_at)
             VALUES (:nom, :adresse, :ville, :telephone, :description, :latitude, :longitude, NOW(), NOW())',
            [
                'nom' => $data['nom'],
                'adresse' => $data['adresse'],
                'ville' => $data['ville'],
                'telephone' => $data['telephone'],
                'description' => $data['description'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]
        )->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE bibliotheques
             SET nom = :nom,
                 adresse = :adresse,
                 ville = :ville,
                 telephone = :telephone,
                 description = :description,
                 latitude = :latitude,
                 longitude = :longitude,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'nom' => $data['nom'],
                'adresse' => $data['adresse'],
                'ville' => $data['ville'],
                'telephone' => $data['telephone'],
                'description' => $data['description'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]
        )->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM bibliotheques WHERE id = :id', ['id' => $id])->rowCount() > 0;
    }
}
