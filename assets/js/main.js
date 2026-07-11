/**
 * SunuJob Étudiant — JavaScript Principal
 * Design basé sur le logo officiel (Bleu #1B3F72, Vert #2D9B4E, Orange #F5A623)
 */

document.addEventListener('DOMContentLoaded', function () {
    initNavbarScroll();
    initAutoAlerts();
    initFormValidation();
    initTooltips();
    initFilePreview();
    initCounterAnimation();
    initPasswordToggle();
});

/* ===== NAVBAR — scroll effet ===== */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar-sunujob');
    if (!navbar) return;

    const onScroll = () => {
        navbar.classList.toggle('scrolled', window.scrollY > 60);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

/* ===== ALERTES — fermeture auto après 5s ===== */
function initAutoAlerts() {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            try {
                bootstrap.Alert.getOrCreateInstance(alert)?.close();
            } catch (e) {}
        }, 5000);
    });
}

/* ===== VALIDATION DES FORMULAIRES =====
   Uniquement sur les formulaires qui ont réellement des champs requis/contraints
   (les formulaires simples — filtres GET, boutons d'action supprimer/fermer...
   n'ont rien à valider et ne doivent jamais être interceptés au submit). */
function initFormValidation() {
    document.querySelectorAll('form').forEach(form => {
        if (!form.querySelector('[required], [pattern], [minlength], [min], [max]')) return;

        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Validation en temps réel
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('is-invalid')) validateField(field);
            });
        });
    });

    // Validation confirmation mot de passe
    const confirmPwd = document.getElementById('mot_de_passe_confirm');
    const pwd = document.getElementById('mot_de_passe');
    if (confirmPwd && pwd) {
        confirmPwd.addEventListener('input', function () {
            if (this.value !== pwd.value) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });
    }
}

function validateField(field) {
    if (field.required && !field.value.trim()) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        return false;
    }
    if (field.type === 'email' && field.value) {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            return false;
        }
    }
    field.classList.remove('is-invalid');
    if (field.value) field.classList.add('is-valid');
    return true;
}

/* ===== TOOLTIPS Bootstrap ===== */
function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { boundary: 'clippingParents' });
    });
}

/* ===== PREVIEW IMAGES ===== */
function initFilePreview() {
    // Photo de profil
    const photoInput = document.getElementById('photo');
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            previewImageTo(this, '.current-photo, .profile-avatar-placeholder');
        });
    }

    // Logo recruteur
    const logoInput = document.getElementById('logo');
    if (logoInput) {
        logoInput.addEventListener('change', function () {
            previewImageTo(this, '.current-logo');
        });
    }
}

function previewImageTo(input, selector) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const target = document.querySelector(selector);
        if (!target) return;
        if (target.tagName === 'IMG') {
            target.src = e.target.result;
        } else {
            target.style.backgroundImage = `url(${e.target.result})`;
            target.innerHTML = `<img src="${e.target.result}" class="rounded-circle" style="width:90px;height:90px;object-fit:cover;">`;
        }
    };
    reader.readAsDataURL(input.files[0]);
}

/* ===== ANIMATION COMPTEURS ===== */
function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number');
    if (!counters.length) return;

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const text = el.textContent.replace(/[^0-9]/g, '');
            if (!text) return;

            const target = parseInt(text, 10);
            const duration = 1200;
            const step = Math.ceil(target / (duration / 16));
            let current = 0;

            const timer = setInterval(() => {
                current = Math.min(current + step, target);
                el.textContent = current.toLocaleString('fr-FR');
                if (current >= target) clearInterval(timer);
            }, 16);

            observer.unobserve(el);
        });
    }, { threshold: 0.5 });

    counters.forEach(el => observer.observe(el));
}

/* ===== TOGGLE MOT DE PASSE ===== */
function initPasswordToggle() {
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        const targetId = btn.getAttribute('data-toggle-password');
        const input = document.getElementById(targetId);
        if (!input) return;
        btn.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.querySelector('i').classList.toggle('fa-eye');
            btn.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });

    // Compatible avec l'ancien toggle
    const oldToggle = document.getElementById('togglePassword');
    const oldInput = document.getElementById('mot_de_passe');
    if (oldToggle && oldInput) {
        oldToggle.addEventListener('click', () => {
            oldInput.type = oldInput.type === 'password' ? 'text' : 'password';
            const icon = oldToggle.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
}

/* ===== TOAST NOTIFICATION ===== */
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const bgMap = { success: '#2D9B4E', danger: '#DC2626', warning: '#F5A623', info: '#2E6DB4' };
    const bg = bgMap[type] || bgMap.info;

    const toast = document.createElement('div');
    toast.className = 'toast text-white border-0 shadow';
    toast.style.background = bg;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex align-items-center gap-2 toast-body">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'times-circle' : 'info-circle'}"></i>
            <span class="flex-grow-1">${message}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>`;

    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

/* ===== SMOOTH SCROLL ===== */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

/* ===== ROLE SELECTOR (register) ===== */
window.selectRole = function (role) {
    const input = document.getElementById('role_input');
    if (input) input.value = role;

    // Mettre à jour les radios btn-check si présents
    const etudiantRadio = document.getElementById('role_etudiant');
    const recruteurRadio = document.getElementById('role_recruteur');
    if (etudiantRadio) etudiantRadio.checked = role === 'etudiant';
    if (recruteurRadio) recruteurRadio.checked = role === 'recruteur';
};
