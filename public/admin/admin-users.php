<?php
declare(strict_types=1);

session_start();
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once __DIR__ . '/../../app/core/helpers.php';
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/User.php';

require_admin_page();

$pageTitle = 'Maison des Livres | Utilisateurs';
$activePage = 'admin-users';
$users = (new User())->all();

require __DIR__ . '/../partials/header.php';
?>

<section class="section">
    <div class="section-head">
        <h1>Comptes utilisateurs</h1>
        <p>Consultez et gérez les accès du site.</p>
    </div>

    <div class="table-tools" data-table-tools data-table-target="usersTable">
        <input class="form-control" type="search" placeholder="Rechercher un compte" data-table-search>
        <select class="form-control" data-table-sort>
            <option value="">Trier par défaut</option>
            <option value="name:asc">Nom A-Z</option>
            <option value="name:desc">Nom Z-A</option>
            <option value="email:asc">Email A-Z</option>
            <option value="role:asc">Rôle A-Z</option>
            <option value="status:asc">Statut A-Z</option>
            <option value="membership:asc">Adhésion A-Z</option>
        </select>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Adhésion</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr
                        data-search="<?= htmlspecialchars(strtolower($user->getFullName() . ' ' . $user->getEmail() . ' ' . $user->getPhone() . ' ' . role_label($user->getRole()) . ' ' . membership_label($user->getMembershipType()) . ' ' . status_label($user->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-name="<?= htmlspecialchars(strtolower($user->getFullName()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-email="<?= htmlspecialchars(strtolower($user->getEmail()), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-role="<?= htmlspecialchars(strtolower(role_label($user->getRole())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-membership="<?= htmlspecialchars(strtolower(membership_label($user->getMembershipType())), ENT_QUOTES, 'UTF-8') ?>"
                        data-sort-status="<?= htmlspecialchars(strtolower(status_label($user->getStatus())), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <td><a class="table-link" href="<?= 'admin-user-view.php?id=' . rawurlencode((string) ($user->getId())) ?>"><?= htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8') ?></a></td>
                        <td><?= htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user->getPhone(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars(role_label($user->getRole()), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><span class="badge badge-info"><?= htmlspecialchars(membership_label($user->getMembershipType()), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><span class="badge <?= badge_class($user->getStatus()) ?>"><?= htmlspecialchars(status_label($user->getStatus()), ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-secondary" href="<?= 'admin-user-view.php?id=' . rawurlencode((string) ($user->getId())) ?>">Détails</a>
                            <?php if ($user->getRole() !== 'admin'): ?>
                                <form method="post" action="<?= 'admin-user-action.php' ?>" class="inline-form">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $user->getId(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button class="btn btn-sm btn-primary" type="submit">Activer/Désactiver</button>
                                </form>
                                <form method="post" action="<?= 'admin-user-action.php' ?>" class="inline-form" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $user->getId(), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-warning">Compte admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../partials/footer.php'; ?>


