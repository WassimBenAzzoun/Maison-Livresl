<?php

class Livre extends Model
{
    private ?int $id = null;
    private string $titre = '';
    private string $auteur = '';
    private string $categorie = '';
    private int $anneePublication = 0;
    private string $description = '';
    private string $couverture = '';
    private array $stocks = [];

    public function __construct(array $data = [])
    {
        parent::__construct();
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
        $this->titre = $data['titre'] ?? '';
        $this->auteur = $data['auteur'] ?? '';
        $this->categorie = $data['categorie'] ?? '';
        $this->anneePublication = (int) ($data['annee_publication'] ?? 0);
        $this->description = $data['description'] ?? '';
        $this->couverture = $data['couverture'] ?? '';
        $this->stocks = $data['stocks'] ?? [];
    }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function getAuteur(): string { return $this->auteur; }
    public function getCategorie(): string { return $this->categorie; }
    public function getAnneePublication(): int { return $this->anneePublication; }
    public function getDescription(): string { return $this->description; }
    public function getCouverture(): string { return $this->couverture; }
    public function getStocks(): array { return $this->stocks; }

    public function getBibliothequeIds(): array
    {
        $ids = [];

        foreach ($this->stocks as $stock) {
            if (!empty($stock['bibliotheque_id'])) {
                $ids[] = (int) $stock['bibliotheque_id'];
            }
        }

        return array_values(array_unique($ids));
    }

    public function getBibliothequeId(): ?int
    {
        $ids = $this->getBibliothequeIds();
        return $ids ? $ids[0] : null;
    }

    public function getTotalExemplaires(): int
    {
        $total = 0;

        foreach ($this->stocks as $stock) {
            $total += (int) ($stock['total_exemplaires'] ?? 0);
        }

        return $total;
    }

    public function getAvailableExemplaires(): int
    {
        $total = 0;

        foreach ($this->stocks as $stock) {
            $total += (int) ($stock['available_exemplaires'] ?? 0);
        }

        return $total;
    }

