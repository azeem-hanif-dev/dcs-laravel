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
    error(msg, d) { this.add('error', msg, d || 5000); },
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
    async fetch(url, opts = {}) {
        const config = { headers: this.headers(opts.isFormData), ...opts };
        delete config.isFormData;
        const res = await fetch(url, config);
        const data = await res.json();
        if (!res.ok) throw { status: res.status, ...data };
        return data;
    },
    async get(url, params = {}) {
        const clean = {};
        for (const [k, v] of Object.entries(params)) { if (v !== undefined && v !== null && v !== '') clean[k] = v; }
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

// Server-side pagination helper (reusable mixin pattern)
Alpine.store('pager', {
    // Returns an object with methods for server-side paginated lists
    create(config) {
        return {
            items: [], loading: false, currentPage: 1, perPage: config.perPage || 10, total: 0, totalPages: 1,
            visiblePages() {
                const p = []; const s = Math.max(1, this.currentPage - 2); const e = Math.min(this.totalPages, this.currentPage + 2);
                for (let i = s; i <= e; i++) p.push(i); return p;
            },
            async fetchPage(params = {}) {
                this.loading = true;
                try {
                    const p = { page: this.currentPage, per_page: this.perPage, ...params };
                    const data = await Alpine.store('api').get(config.endpoint, p);
                    this.items = data.data?.data || data.data || [];
                    this.total = data.data?.total || data.total || data.meta?.total || this.items.length;
                    this.totalPages = data.data?.last_page || data.last_page || data.meta?.last_page || Math.ceil(this.total / this.perPage) || 1;
                    this.currentPage = data.data?.current_page || data.current_page || data.meta?.current_page || this.currentPage;
                    return true;
                } catch (e) {
                    Alpine.store('toast').error('Failed to load data');
                    return false;
                } finally { this.loading = false; }
            },
            goToPage(page) { if (page >= 1 && page <= this.totalPages) { this.currentPage = page; this.fetchPage(); } }
        };
    }
});

window.Alpine = Alpine;
Alpine.start();
