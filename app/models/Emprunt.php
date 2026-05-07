<?php

class Emprunt extends Model
{
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $livreId = null;
    private ?int $bibliothequeId = null;
    private string $fullName = '';
    private string $email = '';
    private string $phone = '';
    private string $borrowDate = '';
    private string $returnDate = '';
    private string $status = 'pending';
    private string $livreTitre = '';
    private string $livreCategorie = '';
    private string $bibliothequeNom = '';
    private string $userName = '';
    private string $userStatus = '';

    public function __construct(array $data = [])
    {
        parent::__construct();
        $this->hydrate($data);
    }

    private function hydrate(array $data): void
    {
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
        $this->userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $this->livreId = isset($data['livre_id']) ? (int) $data['livre_id'] : null;
        $this->bibliothequeId = isset($data['bibliotheque_id']) ? (int) $data['bibliotheque_id'] : null;
        $this->fullName = $data['full_name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->borrowDate = $data['borrow_date'] ?? '';
        $this->returnDate = $data['return_date'] ?? '';
        $this->status = $data['status'] ?? 'pending';
        $this->livreTitre = $data['livre_titre'] ?? '';
        $this->livreCategorie = $data['livre_categorie'] ?? '';
        $this->bibliothequeNom = $data['bibliotheque_nom'] ?? '';
        $this->userName = $data['user_name'] ?? '';
        $this->userStatus = $data['user_status'] ?? '';
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): ?int { return $this->userId; }
    public function getLivreId(): ?int { return $this->livreId; }
    public function getBibliothequeId(): ?int { return $this->bibliothequeId; }
    public function getFullName(): string { return $this->fullName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getBorrowDate(): string { return $this->borrowDate; }
    public function getReturnDate(): string { return $this->returnDate; }
    public function getStatus(): string { return $this->status; }
    public function getLivreTitre(): string { return $this->livreTitre; }
    public function getLivreCategorie(): string { return $this->livreCategorie; }
    public function getBibliothequeNom(): string { return $this->bibliothequeNom; }
    public function getUserName(): string { return $this->userName; }
    public function getUserStatus(): string { return $this->userStatus; }

    private function hydrateRow(array $row): Emprunt
    {
        return new Emprunt($row);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO emprunts (user_id, livre_id, bibliotheque_id, full_name, email, phone, borrow_date, return_date, status, livre_titre, livre_categorie, bibliotheque_nom, created_at, updated_at)
             VALUES (:user_id, :livre_id, :bibliotheque_id, :full_name, :email, :phone, :borrow_date, :return_date, :status, :livre_titre, :livre_categorie, :bibliotheque_nom, NOW(), NOW())',
            [
                'user_id' => $data['user_id'],
                'livre_id' => $data['livre_id'],
                'bibliotheque_id' => $data['bibliotheque_id'],
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'borrow_date' => $data['borrow_date'],
                'return_date' => $data['return_date'],
                'status' => $data['status'] ?? 'pending',
                'livre_titre' => $data['livre_titre'],
                'livre_categorie' => $data['livre_categorie'],
                'bibliotheque_nom' => $data['bibliotheque_nom'],
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function find(int $id): ?Emprunt
    {
        $row = $this->fetchOne('SELECT * FROM emprunts WHERE id = :id LIMIT 1', ['id' => $id]);
        return $row ? $this->hydrateRow($row) : null;
    }

    public function allWithRelations(): array
    {
        $rows = $this->fetchAll(
            'SELECT e.*, u.full_name AS user_name, u.status AS user_status
             FROM emprunts e
             LEFT JOIN users u ON u.id = e.user_id
             ORDER BY e.created_at DESC'
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function byUser(int $userId): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM emprunts WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => $userId]
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function currentByUser(int $userId): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM emprunts WHERE user_id = :user_id AND status IN (\'pending\', \'confirmed\') ORDER BY created_at DESC',
            ['user_id' => $userId]
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function previousByUser(int $userId): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM emprunts WHERE user_id = :user_id AND status IN (\'cancelled\', \'returned\') ORDER BY created_at DESC',
            ['user_id' => $userId]
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function currentByBranch(int $branchId): array
    {
        $rows = $this->fetchAll(
            'SELECT e.*, u.full_name AS user_name, u.status AS user_status
             FROM emprunts e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.bibliotheque_id = :branch_id AND e.status IN (\'pending\', \'confirmed\')
             ORDER BY e.created_at DESC',
            ['branch_id' => $branchId]
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function previousByBranch(int $branchId): array
    {
        $rows = $this->fetchAll(
            'SELECT e.*, u.full_name AS user_name, u.status AS user_status
             FROM emprunts e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.bibliotheque_id = :branch_id AND e.status IN (\'cancelled\', \'returned\')
             ORDER BY e.created_at DESC',
            ['branch_id' => $branchId]
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function byBranch(int $branchId): array
    {
        $rows = $this->fetchAll(
            'SELECT e.*, u.full_name AS user_name, u.status AS user_status
             FROM emprunts e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE e.bibliotheque_id = :branch_id
             ORDER BY e.created_at DESC',
            ['branch_id' => $branchId]
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->execute(
            'UPDATE emprunts SET status = :status, updated_at = NOW() WHERE id = :id',
            ['id' => $id, 'status' => $status]
        )->rowCount() > 0;
    }

    public function countTotal(): int
    {
        return (int) $this->fetchOne('SELECT COUNT(*) AS total FROM emprunts')['total'];
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->fetchOne(
            'SELECT COUNT(*) AS total FROM emprunts WHERE status = :status',
            ['status' => $status]
        )['total'];
    }

    public function latest(int $limit = 5): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM emprunts ORDER BY created_at DESC LIMIT ' . (int) $limit
        );

        return array_map(fn ($row) => $this->hydrateRow($row), $rows);
    }

    public function countByCategory(): array
    {
        return $this->fetchAll(
            'SELECT COALESCE(livre_categorie, \'Sans catégorie\') AS label, COUNT(*) AS total
             FROM emprunts
             GROUP BY COALESCE(livre_categorie, \'Sans catégorie\')
             ORDER BY total DESC'
        );
    }

    public function countByBranch(): array
    {
        return $this->fetchAll(
            'SELECT COALESCE(bibliotheque_nom, \'Sans bibliothèque\') AS label, COUNT(*) AS total
             FROM emprunts
             GROUP BY COALESCE(bibliotheque_nom, \'Sans bibliothèque\')
             ORDER BY total DESC'
        );
    }
}
