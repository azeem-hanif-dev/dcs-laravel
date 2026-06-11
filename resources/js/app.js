import './bootstrap';
import Alpine from 'alpinejs';

// Toast notification store
Alpine.store('toast', {
    toasts: [], counter: 0,
    add(type, message, duration = 4000) {
        const id = ++this.counter;
        this.toasts.push({ id, type, message });
        if (duration > 0) setTimeout(() => this.remove(id), duration);
    },
    remove(id) {
        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
    },
    success(msg, d) { this.add('success', msg, d); },
    error(msg, d) { this.add('error', msg, d || 6000); },
    warning(msg, d) { this.add('warning', msg, d); },
    info(msg, d) { this.add('info', msg, d); }
});

// API helper store
Alpine.store('api', {
    getToken() { return localStorage.getItem('S_S_Token'); },
    headers(isFormData) {
        const h = { 'Accept': 'application/json' };
        if (!isFormData) h['Content-Type'] = 'application/json';
        const t = this.getToken();
        if (t) h['Authorization'] = 'Bearer ' + t;
        return h;
    },
    async refreshTokenSilently() {
        const token = this.getToken();
        if (!token) return false;
        try {
            const res = await fetch('/api/auth/refresh', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.status && data.S_S_Token) {
                localStorage.setItem('S_S_Token', data.S_S_Token);
                return true;
            }
        } catch(e) {}
        return false;
    },
    async fetch(url, opts = {}) {
        const config = { headers: this.headers(opts.isFormData), ...opts };
        delete config.isFormData;
        let res = await fetch(url, config);

        // Token expired — try silent refresh before removing
        if (res.status === 401 && this.getToken()) {
            const refreshed = await this.refreshTokenSilently();
            if (refreshed) {
                // Retry with new token
                config.headers['Authorization'] = 'Bearer ' + this.getToken();
                res = await fetch(url, config);
            }
        }

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch (e) {
            console.error('JSON parse error for', url, text.substring(0, 200));
            throw { status: res.status, message: 'Invalid server response', raw: text };
        }
        if (!res.ok) {
            const err = { status: res.status, ...data };
            if (res.status === 401) {
                // Check if refresh already failed or not available
                if (!this.getToken() || data.expired) {
                    localStorage.removeItem('S_S_Token');
                    Alpine.store('toast').error('Session expired. Redirecting to login...', 2000);
                    setTimeout(function(){ window.location.replace('/login'); }, 1500);
                }
                err.message = data.message || 'Session expired. Please login again.';
            }
            throw err;
        }
        return data;
    },
    async get(url, params = {}) {
        const clean = {};
        for (const [k, v] of Object.entries(params)) {
            if (v !== undefined && v !== null && v !== '') clean[k] = v;
        }
        const qs = new URLSearchParams(clean).toString();
        return this.fetch(qs ? `${url}?${qs}` : url);
    },
    async post(url, body, isFormData) {
        return this.fetch(url, { method: 'POST', body: isFormData ? body : JSON.stringify(body), isFormData });
    },
    async put(url, body, isFormData) {
        return this.fetch(url, { method: 'PUT', body: isFormData ? body : JSON.stringify(body), isFormData });
    },
    async del(url) { return this.fetch(url, { method: 'DELETE' }); }
});

// Utility: parse API response into paginated items
// Handles: Laravel paginated, Laravel non-paginated, plain arrays, wrapped objects
function parsePaginationResponse(data, perPage) {
    let items = [], total = 0, currentPage = 1, lastPage = 1, serverPaginated = false;
    let payload = data;

    // Unwrap {status: true, data: ...} wrapper
    if (data && typeof data === 'object' && data.status && 'data' in data) {
        payload = data.data;
    }

    if (!payload) {
        return { items, total, totalPages: 1, currentPage, serverPaginated };
    }

    // Server-side paginated: {data: [...], current_page, total, last_page, ...}
    if (typeof payload === 'object' && !Array.isArray(payload) && Array.isArray(payload.data)) {
        serverPaginated = true;
        items = payload.data;
        total = payload.total || 0;
        currentPage = payload.current_page || 1;
        lastPage = payload.last_page || Math.max(1, Math.ceil(total / perPage));
        return { items, total, totalPages: lastPage, currentPage, serverPaginated };
    }

    // Non-paginated: plain array
    if (Array.isArray(payload)) {
        total = payload.length;
        lastPage = Math.max(1, Math.ceil(total / perPage));
        return { allItems: payload, items: payload.slice(0, perPage), total, totalPages: lastPage, currentPage: 1, serverPaginated: false };
    }

    // Fallback: empty
    return { items: [], total: 0, totalPages: 1, currentPage: 1, serverPaginated: false };
}

