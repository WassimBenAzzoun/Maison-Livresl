<?php

require_once __DIR__ . '/../config/Database.php';

class User
{
    private PDO $db;
    private ?int $id = null;
    private string $fullName = '';
    private string $email = '';
    private string $phone = '';
    private string $password = '';
    private string $address = '';
    private string $status = 'active';
    private string $role = 'user';
    private string $membershipType = 'none';
    private ?string $membershipPaidAt = null;
    private ?string $membershipExpiresAt = null;
    private ?int $membershipBranchId = null;
    private string $membershipBranchName = '';

    public function __construct(array $data = [])
    {
        $this->db = Database::getConnection();
        $this->id = isset($data['id']) ? (int) $data['id'] : null;
        $this->fullName = $data['full_name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->address = $data['address'] ?? '';
        $this->status = $data['status'] ?? 'active';
        $this->role = $data['role'] ?? 'user';
        $this->membershipType = $data['membership_type'] ?? 'none';
        $this->membershipPaidAt = $data['membership_paid_at'] ?? null;
        $this->membershipExpiresAt = $data['membership_expires_at'] ?? null;
        $this->membershipBranchId = isset($data['membership_branch_id']) ? (int) $data['membership_branch_id'] : null;
        $this->membershipBranchName = $data['membership_branch_name'] ?? '';
    }

    private function run(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    public function getId(): ?int { return $this->id; }
    public function getFullName(): string { return $this->fullName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getPassword(): string { return $this->password; }
    public function getAddress(): string { return $this->address; }
    public function getStatus(): string { return $this->status; }
    public function getRole(): string { return $this->role; }
    public function getMembershipType(): string { return $this->membershipType; }
    public function getMembershipPaidAt(): ?string { return $this->membershipPaidAt; }
    public function getMembershipExpiresAt(): ?string { return $this->membershipExpiresAt; }
    public function getMembershipBranchId(): ?int { return $this->membershipBranchId; }
    public function getMembershipBranchName(): string { return $this->membershipBranchName; }

    public function hasActiveMembership(): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->status !== 'active' || $this->membershipType === 'none' || !$this->membershipExpiresAt) {
            return false;
        }

        return strtotime($this->membershipExpiresAt) >= strtotime(date('Y-m-d'));
    }

    public function setFullName(string $fullName): void { $this->fullName = $fullName; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPhone(string $phone): void { $this->phone = $phone; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setAddress(string $address): void { $this->address = $address; }
    public function setStatus(string $status): void { $this->status = $status; }
    public function setRole(string $role): void { $this->role = $role; }

    public function all(): array
    {
        $rows = $this->run('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
        $items = [];

        foreach ($rows as $row) {
            $items[] = new self($row);
        }

        return $items;
    }

    public function find(int $id): ?User
    {
        $row = $this->run('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $id])->fetch();
        return $row ? new self($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->run('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $email])->fetch();
        return $row ? new self($row) : null;
    }

    public function create(array $data): bool
    {
        return $this->run(
            'INSERT INTO users (full_name, email, phone, password, address, status, role, membership_type, membership_paid_at, membership_expires_at, membership_branch_id, created_at, updated_at)
             VALUES (:full_name, :email, :phone, :password, :address, :status, :role, :membership_type, :membership_paid_at, :membership_expires_at, :membership_branch_id, NOW(), NOW())',
            [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'address' => $data['address'],
                'status' => $data['status'] ?? 'active',
                'role' => $data['role'] ?? 'user',
                'membership_type' => $data['membership_type'] ?? 'none',
                'membership_paid_at' => $data['membership_paid_at'] ?? null,
                'membership_expires_at' => $data['membership_expires_at'] ?? null,
                'membership_branch_id' => $data['membership_branch_id'] ?? null,
            ]
        )->rowCount() > 0;
    }

    public function authenticate(string $email, string $password, ?string $role = null): ?User
    {
        $user = $this->findByEmail($email);
        if (
            $user
            && $user->getStatus() === 'active'
            && ($role === null || $user->getRole() === $role)
            && password_verify($password, $user->getPassword())
        ) {
            return $user;
        }

        return null;
    }

    public function updateProfile(int $id, array $data): bool
    {
        $sql = 'UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address, updated_at = NOW()';
        $params = [
            'id' => $id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
        ];

        if (!empty($data['password'])) {
            $sql .= ', password = :password';
            $params['password'] = $data['password'];
        }

        $sql .= ' WHERE id = :id';
        return $this->run($sql, $params)->rowCount() > 0;
    }

    public function updateMembership(int $id, array $data): bool
    {
        return $this->run(
            'UPDATE users
             SET membership_type = :membership_type,
                 membership_paid_at = :membership_paid_at,
                 membership_expires_at = :membership_expires_at,
                 membership_branch_id = :membership_branch_id,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'membership_type' => $data['membership_type'] ?? 'none',
                'membership_paid_at' => $data['membership_paid_at'] ?? null,
                'membership_expires_at' => $data['membership_expires_at'] ?? null,
                'membership_branch_id' => $data['membership_branch_id'] ?? null,
            ]
        )->rowCount() > 0;
    }

    public function toggleStatus(int $id): bool
    {
        return $this->run(
            'UPDATE users SET status = CASE WHEN status = \'active\' THEN \'inactive\' ELSE \'active\' END, updated_at = NOW() WHERE id = :id',
            ['id' => $id]
        )->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        return $this->run('DELETE FROM users WHERE id = :id', ['id' => $id])->rowCount() > 0;
    }

    public function countTotal(): int
    {
        return (int) $this->run('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function findWithMembership(int $id): ?User
    {
        $row = $this->run(
            'SELECT u.*, b.nom AS membership_branch_name
             FROM users u
             LEFT JOIN bibliotheques b ON b.id = u.membership_branch_id
             WHERE u.id = :id
             LIMIT 1',
            ['id' => $id]
        )->fetch();

        return $row ? new self($row) : null;
    }
}
