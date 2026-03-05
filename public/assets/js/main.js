/**
 * ITAM System - Main JavaScript
 */

const ITAM_TRANSLATABLE_ATTRS = ['placeholder', 'title', 'aria-label', 'data-bs-original-title'];
let itamTranslationKeysCache = null;

function getCurrentLang() {
    return (window.ITAM_LANG || document.documentElement.lang || 'en').toLowerCase();
}

function getTranslationMap() {
    return (window.ITAM_TRANSLATIONS && typeof window.ITAM_TRANSLATIONS === 'object')
        ? window.ITAM_TRANSLATIONS
        : {};
}

function shouldTranslateLao() {
    return getCurrentLang() === 'lo' && Object.keys(getTranslationMap()).length > 0;
}

function getTranslationKeys() {
    if (!itamTranslationKeysCache) {
        itamTranslationKeysCache = Object.keys(getTranslationMap()).sort((first, second) => second.length - first.length);
    }
    return itamTranslationKeysCache;
}

function translateText(input) {
    if (input === null || input === undefined) return input;

    const text = String(input);
    if (!shouldTranslateLao() || text === '') return text;

    const translations = getTranslationMap();
    const trimmed = text.trim();

    if (trimmed !== '' && Object.prototype.hasOwnProperty.call(translations, trimmed)) {
        const leading = text.match(/^\s*/)?.[0] ?? '';
        const trailing = text.match(/\s*$/)?.[0] ?? '';
        return `${leading}${translations[trimmed]}${trailing}`;
    }

    let translated = text;
    const keys = getTranslationKeys();

    for (const key of keys) {
        if (!key || !translated.includes(key)) continue;
        translated = translated.split(key).join(translations[key]);
    }

    return translated;
}

function isNoTranslateElement(element) {
    if (!element || element.nodeType !== Node.ELEMENT_NODE) return false;
    return !!element.closest('[data-no-translate="true"]');
}

function translateTextNode(node) {
    if (!node || node.nodeType !== Node.TEXT_NODE) return;
    const parentTag = node.parentElement ? node.parentElement.tagName : '';
    if (parentTag && ['SCRIPT', 'STYLE', 'NOSCRIPT', 'CODE', 'PRE'].includes(parentTag)) return;
    if (isNoTranslateElement(node.parentElement)) return;
    if (!/[A-Za-z]/.test(node.nodeValue || '')) return;

    const translated = translateText(node.nodeValue);
    if (translated !== node.nodeValue) {
        node.nodeValue = translated;
    }
}

function translateElementAttributes(element) {
    if (!element || element.nodeType !== Node.ELEMENT_NODE) return;
    if (isNoTranslateElement(element)) return;

    ITAM_TRANSLATABLE_ATTRS.forEach((attr) => {
        if (!element.hasAttribute(attr)) return;
        const currentValue = element.getAttribute(attr);
        const translated = translateText(currentValue);
        if (translated !== currentValue) {
            element.setAttribute(attr, translated);
        }
    });

    if (element.tagName === 'INPUT') {
        const type = (element.getAttribute('type') || '').toLowerCase();
        if (['button', 'submit', 'reset'].includes(type)) {
            const value = element.getAttribute('value') || '';
            const translated = translateText(value);
            if (translated !== value) {
                element.setAttribute('value', translated);
            }
        }
    }
}

function translateNodeTree(root) {
    if (!shouldTranslateLao() || !root) return;

    if (root.nodeType === Node.TEXT_NODE) {
        translateTextNode(root);
        return;
    }

    if (root.nodeType !== Node.ELEMENT_NODE && root !== document.body && root !== document.documentElement) {
        return;
    }

    if (root.nodeType === Node.ELEMENT_NODE) {
        translateElementAttributes(root);
    }

    const elementScope = root.nodeType === Node.ELEMENT_NODE ? root : document.body;
    if (elementScope) {
        elementScope.querySelectorAll('*').forEach((el) => {
            translateElementAttributes(el);
        });
    }

    const walkerRoot = root.nodeType === Node.ELEMENT_NODE ? root : document.body;
    if (!walkerRoot) return;

    const walker = document.createTreeWalker(walkerRoot, NodeFilter.SHOW_TEXT);
    let currentNode = walker.nextNode();
    while (currentNode) {
        translateTextNode(currentNode);
        currentNode = walker.nextNode();
    }
}