// Server-side pagination helper (reusable pager)
Alpine.store('pager', {
    create(config) {
        const instance = {
            allItems: [],       // cache for client-side pagination fallback
            items: [],          // items for current page
            loading: false,
            currentPage: 1,
            perPage: config.perPage || 10,
            total: 0,
            totalPages: 1,
            serverPaginated: false,
            searchParams: {},   // active search/filter params

            get visiblePages() {
                const p = [];
                const s = Math.max(1, this.currentPage - 2);
                const e = Math.min(this.totalPages, this.currentPage + 2);
                for (let i = s; i <= e; i++) p.push(i);
                return p;
            },

            // Parse response and update state
            _parse(data) {
                const parsed = parsePaginationResponse(data, this.perPage);
                this.items = parsed.items;
                this.total = parsed.total;
                this.totalPages = parsed.totalPages;
                this.currentPage = parsed.currentPage;
                this.serverPaginated = parsed.serverPaginated;
                if (!parsed.serverPaginated && parsed.allItems) {
                    this.allItems = parsed.allItems;
                }
            },

            // Client-side page slicing
            _slicePage() {
                const start = (this.currentPage - 1) * this.perPage;
                this.items = this.allItems.slice(start, start + this.perPage);
            },

            // Filter allItems client-side by search term
            _clientFilter(searchTerm) {
                if (!searchTerm) return this.allItems;
                const term = searchTerm.toLowerCase();
                return this.allItems.filter(item => {
                    return Object.values(item).some(val => {
                        if (val === null || val === undefined) return false;
                        if (typeof val === 'object') {
                            return Object.values(val).some(v => String(v).toLowerCase().includes(term));
                        }
                        return String(val).toLowerCase().includes(term);
                    });
                });
            },

            async fetchPage(params = {}) {
                this.loading = true;
                try {
                    const queryParams = { page: this.currentPage, per_page: this.perPage, ...this.searchParams, ...params };
                    const data = await Alpine.store('api').get(config.endpoint, queryParams);
                    this._parse(data);
                    return true;
                } catch (e) {
                    console.error('Pager fetchPage error:', e);
                    if (e.status !== 401) {
                        Alpine.store('toast').error(e.message || 'Failed to load data');
                    }
                    this.items = [];
                    this.total = 0;
                    this.totalPages = 1;
                    return false;
                } finally {
                    this.loading = false;
                }
            },

            goToPage(page) {
                if (page < 1 || page > this.totalPages) return;
                this.currentPage = page;
                if (this.serverPaginated) {
                    return this.fetchPage();
                } else {
                    this._slicePage();
                    return Promise.resolve(true);
                }
            },

            // Handle per-page dropdown changes
            changePerPage(n) {
                n = parseInt(n);
                if (!n || n === this.perPage) return;
                this.perPage = n;
                this.currentPage = 1;
                if (this.serverPaginated) {
                    return this.fetchPage();
                } else {
                    this.totalPages = Math.max(1, Math.ceil(this.total / this.perPage));
                    this._slicePage();
                    return Promise.resolve(true);
                }
            },

            // Set search/filter params and reload
            setSearch(params = {}) {
                this.currentPage = 1;
                this.allItems = [];
                this.searchParams = { ...params };
                return this.fetchPage();
            },

            // Reload with current params
            refresh() {
                return this.fetchPage();
            }
        };
        return instance;
    }
});

window.Alpine = Alpine;
Alpine.start();
