/**
 * validation.js — Règles de validation JavaScript (front + back)
 *
 * Règles métier :
 *  - Discussion : objet min 3 chars, garage obligatoire
 *  - Message    : contenu min 2 chars, max 1000
 */
'use strict';

/* ══════════════════════════════════════════════
   Helpers DOM
══════════════════════════════════════════════ */

function _setError(field, errEl, msg) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    if (errEl) { errEl.textContent = msg; errEl.style.display = 'block'; }
    return false;
}

function _setValid(field, errEl) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    if (errEl) { errEl.style.display = 'none'; }
    return true;
}

/** Efface toutes les erreurs dans un conteneur */
function clearAllErrors(container) {
    if (!container) return;
    container.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
        el.classList.remove('is-invalid', 'is-valid');
    });
    container.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent   = '';
    });
}

/* ══════════════════════════════════════════════
   Règles atomiques (retournent true/false)
══════════════════════════════════════════════ */

const RULES = {

    /**
     * Valide un champ texte (objet de discussion)
     * min : 3 caractères
     */
    objet(field, errEl) {
        errEl = errEl || field.parentElement.querySelector('.field-error');
        const v = (field.value || '').trim();
        if (!v)           return _setError(field, errEl, 'L\'objet est obligatoire.');
        if (v.length < 3) return _setError(field, errEl, 'L\'objet doit faire au moins 3 caractères.');
        return _setValid(field, errEl);
    },

    /**
     * Valide un select obligatoire (garage)
     */
    garage(field, errEl) {
        errEl = errEl || field.parentElement.querySelector('.field-error');
        if (!field.value) return _setError(field, errEl, 'Veuillez sélectionner un garage.');
        return _setValid(field, errEl);
    },

    /**
     * Valide un select utilisateur (back uniquement)
     */
    user(field, errEl) {
        errEl = errEl || field.parentElement.querySelector('.field-error');
        if (!field.value) return _setError(field, errEl, 'Veuillez sélectionner un utilisateur.');
        return _setValid(field, errEl);
    },

    /**
     * Valide le contenu d'un message
     * min : 2 chars, max : 1000
     */
    contenu(field, errEl) {
        errEl = errEl || field.parentElement.querySelector('.field-error');
        const v = (field.value || '').trim();
        if (!v)              return _setError(field, errEl, 'Le message ne peut pas être vide.');
        if (v.length < 2)    return _setError(field, errEl, 'Message trop court (minimum 2 caractères).');
        if (v.length > 1000) return _setError(field, errEl, 'Message trop long (maximum 1000 caractères).');
        return _setValid(field, errEl);
    },
};

/* ══════════════════════════════════════════════
   Formulaires complets
══════════════════════════════════════════════ */

/**
 * Valide le formulaire Discussion du FRONT
 * (pas de champ user visible — user_id est caché)
 * @param {{ objet: HTMLElement, garage: HTMLElement }} fields
 */
function validateDiscussionForm({ objet, garage }) {
    const okObjet  = RULES.objet(objet);
    const okGarage = RULES.garage(garage);
    return okObjet && okGarage;
}

/**
 * Valide le formulaire Discussion du BACK (admin)
 * (inclut le champ user visible)
 * @param {{ user: HTMLElement, garage: HTMLElement, objet: HTMLElement }} fields
 */
function validateAdminDiscussionForm({ user, garage, objet }) {
    const okUser   = RULES.user(user);
    const okGarage = RULES.garage(garage);
    const okObjet  = RULES.objet(objet);
    return okUser && okGarage && okObjet;
}

/**
 * Valide le formulaire Message (front)
 * @param {HTMLElement} field   textarea du message
 */
function validateMessageForm(field) {
    return RULES.contenu(field);
}

/* ══════════════════════════════════════════════
   Binding temps réel
══════════════════════════════════════════════ */

/**
 * Bind la validation live sur les champs du formulaire discussion (FRONT)
 * @param {{ objet: HTMLElement, garage: HTMLElement }} fields
 */
function bindDiscussionValidation({ objet, garage }) {
    if (objet) {
        objet.addEventListener('input', () => RULES.objet(objet));
        objet.addEventListener('blur',  () => RULES.objet(objet));
    }
    if (garage) {
        garage.addEventListener('change', () => RULES.garage(garage));
    }
}

/**
 * Bind la validation live sur les champs du formulaire discussion (BACK admin)
 * @param {{ user: HTMLElement, garage: HTMLElement, objet: HTMLElement }} fields
 */
function bindAdminDiscussionValidation({ user, garage, objet }) {
    if (user)   { user.addEventListener('change', () => RULES.user(user)); }
    if (garage) { garage.addEventListener('change', () => RULES.garage(garage)); }
    if (objet)  {
        objet.addEventListener('input', () => RULES.objet(objet));
        objet.addEventListener('blur',  () => RULES.objet(objet));
    }
}

/**
 * Bind la validation live sur le champ message + compteur de caractères
 * @param {HTMLTextAreaElement} field
 * @param {HTMLElement|null}    counter   élément affichant X/1000
 */
function bindMessageValidation(field, counter) {
    if (!field) return;
    const update = () => {
        const len = (field.value || '').length;
        if (counter) {
            counter.textContent = `${len}/1000`;
            counter.style.color = len > 1000 ? 'var(--red)' : '';
        }
        // Validation uniquement si le champ a déjà été touché
        if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) {
            RULES.contenu(field);
        }
    };
    field.addEventListener('input', update);
    field.addEventListener('blur',  () => RULES.contenu(field));
}