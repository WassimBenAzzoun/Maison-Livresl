const compareValues = (left, right, direction) => {
    const factor = direction === 'desc' ? -1 : 1;
    const leftNumber = Number(left);
    const rightNumber = Number(right);

    if (!Number.isNaN(leftNumber) && !Number.isNaN(rightNumber) && String(left).trim() !== '' && String(right).trim() !== '') {
        return (leftNumber - rightNumber) * factor;
    }

    return String(left).localeCompare(String(right), 'fr', {
        sensitivity: 'base',
        numeric: true
    }) * factor;
};

window.LibraryApp = {
    // Used by: public/home.php, public/book.php, public/admin-branch-view.php
    initBranchesMap(containerId, branches) {
        const container = document.getElementById(containerId);

        if (!container || !window.L) {
            return;
        }

        const map = L.map(containerId);
        const markers = [];

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        branches.forEach((branch) => {
                const lat = Number(branch.latitude);
                const lng = Number(branch.longitude);

            if (!Number.isNaN(lat) && !Number.isNaN(lng) && lat !== 0 && lng !== 0) {
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`<strong>${branch.nom || ''}</strong><br>${branch.adresse || ''}<br>${branch.ville || ''}`);
                markers.push([lat, lng]);
            }
        });

        if (markers.length > 0) {
            map.fitBounds(markers, { padding: [24, 24] });
            return;
        }

        map.setView([36.8, 10.1], 10);
    },

    // Used by: public/book.php, public/admin-branch-view.php
    initSingleBranchMap(containerId, branch) {
        const container = document.getElementById(containerId);

        if (!container || !window.L) {
            return;
        }

        const lat = Number(branch.latitude);
        const lng = Number(branch.longitude);
        const map = L.map(containerId).setView([lat || 36.8, lng || 10.1], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
            L.marker([lat, lng]).addTo(map).bindPopup(`<strong>${branch.nom || ''}</strong><br>${branch.adresse || ''}<br>${branch.ville || ''}`).openPopup();
        }
    }
};

// Used by: public/books.php
const initBookFilters = () => {
    const titleInput = document.querySelector('[data-filter-title]');
    const authorInput = document.querySelector('[data-filter-author]');
    const categoryInput = document.querySelector('[data-filter-category]');
    const branchInput = document.querySelector('[data-filter-branch]');
    const availabilityInput = document.querySelector('[data-filter-availability]');
    const sortSelect = document.querySelector('[data-book-sort]');
    const grid = document.querySelector('[data-books-grid]');
    const noResults = document.querySelector('[data-no-results]');
    const cards = Array.from(document.querySelectorAll('[data-book-card]')).map((card, index) => ({
        card,
        index,
        title: card.dataset.title || '',
        author: card.dataset.author || '',
        category: card.dataset.category || '',
        branch: card.dataset.branch || '',
        branchName: card.dataset.branchName || '',
        availability: card.dataset.availability || ''
    }));

    if (!titleInput || !grid || cards.length === 0) {
        return;
    }

    const applyFilters = () => {
        const criteria = {
            title: titleInput.value.trim().toLowerCase(),
            author: authorInput ? authorInput.value.trim().toLowerCase() : '',
            category: categoryInput ? categoryInput.value.trim().toLowerCase() : '',
            branch: branchInput ? branchInput.value.trim() : '',
            availability: availabilityInput ? availabilityInput.value.trim() : ''
        };

            const orderedCards = cards.slice().sort((a, b) => {
                if (!sortSelect || !sortSelect.value) {
                    return a.index - b.index;
                }

                const [field, direction = 'asc'] = sortSelect.value.split(':');
                const left = field === 'availability'
                    ? (a.availability === 'available' ? '1' : '0')
                    : field === 'branch'
                        ? a.branchName
                        : a[field] || '';
                const right = field === 'availability'
                    ? (b.availability === 'available' ? '1' : '0')
                    : field === 'branch'
                        ? b.branchName
                        : b[field] || '';

                if (left < right) {
                    return direction === 'desc' ? 1 : -1;
                }

                if (left > right) {
                    return direction === 'desc' ? -1 : 1;
                }

                return 0;
            });

            const visibleCards = orderedCards.filter((item) => {
                const matchesTitle = !criteria.title || item.title.includes(criteria.title);
                const matchesAuthor = !criteria.author || item.author.includes(criteria.author);
                const matchesCategory = !criteria.category || item.category.includes(criteria.category);
                const matchesBranch = !criteria.branch || item.branch.split(',').includes(criteria.branch);
                const matchesAvailability = !criteria.availability || item.availability === criteria.availability;

                return matchesTitle && matchesAuthor && matchesCategory && matchesBranch && matchesAvailability;
            });

        grid.innerHTML = '';

        orderedCards.forEach((item) => {
            const visible = visibleCards.includes(item);
            item.card.classList.toggle('hidden', !visible);
            grid.appendChild(item.card);
        });

        if (noResults) {
            noResults.classList.toggle('hidden', visibleCards.length !== 0);
        }
    };

    [titleInput, authorInput, categoryInput, branchInput, availabilityInput, sortSelect].forEach((input) => {
        if (input) {
            input.addEventListener('input', applyFilters);
            input.addEventListener('change', applyFilters);
        }
    });

    applyFilters();
};