    public function getBibliothequeNom(): ?string
    {
        if (empty($this->stocks)) {
            return null;
        }

        $names = [];

        foreach ($this->stocks as $stock) {
            if (!empty($stock['bibliotheque_nom'])) {
                $names[] = $stock['bibliotheque_nom'];
            }
        }

        return $names ? implode(', ', array_unique($names)) : null;
    }

    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setAuteur(string $auteur): void { $this->auteur = $auteur; }
    public function setCategorie(string $categorie): void { $this->categorie = $categorie; }
    public function setAnneePublication(int $annee): void { $this->anneePublication = $annee; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setCouverture(string $couverture): void { $this->couverture = $couverture; }
    public function setStocks(array $stocks): void { $this->stocks = $stocks; }

    protected function hydrateRow(array $row): Livre
    {
        return new Livre($row);
    }

    public function all(): array
    {
        $rows = $this->fetchAll(
            'SELECT l.*
             FROM livres l
             ORDER BY l.created_at DESC'
        );

        $items = [];

        foreach ($rows as $row) {
            $book = new Livre($row);
            $book->setStocks($this->stocksByLivreId((int) $book->getId()));
            $items[] = $book;
        }

        return $items;
    }

    public function featured(int $limit = 3): array
    {
        $rows = $this->fetchAll(
            'SELECT l.*
             FROM livres l
             ORDER BY l.created_at DESC
             LIMIT ' . (int) $limit
        );

        $items = [];

        foreach ($rows as $row) {
            $book = new Livre($row);
            $book->setStocks($this->stocksByLivreId((int) $book->getId()));
            $items[] = $book;
        }

        return $items;
    }

    public function find(int $id): ?Livre
    {
        $row = $this->fetchOne(
            'SELECT l.*
             FROM livres l
             WHERE l.id = :id
             LIMIT 1',
            ['id' => $id]
        );

        if (!$row) {
            return null;
        }

        $book = new Livre($row);
        $book->setStocks($this->stocksByLivreId((int) $book->getId()));

        return $book;
    }

    public function byBibliotheque(int $bibliothequeId): array
    {
        return $this->findByBranch($bibliothequeId);
    }

    public function findByBranch(int $bibliothequeId): array
    {
        $rows = $this->fetchAll(
            'SELECT l.*, bl.total_exemplaires, bl.available_exemplaires, b.nom AS bibliotheque_nom, b.adresse AS bibliotheque_adresse, b.ville AS bibliotheque_ville, b.latitude AS bibliotheque_latitude, b.longitude AS bibliotheque_longitude, b.id AS bibliotheque_id
             FROM bibliotheque_livres bl
             INNER JOIN livres l ON l.id = bl.livre_id
             INNER JOIN bibliotheques b ON b.id = bl.bibliotheque_id
             WHERE bl.bibliotheque_id = :bibliotheque_id
             ORDER BY l.created_at DESC',
            ['bibliotheque_id' => $bibliothequeId]
        );

        $items = [];

        foreach ($rows as $row) {
            $book = new Livre($row);
            $book->setStocks([[
                'bibliotheque_id' => (int) $row['bibliotheque_id'],
                'bibliotheque_nom' => $row['bibliotheque_nom'],
                'total_exemplaires' => (int) $row['total_exemplaires'],
                'available_exemplaires' => (int) $row['available_exemplaires'],
            ]]);
            $items[] = $book;
        }

        return $items;
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO livres (titre, auteur, categorie, annee_publication, description, couverture, created_at, updated_at)
             VALUES (:titre, :auteur, :categorie, :annee_publication, :description, :couverture, NOW(), NOW())',
            [
                'titre' => $data['titre'],
                'auteur' => $data['auteur'],
                'categorie' => $data['categorie'],
                'annee_publication' => $data['annee_publication'],
                'description' => $data['description'],
                'couverture' => $data['couverture'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE livres
             SET titre = :titre,
                 auteur = :auteur,
                 categorie = :categorie,
                 annee_publication = :annee_publication,
                 description = :description,
                 couverture = :couverture,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'titre' => $data['titre'],
                'auteur' => $data['auteur'],
                'categorie' => $data['categorie'],
                'annee_publication' => $data['annee_publication'],
                'description' => $data['description'],
                'couverture' => $data['couverture'],
            ]
        )->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM livres WHERE id = :id', ['id' => $id])->rowCount() > 0;
    }

    public function countTotal(): int
    {
        return (int) $this->fetchOne('SELECT COUNT(*) AS total FROM livres')['total'];
    }

    public function countAvailable(): int
    {
        return (int) $this->fetchOne('SELECT COUNT(*) AS total FROM bibliotheque_livres WHERE available_exemplaires > 0')['total'];
    }

    public function countByBranch(int $bibliothequeId): int
    {
        return (int) $this->fetchOne(
            'SELECT COUNT(*) AS total FROM bibliotheque_livres WHERE bibliotheque_id = :id',
            ['id' => $bibliothequeId]
        )['total'];
    }

    public function addStock(int $livreId, int $bibliothequeId, int $totalExemplaires, int $availableExemplaires): bool
    {
        return $this->execute(
            'INSERT INTO bibliotheque_livres (bibliotheque_id, livre_id, total_exemplaires, available_exemplaires, created_at, updated_at)
             VALUES (:bibliotheque_id, :livre_id, :total_exemplaires, :available_exemplaires, NOW(), NOW())
             ON DUPLICATE KEY UPDATE total_exemplaires = VALUES(total_exemplaires), available_exemplaires = VALUES(available_exemplaires), updated_at = NOW()',
            [
                'bibliotheque_id' => $bibliothequeId,
                'livre_id' => $livreId,
                'total_exemplaires' => $totalExemplaires,
                'available_exemplaires' => $availableExemplaires,
            ]
        )->rowCount() > 0;
    }

    public function deleteStocksByLivreId(int $livreId): bool
    {
        $this->execute(
            'DELETE FROM bibliotheque_livres WHERE livre_id = :livre_id',
            ['livre_id' => $livreId]
        );

        return true;
    }

    public function stockByBibliothequeAndLivre(int $bibliothequeId, int $livreId): ?array
    {
        $row = $this->fetchOne(
            'SELECT bl.*, b.nom AS bibliotheque_nom
             FROM bibliotheque_livres bl
             INNER JOIN bibliotheques b ON b.id = bl.bibliotheque_id
             WHERE bl.bibliotheque_id = :bibliotheque_id AND bl.livre_id = :livre_id
             LIMIT 1',
            [
                'bibliotheque_id' => $bibliothequeId,
                'livre_id' => $livreId,
            ]
        );

        return $row ?: null;
    }

    public function stockByLivreId(int $livreId): array
    {
        return $this->stocksByLivreId($livreId);
    }

    public function decrementStock(int $bibliothequeId, int $livreId): bool
    {
        return $this->execute(
            'UPDATE bibliotheque_livres
             SET available_exemplaires = CASE WHEN available_exemplaires > 0 THEN available_exemplaires - 1 ELSE 0 END,
                 updated_at = NOW()
             WHERE bibliotheque_id = :bibliotheque_id AND livre_id = :livre_id',
            [
                'bibliotheque_id' => $bibliothequeId,
                'livre_id' => $livreId,
            ]
        )->rowCount() > 0;
    }

    public function incrementStock(int $bibliothequeId, int $livreId): bool
    {
        return $this->execute(
            'UPDATE bibliotheque_livres
             SET available_exemplaires = CASE WHEN available_exemplaires < total_exemplaires THEN available_exemplaires + 1 ELSE total_exemplaires END,
                 updated_at = NOW()
             WHERE bibliotheque_id = :bibliotheque_id AND livre_id = :livre_id',
            [
                'bibliotheque_id' => $bibliothequeId,
                'livre_id' => $livreId,
            ]
        )->rowCount() > 0;
    }

    private function stocksByLivreId(int $livreId): array
    {
        return $this->fetchAll(
            'SELECT bl.*, b.nom AS bibliotheque_nom
             FROM bibliotheque_livres bl
             INNER JOIN bibliotheques b ON b.id = bl.bibliotheque_id
             WHERE bl.livre_id = :livre_id
             ORDER BY b.nom ASC',
            ['livre_id' => $livreId]
        );
    }
}