function applyLaoTranslations() {
    if (!shouldTranslateLao()) return;

    document.title = translateText(document.title);
    translateNodeTree(document.body);
}

function observeLaoTranslations() {
    if (!shouldTranslateLao() || !document.body) return;

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'characterData') {
                translateTextNode(mutation.target);
                return;
            }

            if (mutation.type === 'attributes') {
                translateElementAttributes(mutation.target);
                return;
            }

            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach((node) => {
                    translateNodeTree(node);
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
        attributes: true,
        attributeFilter: [...ITAM_TRANSLATABLE_ATTRS, 'value']
    });
}

window.itamTranslate = translateText;

// Toast Notification System
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer') || document.querySelector('.toast-container');
    if (!container) return;

    message = translateText(message);

    const toastId = 'toast-' + Date.now();

    const bgClass = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning text-dark',
        info: 'bg-info'
    }[type] || 'bg-info';

    const icon = {
        success: 'bi-check-circle',
        error: 'bi-x-circle',
        warning: 'bi-exclamation-triangle',
        info: 'bi-info-circle'
    }[type] || 'bi-info-circle';

    const toastHtml = `
        <div id="${toastId}" class="toast toast-glass align-items-center text-white ${bgClass} border-0 mb-2" role="alert" style="min-width: 300px;">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Sidebar Toggle for Mobile
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.btn-icon.d-md-none');

    if (window.innerWidth < 768 && 
        sidebar && 
        sidebar.classList.contains('open') &&
        !sidebar.contains(event.target) && 
        event.target !== toggleBtn &&
        !toggleBtn.contains(event.target)) {
        sidebar.classList.remove('open');
    }
});

// Form Validation Helpers
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Password Strength Checker
function checkPasswordStrength(password) {
    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };

    const strength = Object.values(checks).filter(Boolean).length;
    return { checks, strength };
}

// Confirm Delete Helper
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(translateText(message));
}

// Print Helper
function printPage() {
    window.print();
}

// Export to CSV Helper
function exportToCSV(data, filename) {
    const csv = convertToCSV(data);
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function convertToCSV(data) {
    if (data.length === 0) return '';

    const headers = Object.keys(data[0]);
    const rows = data.map(obj => 
        headers.map(header => {
            const value = obj[header];
            // Escape quotes and wrap in quotes if contains comma
            const escaped = String(value).replace(/"/g, '""');
            return escaped.includes(',') ? `"${escaped}"` : escaped;
        }).join(',')
    );

    return [headers.join(','), ...rows].join('\n');
}

// Initialize Tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// AJAX Helper
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        showToast('An error occurred while fetching data', 'error');
        return null;
    }
}

// Debounce Helper
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format Currency
function formatCurrency(amount, currency = 'USD') {
    const locale = getCurrentLang() === 'lo' ? 'lo-LA' : 'en-US';
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency
    }).format(amount);
}

// Format Date
function formatDate(dateString, options = {}) {
    const date = new Date(dateString);
    const locale = getCurrentLang() === 'lo' ? 'lo-LA' : 'en-US';
    return date.toLocaleDateString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        ...options
    });
}

// Search Table Helper
function searchTable(inputId, tableId, columnIndex = null) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (!input || !table) return;

    const filter = input.value.toLowerCase();
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length; j++) {
            if (columnIndex !== null && j !== columnIndex) continue;

            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().indexOf(filter) > -1) {
                found = true;
                break;
            }
        }

        rows[i].style.display = found ? '' : 'none';
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    applyLaoTranslations();
    observeLaoTranslations();

    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // M3 Global Search
    initGlobalSearch();
});

// M3 Global Search
function initGlobalSearch() {
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('globalSearchResults');
    if (!searchInput || !searchResults) return;

    function asText(value) {
        if (value === null || value === undefined) return '';
        return String(value);
    }

    function safeSearchUrl(url) {
        const value = asText(url).trim();
        return value.startsWith('/') ? value : '#';
    }

    function clearSearchResults() {
        searchResults.replaceChildren();
    }

    function renderEmptySearchResult(message) {
        const emptyState = document.createElement('div');
        emptyState.className = 'p-3 text-center';
        emptyState.style.color = 'var(--md-sys-color-on-surface-variant)';
        emptyState.textContent = message;
        return emptyState;
    }

    function buildUserSearchResult(item) {
        const link = document.createElement('a');
        link.href = safeSearchUrl(item.url);
        link.className = 'm3-search-result-item';

        const icon = document.createElement('i');
        icon.className = 'bi bi-person-circle';
        icon.style.cssText = 'font-size:20px;color:var(--md-sys-color-tertiary)';
        link.appendChild(icon);

        const content = document.createElement('div');
        content.className = 'flex-grow-1';

        const name = document.createElement('div');
        name.className = 'fw-medium';
        name.textContent = translateText(asText(item.name));
        content.appendChild(name);

        const email = document.createElement('small');
        email.style.color = 'var(--md-sys-color-on-surface-variant)';
        email.textContent = asText(item.email);
        content.appendChild(email);

        link.appendChild(content);

        const roleValue = asText(item.role);
        const role = document.createElement('span');
        role.style.cssText = 'font-size:12px;font-weight:500;' +
            (roleValue.toLowerCase() === 'admin' ? 'color:#7D5700' : 'color:#386A20');
        role.textContent = translateText(
            roleValue ? roleValue.charAt(0).toUpperCase() + roleValue.slice(1).toLowerCase() : ''
        );
        link.appendChild(role);

        return link;
    }

    function buildAssetSearchResult(item) {
        const link = document.createElement('a');
        link.href = safeSearchUrl(item.url);
        link.className = 'm3-search-result-item';

        const icon = document.createElement('i');
        icon.className = 'bi bi-box-seam';
        icon.style.cssText = 'font-size:20px;color:var(--md-sys-color-primary)';
        link.appendChild(icon);

        const content = document.createElement('div');
        content.className = 'flex-grow-1';

        const name = document.createElement('div');
        name.className = 'fw-medium';
        name.setAttribute('data-no-translate', 'true');
        name.textContent = asText(item.name);
        content.appendChild(name);

        const meta = document.createElement('small');
        meta.style.color = 'var(--md-sys-color-on-surface-variant)';
        meta.textContent = asText(item.code) + ' | ' + translateText(asText(item.category));
        content.appendChild(meta);

        link.appendChild(content);

        const status = asText(item.status);
        const statusBadge = document.createElement('span');
        statusBadge.style.cssText = 'font-size:12px;font-weight:500;' +
            (status === 'Available' ? 'color:#386A20' : 'color:#7D5700');
        statusBadge.textContent = translateText(status);
        link.appendChild(statusBadge);

        return link;
    }

    function buildSearchResultItem(item) {
        if (!item || typeof item !== 'object') return null;
        return item.type === 'user' ? buildUserSearchResult(item) : buildAssetSearchResult(item);
    }

    const handleSearch = debounce(async function() {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            searchResults.classList.remove('show');
            return;
        }

        const data = await fetchData('/controllers/api_search.php?q=' + encodeURIComponent(query));
        clearSearchResults();

        if (!data || !Array.isArray(data.results) || data.results.length === 0) {
            searchResults.appendChild(renderEmptySearchResult(translateText('No matches found')));
            searchResults.classList.add('show');
            return;
        }

        data.results.forEach(function(item) {
            const element = buildSearchResultItem(item);
            if (element) {
                searchResults.appendChild(element);
            }
        });

        if (!searchResults.children.length) {
            searchResults.appendChild(renderEmptySearchResult(translateText('No matches found')));
        }

        searchResults.classList.add('show');
    }, 300);

    searchInput.addEventListener('input', handleSearch);

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('show');
        }
    });
}
