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
                        data-search="<?= e(strtolower($user->getFullName() . ' ' . $user->getEmail() . ' ' . $user->getPhone() . ' ' . role_label($user->getRole()) . ' ' . membership_label($user->getMembershipType()) . ' ' . status_label($user->getStatus()))) ?>"
                        data-sort-name="<?= e(strtolower($user->getFullName())) ?>"
                        data-sort-email="<?= e(strtolower($user->getEmail())) ?>"
                        data-sort-role="<?= e(strtolower(role_label($user->getRole()))) ?>"
                        data-sort-membership="<?= e(strtolower(membership_label($user->getMembershipType()))) ?>"
                        data-sort-status="<?= e(strtolower(status_label($user->getStatus()))) ?>"
                    >
                        <td><a class="table-link" href="<?= url('admin-user-view', ['id' => $user->getId()]) ?>"><?= e($user->getFullName()) ?></a></td>
                        <td><?= e($user->getEmail()) ?></td>
                        <td><?= e($user->getPhone()) ?></td>
                        <td><span class="badge badge-info"><?= e(role_label($user->getRole())) ?></span></td>
                        <td><span class="badge badge-info"><?= e(membership_label($user->getMembershipType())) ?></span></td>
                        <td><span class="badge <?= badge_class($user->getStatus()) ?>"><?= e(status_label($user->getStatus())) ?></span></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-secondary" href="<?= url('admin-user-view', ['id' => $user->getId()]) ?>">Détails</a>
                            <?php if ($user->getRole() !== 'admin'): ?>
                                <form method="post" action="<?= url('admin-user-action') ?>" class="inline-form">
                                    <input type="hidden" name="id" value="<?= e((string) $user->getId()) ?>">
                                    <input type="hidden" name="action" value="toggle">
                                    <button class="btn btn-sm btn-primary" type="submit">Activer/Désactiver</button>
                                </form>
                                <form method="post" action="<?= url('admin-user-action') ?>" class="inline-form" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="id" value="<?= e((string) $user->getId()) ?>">
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