// Used by: public/admin-branch-view.php, public/admin-borrowings.php, public/admin-branches.php, public/admin-dashboard.php, public/admin-user-view.php, public/admin-users.php, public/my-borrowings.php
const initTableTools = () => {
    document.querySelectorAll('[data-table-tools]').forEach((tools) => {
        const tableId = tools.dataset.tableTarget;
        const table = tableId ? document.getElementById(tableId) : null;

        if (!table || !table.tBodies.length) {
            return;
        }

        const tbody = table.tBodies[0];
        const originalRows = Array.from(tbody.querySelectorAll('tr')).map((row, index) => ({ row, index }));
        const searchInput = tools.querySelector('[data-table-search]');
        const sortSelect = tools.querySelector('[data-table-sort]');
        const emptyState = tools.querySelector('[data-table-empty]');

        const apply = () => {
            const term = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const sortValue = sortSelect ? sortSelect.value : '';
            const [field, direction = 'asc'] = sortValue ? sortValue.split(':') : [];
            const ordered = originalRows.slice().sort((left, right) => {
                let leftValue;
                let rightValue;

                if (!field) {
                    return left.index - right.index;
                }

                leftValue = left.row.dataset[`sort${field.charAt(0).toUpperCase()}${field.slice(1)}`] || left.row.dataset[field] || '';
                rightValue = right.row.dataset[`sort${field.charAt(0).toUpperCase()}${field.slice(1)}`] || right.row.dataset[field] || '';

                if (leftValue < rightValue) {
                    return direction === 'desc' ? 1 : -1;
                }

                if (leftValue > rightValue) {
                    return direction === 'desc' ? -1 : 1;
                }

                return 0;
            });

            let visibleCount = 0;

            tbody.innerHTML = '';
            ordered.forEach(({ row }) => {
                const haystack = (row.dataset.search || row.textContent || '').toLowerCase();
                const visible = !term || haystack.includes(term);

                row.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount += 1;
                }
                tbody.appendChild(row);
            });

            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount !== 0);
            }
        };

        if (searchInput) {
            searchInput.addEventListener('input', apply);
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', apply);
        }

        apply();
    });
};

