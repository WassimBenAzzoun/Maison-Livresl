<?php

class Livre extends Model
{
    private ?int $id = null;
    private ?int $bibliothequeId = null;
    private string $titre = '';
    private string $auteur = '';
    private string $categorie = '';
    private int $anneePublication = 0;
    private string $description = '';
    private string $couverture = '';
    private int $totalExemplaires = 0;
    private int $availableExemplaires = 0;
    private ?string $bibliothequeNom = null;

    public function __construct(array $data = [])
    {
        parent::__construct();
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
        $this->bibliothequeId = isset($data['bibliotheque_id']) ? (int) $data['bibliotheque_id'] : null;
        $this->titre = $data['titre'] ?? '';
        $this->auteur = $data['auteur'] ?? '';
        $this->categorie = $data['categorie'] ?? '';
        $this->anneePublication = (int) ($data['annee_publication'] ?? 0);
        $this->description = $data['description'] ?? '';
        $this->couverture = $data['couverture'] ?? '';
        $this->totalExemplaires = (int) ($data['total_exemplaires'] ?? 0);
        $this->availableExemplaires = (int) ($data['available_exemplaires'] ?? 0);
        $this->bibliothequeNom = $data['bibliotheque_nom'] ?? null;
    }

    public function getId(): ?int { return $this->id; }
    public function getBibliothequeId(): ?int { return $this->bibliothequeId; }
    public function getTitre(): string { return $this->titre; }
    public function getAuteur(): string { return $this->auteur; }
    public function getCategorie(): string { return $this->categorie; }
    public function getAnneePublication(): int { return $this->anneePublication; }
    public function getDescription(): string { return $this->description; }
    public function getCouverture(): string { return $this->couverture; }
    public function getTotalExemplaires(): int { return $this->totalExemplaires; }
    public function getAvailableExemplaires(): int { return $this->availableExemplaires; }
    public function getBibliothequeNom(): ?string { return $this->bibliothequeNom; }

    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setAuteur(string $auteur): void { $this->auteur = $auteur; }
    public function setCategorie(string $categorie): void { $this->categorie = $categorie; }
    public function setAnneePublication(int $annee): void { $this->anneePublication = $annee; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setCouverture(string $couverture): void { $this->couverture = $couverture; }
    public function setTotalExemplaires(int $total): void { $this->totalExemplaires = $total; }
    public function setAvailableExemplaires(int $available): void { $this->availableExemplaires = $available; }
    public function setBibliothequeId(?int $bibliothequeId): void { $this->bibliothequeId = $bibliothequeId; }

    private function hydrateRow(array $row): Livre
    {
        return new Livre($row);
    }

    public function all(?int $bibliothequeId = null): array
    {
        $sql = 
            'SELECT l.*, b.nom AS bibliotheque_nom
             FROM livres l
             LEFT JOIN bibliotheques b ON b.id = l.bibliotheque_id';
        $params = [];

        if ($bibliothequeId !== null && $bibliothequeId > 0) {
            $sql .= ' WHERE l.bibliotheque_id = :bibliotheque_id';
            $params['bibliotheque_id'] = $bibliothequeId;
        }

        $sql .= ' ORDER BY l.created_at DESC';

        $rows = $this->fetchAll(
            $sql,
            $params
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function featured(int $limit = 3): array
    {
        $rows = $this->fetchAll(
            'SELECT l.*, b.nom AS bibliotheque_nom
             FROM livres l
             LEFT JOIN bibliotheques b ON b.id = l.bibliotheque_id
             ORDER BY l.created_at DESC
             LIMIT ' . (int) $limit
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function find(int $id): ?Livre
    {
        $row = $this->fetchOne(
            'SELECT l.*, b.nom AS bibliotheque_nom, b.adresse AS bibliotheque_adresse, b.ville AS bibliotheque_ville, b.latitude, b.longitude
             FROM livres l
             LEFT JOIN bibliotheques b ON b.id = l.bibliotheque_id
             WHERE l.id = :id
             LIMIT 1',
            ['id' => $id]
        );

        return $row ? $this->hydrateRow($row) : null;
    }

    public function byBibliotheque(int $bibliothequeId): array
    {
        return $this->all($bibliothequeId);
    }

    public function create(array $data): bool
    {
        return $this->execute(
            'INSERT INTO livres (bibliotheque_id, titre, auteur, categorie, annee_publication, description, couverture, total_exemplaires, available_exemplaires, created_at, updated_at)
             VALUES (:bibliotheque_id, :titre, :auteur, :categorie, :annee_publication, :description, :couverture, :total_exemplaires, :available_exemplaires, NOW(), NOW())',
            [
                'bibliotheque_id' => $data['bibliotheque_id'] ?: null,
                'titre' => $data['titre'],
                'auteur' => $data['auteur'],
                'categorie' => $data['categorie'],
                'annee_publication' => $data['annee_publication'],
                'description' => $data['description'],
                'couverture' => $data['couverture'],
                'total_exemplaires' => $data['total_exemplaires'],
                'available_exemplaires' => $data['available_exemplaires'],
            ]
        )->rowCount() > 0;
    }

    public function update(int $id, array $data): bool
    {
        return $this->execute(
            'UPDATE livres
             SET bibliotheque_id = :bibliotheque_id,
                 titre = :titre,
                 auteur = :auteur,
                 categorie = :categorie,
                 annee_publication = :annee_publication,
                 description = :description,
                 couverture = :couverture,
                 total_exemplaires = :total_exemplaires,
                 available_exemplaires = :available_exemplaires,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'bibliotheque_id' => $data['bibliotheque_id'] ?: null,
                'titre' => $data['titre'],
                'auteur' => $data['auteur'],
                'categorie' => $data['categorie'],
                'annee_publication' => $data['annee_publication'],
                'description' => $data['description'],
                'couverture' => $data['couverture'],
                'total_exemplaires' => $data['total_exemplaires'],
                'available_exemplaires' => $data['available_exemplaires'],
            ]
        )->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM livres WHERE id = :id', ['id' => $id])->rowCount() > 0;
    }

    public function decrementAvailability(int $id): bool
    {
        return $this->execute(
            'UPDATE livres SET available_exemplaires = CASE WHEN available_exemplaires > 0 THEN available_exemplaires - 1 ELSE 0 END, updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        )->rowCount() > 0;
    }

    public function incrementAvailability(int $id): bool
    {
        return $this->execute(
            'UPDATE livres SET available_exemplaires = available_exemplaires + 1, updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        )->rowCount() > 0;
    }

    public function countTotal(): int
    {
        return (int) $this->fetchOne('SELECT COUNT(*) AS total FROM livres')['total'];
    }

    public function countAvailable(): int
    {
        return (int) $this->fetchOne('SELECT COUNT(*) AS total FROM livres WHERE available_exemplaires > 0')['total'];
    }
}
