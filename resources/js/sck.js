import axios from 'axios';
import Alpine from 'alpinejs';
import QRious from 'qrious';
import './modal-drag-guard';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set up CSRF token in Axios from meta tag if available
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.QRious = QRious;
window.Alpine = Alpine;
Alpine.data('sckWeekPicker', (initialWeek) => ({
    selected: initialWeek,
    open: false,
    currentWeek: '',
    cursor: null,
    init() {
        this.currentWeek = this.toWeekValue(new Date());
        this.cursor = this.startOfWeek(this.dateFromWeek(this.selected));
    },
    startOfWeek(date) {
        const result = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        result.setDate(result.getDate() - ((result.getDay() + 6) % 7));
        return result;
    },
    dateFromWeek(value) {
        const match = /^(\d{4})-W(\d{2})$/.exec(value);
        if (!match) return this.startOfWeek(new Date());
        const year = Number(match[1]);
        const week = Number(match[2]);
        const januaryFourth = new Date(year, 0, 4);
        const firstWeek = this.startOfWeek(januaryFourth);
        firstWeek.setDate(firstWeek.getDate() + ((week - 1) * 7));
        return firstWeek;
    },
    toWeekValue(date) {
        const target = this.startOfWeek(date);
        const thursday = new Date(target.getFullYear(), target.getMonth(), target.getDate() + 3);
        const isoYear = thursday.getFullYear();
        const firstWeek = this.startOfWeek(new Date(isoYear, 0, 4));
        const week = Math.round((target - firstWeek) / 604800000) + 1;
        return `${isoYear}-W${String(week).padStart(2, '0')}`;
    },
    shift(amount) {
        const next = this.dateFromWeek(this.selected);
        next.setDate(next.getDate() + (amount * 7));
        this.choose(this.toWeekValue(next), true);
    },
    choose(value, submit = false) {
        this.selected = value;
        this.cursor = this.dateFromWeek(value);
        this.open = false;
        const field = this.$root.querySelector('input[type="hidden"]');
        if (field) field.value = value;
        if (submit) this.$root.closest('form')?.requestSubmit();
    },
    selectCurrentWeek() { this.choose(this.currentWeek, true); },
    changeMonth(amount) {
        this.cursor = new Date(this.cursor.getFullYear(), this.cursor.getMonth() + amount, 1);
    },
    formatRange(start) {
        const end = new Date(start.getFullYear(), start.getMonth(), start.getDate() + 6);
        const sameYear = start.getFullYear() === end.getFullYear();
        const first = start.toLocaleDateString('de-DE', { day: '2-digit', month: 'short', ...(sameYear ? {} : { year: 'numeric' }) });
        const last = end.toLocaleDateString('de-DE', { day: '2-digit', month: 'short', year: 'numeric' });
        return `${first} – ${last}`;
    },
    get weekLabel() { return `KW ${Number(this.selected.slice(-2))}`; },
    get rangeLabel() { return this.formatRange(this.dateFromWeek(this.selected)); },
    get monthLabel() { return new Intl.DateTimeFormat('de-DE', { month: 'long', year: 'numeric' }).format(this.cursor); },
    get weeksForMonth() {
        const first = this.startOfWeek(new Date(this.cursor.getFullYear(), this.cursor.getMonth(), 1));
        return Array.from({ length: 6 }, (_, index) => {
            const start = new Date(first.getFullYear(), first.getMonth(), first.getDate() + (index * 7));
            const value = this.toWeekValue(start);
            return { value, number: Number(value.slice(-2)), range: this.formatRange(start) };
        });
    },
}));
Alpine.data('sckAddressSearch', (prefix = '') => ({
    query: '', results: [], loading: false, open: false, timer: null, canSearchOnline: false, searchedOnline: false, request: null, cache: new Map(),
    changed() {
        clearTimeout(this.timer);
        const query = this.query.trim();
        if (query.length < 3) {
            this.results = []; this.open = false; this.canSearchOnline = false; this.searchedOnline = false;
            return;
        }
        this.timer = setTimeout(() => this.search(false), 400);
    },
    async search(online = false) {
        const query = this.query.trim();
        if (query.length < 3) return;
        const cacheKey = `${query.toLocaleLowerCase()}|${online}`;
        if (this.cache.has(cacheKey)) {
            this.apply(this.cache.get(cacheKey));
            return;
        }
        this.request?.abort();
        this.request = new AbortController();
        this.loading = true;
        try {
            const response = await axios.get('/sck/suche/adressen', { params: { q: query, online: online ? 1 : 0 }, signal: this.request.signal });
            if (query !== this.query.trim()) return;
            this.cache.set(cacheKey, response.data);
            this.apply(response.data);
        } catch (error) {
            if (error.code !== 'ERR_CANCELED') { this.results = []; this.canSearchOnline = false; }
        } finally { this.loading = false; }
    },
    apply(data) {
        this.results = (data.results || []).map(item => ({ ...item, group: ({ tomtom: 'TomTom', custom_point: 'Eigener Punkt', customer: 'Kunde', stop: 'Stopp' })[item.source] || 'Lokal' }));
        this.canSearchOnline = Boolean(data.online_available);
        this.searchedOnline = Boolean(data.searched_online);
        this.open = true;
    },
    searchOnline() { this.search(true); },
    choose(item) {
        const form = this.$root.closest('form');
        const set = (name, value) => { const field = form?.querySelector(`[name="${prefix}${name}"]`); if (field) { field.value = value ?? ''; field.dispatchEvent(new Event('input', { bubbles: true })); } };
        if (prefix !== '') { set('address', item.label); set('latitude', item.lat); set('longitude', item.lng); }
        else { ['street','house_number','postal_code','city','country_code'].forEach(name => set(name, item[name])); const customer = form?.querySelector('[name="customer_id"]'); if (customer && item.customer_id) customer.value = item.customer_id; }
        this.query = item.label; this.open = false;
    },
}));
Alpine.data('sckStopCreation', (customers = []) => ({
    customers,
    preloadCustomerAddress(event) {
        const customer = this.customers.find(({ id }) => String(id) === event.target.value);
        if (!customer) return;

        const form = event.target.closest('form');
        ['street', 'house_number', 'postal_code', 'city', 'country_code'].forEach((name) => {
            const field = form?.querySelector(`[name="${name}"]`);
            if (!field) return;

            field.value = customer[name] ?? '';
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
    },
}));
Alpine.data('sckMap', (config) => {
    // TomTom SDK instances contain non-configurable properties and must never
    // be wrapped in Alpine's reactive Proxy. Keep them in this closure instead.
    let mapInstance = null;
    let markerInstances = [];
    let routeLayerIds = [];
    let mapInitAttempts = 0;
    let mapResizeObserver = null;
    let markerRefreshQueued = false;
    let mapReady = false;
    let pendingFit = false;
    return ({
    configured: Boolean(config.configured), mode: config.initialMode || 'next', week: config.initialWeek,
    selectedTourId: config.initialTourId, tourQuery: '', tourResults: [], loading: false, error: '',
    data: { home: null, customers: [], points: [], tours: [] }, legendOpen: config.initialLegendOpen ?? true, placing: false,
    layers: { home: true, customers: false, points: true, tours: true },
    pointModalOpen: false, pointSaving: false, pointError: '', addressQuery: '', addressResults: [], addressCanSearchOnline: false, addressSearchedOnline: false,
    draft: {}, colors: ['#a855f7', '#10b981', '#f97316', '#06b6d4', '#ec4899', '#eab308', '#6366f1'],
    init() {
        this.layers = { ...this.layers, ...(config.initialLayers || {}) };
        this.$watch('layers', layers => {
            this.renderData(false);
            axios.put(config.layersUrl, { ...Alpine.raw(layers), legend_open: this.legendOpen }).catch(() => {
                this.error = 'Die Ebenenauswahl konnte nicht für diese Sitzung gespeichert werden.';
            });
        }, { deep: true });
        this.$watch('legendOpen', legendOpen => {
            axios.put(config.layersUrl, { ...Alpine.raw(this.layers), legend_open: legendOpen }).catch(() => {
                this.error = 'Der Zustand der Legende konnte nicht für diese Sitzung gespeichert werden.';
            });
        });
        // The map deliberately remains TomTom's standard bright style in both
        // portal themes, so a UI theme change must not recreate it.
        window.addEventListener('sck-map-edit-point', event => this.editPoint(event.detail.id));
        window.addEventListener('sck-map-delete-point', event => { const point = this.data.points.find(item => item.id === event.detail.id); if (point) { this.editPoint(point.id); this.deletePoint(); } });
        if (this.configured) {
            // Load the enabled layers immediately. The map's load event will
            // render this payload once the style is ready, avoiding a race
            // where data appeared only after a layer toggle.
            this.loadData();
            if (window.tt) this.initMap();
            else {
                this.error = 'Das TomTom Karten-SDK konnte nicht geladen werden.';
            }
        }
    },
    initMap(load = true) {
        if (mapInstance || !window.tt) return;
        let center = [7.0982, 50.7374], zoom = 9;
        try { center = JSON.parse(localStorage.getItem('sck-map-center')) || center; zoom = Number(localStorage.getItem('sck-map-zoom')) || zoom; } catch (_) {}
        const container = document.getElementById('sck-map');
        if (!container) { this.error = 'Der Karten-Container wurde nicht gefunden.'; return; }
        if (container.clientWidth === 0 || container.clientHeight === 0) {
            mapInitAttempts += 1;
            if (mapInitAttempts <= 120) {
                requestAnimationFrame(() => this.initMap(load));
                return;
            }
            this.error = 'Der Kartenbereich hat keine sichtbare Größe.';
            return;
        }
        mapInitAttempts = 0;
        const mapOptions = {
            key: config.apiKey,
            container,
            center,
            zoom,
        };
        try {
            mapInstance = tt.map(mapOptions);
        } catch (error) {
            this.error = error?.message || 'Die TomTom-Karte konnte nicht initialisiert werden.';
            return;
        }
        if (window.ResizeObserver) {
            mapResizeObserver?.disconnect();
            mapResizeObserver = new ResizeObserver(entries => {
                const { width, height } = entries[0].contentRect;
                if (width > 0 && height > 0 && mapInstance) {
                    mapInstance.resize();
                }
            });
            mapResizeObserver.observe(container);
        }
        mapInstance.addControl(new tt.NavigationControl(), 'bottom-right');
        mapInstance.on('click', event => this.mapClicked(event));
        mapInstance.on('moveend', () => { const point = mapInstance.getCenter(); localStorage.setItem('sck-map-center', JSON.stringify([point.lng, point.lat])); localStorage.setItem('sck-map-zoom', String(mapInstance.getZoom())); });
        mapInstance.on('load', () => {
            // Alpine can initialise while the surrounding layout is still
            // settling. Do not render data until this event: marker and layer
            // insertion before TomTom's first layout can be culled until an
            // unrelated layer toggle causes another render.
            mapReady = true;
            pendingFit ||= load;
            requestAnimationFrame(() => {
                mapInstance?.resize();
                this.renderData(pendingFit);
            });
        });
        mapInstance.on('error', event => {
            const message = event?.error?.message || 'Die TomTom-Karte konnte nicht geladen werden.';
            if (message.includes('AbortError')) return;
            this.error = message;
        });
    },
    rebuildMap() {
        if (!mapInstance) return;
        this.error = ''; this.clearMap(); mapResizeObserver?.disconnect(); mapResizeObserver = null; mapInstance.remove(); mapInstance = null; mapReady = false; pendingFit = false; this.initMap(false);
    },
    async loadData(fit = true) {
        if (!this.configured) return;
        this.loading = true; this.error = '';
        try {
            const params = { mode: this.mode };
            if (this.mode === 'week') params.week = this.weekFromPicker();
            if (this.mode === 'tour' && this.selectedTourId) params.tour_id = this.selectedTourId;
            const response = await axios.get(config.dataUrl, { params });
            this.data = response.data;
            this.renderData(fit);
            this.syncUrl(params);
        } catch (error) {
            this.error = error.response?.data?.message || error.message || 'Die Kartendaten konnten nicht geladen werden.';
        }
        finally { this.loading = false; }
    },
    reload() { this.loadData(false); },
    weekFromPicker() {
        const value = this.$root.querySelector('[name="map_week"]')?.value;
        if (value) this.week = value;
        return this.week;
    },
    setMode(mode) {
        this.mode = mode;
        if (mode !== 'tour' || this.selectedTourId) this.$nextTick(() => this.loadData());
        else { this.clearToursOnly(); this.syncUrl({ mode }); }
    },
    async searchTours() {
        const query = this.tourQuery.trim();
        if (query.length < 2) { this.tourResults = []; return; }
        try { this.tourResults = (await axios.get(config.tourSearchUrl, { params: { q: query } })).data.results || []; }
        catch (_) { this.tourResults = []; }
    },
    chooseTour(tour) {
        this.selectedTourId = tour.id; this.tourQuery = `${tour.number} – ${tour.title}`; this.tourResults = []; this.loadData();
    },
    renderData(fit = true) {
        if (!mapInstance || !mapReady) {
            pendingFit ||= fit;
            return;
        }
        // map.loaded() also becomes false while ordinary tiles are loading.
        // Only the style has to be ready before sources/layers can be added.
        if (typeof mapInstance.isStyleLoaded === 'function' && !mapInstance.isStyleLoaded()) {
            mapInstance.once('styledata', () => this.renderData(fit));
            return;
        }
        pendingFit = false;
        this.clearMap();
        // Axios payloads become deeply reactive inside Alpine. TomTom must only
        // receive plain data, otherwise WebKit enforces Proxy invariants inside
        // the SDK and marker creation fails.
        const mapData = JSON.parse(JSON.stringify(Alpine.raw(this.data)));
        const activeLayers = { ...Alpine.raw(this.layers) };
        const bounds = new tt.LngLatBounds(); let hasBounds = false;
        const extend = (lng, lat) => { if (Number.isFinite(Number(lng)) && Number.isFinite(Number(lat))) { bounds.extend([Number(lng), Number(lat)]); hasBounds = true; } };
        if (activeLayers.home && mapData.home) { this.addMarker(mapData.home.lng, mapData.home.lat, 'home', 'fa-house', this.popup('Home', mapData.home.name, mapData.home.address)); extend(mapData.home.lng, mapData.home.lat); }
        if (activeLayers.customers) mapData.customers.forEach(customer => { this.addMarker(customer.lng, customer.lat, 'customer', 'fa-address-book', this.popup('Kunde', customer.name, customer.address, `<a class="sck-map-popup-link" href="${this.escape(customer.url)}">Kunde öffnen</a>`)); extend(customer.lng, customer.lat); });
        if (activeLayers.points) mapData.points.forEach(point => { const actions = `<div class="sck-map-popup-actions"><button onclick="window.dispatchEvent(new CustomEvent('sck-map-edit-point',{detail:{id:${point.id}}}))">Bearbeiten</button><button class="is-danger" onclick="window.dispatchEvent(new CustomEvent('sck-map-delete-point',{detail:{id:${point.id}}}))">Löschen</button></div>`; this.addMarker(point.lng, point.lat, 'point', 'fa-location-dot', this.popup('Eigener Punkt', point.name, point.address, actions, point.note)); extend(point.lng, point.lat); });
        if (activeLayers.tours) mapData.tours.forEach((tour, index) => {
            const color = this.tourColor(index); const routeId = `sck-tour-${tour.id}`;
            const coordinates = (tour.polyline || []).filter(point => point.lat !== null && point.lng !== null).map(point => [Number(point.lng), Number(point.lat)]);
            if (coordinates.length > 1) {
                mapInstance.addSource(routeId, { type: 'geojson', data: { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates } } });
                mapInstance.addLayer({ id: routeId, type: 'line', source: routeId, layout: { 'line-cap': 'round', 'line-join': 'round' }, paint: { 'line-color': color, 'line-width': 5, 'line-opacity': .85, ...(tour.approximate ? { 'line-dasharray': [2, 2] } : {}) } });
                routeLayerIds.push(routeId); coordinates.forEach(point => extend(point[0], point[1]));
            }
            const detail = `<a class="sck-map-popup-link" href="${this.escape(tour.url)}">Tour öffnen</a>`;
            if (tour.start?.lat != null && tour.start?.lng != null) this.addMarker(tour.start.lng, tour.start.lat, 'tour-start', 'fa-play', this.popup(tour.number, tour.start.name || 'Start', tour.start.address, detail), null, color);
            tour.stops.forEach(stop => { if (stop.lat == null || stop.lng == null) return; this.addMarker(stop.lng, stop.lat, 'tour-stop', '', this.popup(`${tour.number} · Stopp ${stop.position}`, stop.title, stop.address, detail), stop.position, color); extend(stop.lng, stop.lat); });
            if (tour.end?.lat != null && tour.end?.lng != null) this.addMarker(tour.end.lng, tour.end.lat, 'tour-end', 'fa-flag-checkered', this.popup(tour.number, tour.end.name || 'Ziel', tour.end.address, detail), null, color);
        });
        if (fit && hasBounds) mapInstance.fitBounds(bounds, { padding: { top: 90, right: 70, bottom: 70, left: this.legendOpen ? 330 : 100 }, maxZoom: 15, duration: 900 });
    },
    addMarker(lng, lat, type, icon, html, number = null, color = null) {
        if (lng == null || lat == null) return;
        const element = document.createElement('div'); element.className = `sck-map-marker sck-map-marker--${type}`;
        if (color) element.style.setProperty('--marker-color', color);
        // Keep this outer element free of transforms and transitions. TomTom
        // continuously transforms it to follow the map; animating it makes
        // markers visibly trail behind the canvas during pans and zooms.
        const content = number !== null ? `<span>${Number(number)}</span>` : `<i class="fa-solid ${icon}"></i>`;
        element.innerHTML = `<span class="sck-map-marker__inner">${content}</span>`;
        const marker = new tt.Marker({ element }).setLngLat([Number(lng), Number(lat)]).setPopup(new tt.Popup({ offset: 32, className: 'sck-map-popup' }).setHTML(html)).addTo(mapInstance);
        markerInstances.push(marker);
        // Markers added while the map is completing its first layout can be
        // culled until the next map interaction. Force that layout now rather
        // than relying on a layer toggle to make them appear.
        if (!markerRefreshQueued) {
            markerRefreshQueued = true;
            requestAnimationFrame(() => {
                markerRefreshQueued = false;
                mapInstance?.resize();
                mapInstance?.triggerRepaint?.();
            });
        }
    },
    clearMap() {
        markerInstances.forEach(marker => marker.remove()); markerInstances = [];
        routeLayerIds.forEach(id => { if (mapInstance?.getLayer(id)) mapInstance.removeLayer(id); if (mapInstance?.getSource(id)) mapInstance.removeSource(id); }); routeLayerIds = [];
    },
    clearToursOnly() { this.data.tours = []; this.renderData(false); },
    focusTour(id) {
        const tour = this.data.tours.find(item => item.id === id); if (!tour || !mapInstance) return;
        routeLayerIds.forEach(routeId => mapInstance.setPaintProperty(routeId, 'line-opacity', routeId === `sck-tour-${id}` ? 1 : .22));
        const bounds = new tt.LngLatBounds(); (tour.polyline || []).forEach(point => bounds.extend([point.lng, point.lat]));
        if (!bounds.isEmpty()) mapInstance.fitBounds(bounds, { padding: 80, maxZoom: 15, duration: 800 });
    },
    startPlacement() { if (!this.configured) return; this.placing = true; this.pointModalOpen = false; },
    openAddressPoint() { this.resetDraft(); this.placing = false; this.pointModalOpen = true; },
    async mapClicked(event) {
        if (!this.placing) return;
        this.placing = false; this.resetDraft(); this.draft.latitude = event.lngLat.lat; this.draft.longitude = event.lngLat.lng;
        this.pointModalOpen = true; this.addressQuery = 'Adresse wird ermittelt …';
        try { const result = (await axios.get(config.reverseUrl, { params: { lat: event.lngLat.lat, lng: event.lngLat.lng } })).data.result; if (result) this.chooseAddress(result); else this.addressQuery = ''; }
        catch (_) { this.addressQuery = ''; }
    },
    resetDraft() { this.draft = { id: null, name: '', note: '', formatted_address: '', street: '', house_number: '', postal_code: '', city: '', country_code: 'DE', latitude: null, longitude: null }; this.pointError = ''; this.addressQuery = ''; this.addressResults = []; },
    editPoint(id) { const point = this.data.points.find(item => item.id === id); if (!point) return; this.draft = { ...point, latitude: point.lat, longitude: point.lng, formatted_address: point.formatted_address || point.address || '' }; this.addressQuery = point.address || ''; this.pointError = ''; this.pointModalOpen = true; },
    async searchAddresses(online = false) {
        const query = this.addressQuery.trim(); if (query.length < 3 || query === 'Adresse wird ermittelt …') { this.addressResults = []; return; }
        try { const data = (await axios.get(config.addressSearchUrl, { params: { q: query, online: online ? 1 : 0 } })).data; this.addressResults = data.results || []; this.addressCanSearchOnline = Boolean(data.online_available); this.addressSearchedOnline = Boolean(data.searched_online); }
        catch (_) { this.addressResults = []; }
    },
    chooseAddress(item) {
        this.draft = { ...this.draft, formatted_address: item.formatted_address || item.label || '', street: item.street || '', house_number: item.house_number || '', postal_code: item.postal_code || '', city: item.city || '', country_code: item.country_code || 'DE', latitude: Number(item.lat), longitude: Number(item.lng) };
        this.addressQuery = item.label || ''; this.addressResults = [];
    },
    async savePoint() {
        this.pointSaving = true; this.pointError = '';
        try { const payload = { ...this.draft }; delete payload.id; delete payload.lat; delete payload.lng; delete payload.address; delete payload.creator; if (this.draft.id) await axios.put(`${config.pointsUrl}/${this.draft.id}`, payload); else await axios.post(config.pointsUrl, payload); this.pointModalOpen = false; await this.loadData(false); }
        catch (error) { this.pointError = Object.values(error.response?.data?.errors || {}).flat()[0] || error.response?.data?.message || 'Der Punkt konnte nicht gespeichert werden.'; }
        finally { this.pointSaving = false; }
    },
    async deletePoint() {
        if (!this.draft.id || !window.confirm(`„${this.draft.name}“ wirklich löschen?`)) return;
        try { await axios.delete(`${config.pointsUrl}/${this.draft.id}`); this.pointModalOpen = false; await this.loadData(false); }
        catch (error) { this.pointError = error.response?.data?.message || 'Der Punkt konnte nicht gelöscht werden.'; }
    },
    syncUrl(params) { const url = new URL(window.location.href); ['mode', 'week', 'tour_id'].forEach(key => url.searchParams.delete(key)); Object.entries(params).forEach(([key, value]) => { if (value) url.searchParams.set(key, value); }); history.replaceState({}, '', url); },
    tourColor(index) { return this.colors[index % this.colors.length]; },
    statusLabel(status) { return ({ draft: 'Entwurf', planned: 'Geplant', in_progress: 'Unterwegs', completed: 'Abgeschlossen', cancelled: 'Storniert' })[status] || status; },
    sourceLabel(source) { return ({ tomtom: 'TomTom', custom_point: 'Eigener Punkt', customer: 'Kunde', stop: 'Stopp' })[source] || 'Lokal'; },
    escape(value) { return String(value ?? '').replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]); },
    popup(kicker, title, address, actions = '', note = '') { return `<div class="sck-map-popup-card"><small>${this.escape(kicker)}</small><strong>${this.escape(title)}</strong>${address ? `<p>${this.escape(address)}</p>` : ''}${note ? `<p class="is-note">${this.escape(note)}</p>` : ''}${actions}</div>`; },
    });
});
Alpine.start();