// Used by: public/borrow.php
const initBorrowDuration = () => {
    const form = document.querySelector('[data-borrow-form]');

    if (!form) {
        return;
    }

    const startInput = form.querySelector('[data-borrow-start]');
    const endInput = form.querySelector('[data-borrow-end]');
    const output = form.querySelector('[data-borrow-duration]');

    const updateDuration = () => {
        if (!startInput.value || !endInput.value) {
            output.textContent = '-';
            return;
        }

        const start = new Date(`${startInput.value}T00:00:00`);
        const end = new Date(`${endInput.value}T00:00:00`);
        const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24));

        output.textContent = diff > 0 ? `${diff} jour(s)` : 'Veuillez vérifier les dates';
    };

    [startInput, endInput].forEach((input) => input.addEventListener('change', updateDuration));
    updateDuration();
};

const addDuration = (startValue, type) => {
    const start = new Date(`${startValue}T00:00:00`);

    if (Number.isNaN(start.getTime())) {
        return '';
    }

    const end = new Date(start);

    if (type === 'monthly') {
        end.setMonth(end.getMonth() + 1);
    } else if (type === 'yearly') {
        end.setFullYear(end.getFullYear() + 1);
    } else {
        return '';
    }

    const year = end.getFullYear();
    const month = String(end.getMonth() + 1).padStart(2, '0');
    const day = String(end.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const formatTodayForInput = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

// Used by: public/admin-user-view.php
const initMembershipDates = (formSelector) => {
    const form = document.querySelector(formSelector);

    if (!form) {
        return;
    }

    const typeInput = form.querySelector('[data-membership-type]');
    const startInput = form.querySelector('[data-membership-start]');
    const endInput = form.querySelector('[data-membership-end]');

    if (!typeInput || !startInput || !endInput) {
        return;
    }

    const syncDates = () => {
        const type = typeInput.value;

        if (type === 'none') {
            startInput.value = '';
            endInput.value = '';
            return;
        }

        const startValue = startInput.value || formatTodayForInput();
        startInput.value = startValue;
        endInput.value = addDuration(startValue, type);
    };

    typeInput.addEventListener('change', syncDates);
    startInput.addEventListener('change', syncDates);
    syncDates();
};

const initAuthLoginValidation = () => {
    const form = document.querySelector('form.auth-form');

    if (!form || form.querySelector('input[name="password_confirm"]')) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const emailField = form.querySelector('input[type="email"]');
        const passwordField = form.querySelector('input[name="password"]');

        if (!emailField.value.trim() || !passwordField.value.trim()) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
            event.preventDefault();
            emailField.focus();
            alert('Veuillez saisir une adresse email valide.');
        }
    });
};

const initRegisterValidation = () => {
    const form = document.querySelector('form.auth-form');

    if (!form || !form.querySelector('input[name="password_confirm"]')) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const fullName = form.querySelector('input[name="full_name"]');
        const emailField = form.querySelector('input[name="email"]');
        const phoneField = form.querySelector('input[name="phone"]');
        const password = form.querySelector('input[name="password"]');
        const passwordConfirm = form.querySelector('input[name="password_confirm"]');

        if (!fullName.value.trim() || !emailField.value.trim() || !phoneField.value.trim() || !password.value.trim() || !passwordConfirm.value.trim()) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
            event.preventDefault();
            emailField.focus();
            alert('Veuillez saisir une adresse email valide.');
            return;
        }

        if (passwordConfirm.value !== password.value) {
            event.preventDefault();
            passwordConfirm.focus();
            alert('Les mots de passe doivent correspondre.');
        }
    });
};

const initProfileValidation = () => {
    const form = document.querySelector('[data-profile-form]');

    if (!form || form.querySelector('[data-membership-type]') || form.querySelector('[data-borrow-start]')) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const fullName = form.querySelector('input[name="full_name"]');
        const emailField = form.querySelector('input[name="email"]');
        const phoneField = form.querySelector('input[name="phone"]');

        if (!fullName.value.trim() || !emailField.value.trim() || !phoneField.value.trim()) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
            event.preventDefault();
            emailField.focus();
            alert('Veuillez saisir une adresse email valide.');
        }
    });
};

const initBranchFormValidation = () => {
    const form = document.querySelector('[data-branch-form]');

    if (!form || !form.querySelector('input[name="latitude"]')) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const nom = form.querySelector('input[name="nom"]');
        const adresse = form.querySelector('input[name="adresse"]');
        const ville = form.querySelector('input[name="ville"]');
        const latitude = form.querySelector('input[name="latitude"]');
        const longitude = form.querySelector('input[name="longitude"]');

        if (!nom.value.trim() || !adresse.value.trim() || !ville.value.trim() || !latitude.value.trim() || !longitude.value.trim()) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
};

const initBookFormValidation = () => {
    const form = document.querySelector('form[enctype="multipart/form-data"]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const titre = form.querySelector('input[name="titre"]');
        const auteur = form.querySelector('input[name="auteur"]');
        const categorie = form.querySelector('input[name="categorie"]');

        if (!titre.value.trim() || !auteur.value.trim() || !categorie.value.trim()) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
};

const initBorrowFormValidation = () => {
    const form = document.querySelector('[data-borrow-form]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const bibliotheque = form.querySelector('select[name="bibliotheque_id"]');
        const fullName = form.querySelector('input[name="full_name"]');
        const emailField = form.querySelector('input[name="email"]');
        const phoneField = form.querySelector('input[name="phone"]');
        const borrowStart = form.querySelector('[data-borrow-start]');
        const borrowEnd = form.querySelector('[data-borrow-end]');

        if (!bibliotheque.value || !fullName.value.trim() || !emailField.value.trim() || !phoneField.value.trim() || !borrowStart.value || !borrowEnd.value) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value.trim())) {
            event.preventDefault();
            emailField.focus();
            alert('Veuillez saisir une adresse email valide.');
            return;
        }

        const start = new Date(`${borrowStart.value}T00:00:00`);
        const end = new Date(`${borrowEnd.value}T00:00:00`);

        if (end <= start) {
            event.preventDefault();
            borrowEnd.focus();
            alert('La date de retour doit être postérieure à la date d\'emprunt.');
        }
    });
};

const initMembershipFormValidation = () => {
    const form = document.querySelector('[data-membership-form]');

    if (!form) {
        return;
    }

    form.addEventListener('submit', (event) => {
        const typeInput = form.querySelector('[data-membership-type]');
        const startInput = form.querySelector('[data-membership-start]');
        const endInput = form.querySelector('[data-membership-end]');

        if (!typeInput.value || !startInput.value || !endInput.value) {
            event.preventDefault();
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });
};

const renderBarChart = (containerId, entries, colorClass) => {
    const container = document.getElementById(containerId);

    if (!container) {
        return;
    }

    const normalized = entries.map((entry) => ({
        label: entry.label || 'Sans libellé',
        total: Number(entry.total || 0)
    }));
    const maxValue = normalized.reduce((max, entry) => Math.max(max, entry.total), 1);

    container.innerHTML = '';

    normalized.forEach((entry) => {
        const row = document.createElement('div');
        row.className = 'bar-row';
        row.innerHTML = `
            <div class="bar-label">${entry.label}</div>
            <div class="bar-track"><div class="bar-fill ${colorClass || ''}" style="width: ${(entry.total / maxValue) * 100}%"></div></div>
            <div class="bar-value">${entry.total}</div>
        `;
        container.appendChild(row);
    });
};

// Used by: public/admin-statistics.php
const initStatisticsCharts = () => {
    if (!window.libraryStats) {
        return;
    }

    renderBarChart('categoryChart', window.libraryStats.by_category || [], 'chart-primary');
    renderBarChart('branchChart', window.libraryStats.by_branch || [], 'chart-secondary');
};

document.addEventListener('DOMContentLoaded', () => {
    initBookFilters();
    initTableTools();
    initBorrowDuration();
    initMembershipDates('[data-membership-form]');
    initAuthLoginValidation();
    initRegisterValidation();
    initProfileValidation();
    initBranchFormValidation();
    initBookFormValidation();
    initBorrowFormValidation();
    initMembershipFormValidation();
    initStatisticsCharts();
});
