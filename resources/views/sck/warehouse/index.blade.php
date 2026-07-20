@extends('sck.layouts.sck')

@section('content')
<x-multi-select.state-script />

<script>
        window.warehouseInitialData = {
            showItem: @json($showItem ?? null),
            logs: @json($logs)
        };

        window.warehouseManager = function() {
                const initData = window.warehouseInitialData || { showItem: null, logs: [] };
                const showItem = initData.showItem;
                
                return {
                    openAddModal: false, 
                    openEditModal: false, 
                    openPrintModal: false,
                    openZoomModal: false,
                    openShowModal: showItem ? true : false,
                    wasOpenedFromShowModal: false,

                    // Invoice state & methods
                    openInvoiceModal: false,
                    invoiceStep: 'upload', // 'upload', 'loading', 'review'
                    invoiceFileName: '',
                    invoiceError: null,
                    invoiceData: null,
                    isSubmittingInvoiceDeduction: false,
                    showInvoiceDebug: false,
                    invoiceDebugTab: 'logs', // 'logs' or 'rawtext'

                    prefillAddModalFromInvoiceItem(item) {
                        this.addArtikelNr = item.invoice_artikelnummer && /^\d{5}$/.test(item.invoice_artikelnummer) ? item.invoice_artikelnummer : '';
                        this.openAddModal = true;
                        this.openInvoiceModal = false;
                        
                        this.$nextTick(() => {
                            const modal = document.querySelector('form[action*="lager/store"]');
                            if (modal) {
                                const setInput = (name, val) => {
                                    const el = modal.querySelector(`[name="${name}"]`);
                                    if (el) el.value = val;
                                };
                                setInput('bezeichnung', item.invoice_bezeichnung || '');
                                setInput('ek_ohne_st', item.invoice_netto_preis ? item.invoice_netto_preis.toFixed(2) : '0.00');
                                setInput('vk_ohne_st', item.invoice_netto_preis ? item.invoice_netto_preis.toFixed(2) : '0.00');
                                if (item.invoice_einheit) setInput('einheit', item.invoice_einheit);
                                if (item.invoice_ust) setInput('steuersatz', Math.round(item.invoice_ust).toString());
                                if (this.addArtikelNr) {
                                    setInput('neue_artikelnummer', this.addArtikelNr);
                                } else {
                                    this.fetchNewArtikelNr();
                                }
                            }
                        });
                    },

                    uploadAndParseInvoice(event) {
                        const file = event.target.files ? event.target.files[0] : null;
                        if (!file) return;
                        
                        this.invoiceFileName = file.name;
                        this.invoiceStep = 'loading';
                        this.invoiceError = null;
                        this.showInvoiceDebug = false;
                        
                        const formData = new FormData();
                        formData.append('invoice_file', file);
                        formData.append('_token', '{{ csrf_token() }}');
                        
                        fetch('{{ route("sck.lager.parse-invoice") }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData
                        })
                        .then(r => r.json())
                        .then(res => {
                            console.log('[PDF Invoice Parser Response]', res);
                            if (!res.success) {
                                this.invoiceError = res.message || 'Fehler beim Analysieren der Rechnung.';
                                this.invoiceStep = 'upload';
                                return;
                            }
                            if (res.items) {
                                res.items.forEach(it => {
                                    it.quantity = it.invoice_menge || 1;
                                    it.update_price = false;
                                    it.selected_item_id = it.matched_item ? it.matched_item.id : null;
                                });
                            }
                            this.invoiceData = res;
                            this.invoiceStep = 'review';

                            // Auto-expand debug logs if 0 items were detected
                            if (!res.items || res.items.length === 0) {
                                this.showInvoiceDebug = true;
                            }
                        })
                        .catch(err => {
                            console.error('[PDF Invoice Parser Error]', err);
                            this.invoiceError = 'Fehler beim Hochladen: ' + err.message;
                            this.invoiceStep = 'upload';
                        });
                    },

                    submitInvoiceDeduction() {
                        if (!this.invoiceData || !this.invoiceData.items.length) return;
                        
                        const validItems = this.invoiceData.items
                            .filter(it => it.selected_item_id)
                            .map(it => ({
                                item_id: it.selected_item_id,
                                quantity: parseInt(it.quantity) || 1,
                                update_price: it.update_price ? 1 : 0,
                                new_price: parseFloat(it.invoice_netto_preis) || 0
                            }));
                            
                        if (!validItems.length) {
                            alert('Keine gültigen Lagerartikel für den Abzug zugeordnet.');
                            return;
                        }
                        
                        this.isSubmittingInvoiceDeduction = true;
                        
                        fetch('{{ route("sck.lager.process-invoice-deduction") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                items: validItems,
                                invoice_number: this.invoiceData.invoice_info ? this.invoiceData.invoice_info.number : 'ausstehend'
                            })
                        })
                        .then(r => r.json())
                        .then(res => {
                            this.isSubmittingInvoiceDeduction = false;
                            if (res.success) {
                                this.openInvoiceModal = false;
                                window.location.reload();
                            } else {
                                alert(res.message || 'Fehler beim Abziehen des Bestands.');
                            }
                        })
                        .catch(err => {
                            this.isSubmittingInvoiceDeduction = false;
                            alert('Netzwerkfehler: ' + err.message);
                        });
                    },
                    addArtikelNr: '',
                    addArtikelNrLoading: false,
                    addArtikelNrError: '',
                    editArtikelNrError: '',
                    showDatevStatus: {{ $showDatevStatus ? 'true' : 'false' }},
                    openExport: false,
                    selectedItem: {
                        id: showItem ? showItem.id : '',
                        bezeichnung: showItem ? showItem.bezeichnung : '',
                        geraet: showItem ? showItem.geraet : '',
                        artikelgruppe: showItem ? (showItem.artikelgruppe || '') : '',
                        einheit: showItem ? (showItem.einheit || 'Stück') : 'Stück',
                        steuersatz: showItem ? (showItem.steuersatz || '19') : '19',
                        lieferant: showItem ? showItem.lieferant : '',
                        ek_ohne_st: showItem ? parseFloat(showItem.ek_ohne_st).toFixed(2) : '',
                        vk_ohne_st: showItem ? parseFloat(showItem.vk_ohne_st).toFixed(2) : '',
                        alte_artikelnummer: showItem ? showItem.alte_artikelnummer : '',
                        neue_artikelnummer: showItem ? showItem.neue_artikelnummer : '',
                        stueckzahl: showItem ? showItem.stueckzahl : 0,
                        kommentar: showItem ? (showItem.kommentar || '') : '',
                        datev_exported: showItem ? showItem.datev_exported : false
                    },
                    printSize: 'small',
                    printFields: {
                        geraet: true,
                        lieferant: true,
                        ek: false,
                        vk: false,
                        alte_nr: false,
                        neue_nr: true,
                        kommentar: false
                    },
                    tplQuery: '',
                    tplResults: [],
                    tplLoading: false,
                    
                    logs: initData.logs || [],
                    showHistoryModal: false,
                    historySearch: '',
                    historyPage: 1,
                    historyPerPage: 10,
                    historyTypeFilter: 'all',
                    historyItemFilter: 'all',
                    
                    get filteredLogs() {
                        let list = this.logs;
                        if (this.historyTypeFilter !== 'all') {
                            list = list.filter(log => log.type === this.historyTypeFilter);
                        }
                        if (this.historyItemFilter !== 'all') {
                            const itemId = parseInt(this.historyItemFilter);
                            list = list.filter(log => log.item_id === itemId);
                        }
                        if (!this.historySearch.trim()) return list;
                        const q = this.historySearch.toLowerCase();
                        return list.filter(log => 
                            log.message.toLowerCase().includes(q) || 
                            log.action.toLowerCase().includes(q) ||
                            log.time.toLowerCase().includes(q)
                        );
                    },
                    get paginatedLogs() {
                        const start = (this.historyPage - 1) * this.historyPerPage;
                        return this.filteredLogs.slice(start, start + this.historyPerPage);
                    },
                    get totalPages() {
                        return Math.ceil(this.filteredLogs.length / this.historyPerPage) || 1;
                    },
                    clearLogs() {
                        window.axios.post('{{ route('sck.lager.scan.clear_logs') }}')
                        .then(res => {
                            if (res.data.success) {
                                this.logs = [];
                            }
                        })
                        .catch(err => {
                            console.error("Failed to clear logs: ", err);
                        });
                    },
                    init() {
                        if (this.openShowModal) {
                            this.$nextTick(() => {
                                renderDetailQR();
                            });
                        }
                        this.$watch('openShowModal', val => {
                            if (val) {
                                this.$nextTick(() => {
                                    renderDetailQR();
                                });
                            } else {
                                if (window.location.pathname.includes('/artikel/')) {
                                    history.pushState(null, '', '{{ route('sck.lager.index') }}');
                                }
                            }
                        });
                        this.$watch('showDatevStatus', val => {
                            fetch('{{ route('sck.lager.toggle-datev-status-session') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ show: val })
                            });
                        });
                    },
                    searchTemplate() {
                        if (this.tplQuery.length < 2) {
                            this.tplResults = [];
                            return;
                        }
                        this.tplLoading = true;
                        fetch('{{ route('sck.lager.search-json') }}?q=' + encodeURIComponent(this.tplQuery))
                            .then(res => res.json())
                            .then(data => {
                                this.tplResults = data;
                                this.tplLoading = false;
                            })
                            .catch(() => {
                                this.tplLoading = false;
                            });
                    },
                    applyTemplate(item) {
                        document.getElementById('bezeichnung').value = item.bezeichnung;
                        document.getElementById('geraet').value = item.geraet;
                        document.getElementById('lieferant').value = item.lieferant;
                        document.getElementById('ek_ohne_st').value = item.ek_ohne_st;
                        document.getElementById('vk_ohne_st').value = item.vk_ohne_st;
                        document.getElementById('alte_artikelnummer').value = item.alte_artikelnummer || '';
                        document.getElementById('kommentar').value = item.kommentar || '';
                        this.tplQuery = '';
                        this.tplResults = [];
                    },
                    async fetchNewArtikelNr() {
                        this.addArtikelNrLoading = true;
                        this.addArtikelNrError = '';
                        try {
                            const res = await fetch('{{ route('sck.lager.generate-number') }}');
                            const data = await res.json();
                            this.addArtikelNr = data.number;
                        } catch (e) {
                            this.addArtikelNrError = 'Fehler beim Generieren.';
                        }
                        this.addArtikelNrLoading = false;
                    },
                    async rerollEditArtikelNr() {
                        this.editArtikelNrError = '';
                        try {
                            const res = await fetch('{{ route('sck.lager.generate-number') }}');
                            const data = await res.json();
                            this.selectedItem.neue_artikelnummer = data.number;
                        } catch (e) {
                            this.editArtikelNrError = 'Fehler beim Generieren.';
                        }
                    }
                };
            };

        if (window.Alpine) {
            registerWarehouseManager();
        } else {
            document.addEventListener('alpine:init', registerWarehouseManager);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Render table row QR codes using QRious
            document.querySelectorAll('.qr-canvas').forEach(canvas => {
                new QRious({
                    element: canvas,
                    value: canvas.dataset.url,
                    size: 40,
                    background: '#ffffff',
                    foreground: '#000000',
                    level: 'M'
                });
            });

            // Global Tooltip system
            let activeTooltip = null;
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'global-tooltip-item';
            document.body.appendChild(tooltipEl);

            document.addEventListener('mouseover', (e) => {
                const target = e.target.closest('.has-tooltip');
                if (!target) return;
                if (activeTooltip === target) return;
                
                const textContainer = target.querySelector('.tooltip-item');
                const text = textContainer ? textContainer.innerHTML : target.title;
                if (!text) return;

                tooltipEl.innerHTML = text;
                tooltipEl.classList.add('show');
                activeTooltip = target;
                
                positionTooltip(target);
            });

            document.addEventListener('mouseout', (e) => {
                if (!activeTooltip) return;
                const related = e.relatedTarget;
                if (!related || !activeTooltip.contains(related)) {
                    tooltipEl.classList.remove('show');
                    activeTooltip = null;
                }
            });

            function positionTooltip(target) {
                if (!activeTooltip) return;
                const rect = target.getBoundingClientRect();
                const tooltipRect = tooltipEl.getBoundingClientRect();
                
                let top = rect.top - tooltipRect.height - 8 + window.scrollY;
                let left = rect.left + (rect.width - tooltipRect.width) / 2 + window.scrollX;
                
                if (rect.top - tooltipRect.height - 8 < 0) {
                    top = rect.bottom + 8 + window.scrollY;
                }
                
                if (left < 10) left = 10;
                if (left + tooltipRect.width > window.innerWidth - 10) {
                    left = window.innerWidth - tooltipRect.width - 10;
                }
                
                tooltipEl.style.top = `${top}px`;
                tooltipEl.style.left = `${left}px`;
            }

            window.addEventListener('scroll', () => { if (activeTooltip) positionTooltip(activeTooltip); }, true);
            window.addEventListener('resize', () => { if (activeTooltip) positionTooltip(activeTooltip); });

            // Right-Click Context Menu Logic
            const contextMenu = document.getElementById('custom-context-menu');

            function positionContextMenu(clientX, clientY) {
                // Temporarily show off-screen to measure
                contextMenu.style.visibility = 'hidden';
                contextMenu.style.left = '0px';
                contextMenu.style.top  = '0px';
                contextMenu.classList.remove('hidden');

                const menuW = contextMenu.offsetWidth;
                const menuH = contextMenu.offsetHeight;

                contextMenu.classList.add('hidden');
                contextMenu.style.visibility = '';

                const viewW = window.innerWidth;
                const viewH = window.innerHeight;

                // Flip horizontally if overflows right
                let left = clientX;
                if (clientX + menuW + 12 > viewW) {
                    left = clientX - menuW;
                }
                // Clamp left
                left = Math.max(8, left);

                // Flip vertically if overflows bottom
                let top = clientY;
                if (clientY + menuH + 12 > viewH) {
                    top = clientY - menuH;
                }
                // Clamp top
                top = Math.max(8, top);

                contextMenu.style.left = `${left}px`;
                contextMenu.style.top  = `${top}px`;
                contextMenu.classList.remove('hidden');
            }

            function triggerContextMenu(row, clientX, clientY) {
                const id = row.dataset.id;
                const bezeichnung = row.dataset.bezeichnung;
                const geraet = row.dataset.geraet;
                const lieferant = row.dataset.lieferant;
                const ek = row.dataset.ek;
                const vk = row.dataset.vk;
                const alteNr = row.dataset.alteNr;
                const neueNr = row.dataset.neueNr;
                const stueckzahl = parseInt(row.dataset.stueckzahl);
                const kommentar = row.dataset.kommentar;
                const datevExported = row.dataset.datevExported === '1' || row.dataset.datevExported === 'true';

                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                alpineData.selectedItem = {
                    id, bezeichnung, geraet, lieferant,
                    ek_ohne_st: ek, vk_ohne_st: vk,
                    alte_artikelnummer: alteNr, neue_artikelnummer: neueNr,
                    stueckzahl, kommentar, datev_exported: datevExported
                };

                positionContextMenu(clientX, clientY);
            }

            let touchTimeout = null;
            let touchStartX = 0;
            let touchStartY = 0;
            const LONG_PRESS_DURATION = 600; // ms

            document.querySelectorAll('.cursor-context-menu').forEach(row => {
                row.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    triggerContextMenu(row, e.clientX, e.clientY);
                });

                row.addEventListener('touchstart', (e) => {
                    if (e.touches.length !== 1) return;
                    const touch = e.touches[0];
                    touchStartX = touch.clientX;
                    touchStartY = touch.clientY;

                    touchTimeout = setTimeout(() => {
                        e.preventDefault();
                        triggerContextMenu(row, touchStartX, touchStartY);
                    }, LONG_PRESS_DURATION);
                }, { passive: false });

                row.addEventListener('touchmove', (e) => {
                    if (touchTimeout) {
                        const touch = e.touches[0];
                        if (Math.abs(touch.clientX - touchStartX) > 10 || Math.abs(touch.clientY - touchStartY) > 10) {
                            clearTimeout(touchTimeout);
                            touchTimeout = null;
                        }
                    }
                });

                row.addEventListener('touchend', () => {
                    if (touchTimeout) {
                        clearTimeout(touchTimeout);
                        touchTimeout = null;
                    }
                });
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#custom-context-menu')) {
                    contextMenu.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    contextMenu.classList.add('hidden');
                }
            });


            // Client-Side Instant Filtering
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.cursor-context-menu');
                    
                    rows.forEach(row => {
                        const bezeichnung = (row.dataset.bezeichnung || '').toLowerCase();
                        const geraet = (row.dataset.geraet || '').toLowerCase();
                        const lieferant = (row.dataset.lieferant || '').toLowerCase();
                        const alteNr = (row.dataset.alteNr || '').toLowerCase();
                        const neueNr = (row.dataset.neueNr || '').toLowerCase();
                        
                        const matches = bezeichnung.includes(query) ||
                                        geraet.includes(query) ||
                                        lieferant.includes(query) ||
                                        alteNr.includes(query) ||
                                        neueNr.includes(query);
                                        
                        if (matches) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });

            // Global helper functions
            window.renderGlobalZoomQR = function() {
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const neueNr = alpineData.selectedItem.neue_artikelnummer;
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                
                setTimeout(() => {
                    const canvas = document.getElementById('global-zoom-qr');
                    if (canvas) {
                        new QRious({
                            element: canvas,
                            value: targetUrl,
                            size: 180,
                            background: '#ffffff',
                            foreground: '#000000',
                            level: 'H'
                        });
                    }
                }, 50);
            };

            window.renderDetailQR = function() {
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const neueNr = alpineData.selectedItem.neue_artikelnummer;
                if (!neueNr) return;
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                
                setTimeout(() => {
                    const canvas = document.getElementById('detail-qr-canvas');
                    if (canvas) {
                        new QRious({
                            element: canvas,
                            value: targetUrl,
                            size: 96,
                            padding: 0,
                            background: '#ffffff',
                            foreground: '#000000',
                            level: 'H'
                        });
                    }
                }, 50);
            };

            window.renderPrintQR = function() {
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const neueNr = alpineData.selectedItem.neue_artikelnummer;
                if (!neueNr) return;
                const size = alpineData.printSize;
                const qrSize = size === 'small' ? 80 : 120;
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                
                setTimeout(() => {
                    const canvas = document.getElementById('print-label-qr');
                    if (canvas) {
                        new QRious({
                            element: canvas,
                            value: targetUrl,
                            size: qrSize,
                            background: '#ffffff',
                            foreground: '#000000',
                            level: 'H'
                        });
                    }
                }, 50);
            };

            window.printLabel = function(neueNr, size) {
                // Gather Alpine state
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const item = alpineData.selectedItem;
                const fields = alpineData.printFields;

                // Label dimensions (matching CSS preview sizes)
                const labelW = size === 'small' ? 320 : 480;
                const labelH = size === 'small' ? 180 : 300;
                const qrSize = size === 'small' ? 80 : 120;
                const padding = size === 'small' ? 14 : 22;

                // Generate QR code as data URL via a temp canvas
                const tmpCanvas = document.createElement('canvas');
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                new QRious({
                    element: tmpCanvas,
                    value: targetUrl,
                    size: qrSize,
                    background: '#ffffff',
                    foreground: '#000000',
                    level: 'H'
                });
                const qrDataUrl = tmpCanvas.toDataURL('image/png');

                // Build field rows HTML
                let fieldsHtml = '';
                if (fields.geraet && item.geraet)      fieldsHtml += `<div class="field-row"><b>Kat:</b> ${esc(item.geraet)}</div>`;
                if (fields.lieferant && item.lieferant) fieldsHtml += `<div class="field-row"><b>Lief:</b> ${esc(item.lieferant)}</div>`;
                if (fields.ek && item.ek_ohne_st)       fieldsHtml += `<div class="field-row"><b>EK:</b> ${esc(item.ek_ohne_st)} €</div>`;
                if (fields.vk && item.vk_ohne_st)       fieldsHtml += `<div class="field-row"><b>VK:</b> ${esc(item.vk_ohne_st)} €</div>`;
                if (fields.alte_nr && item.alte_artikelnummer) fieldsHtml += `<div class="field-row"><b>Alt:</b> ${esc(item.alte_artikelnummer)}</div>`;
                if (fields.neue_nr)                     fieldsHtml += `<div class="field-row"><b>Neu:</b> ${esc(item.neue_artikelnummer)}</div>`;
                let kommentarHtml = '';
                if (fields.kommentar && item.kommentar) {
                    kommentarHtml = `<div style="border-top:1px solid #ccc;padding-top:4px;margin-top:4px;font-size:8px;color:#555;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${esc(item.kommentar)}</div>`;
                }

                const titleFontSize = size === 'small' ? '13px' : '18px';
                const fieldFontSize = size === 'small' ? '9px'  : '11px';

                const html = `<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Label: ${esc(item.bezeichnung)}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  @page {
    size: ${labelW}px ${labelH}px;
    margin: 0;
  }
  body {
    width: ${labelW}px;
    height: ${labelH}px;
    font-family: 'Figtree', Arial, sans-serif;
    background: white;
    color: black;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .label {
    width: ${labelW}px;
    height: ${labelH}px;
    padding: ${padding}px;
    display: flex;
    align-items: center;
    gap: 14px;
    overflow: hidden;
  }
  .qr-wrap {
    flex-shrink: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .qr-wrap img {
    display: block;
    width: ${qrSize}px;
    height: ${qrSize}px;
  }
  .info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    overflow: hidden;
    min-width: 0;
  }
  .title {
    font-size: ${titleFontSize};
    font-weight: 900;
    line-height: 1.2;
    color: #000;
    word-break: break-word;
  }
  .fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2px 8px;
    margin-top: 5px;
  }
  .field-row {
    font-size: ${fieldFontSize};
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .field-row b {
    font-weight: 700;
    color: #111;
  }
</style>
</head>
<body>
<div class="label">
  <div class="qr-wrap">
    <img src="${qrDataUrl}" alt="QR Code">
  </div>
  <div class="info">
    <div>
      <div class="title">${esc(item.bezeichnung)}</div>
      <div class="fields">${fieldsHtml}</div>
    </div>
    ${kommentarHtml}
  </div>
</div>
\x3Cscript\x3Ewindow.onload = function() { window.print(); window.onafterprint = function() { window.close(); }; };\x3C/script\x3E
</body>
</html>`;

                function esc(str) {
                    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                }

                const win = window.open('', '_blank', `width=${labelW + 40},height=${labelH + 120},menubar=no,toolbar=no,status=no`);
                if (win) {
                    win.document.write(html);
                    win.document.close();
                } else {
                    alert('Bitte erlauben Sie Pop-ups für diese Seite, um den Druckdialog zu öffnen.');
                }
            };

    function registerWarehouseManager() {
        if (window.Alpine) {
            Alpine.data('warehouseManager', window.warehouseManager);
        }
    }
    registerWarehouseManager();
    document.addEventListener('alpine:init', registerWarehouseManager);
</script>

<div class="space-y-6" x-data="{ ...massSelectionState({ selected: @js($selectedIds), page: @js($pageIds), all: @js($matchingIds), selectionUrl: @js(route('sck.lager.bulk-selection')), csrf: @js(csrf_token()), actions: { delete: @js(route('sck.lager.bulk-destroy')), export: @js(route('sck.lager.bulk-export')), exportDatev: @js(route('sck.lager.bulk-export-datev')), adjustStock: @js(route('sck.lager.bulk-update-stock')), toggleDatev: @js(route('sck.lager.bulk-toggle-datev-exported')) } }), ...warehouseManager() }" x-init="initMassSelection()" @toggle-datev-status.window="showDatevStatus = !showDatevStatus">

    <!-- Multi-select Action Rail -->
    <x-multi-select.index-panel :page-count="count($pageIds)" :all-count="count($matchingIds)" item-name="Artikel">
        
        <!-- Mass Export Dropdown -->
        <x-multi-select.action icon="fa-solid fa-file-export" tooltip="Ausgewählte exportieren" active-color="cyan" :dropdown="true">
            <a href="#" @click.prevent="submitBulk('export', { include_stock: 1 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 font-semibold transition-colors">
                <i class="fa-solid fa-file-invoice-dollar text-cyan-400"></i>
                <span>Mit Lagerbestand</span>
            </a>
            <a href="#" @click.prevent="submitBulk('export', { include_stock: 0 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 font-semibold transition-colors">
                <i class="fa-solid fa-file-lines text-purple-400"></i>
                <span>Ohne Lagerbestand</span>
            </a>
            <a href="#" @click.prevent="submitBulk('exportDatev')" class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 font-semibold transition-colors border-t border-gray-800">
                <i class="fa-solid fa-file-invoice text-emerald-400"></i>
                <span>DATEV Export (CSV)</span>
            </a>
        </x-multi-select.action>

        <!-- Mass DATEV Status Adjustment Dropdown -->
        <x-multi-select.action icon="fa-solid fa-file-shield" tooltip="DATEV Export-Status ändern" active-color="emerald" :dropdown="true">
            <a href="#" @click.prevent="submitBulk('toggleDatev', { status: 1 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-emerald-300 hover:bg-emerald-500/10 font-semibold transition-colors">
                <i class="fa-solid fa-check text-emerald-400"></i>
                <span>Als exportiert markieren</span>
            </a>
            <a href="#" @click.prevent="submitBulk('toggleDatev', { status: 0 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-rose-300 hover:bg-rose-500/10 font-semibold transition-colors border-t border-gray-800">
                <i class="fa-solid fa-rotate-left text-rose-400"></i>
                <span>Export-Status zurücksetzen</span>
            </a>
        </x-multi-select.action>

        <!-- Mass Stock Adjustment Dropdown -->
        <x-multi-select.action icon="fa-solid fa-boxes-packing" tooltip="Massen-Bestandsänderung" active-color="amber" :dropdown="true">
            <a href="#" @click.prevent="submitBulk('adjustStock', { mode: 'set', amount: 0 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-amber-300 hover:bg-amber-500/10 font-semibold transition-colors">
                <i class="fa-solid fa-rotate-left text-amber-400"></i>
                <span>Bestand auf 0 setzen</span>
            </a>
            <a href="#" @click.prevent="submitBulk('adjustStock', { mode: 'add', amount: 1 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-400 font-semibold transition-colors">
                <i class="fa-solid fa-plus text-emerald-400"></i>
                <span>Bestand +1 erhöhen</span>
            </a>
            <a href="#" @click.prevent="submitBulk('adjustStock', { mode: 'add', amount: 5 })" class="flex items-center gap-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-emerald-500/10 hover:text-emerald-400 font-semibold transition-colors">
                <i class="fa-solid fa-layer-group text-cyan-400"></i>
                <span>Bestand +5 erhöhen</span>
            </a>
        </x-multi-select.action>

        <!-- Mass Delete -->
        <x-multi-select.action icon="fa-solid fa-trash-can" tooltip="Markierte Artikel löschen" active-color="rose" action="submitBulk('delete')" />

    </x-multi-select.index-panel>

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('sck.dashboard', ['no_redirect' => 1]) }}" class="text-gray-400 hover:text-cyan-400 transition-colors">
                    <i class="fa-solid fa-house-laptop"></i>
                </a>
                <span class="text-gray-600">/</span>
                <span class="text-gray-300 font-semibold">Lagersystem</span>
            </div>
            <h2 class="text-2xl font-black mt-1">Lagerbestand & Artikelliste</h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- QR Scanner button -->
            <a href="{{ route('sck.lager.scan') }}" class="btn-neon-purple text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center space-x-2 transition-all duration-200 has-tooltip">
                <i class="fa-solid fa-camera"></i>
                <span>QR-Scanner öffnen</span>
                <div class="tooltip-item tooltip-left">Öffnet die Smartphone-Kamera, um QR-Codes direkt ein- oder auszulesen und den Bestand anzupassen.</div>
            </a>

            <!-- Activity log button -->
            <button @click="historyItemFilter = 'all'; historyTypeFilter = 'all'; historyPage = 1; showHistoryModal = true;" class="bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 px-4 py-2 rounded-xl text-sm font-semibold flex items-center space-x-2 transition-colors has-tooltip">
                <i class="fa-solid fa-clock-rotate-left text-cyan-400"></i>
                <span>Aktivitätsprotokoll</span>
                <div class="tooltip-item">Öffnet das dauerhafte Protokoll aller Bestandsänderungen (Scanner, manuelle Updates, Schnelländerungen).</div>
            </button>

            <!-- Add new item button -->
            <button @click="openAddModal = true; fetchNewArtikelNr()" class="btn-neon-cyan text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center space-x-2 transition-all duration-200 has-tooltip">
                <i class="fa-solid fa-circle-plus"></i>
                <span>Neuer Artikel</span>
                <div class="tooltip-item">Fügt einen neuen Artikel im Lagersystem hinzu. Die 5-stellige Artikelnummer wird automatisch generiert.</div>
            </button>

            <!-- Redesigned Upload & Export Button Group (Completely Green) -->
            <div x-data="{ openExport: false }" class="inline-flex rounded-xl shadow-lg shadow-emerald-600/20 bg-gradient-to-r from-emerald-600 to-teal-600 border border-emerald-500/30 relative">
                <!-- Main Upload Button -->
                <button @click="openInvoiceModal = true; invoiceStep = 'upload'; invoiceError = null;" class="hover:from-emerald-500 hover:to-teal-500 text-white px-4 py-2 text-sm font-bold flex items-center space-x-2 transition-all duration-200 border-r border-emerald-500/20 rounded-l-xl has-tooltip">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span>Rechnung hochladen</span>
                    <div class="tooltip-item tooltip-left">Lädt eine DATEV-Rechnung als PDF hoch, erkennt genutzte Artikel und zieht diese nach Prüfung automatisch vom Lagerbestand ab.</div>
                </button>
                <!-- Export Dropdown Sub-part Toggle Button -->
                <button @click="openExport = !openExport" class="hover:bg-black/10 text-white px-3 py-2 text-xs font-semibold flex items-center justify-center transition-colors rounded-r-xl has-tooltip">
                    <i class="fa-solid fa-file-export"></i>
                    <i class="fa-solid fa-chevron-down text-[9px] ml-1.5"></i>
                    <div class="tooltip-item">DATEV Export herunterladen oder Spalten einblenden.</div>
                </button>
                <!-- Dropdown Menu -->
                <div x-show="openExport" @click.away="openExport = false" class="origin-top-right absolute right-0 mt-10 w-60 rounded-xl shadow-2xl bg-gray-950 border border-gray-850 focus:outline-none z-50 glass-panel" x-cloak>
                    <div class="py-1">
                        <a href="{{ route('sck.lager.export', ['include_stock' => 1]) }}" class="flex items-center space-x-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                            <i class="fa-solid fa-file-invoice-dollar text-cyan-400"></i>
                            <span>Mit aktuellem Lagerbestand</span>
                        </a>
                        <a href="{{ route('sck.lager.export', ['include_stock' => 0]) }}" class="flex items-center space-x-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                            <i class="fa-solid fa-file-lines text-purple-400"></i>
                            <span>Ohne Lagerbestand (nur Katalog)</span>
                        </a>
                        <a href="{{ route('sck.lager.export-datev') }}" class="flex items-center space-x-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors border-t border-gray-800">
                            <i class="fa-solid fa-file-invoice text-emerald-400"></i>
                            <span>DATEV Export (CSV)</span>
                        </a>
                        <button type="button" @click="window.dispatchEvent(new CustomEvent('toggle-datev-status')); openExport = false" class="w-full text-left flex items-center space-x-2 px-4 py-2.5 text-xs text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors border-t border-gray-800">
                            <i class="fa-solid" :class="showDatevStatus ? 'fa-eye-slash text-amber-400' : 'fa-eye text-cyan-400'"></i>
                            <span x-text="showDatevStatus ? 'DATEV-Status ausblenden' : 'DATEV-Status einblenden'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Artikel im System</p>
                <h4 class="text-xl font-bold mt-1">{{ \App\Models\Sck\SckWarehouseItem::count() }}</h4>
            </div>
            <div class="text-cyan-400"><i class="fa-solid fa-box text-xl"></i></div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Geringer Bestand</p>
                <h4 class="text-xl font-bold mt-1 text-amber-400">{{ \App\Models\Sck\SckWarehouseItem::where('stueckzahl', '<', 5)->count() }}</h4>
            </div>
            <div class="text-amber-400"><i class="fa-solid fa-circle-exclamation text-xl"></i></div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Gesamtwert (EK)</p>
                <h4 class="text-xl font-bold mt-1 text-emerald-400">
                    {{ number_format(\App\Models\Sck\SckWarehouseItem::sum(\DB::raw('ek_ohne_st * stueckzahl')), 2, ',', '.') }} €
                </h4>
            </div>
            <div class="text-emerald-400"><i class="fa-solid fa-coins text-xl"></i></div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Gesamteinheiten</p>
                <h4 class="text-xl font-bold mt-1 text-purple-400">{{ \App\Models\Sck\SckWarehouseItem::sum('stueckzahl') }} Stk.</h4>
            </div>
            <div class="text-purple-400"><i class="fa-solid fa-warehouse text-xl"></i></div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="glass-panel p-4 rounded-xl border border-gray-800">
        <form action="{{ route('sck.lager.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-grow">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Suche nach Bezeichnung, Gerät, Lieferant oder Artikelnummer..." class="sck-input pl-10 pr-4 py-2 rounded-xl text-sm w-full">
            </div>
            <div class="flex gap-2">
                @if(request('search') || request('sort_by'))
                    <a href="{{ route('sck.lager.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-xl text-sm font-semibold flex items-center space-x-1.5 transition-colors">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                        <span>Zurücksetzen</span>
                    </a>
                @endif
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-2 rounded-xl text-sm font-bold transition-colors">
                    Filter anwenden
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="min-w-0 transition-[margin,width] duration-500 ease-out glass-panel rounded-2xl border border-gray-800 overflow-hidden shadow-2xl" x-bind:style="panelNeedsSpace ? 'margin-left: 76px; width: calc(100% - 76px); max-width: calc(100% - 76px);' : ''">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-950/65 text-gray-400 border-b border-gray-800 text-xs uppercase font-bold tracking-wider">
                        <th class="py-4 px-3 text-center !w-12">
                            <x-multi-select.header-checkbox />
                        </th>
                        <th class="py-4 px-5">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'bezeichnung', 'sort_dir' => request('sort_by') === 'bezeichnung' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors">
                                <span>Bezeichnung</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                            </a>
                        </th>
                        <th class="py-4 px-4">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'geraet', 'sort_dir' => request('sort_by') === 'geraet' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Gerät</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Der Gerätetyp oder die Bezeichnung der Maschine.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'artikelgruppe', 'sort_dir' => request('sort_by') === 'artikelgruppe' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Artikelgruppe</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Die DATEV-Artikelgruppe des Artikels.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'lieferant', 'sort_dir' => request('sort_by') === 'lieferant' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors">
                                <span>Lieferant</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-right">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'ek_ohne_st', 'sort_dir' => request('sort_by') === 'ek_ohne_st' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>EK o. St.</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Einkaufspreis netto (ohne Steuer) pro Einheit.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-right">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'vk_ohne_st', 'sort_dir' => request('sort_by') === 'vk_ohne_st' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>VK o. St.</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Verkaufspreis netto (ohne Steuer) pro Einheit.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-center" x-show="showDatevStatus" x-cloak>
                            DATEV Export
                        </th>
                        <th class="py-4 px-4 text-center">Einheit</th>
                        <th class="py-4 px-4 text-center font-semibold">Steuer</th>
                        <th class="py-4 px-4 text-center font-normal">Alte Nr.</th>
                        <th class="py-4 px-4 text-center">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'neue_artikelnummer', 'sort_dir' => request('sort_by') === 'neue_artikelnummer' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Neue Nr.</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Systemgenerierte, eindeutige 5-stellige Artikelnummer.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-center">QR Code</th>
                        <th class="py-4 px-4 text-center">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'stueckzahl', 'sort_dir' => request('sort_by') === 'stueckzahl' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Bestand</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Aktueller Lagerbestand. Kann über die Tasten direkt erhöht oder verringert werden.</div>
                            </a>
                        </th>
                        <th class="py-4 px-5">Kommentar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-900/30 transition-colors cursor-context-menu"
                            :class="{ '!bg-cyan-500/10': selectedIds.includes('{{ $item->id }}') }"
                            data-id="{{ $item->id }}"
                            data-bezeichnung="{{ $item->bezeichnung }}"
                            data-geraet="{{ $item->geraet }}"
                            data-artikelgruppe="{{ $item->artikelgruppe ?? '' }}"
                            data-einheit="{{ $item->einheit ?? '' }}"
                            data-steuersatz="{{ $item->steuersatz ?? '' }}"
                            data-lieferant="{{ $item->lieferant }}"
                            data-ek="{{ number_format($item->ek_ohne_st, 2, '.', '') }}"
                            data-vk="{{ number_format($item->vk_ohne_st, 2, '.', '') }}"
                            data-alte-nr="{{ $item->alte_artikelnummer ?? '' }}"
                            data-neue-nr="{{ $item->neue_artikelnummer }}"
                            data-stueckzahl="{{ $item->stueckzahl }}"
                            data-kommentar="{{ $item->kommentar ?? '' }}"
                            data-datev-exported="{{ $item->datev_exported ? '1' : '0' }}">
                            <x-multi-select.row-checkbox :id="$item->id" />
                            <td class="py-4 px-5 font-semibold text-gray-200">
                                <a href="{{ route('sck.lager.artikel', $item->neue_artikelnummer) }}" 
                                   @click.prevent="selectedItem = {
                                        id: '{{ $item->id }}',
                                        bezeichnung: '{{ addslashes($item->bezeichnung) }}',
                                        geraet: '{{ addslashes($item->geraet) }}',
                                        artikelgruppe: '{{ addslashes($item->artikelgruppe ?? '') }}',
                                        einheit: '{{ addslashes($item->einheit ?? 'Stück') }}',
                                        steuersatz: '{{ $item->steuersatz ?? '19' }}',
                                        lieferant: '{{ addslashes($item->lieferant) }}',
                                        ek_ohne_st: '{{ number_format($item->ek_ohne_st, 2, '.', '') }}',
                                        vk_ohne_st: '{{ number_format($item->vk_ohne_st, 2, '.', '') }}',
                                        alte_artikelnummer: '{{ $item->alte_artikelnummer ?? '' }}',
                                        neue_artikelnummer: '{{ $item->neue_artikelnummer }}',
                                        stueckzahl: '{{ $item->stueckzahl }}',
                                        kommentar: '{{ addslashes($item->kommentar ?? '') }}',
                                        datev_exported: {{ $item->datev_exported ? 'true' : 'false' }}
                                   };
                                   openShowModal = true;
                                   history.pushState(null, '', '{{ route('sck.lager.artikel', $item->neue_artikelnummer) }}');"
                                   class="hover:text-cyan-400 transition-colors">
                                    {{ $item->bezeichnung }}
                                </a>
                            </td>
                            <td class="py-4 px-4 text-gray-300">{{ $item->geraet }}</td>
                            <td class="py-4 px-4 text-gray-300 text-xs font-semibold">{{ $item->artikelgruppe ?? '-' }}</td>
                            <td class="py-4 px-4 text-gray-400">{{ $item->lieferant }}</td>
                            <td class="py-4 px-4 text-right text-gray-300 font-mono">{{ number_format($item->ek_ohne_st, 2, ',', '.') }} €</td>
                            <td class="py-4 px-4 text-right text-gray-300 font-mono">{{ number_format($item->vk_ohne_st, 2, ',', '.') }} €</td>
                            <td class="py-4 px-4 text-center" x-show="showDatevStatus" x-cloak>
                                @if($item->datev_exported)
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">Exportiert</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-gray-800 text-gray-500 border border-gray-700">Bereit</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-gray-300">{{ $item->einheit ?? 'Stück' }}</td>
                            <td class="py-4 px-4 text-center text-gray-400 font-mono">{{ $item->steuersatz ?? '19' }}%</td>
                            <td class="py-4 px-4 text-center text-gray-500 font-mono">{{ $item->alte_artikelnummer ?? '-' }}</td>
                            <td class="py-4 px-4 text-center text-cyan-400 font-mono font-bold">{{ $item->neue_artikelnummer }}</td>
                            <td class="py-3 px-4 text-center">
                                <!-- Trigger click for modal zoom -->
                                <div class="inline-block">
                                    <div @click="selectedItem = {
                                        id: '{{ $item->id }}',
                                        bezeichnung: '{{ addslashes($item->bezeichnung) }}',
                                        geraet: '{{ addslashes($item->geraet) }}',
                                        artikelgruppe: '{{ addslashes($item->artikelgruppe ?? '') }}',
                                        einheit: '{{ addslashes($item->einheit ?? 'Stück') }}',
                                        steuersatz: '{{ $item->steuersatz ?? '19' }}',
                                        lieferant: '{{ addslashes($item->lieferant) }}',
                                        ek_ohne_st: '{{ number_format($item->ek_ohne_st, 2, '.', '') }}',
                                        vk_ohne_st: '{{ number_format($item->vk_ohne_st, 2, '.', '') }}',
                                        alte_artikelnummer: '{{ $item->alte_artikelnummer ?? '' }}',
                                        neue_artikelnummer: '{{ $item->neue_artikelnummer }}',
                                        stueckzahl: '{{ $item->stueckzahl }}',
                                        kommentar: '{{ addslashes($item->kommentar ?? '') }}',
                                        datev_exported: {{ $item->datev_exported ? 'true' : 'false' }}
                                     };
                                     openZoomModal = true;
                                     renderGlobalZoomQR();" 
                                     class="cursor-pointer bg-white p-1 rounded inline-block shadow hover:scale-105 transition-transform has-tooltip">
                                        <canvas class="qr-canvas inline-block" data-url="{{ route('sck.lager.artikel', $item->neue_artikelnummer) }}" width="40" height="40"></canvas>
                                        <div class="tooltip-item">Klicken, um den Barcode vergrößert anzuzeigen oder herunterzuladen.</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center">
                                    <form action="{{ route('sck.lager.update-stock') }}" method="POST" class="flex items-center space-x-1.5 bg-gray-900/60 p-1 rounded-lg border border-gray-800">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        
                                        <!-- Decrement button -->
                                        <button type="submit" name="action" value="remove" class="w-6 h-6 rounded bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white flex items-center justify-center transition-all font-bold text-xs">
                                            -
                                        </button>
                                        
                                        <!-- Editable quantity input -->
                                        <input type="number" name="quantity" value="1" min="1" class="sck-input w-9 text-center text-xs py-0.5 px-0 rounded border-0 bg-transparent text-gray-200 font-bold font-mono">
                                        
                                        <!-- Increment button -->
                                        <button type="submit" name="action" value="add" class="w-6 h-6 rounded bg-emerald-500/10 hover:bg-emerald-600 text-emerald-400 hover:text-white flex items-center justify-center transition-all font-bold text-xs">
                                            +
                                        </button>
                                    </form>
                                    
                                    <!-- Stock label -->
                                    <span class="ml-3 font-mono font-bold w-12 text-left" :class="{{ $item->stueckzahl }} < 5 ? 'text-amber-400 font-black animate-pulse' : 'text-gray-200'">
                                        {{ $item->stueckzahl }} Stk.
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-5 text-gray-500 text-xs italic max-w-xs truncate" title="{{ $item->kommentar }}">
                                {{ $item->kommentar ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-gray-500">
                                <i class="fa-solid fa-ban text-3xl mb-2 block"></i>
                                <span>Keine Artikel im Lager gefunden.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="bg-gray-950/45 px-5 py-4 border-t border-gray-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>



    <!-- Create Product Modal Overlay -->
    <div x-show="openAddModal" @keydown.escape.window="openAddModal = false" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/75" x-cloak>
        <div @click.away="openAddModal = false" class="glass-panel max-w-lg w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-circle-plus text-cyan-400"></i>
                    <span>Neuen Artikel anlegen</span>
                </h3>
                <button @click="openAddModal = false" class="text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('sck.lager.store') }}" method="POST" class="p-6 overflow-y-auto space-y-4 flex-grow text-left">
                @csrf
                
                <!-- Autocomplete Template Search -->
                <div class="bg-cyan-950/20 border border-cyan-800/30 rounded-xl p-3.5 space-y-2 relative" x-data="{ tplOpen: false }">
                    <label class="text-xxs font-black text-cyan-400 uppercase tracking-widest block">
                        Bestehenden Artikel als Vorlage verwenden
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                        <input type="text" x-model="tplQuery" @input.debounce.300ms="searchTemplate()" @focus="tplOpen = true" placeholder="Tippe zur Suche nach Name, alter/neuer Nummer..." class="sck-input text-xs rounded-lg pl-9 pr-4 py-2 w-full">
                    </div>
                    
                    <div x-show="tplOpen && tplResults.length > 0" @click.away="tplOpen = false" class="absolute left-3 right-3 top-full mt-1 bg-gray-950 border border-gray-800 rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto divide-y divide-gray-850 glass-panel" x-cloak>
                        <template x-for="item in tplResults" :key="item.id">
                            <button type="button" @click="applyTemplate(item); tplOpen = false" class="w-full text-left px-4 py-2.5 hover:bg-cyan-500/10 text-xs flex justify-between items-center transition-colors">
                                <div>
                                    <span class="font-bold text-gray-200 block" x-text="item.bezeichnung"></span>
                                    <span class="text-gray-500 text-xxs block" x-text="'Lieferant: ' + item.lieferant + ' | Gerät: ' + item.geraet"></span>
                                </div>
                                <span class="text-cyan-400 font-mono font-bold" x-text="item.neue_artikelnummer"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="tplLoading" class="absolute right-6 bottom-5">
                        <i class="fa-solid fa-circle-notch animate-spin text-cyan-400 text-xs"></i>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="bezeichnung" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Bezeichnung *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Der offizielle Name des Artikels oder Materials (z. B. 'Akkuschrauber GSR 18V').</div>
                        </label>
                        <input type="text" name="bezeichnung" id="bezeichnung" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="geraet" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Gerätetyp / Kategorie *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Zweck oder Art (z. B. 'Verbrauchsmaterial', 'Elektrowerkzeug', 'Schutzkleidung').</div>
                        </label>
                        <input type="text" name="geraet" id="geraet" required list="category-list" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                        <datalist id="category-list">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="lieferant" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Lieferant *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Unternehmen, das den Artikel liefert (z. B. 'Würth GmbH', 'Bosch Professional').</div>
                        </label>
                        <input type="text" name="lieferant" id="lieferant" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="artikelgruppe" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Artikelgruppe</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">DATEV Artikelgruppe (z. B. 'Ersatzteile Kaffee', 'Dienstleistung').</div>
                        </label>
                        <input type="text" name="artikelgruppe" id="artikelgruppe" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="einheit" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Einheit *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Maßeinheit für den Verkauf (z. B. 'Stück', 'Std.', 'Meter').</div>
                        </label>
                        <input type="text" name="einheit" id="einheit" value="Stück" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="steuersatz" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Steuersatz *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">DATEV Steuersatz für den Artikel.</div>
                        </label>
                        <select name="steuersatz" id="steuersatz" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                            <option value="19" selected>19% (normal)</option>
                            <option value="7">7% (ermäßigt)</option>
                            <option value="0">0% (steuerfrei)</option>
                        </select>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="ek_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>EK Netto (in €) *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Einkaufspreis ohne Mehrwertsteuer. Format: Zahl mit Punkt oder Komma (z. B. '45.50').</div>
                        </label>
                        <input type="number" name="ek_ohne_st" id="ek_ohne_st" step="0.01" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="vk_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>VK Netto (in €) *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Verkaufspreis ohne Mehrwertsteuer. Format: Zahl (z. B. '89.90').</div>
                        </label>
                        <input type="number" name="vk_ohne_st" id="vk_ohne_st" step="0.01" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="alte_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Alte Artikelnummer</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Die Artikelnummer aus dem vorherigen Softwaresystem zur Rückverfolgung.</div>
                        </label>
                        <input type="text" name="alte_artikelnummer" id="alte_artikelnummer" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <!-- Neue Artikelnummer field -->
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="add_neue_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1">
                            <span>Neue Artikelnummer</span>
                            <span class="ml-1 text-[10px] font-bold text-amber-400 uppercase tracking-wider">5-stellig</span>
                        </label>
                        <!-- Warning Banner -->
                        <div class="sck-artNr-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>
                                <strong>Wichtig:</strong> Die Artikelnummer ist dauerhaft mit dem QR-Code verknüpft. Nach dem Anlegen sollte sie <strong>nicht mehr geändert werden</strong>, da sonst alle gedruckten Etiketten ungültig werden.
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 mt-1">
                            <input type="text" name="neue_artikelnummer" id="add_neue_artikelnummer"
                                   x-model="addArtikelNr"
                                   maxlength="5" minlength="5" pattern="[0-9]{5}"
                                   required
                                   placeholder="z. B. 48291"
                                   class="sck-input text-sm rounded-lg px-3 py-2 font-mono font-bold flex-grow">
                            <button type="button" @click="fetchNewArtikelNr()"
                                    :disabled="addArtikelNrLoading"
                                    class="sck-reroll-btn"
                                    title="Neue zufällige Nummer generieren">
                                <i class="fa-solid fa-dice" :class="addArtikelNrLoading ? 'animate-spin' : ''"></i>
                                <span x-show="!addArtikelNrLoading">Neu würfeln</span>
                                <span x-show="addArtikelNrLoading" x-cloak>...</span>
                            </button>
                        </div>
                        <p x-show="addArtikelNrError" x-text="addArtikelNrError" class="text-red-400 text-xs mt-1" x-cloak></p>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="stueckzahl" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Anfangsbestand *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Die anfänglich verfügbare Stückzahl, die sich im Lager befindet.</div>
                        </label>
                        <input type="number" name="stueckzahl" id="stueckzahl" value="0" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="kommentar" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Kommentar</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Zusätzliche Notizen (z. B. Lagerfach, Maße, Verpackungseinheiten).</div>
                        </label>
                        <textarea name="kommentar" id="kommentar" rows="3" class="sck-input text-sm rounded-lg px-3 py-2 mt-1"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-850">
                    <button type="button" @click="openAddModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                        Artikel speichern
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal Overlay -->
    <div x-show="openEditModal" @keydown.escape.window="openEditModal = false; if (wasOpenedFromShowModal) { openShowModal = true; wasOpenedFromShowModal = false; }" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/75" x-cloak>
        <div @click.away="openEditModal = false; if (wasOpenedFromShowModal) { openShowModal = true; wasOpenedFromShowModal = false; }" class="glass-panel max-w-lg w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-pen text-cyan-400"></i>
                    <span>Artikel bearbeiten</span>
                </h3>
                <button @click="openEditModal = false; if (wasOpenedFromShowModal) { openShowModal = true; wasOpenedFromShowModal = false; }" class="text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('sck.lager.update') }}" method="POST" class="p-6 overflow-y-auto space-y-4 flex-grow text-left">
                @csrf
                <input type="hidden" name="id" :value="selectedItem.id">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_bezeichnung" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bezeichnung *</label>
                        <input type="text" name="bezeichnung" id="edit_bezeichnung" x-model="selectedItem.bezeichnung" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_geraet" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gerätetyp / Kategorie *</label>
                        <input type="text" name="geraet" id="edit_geraet" x-model="selectedItem.geraet" required list="category-list" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_lieferant" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lieferant *</label>
                        <input type="text" name="lieferant" id="edit_lieferant" x-model="selectedItem.lieferant" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_artikelgruppe" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Artikelgruppe</label>
                        <input type="text" name="artikelgruppe" id="edit_artikelgruppe" x-model="selectedItem.artikelgruppe" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_einheit" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Einheit *</label>
                        <input type="text" name="einheit" id="edit_einheit" x-model="selectedItem.einheit" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_steuersatz" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Steuersatz *</label>
                        <select name="steuersatz" id="edit_steuersatz" x-model="selectedItem.steuersatz" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                            <option value="19">19% (normal)</option>
                            <option value="7">7% (ermäßigt)</option>
                            <option value="0">0% (steuerfrei)</option>
                        </select>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_ek_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">EK Netto (in €) *</label>
                        <input type="number" name="ek_ohne_st" id="edit_ek_ohne_st" step="0.01" min="0" x-model="selectedItem.ek_ohne_st" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_vk_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">VK Netto (in €) *</label>
                        <input type="number" name="vk_ohne_st" id="edit_vk_ohne_st" step="0.01" min="0" x-model="selectedItem.vk_ohne_st" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_alte_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Alte Artikelnummer</label>
                        <input type="text" name="alte_artikelnummer" id="edit_alte_artikelnummer" x-model="selectedItem.alte_artikelnummer" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <!-- Neue Artikelnummer field (editable) -->
                    <div class="flex flex-col space-y-1 sm:col-span-2" x-data="{ showArtNrEdit: false }">
                        <button type="button" @click="showArtNrEdit = !showArtNrEdit" class="text-xs font-bold text-cyan-400 hover:text-cyan-300 flex items-center space-x-1.5 focus:outline-none transition-colors w-fit">
                            <i class="fa-solid" :class="showArtNrEdit ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                            <span>Artikelnummer ändern / Neu generieren</span>
                        </button>
                        
                        <div x-show="showArtNrEdit" x-transition class="space-y-2 mt-2" x-cloak>
                            <label for="edit_neue_artikelnummer" class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1">
                                <span>Neue Artikelnummer</span>
                                <span class="ml-1 text-[9px] font-bold text-amber-400 uppercase tracking-wider">5-stellig</span>
                            </label>
                            <!-- Warning Banner -->
                            <div class="sck-artNr-warning text-xs">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>
                                    <strong>Achtung:</strong> Das Ändern der Artikelnummer macht alle bestehenden QR-Etiketten ungültig. Nur ändern, wenn wirklich nötig und alle Etiketten neu gedruckt werden.
                                </span>
                            </div>
                            <div class="flex items-center space-x-2 mt-1">
                                <input type="text" name="neue_artikelnummer" id="edit_neue_artikelnummer"
                                       x-model="selectedItem.neue_artikelnummer"
                                       maxlength="5" minlength="5" pattern="[0-9]{5}"
                                       required
                                       class="sck-input text-sm rounded-lg px-3 py-2 font-mono font-bold flex-grow">
                                <button type="button" @click="rerollEditArtikelNr()"
                                        class="sck-reroll-btn"
                                        title="Neue zufällige Nummer generieren">
                                    <i class="fa-solid fa-dice"></i>
                                    <span>Neu würfeln</span>
                                </button>
                            </div>
                            <p x-show="editArtikelNrError" x-text="editArtikelNrError" class="text-red-400 text-xs mt-1" x-cloak></p>
                        </div>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_stueckzahl" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bestand *</label>
                        <input type="number" name="stueckzahl" id="edit_stueckzahl" x-model="selectedItem.stueckzahl" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_kommentar" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kommentar</label>
                        <textarea name="kommentar" id="edit_kommentar" rows="3" x-model="selectedItem.kommentar" class="sck-input text-sm rounded-lg px-3 py-2 mt-1"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-850">
                    <button type="button" @click="openEditModal = false; if (wasOpenedFromShowModal) { openShowModal = true; wasOpenedFromShowModal = false; }" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Label Print Modal Overlay -->
    <div x-show="openPrintModal" @keydown.escape.window="openPrintModal = false" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/75" x-cloak x-init="$watch('printSize', () => renderPrintQR()); $watch('openPrintModal', val => { if (val) $nextTick(() => renderPrintQR()); })">
        <div @click.away="openPrintModal = false" class="glass-panel max-w-2xl w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-print text-cyan-400"></i>
                    <span>Label drucken - Konfiguration</span>
                </h3>
                <button @click="openPrintModal = false" class="text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-6 flex-grow text-left grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Settings Panel -->
                <div class="space-y-4">
                    <!-- Size Selector -->
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">Label-Größe</span>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="printSize = 'small'; renderPrintQR();" class="px-4 py-2 rounded-lg text-xs font-bold border transition-all"
                                    :class="printSize === 'small' ? 'bg-cyan-500/20 border-cyan-500 text-cyan-400' : 'bg-gray-900 border-gray-800 text-gray-400 hover:text-gray-200'">
                                Klein (Space-efficient)
                            </button>
                            <button type="button" @click="printSize = 'big'; renderPrintQR();" class="px-4 py-2 rounded-lg text-xs font-bold border transition-all"
                                    :class="printSize === 'big' ? 'bg-cyan-500/20 border-cyan-500 text-cyan-400' : 'bg-gray-900 border-gray-800 text-gray-400 hover:text-gray-200'">
                                Groß (Besser lesbar)
                            </button>
                        </div>
                    </div>

                    <!-- Fields Selector -->
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">Zu druckende Felder</span>
                        <div class="space-y-2 bg-gray-950/40 p-3 rounded-lg border border-gray-850">
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.geraet" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Gerät / Kategorie</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.lieferant" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Lieferant</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.ek" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>EK o. St.</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.vk" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>VK o. St.</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.alte_nr" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Alte Artikelnummer</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.neue_nr" disabled class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span class="text-gray-500">Neue Artikelnummer (Immer gedruckt)</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.kommentar" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Kommentar / Lagerhinweis</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="flex flex-col items-center justify-center space-y-4">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block text-center">Live-Vorschau</span>
                    
                    <div class="border border-gray-800 p-4 rounded-xl bg-gray-950 flex flex-col items-center justify-center overflow-auto max-w-full min-h-[220px] w-full">
                        <!-- Printed label frame -->
                        <div id="print-preview-container" class="print-label-container" :class="printSize === 'small' ? 'size-small' : 'size-big'">
                            <div class="flex items-center w-full h-full space-x-4">
                                <div class="flex-shrink-0 bg-white p-1 rounded border border-gray-200">
                                    <canvas id="print-label-qr"></canvas>
                                </div>
                                <div class="flex-grow flex flex-col justify-between h-full overflow-hidden text-left text-black">
                                    <div>
                                        <h3 class="font-black leading-tight text-black" :class="printSize === 'small' ? 'text-sm' : 'text-lg'" x-text="selectedItem.bezeichnung"></h3>
                                        
                                        <div class="grid grid-cols-2 gap-x-2 mt-1 text-[9px] text-gray-700" :class="printSize === 'small' ? '' : 'text-xs'">
                                            <div x-show="printFields.geraet" class="truncate font-medium"><span class="font-bold">Kat:</span> <span x-text="selectedItem.geraet"></span></div>
                                            <div x-show="printFields.lieferant" class="truncate font-medium"><span class="font-bold">Lief:</span> <span x-text="selectedItem.lieferant"></span></div>
                                            <div x-show="printFields.ek" class="truncate font-medium"><span class="font-bold">EK:</span> <span x-text="selectedItem.ek_ohne_st"></span> €</div>
                                            <div x-show="printFields.vk" class="truncate font-medium"><span class="font-bold">VK:</span> <span x-text="selectedItem.vk_ohne_st"></span> €</div>
                                            <div x-show="printFields.alte_nr" class="truncate font-medium"><span class="font-bold">Alt:</span> <span x-text="selectedItem.alte_artikelnummer || '-'"></span></div>
                                            <div x-show="printFields.neue_nr" class="truncate font-medium"><span class="font-bold">Neu:</span> <span x-text="selectedItem.neue_artikelnummer"></span></div>
                                        </div>
                                    </div>
                                    <div x-show="printFields.kommentar" class="border-t border-gray-300 pt-1 mt-1 text-gray-600 leading-tight truncate text-[8px]" :class="printSize === 'small' ? '' : 'text-[9px]'" x-text="selectedItem.kommentar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t border-gray-850 bg-gray-950/20">
                <button type="button" @click="openPrintModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Schließen
                </button>
                <button type="button" @click="printLabel(selectedItem.neue_artikelnummer, printSize)" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                    <i class="fa-solid fa-print mr-1"></i>
                    Jetzt drucken
                </button>
            </div>
        </div>
    </div>

    <!-- Product Detail (Show) Modal Overlay -->
    <div x-show="openShowModal" @keydown.escape.window="openShowModal = false" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/75" x-cloak>
        <div @click.away="openShowModal = false" class="glass-panel max-w-md w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-circle-info text-cyan-400"></i>
                    <span>Artikel-Details</span>
                </h3>
                <div class="flex items-center space-x-3">
                    <button @click="openShowModal = false; openEditModal = true; wasOpenedFromShowModal = true" class="text-xs bg-gray-900 hover:bg-cyan-500/10 hover:text-cyan-400 border border-gray-800 hover:border-cyan-500/30 px-3 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all">
                        <i class="fa-solid fa-pen"></i>
                        <span>Bearbeiten</span>
                    </button>
                    <button @click="openShowModal = false" class="text-gray-500 hover:text-gray-300 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="p-5 overflow-y-auto space-y-4 flex-grow text-left">
                <!-- Redesigned Header: More compact, larger QR code -->
                <div class="flex items-center justify-between gap-4 pb-4 border-b border-gray-850">
                    <div class="flex-grow min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xxs font-bold uppercase tracking-widest px-2 py-0.5 rounded show-modal-badge" x-text="selectedItem.geraet"></span>
                            <span class="text-xxs font-mono font-bold text-gray-500 bg-gray-950/40 px-2 py-0.5 rounded border border-gray-850" x-text="'Nr: ' + selectedItem.neue_artikelnummer"></span>
                        </div>
                        <h2 class="text-lg font-black leading-tight show-modal-title break-words" x-text="selectedItem.bezeichnung"></h2>
                    </div>
                    <!-- Large QR Code display -->
                    <div class="w-24 h-24 flex-shrink-0 bg-white p-1 rounded-lg shadow border border-gray-250 flex items-center justify-center overflow-hidden">
                        <canvas id="detail-qr-canvas" class="w-full h-full block" width="96" height="96"></canvas>
                    </div>
                </div>

                <!-- Specifications list -->
                <div class="space-y-3.5 text-sm">
                    <!-- Current Stock + Integrated Quick Actions -->
                    <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-850 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Lagerbestand</span>
                            <div class="text-xl font-black font-mono mt-0.5 show-modal-stock-text" :class="selectedItem.stueckzahl < 5 ? 'text-amber-500' : ''">
                                <span x-text="selectedItem.stueckzahl"></span> Stk.
                            </div>
                        </div>
                        
                        <!-- Inline Quick Actions Form -->
                        <form action="{{ route('sck.lager.update-stock') }}" method="POST" class="flex items-center space-x-2 bg-gray-900/60 p-1 rounded-lg border border-gray-800 self-start sm:self-auto">
                            @csrf
                            <input type="hidden" name="item_id" :value="selectedItem.id">
                            
                            <button type="submit" name="action" value="remove" class="w-7 h-7 rounded bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white flex items-center justify-center transition-all font-bold text-xs" title="Entnehmen">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            
                            <input type="number" name="quantity" value="1" min="1" class="sck-input w-9 text-center text-xs py-0.5 px-0 rounded border-0 bg-transparent text-gray-200 font-bold font-mono focus:ring-0">
                            
                            <button type="submit" name="action" value="add" class="w-7 h-7 rounded bg-emerald-500/10 hover:bg-emerald-600 text-emerald-400 hover:text-white flex items-center justify-center transition-all font-bold text-xs" title="Auffüllen">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Details List -->
                    <div class="space-y-2 pt-1">
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40">
                            <span class="text-gray-400 font-medium">Lieferant</span>
                            <span class="show-modal-value font-semibold" x-text="selectedItem.lieferant"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40" x-show="selectedItem.artikelgruppe">
                            <span class="text-gray-400 font-medium">Artikelgruppe</span>
                            <span class="show-modal-value font-semibold" x-text="selectedItem.artikelgruppe"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40">
                            <span class="text-gray-400 font-medium">Einheit</span>
                            <span class="show-modal-value font-semibold" x-text="selectedItem.einheit || 'Stück'"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40">
                            <span class="text-gray-400 font-medium">Steuersatz</span>
                            <span class="show-modal-value font-mono font-semibold" x-text="(selectedItem.steuersatz || '19') + '%'"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40">
                            <span class="text-gray-400 font-medium">Einkaufspreis (netto)</span>
                            <span class="show-modal-value font-mono" x-text="parseFloat(selectedItem.ek_ohne_st).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €'"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40">
                            <span class="text-gray-400 font-medium">Verkaufspreis (netto)</span>
                            <span class="show-modal-value font-mono" x-text="parseFloat(selectedItem.vk_ohne_st).toLocaleString('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €'"></span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-gray-850/40">
                            <span class="text-gray-400 font-medium">Alte Artikelnummer</span>
                            <span class="show-modal-value font-mono" x-text="selectedItem.alte_artikelnummer || '-'"></span>
                        </div>
                        <div class="py-1" x-show="selectedItem.kommentar">
                            <span class="text-gray-400 font-medium block mb-1">Lager-Hinweis / Kommentar:</span>
                            <p class="show-modal-comment p-2.5 rounded-lg border border-gray-850 text-xs leading-relaxed" x-text="selectedItem.kommentar"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-between items-center p-4 border-t border-gray-850 bg-gray-950/20">
                <button type="button" 
                        @click="historyItemFilter = selectedItem.id; historyTypeFilter = 'all'; historyPage = 1; showHistoryModal = true; openShowModal = false;" 
                        class="bg-cyan-950/40 text-cyan-400 border border-cyan-500/20 hover:bg-cyan-900/30 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider flex items-center space-x-1.5 transition-all">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Verlauf anzeigen</span>
                </button>
                <button type="button" @click="openShowModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Schließen
                </button>
            </div>
        </div>
    </div>

    <!-- Global QR Zoom Modal -->
    <div x-show="openZoomModal" @keydown.escape.window="openZoomModal = false" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/80" x-cloak>
        <div @click.away="openZoomModal = false" class="glass-panel p-8 rounded-2xl border border-gray-800 text-center max-w-sm w-full space-y-6">
            <h3 class="text-xl font-bold" x-text="selectedItem.bezeichnung"></h3>
            <p class="text-xs text-gray-400">Artikelnummer: <span x-text="selectedItem.neue_artikelnummer"></span></p>
            <div class="bg-white p-4 rounded-xl inline-block">
                <canvas id="global-zoom-qr" width="180" height="180"></canvas>
            </div>
            <div class="flex justify-center space-x-3 pt-2">
                <button @click="openZoomModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
                    Schließen
                </button>
                <button @click="openPrintModal = true; openZoomModal = false" class="btn-neon-cyan text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors">
                    Drucken
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Left- & Right-Click Context Menu -->
    <div id="custom-context-menu" class="context-menu hidden" x-cloak>
        <button type="button" @click="openShowModal = true; document.getElementById('custom-context-menu').classList.add('hidden'); history.pushState(null, '', '{{ route('sck.lager.artikel', '') }}/' + selectedItem.neue_artikelnummer); renderDetailQR();" class="context-menu-item">
            <i class="fa-solid fa-circle-info text-cyan-400"></i>
            <span>Details anzeigen</span>
        </button>
        <button type="button" @click="openEditModal = true; document.getElementById('custom-context-menu').classList.add('hidden')" class="context-menu-item">
            <i class="fa-solid fa-pen text-purple-400"></i>
            <span>Artikel bearbeiten</span>
        </button>
        <button type="button" @click="openPrintModal = true; document.getElementById('custom-context-menu').classList.add('hidden'); setTimeout(() => renderPrintQR(), 100);" class="context-menu-item">
            <i class="fa-solid fa-print text-purple-400"></i>
            <span>Label drucken</span>
        </button>
        <button type="button" @click="openZoomModal = true; document.getElementById('custom-context-menu').classList.add('hidden'); renderGlobalZoomQR();" class="context-menu-item">
            <i class="fa-solid fa-qrcode text-emerald-400"></i>
            <span>QR-Code vergrößern</span>
        </button>
        <form :action="'{{ route('sck.lager.toggle-datev-exported', '') }}/' + selectedItem.id" method="POST" class="block">
            @csrf
            <button type="submit" class="context-menu-item">
                <i class="fa-solid fa-file-shield text-emerald-400"></i>
                <span x-text="selectedItem.datev_exported ? 'Als Bereit markieren' : 'Als Exportiert markieren'"></span>
            </button>
        </form>
        <div class="context-menu-divider"></div>
        <form action="{{ route('sck.lager.update-stock') }}" method="POST" class="block">
            @csrf
            <input type="hidden" name="item_id" :value="selectedItem.id">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" name="action" value="add" class="context-menu-item">
                <i class="fa-solid fa-plus text-emerald-500"></i>
                <span>Bestand +1</span>
            </button>
            <button type="submit" name="action" value="remove" class="context-menu-item">
                <i class="fa-solid fa-minus text-red-500"></i>
                <span>Bestand -1</span>
            </button>
        </form>
    </div>

    <!-- History Modal Overlay -->
    <div x-show="showHistoryModal" 
         class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <div class="glass-panel w-full max-w-4xl rounded-2xl border border-gray-800 bg-gray-900/95 flex flex-col max-h-[85vh] overflow-hidden shadow-2xl text-left"
             @click.away="showHistoryModal = false">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/45">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-300 flex items-center space-x-2">
                    <i class="fa-solid fa-clock-rotate-left text-cyan-400"></i>
                    <span>Dauerhaftes Aktivitätsprotokoll</span>
                </h3>
                <button @click="showHistoryModal = false" class="text-gray-500 hover:text-cyan-400 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Content Filters -->
            <div class="p-4 border-b border-gray-850 bg-gray-950/20 flex flex-col sm:flex-row items-center gap-4">
                <!-- Search -->
                <div class="relative w-full sm:max-w-xs">
                    <input type="text" 
                           x-model="historySearch" 
                           @input="historyPage = 1"
                           placeholder="Protokoll durchsuchen..." 
                           class="sck-input pl-9 pr-4 py-2 text-xs rounded-xl w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-gray-500 text-[10px]"></i>
                </div>

                <!-- Type of change Filter -->
                <div class="w-full sm:w-auto flex items-center space-x-2">
                    <label class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Kategorie:</label>
                    <select x-model="historyTypeFilter" @change="historyPage = 1" class="sck-input text-xs py-1.5 px-3 rounded-xl">
                        <option value="all">Alle</option>
                        <option value="scanner">Scanner</option>
                        <option value="manual">Manuell (Details)</option>
                        <option value="quick">Schnelländerung</option>
                    </select>
                </div>

                <!-- Specific Item Filter Indicator -->
                <div x-show="historyItemFilter !== 'all'" class="flex items-center space-x-2 bg-cyan-950/40 text-cyan-400 border border-cyan-500/25 px-3 py-1.5 rounded-xl text-xs" x-cloak>
                    <i class="fa-solid fa-filter"></i>
                    <span>Gefiltert nach Artikel</span>
                    <button @click="historyItemFilter = 'all'; historyPage = 1" class="text-cyan-300 hover:text-white transition-colors ml-1">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>

                <div class="flex-grow"></div>
                <button @click="if(confirm('Möchtest du wirklich alle Protokolle dauerhaft löschen?')) { clearLogs(); showHistoryModal = false; }" 
                        class="btn-danger-soft text-[9px] bg-red-950/40 text-red-400 border border-red-500/20 hover:bg-red-900/30 px-3.5 py-1.5 rounded-xl transition-all font-bold uppercase tracking-wider">
                    Gesamten Verlauf leeren
                </button>
            </div>

            <!-- Modal Table -->
            <div class="flex-grow overflow-auto p-6">
                <div class="glass-panel rounded-2xl border border-gray-800 overflow-hidden shadow-md">
                    <table class="w-full text-xs text-left border-collapse history-table">
                        <thead>
                            <tr class="text-gray-500 font-bold uppercase tracking-wider text-[10px] text-left">
                                <th class="pb-3 pt-3 px-3 w-36">Zeit</th>
                                <th class="pb-3 pt-3 px-3 w-28">Herkunft</th>
                                <th class="pb-3 pt-3 px-3 w-24">Aktion</th>
                                <th class="pb-3 pt-3 px-3">Nachricht</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(log, idx) in paginatedLogs" :key="idx">
                                <tr :class="log.success ? 'log-item-success border-l-4 border-emerald-500' : 'log-item-error border-l-4 border-red-500'" class="transition-colors duration-75">
                                    <td class="py-3 px-3 font-semibold text-[10px] text-gray-400 font-mono" x-text="log.time"></td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded font-bold text-[8px] uppercase tracking-wider"
                                              :class="log.type === 'scanner' ? 'badge-scanner bg-purple-900/60 text-purple-300 border border-purple-500/20' : (log.type === 'quick' ? 'badge-quick bg-cyan-900/60 text-cyan-300 border border-cyan-500/20' : 'badge-manual bg-gray-800 text-gray-300 border border-gray-700')"
                                              x-text="log.type === 'scanner' ? 'Scanner' : (log.type === 'quick' ? 'Schnell' : 'Manuell')"></span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded font-black uppercase text-[8px] tracking-wider text-white" 
                                              :class="log.action === 'add' ? 'bg-emerald-600' : 'bg-rose-600'"
                                              x-text="log.action === 'add' ? 'Eingebucht' : 'Entnahme'"></span>
                                    </td>
                                    <td class="py-3 px-3 text-[11px] font-sans" x-text="log.message"></td>
                                </tr>
                            </template>
                            
                            <template x-if="filteredLogs.length === 0">
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-500 font-sans">
                                        Keine passenden Protokolleinträge gefunden.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer (Pagination) -->
            <div class="px-6 py-4 border-t border-gray-850 flex flex-col sm:flex-row items-center justify-between bg-gray-950/45 text-xs gap-3">
                <span class="text-gray-500" x-text="`Eintrag ${(historyPage-1)*historyPerPage + 1} - ${Math.min(historyPage*historyPerPage, filteredLogs.length)} von ${filteredLogs.length}`"></span>
                <div class="flex items-center space-x-1">
                    <button @click="if(historyPage > 1) historyPage--" 
                            :disabled="historyPage === 1"
                            class="px-3 py-1.5 rounded-xl border border-gray-800 bg-gray-900/60 text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 disabled:opacity-40 disabled:hover:text-gray-400 disabled:hover:border-gray-800 transition-colors">
                        Zurück
                    </button>
                    <span class="px-3 text-gray-400 font-bold" x-text="`${historyPage} / ${totalPages}`"></span>
                    <button @click="if(historyPage < totalPages) historyPage++" 
                            :disabled="historyPage === totalPages"
                            class="px-3 py-1.5 rounded-xl border border-gray-800 bg-gray-900/60 text-gray-400 hover:text-cyan-400 hover:border-cyan-500/30 disabled:opacity-40 disabled:hover:text-gray-400 disabled:hover:border-gray-800 transition-colors">
                        Weiter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- DATEV Invoice Upload & Verification Modal -->
    <div x-show="openInvoiceModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="openInvoiceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-950/85 backdrop-blur-md" @click="openInvoiceModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="openInvoiceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-6xl p-6 sm:p-8 my-8 overflow-hidden text-left align-middle transition-all transform glass-panel border border-slate-200 dark:border-gray-800/90 rounded-3xl shadow-2xl bg-white dark:bg-gray-900/95 text-slate-800 dark:text-gray-100">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-6 border-b border-slate-200 dark:border-gray-800/80">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shadow-lg shadow-emerald-500/10">
                            <i class="fa-solid fa-file-invoice text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 dark:text-gray-100 tracking-tight">DATEV Rechnung / Beleg verbuchen</h3>
                            <p class="text-xs text-slate-500 dark:text-gray-400 mt-0.5">Automatische Artikel- und Bestandsanalyse mit Prüfung & Abgleich</p>
                        </div>
                    </div>
                    <button @click="openInvoiceModal = false" class="text-slate-400 hover:text-slate-600 dark:text-gray-400 dark:hover:text-gray-200 transition-colors p-2.5 rounded-xl hover:bg-slate-100 dark:hover:bg-gray-800/60">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Error Alert -->
                <div x-show="invoiceError" class="mt-4 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-sm flex items-start space-x-3" x-cloak>
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 dark:text-rose-400 text-lg mt-0.5"></i>
                    <div class="flex-1" x-text="invoiceError"></div>
                </div>

                <!-- STEP 1: Upload Step -->
                <div x-show="invoiceStep === 'upload'" class="py-12 text-center space-y-6">
                    <div class="max-w-lg mx-auto p-10 border-2 border-dashed border-slate-300 dark:border-gray-700/80 hover:border-emerald-500/60 rounded-3xl bg-slate-50/50 dark:bg-gray-950/50 transition-all shadow-inner">
                        <i class="fa-solid fa-file-pdf text-6xl text-emerald-500 dark:text-emerald-400 mb-4 animate-bounce"></i>
                        <h4 class="text-xl font-bold text-slate-900 dark:text-gray-100">DATEV-Rechnung (PDF) auswählen</h4>
                        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1 mb-6">Ziehen Sie die PDF-Datei hierher oder klicken Sie zum Auswählen</p>
                        
                        <label class="btn-neon-emerald cursor-pointer text-white px-6 py-3 rounded-2xl text-sm font-black inline-flex items-center space-x-2.5 transition-all shadow-lg shadow-emerald-600/20 hover:scale-105">
                            <i class="fa-solid fa-upload"></i>
                            <span>PDF auswählen & analysieren</span>
                            <input type="file" accept=".pdf" class="hidden" @change="uploadAndParseInvoice($event)">
                        </label>
                    </div>

                    <div class="text-xs text-slate-600 dark:text-gray-400 max-w-xl mx-auto bg-slate-50 dark:bg-gray-950/40 p-5 rounded-2xl border border-slate-200 dark:border-gray-800/80 text-left space-y-2">
                        <div class="font-bold text-slate-800 dark:text-gray-300 flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-shield-halved text-cyan-600 dark:text-cyan-400"></i>
                            <span>Funktionsweise des automatischen Abgleichs</span>
                        </div>
                        <p class="text-slate-600 dark:text-gray-400 leading-relaxed text-xs">
                            Das System liest Artikelbezeichnung, Artikelnummer, Mengeneinheiten und Nettopreise aus. Korrekte Treffer werden grün verifiziert. Ähnliche Treffer werden gelb markiert und nicht gefundene Artikel können direkt mit vorausgefüllten Werten angelegt werden.
                        </p>
                    </div>
                </div>

                <!-- STEP 2: Loading Step -->
                <div x-show="invoiceStep === 'loading'" class="py-20 text-center space-y-4">
                    <div class="inline-block animate-spin w-14 h-14 border-4 border-emerald-500 border-t-transparent rounded-full mb-3 shadow-lg shadow-emerald-500/20"></div>
                    <h4 class="text-xl font-bold text-slate-900 dark:text-gray-100">DATEV-Rechnung wird analysiert...</h4>
                    <p class="text-xs font-mono text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-4 py-1.5 rounded-full inline-block border border-emerald-200 dark:border-emerald-800/50" x-text="invoiceFileName"></p>
                </div>

                <!-- STEP 3: Review & Submit Step -->
                <div x-show="invoiceStep === 'review' && invoiceData" class="py-6 space-y-6">
                    
                    <!-- Invoice Summary Metadata Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <!-- Card 1: Rechnungs-Nr -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gray-950/60 border border-slate-200 dark:border-gray-800/80 flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm font-bold shadow-inner">
                                <i class="fa-solid fa-hashtag"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-gray-400 uppercase font-black tracking-wider block">Rechnungs-Nr.</span>
                                <p class="font-black text-emerald-600 dark:text-emerald-400 text-sm" x-text="invoiceData?.invoice_info?.number || 'Ohne Nummer'"></p>
                            </div>
                        </div>

                        <!-- Card 2: Belegdatum -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gray-950/60 border border-slate-200 dark:border-gray-800/80 flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-sm font-bold shadow-inner">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-gray-400 uppercase font-black tracking-wider block">Belegdatum</span>
                                <p class="font-bold text-slate-800 dark:text-gray-200 text-sm" x-text="invoiceData?.invoice_info?.date || '-'"></p>
                            </div>
                        </div>

                        <!-- Card 3: Kundennummer -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gray-950/60 border border-slate-200 dark:border-gray-800/80 flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm font-bold shadow-inner">
                                <i class="fa-solid fa-user-tag"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500 dark:text-gray-400 uppercase font-black tracking-wider block">Kundennummer</span>
                                <p class="font-bold text-slate-800 dark:text-gray-200 text-sm" x-text="invoiceData?.invoice_info?.customer_number || '-'"></p>
                            </div>
                        </div>

                        <!-- Card 4: Status / Debug Toggle -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-gray-950/60 border border-slate-200 dark:border-gray-800/80 flex items-center justify-between gap-2 min-w-0">
                            <div class="shrink-0">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold border whitespace-nowrap inline-block" :class="(invoiceData?.items?.length || 0) > 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30'" x-text="(invoiceData?.items?.length || 0) + ' Artikel erkannt'"></span>
                            </div>
                            <button type="button" @click="showInvoiceDebug = !showInvoiceDebug" title="Debug-Log & Rohtext anzeigen" :class="showInvoiceDebug ? 'bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border-cyan-500/50' : 'bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border-slate-300 dark:border-gray-700'" class="px-2.5 py-1.5 rounded-xl text-xs font-bold border flex items-center space-x-1.5 transition-all shadow-sm shrink-0">
                                <i class="fa-solid fa-bug text-cyan-600 dark:text-cyan-400 text-sm"></i>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-white dark:bg-gray-950 text-cyan-800 dark:text-cyan-300 font-mono border border-cyan-300 dark:border-cyan-900" x-text="invoiceData?.debug?.logs?.length || 0"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 0 Items Warning Banner -->
                    <template x-if="!invoiceData?.items || invoiceData?.items?.length === 0">
                        <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-sm space-y-2">
                            <div class="flex items-center space-x-2 font-bold text-base">
                                <i class="fa-solid fa-triangle-exclamation text-amber-500 dark:text-amber-400"></i>
                                <span>Keine Artikel automatisch aus der PDF erkannt</span>
                            </div>
                            <p class="text-xs text-amber-800 dark:text-amber-200/80">
                                Es konnten keine passenden Zeilenartikel extrahiert werden. Sie können den extrahierten Rohtext und die Log-Meldungen unten prüfen, um das PDF-Format einzusehen.
                            </p>
                            <button type="button" @click="showInvoiceDebug = true" class="px-4 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-bold border border-amber-500/40 inline-flex items-center space-x-1.5 transition-colors mt-1">
                                <i class="fa-solid fa-bug"></i>
                                <span>Debug-Log jetzt anzeigen</span>
                            </button>
                        </div>
                    </template>

                    <!-- Debug Logs & Raw Text Section -->
                    <div x-show="showInvoiceDebug" x-transition class="border border-cyan-500/30 rounded-2xl p-5 bg-slate-900 dark:bg-gray-950/90 text-white space-y-4 shadow-inner" x-cloak>
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <div class="flex items-center space-x-2 text-cyan-400 font-bold text-sm">
                                <i class="fa-solid fa-terminal"></i>
                                <span>PDF Parser Debug & Rohtext Analyse</span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="invoiceDebugTab = 'logs'" :class="invoiceDebugTab === 'logs' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50' : 'bg-gray-800 text-gray-400 border-transparent'" class="px-3 py-1 rounded-lg text-xs font-bold border transition-colors">
                                    <i class="fa-solid fa-list-check mr-1"></i> Parser Logs (<span x-text="invoiceData?.debug?.logs?.length || 0"></span>)
                                </button>
                                <button type="button" @click="invoiceDebugTab = 'rawtext'" :class="invoiceDebugTab === 'rawtext' ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50' : 'bg-gray-800 text-gray-400 border-transparent'" class="px-3 py-1 rounded-lg text-xs font-bold border transition-colors">
                                    <i class="fa-solid fa-file-lines mr-1"></i> Extrahierter Rohtext (<span x-text="invoiceData?.debug?.line_count || 0"></span> Zeilen)
                                </button>
                            </div>
                        </div>

                        <!-- Tab 1: Debug Logs -->
                        <div x-show="invoiceDebugTab === 'logs'" class="space-y-1.5 max-h-64 overflow-y-auto font-mono text-xs pr-1">
                            <template x-for="(logLine, lIdx) in (invoiceData?.debug?.logs || [])" :key="lIdx">
                                <div class="p-2.5 rounded-xl bg-gray-900 border border-gray-800/80 text-gray-300 flex items-start space-x-2.5">
                                    <span class="text-cyan-500 select-none font-bold min-w-[28px]" x-text="'[' + (lIdx + 1) + ']'"></span>
                                    <span class="break-all" :class="{
                                        'text-emerald-400 font-bold': logLine.includes('SUCCESS') || logLine.includes('MATCH'),
                                        'text-cyan-300': logLine.includes('STRATEGY') || logLine.includes('PARSER') || logLine.includes('TIER'),
                                        'text-amber-400 font-bold': logLine.includes('FAILED') || logLine.includes('Not found') || logLine.includes('not_found'),
                                        'text-gray-300': !logLine.includes('SUCCESS') && !logLine.includes('STRATEGY') && !logLine.includes('FAILED')
                                    }" x-text="logLine"></span>
                                </div>
                            </template>
                            <template x-if="!invoiceData?.debug?.logs || invoiceData?.debug?.logs?.length === 0">
                                <div class="text-gray-500 italic text-center py-4">Keine Log-Einträge vorhanden.</div>
                            </template>
                        </div>

                        <!-- Tab 2: Raw Extracted PDF Text -->
                        <div x-show="invoiceDebugTab === 'rawtext'" class="space-y-2">
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>Aus der PDF ausgelesener Text (Multi-Tier Parser Output):</span>
                                <button type="button" @click="navigator.clipboard.writeText(invoiceData?.debug?.raw_text || '')" class="text-cyan-400 hover:text-cyan-300 font-bold flex items-center space-x-1 transition-colors">
                                    <i class="fa-solid fa-copy"></i>
                                    <span>Rohtext kopieren</span>
                                </button>
                            </div>
                            <pre class="bg-gray-900 border border-gray-800 p-4 rounded-2xl text-emerald-400 font-mono text-xs max-h-64 overflow-y-auto whitespace-pre-wrap break-all select-all leading-relaxed" x-text="invoiceData?.debug?.raw_text || 'Kein Rohtext extrahiert.'"></pre>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <div class="overflow-x-auto border border-slate-200 dark:border-gray-800/90 rounded-3xl max-h-[28rem] overflow-y-auto shadow-inner bg-slate-50/40 dark:bg-gray-950/40">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 dark:bg-gray-950 text-slate-700 dark:text-gray-400 sticky top-0 border-b border-slate-200 dark:border-gray-800 uppercase font-black tracking-wider z-10">
                                <tr>
                                    <th class="py-4 px-5 w-5/12">Artikel (Rechnung & Lager)</th>
                                    <th class="py-4 px-5 text-center w-3/12">Status / Zuordnung</th>
                                    <th class="py-4 px-4 text-center w-2/12">Menge</th>
                                    <th class="py-4 px-5 text-right w-2/12">Preis-Check (Netto)</th>
                                    <th class="py-4 px-5 text-center w-2/12">Bestand-Vorschau</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-gray-800/60 bg-white dark:bg-gray-900/50">
                                <template x-for="(item, idx) in (invoiceData?.items || [])" :key="idx">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/50 transition-colors">
                                        
                                        <!-- Article Info -->
                                        <td class="py-4 px-5">
                                            <div class="font-bold text-slate-900 dark:text-gray-100 text-sm leading-snug" x-text="item.invoice_bezeichnung"></div>
                                            <div class="text-[11px] text-slate-500 dark:text-gray-400 flex items-center space-x-2.5 mt-1">
                                                <span class="bg-slate-100 dark:bg-gray-950 px-2 py-0.5 rounded-md border border-slate-200 dark:border-gray-800 font-mono text-slate-700 dark:text-gray-300">Art.-Nr.: <strong class="text-emerald-600 dark:text-emerald-400" x-text="item.invoice_artikelnummer || 'Keine'"></strong></span>
                                                <span class="bg-slate-100 dark:bg-gray-950 px-2 py-0.5 rounded-md border border-slate-200 dark:border-gray-800 font-mono text-slate-700 dark:text-gray-400">USt: <strong class="text-slate-800 dark:text-gray-300" x-text="item.invoice_ust + '%' "></strong></span>
                                            </div>
                                        </td>

                                        <!-- Status & Verification Badge -->
                                        <td class="py-4 px-5 text-center">
                                            
                                            <!-- Exact Match -->
                                            <template x-if="item.status === 'exact_match'">
                                                <div class="inline-flex items-center space-x-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold shadow-sm">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    <span>Im Lager verifiziert</span>
                                                </div>
                                            </template>

                                            <!-- Fuzzy Match -->
                                            <template x-if="item.status === 'fuzzy_match'">
                                                <div class="flex flex-col items-center space-y-1.5">
                                                    <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-xl bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/30 text-xs font-bold">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        <span x-text="'Ähnlicher Treffer (' + item.similarity_percent + '%)'"></span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-500 dark:text-gray-400 font-medium" x-text="'Zugewiesen: ' + (item.matched_item?.bezeichnung || '')"></div>
                                                    <button type="button" @click="prefillAddModalFromInvoiceItem(item)" class="text-[11px] text-cyan-600 dark:text-cyan-400 hover:underline font-bold flex items-center space-x-1">
                                                        <i class="fa-solid fa-circle-plus"></i>
                                                        <span>Falscher Treffer? Als neu anlegen</span>
                                                    </button>
                                                </div>
                                            </template>

                                            <!-- Not Found -->
                                            <template x-if="item.status === 'not_found'">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <span class="px-3 py-1 rounded-xl bg-rose-500/10 text-rose-700 dark:text-red-400 border border-rose-500/30 text-xs font-bold flex items-center space-x-1">
                                                        <i class="fa-solid fa-xmark"></i>
                                                        <span>Nicht gefunden</span>
                                                    </span>
                                                    <button type="button" @click="prefillAddModalFromInvoiceItem(item)" class="bg-cyan-600 hover:bg-cyan-500 dark:from-cyan-600 dark:to-blue-600 text-white px-3 py-1 rounded-xl text-xs font-bold flex items-center space-x-1.5 transition-all shadow-md shadow-cyan-600/20 hover:scale-105">
                                                        <i class="fa-solid fa-plus-circle"></i>
                                                        <span>Artikel anlegen</span>
                                                    </button>
                                                </div>
                                            </template>
                                        </td>

                                        <!-- Quantity Input -->
                                        <td class="py-4 px-4 text-center">
                                            <div class="inline-flex items-center justify-center space-x-1.5">
                                                <input type="number" min="1" x-model.number="item.quantity" class="w-20 sck-input text-center py-1.5 text-xs font-black rounded-xl border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-slate-900 dark:text-gray-100">
                                                <span class="text-slate-500 dark:text-gray-400 text-xs font-medium" x-text="item.invoice_einheit"></span>
                                            </div>
                                        </td>

                                        <!-- Price Check & Option -->
                                        <td class="py-4 px-5 text-right">
                                            <div class="font-black text-slate-900 dark:text-gray-100 text-sm" x-text="item.invoice_netto_preis.toFixed(2) + ' €'"></div>
                                            
                                            <template x-if="item.matched_item && item.price_match">
                                                <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-end space-x-1 mt-1">
                                                    <i class="fa-solid fa-check text-[10px]"></i>
                                                    <span>EK identisch</span>
                                                </div>
                                            </template>

                                            <template x-if="item.matched_item && !item.price_match">
                                                <div class="mt-1 space-y-1">
                                                    <div class="text-[10px] text-amber-600 dark:text-amber-400 font-bold" x-text="'Lager EK: ' + item.matched_item.ek_ohne_st.toFixed(2) + ' €'"></div>
                                                    <label class="inline-flex items-center space-x-1 cursor-pointer text-[10px] text-cyan-600 dark:text-cyan-300 hover:text-cyan-500">
                                                        <input type="checkbox" x-model="item.update_price" class="rounded border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-950 text-cyan-500 text-[10px]">
                                                        <span>Lager EK auf <strong x-text="item.invoice_netto_preis.toFixed(2) + ' €'"></strong> anpassen</span>
                                                    </label>
                                                </div>
                                            </template>
                                        </td>

                                        <!-- Stock Projection -->
                                        <td class="py-4 px-5 text-center">
                                            <template x-if="item.matched_item">
                                                <div>
                                                    <div class="text-xs font-bold text-slate-800 dark:text-gray-200 font-mono bg-slate-100 dark:bg-gray-950 px-2.5 py-1 rounded-xl border border-slate-200 dark:border-gray-800 inline-block" x-text="item.matched_item.stueckzahl + ' → ' + Math.max(0, item.matched_item.stueckzahl - item.quantity) + ' Stk.'"></div>
                                                    <template x-if="(item.matched_item.stueckzahl - item.quantity) < 0">
                                                        <span class="text-[9px] font-black text-rose-500 dark:text-rose-400 uppercase tracking-tight block mt-1">Bestand negativ</span>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!item.matched_item">
                                                <span class="text-slate-400 dark:text-gray-500 text-xs font-mono">-</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Modal Actions Bar -->
                    <div class="flex items-center justify-between pt-6 border-t border-slate-200 dark:border-gray-800/80">
                        <button type="button" @click="invoiceStep = 'upload'" class="bg-slate-200 hover:bg-slate-300 text-slate-800 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all border border-slate-300 dark:border-gray-700/50 flex items-center space-x-2">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Andere Datei wählen</span>
                        </button>

                        <div class="flex items-center space-x-3">
                            <button type="button" @click="openInvoiceModal = false" class="bg-slate-200 hover:bg-slate-300 text-slate-800 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 dark:text-gray-300 px-5 py-2.5 rounded-2xl text-xs font-bold transition-all border border-slate-300 dark:border-gray-700/50">
                                Abbrechen
                            </button>

                            <button type="button" @click="submitInvoiceDeduction()" :disabled="isSubmittingInvoiceDeduction || !invoiceData?.items?.some(i => i.selected_item_id)" class="bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white px-7 py-3 rounded-2xl text-sm font-black flex items-center space-x-2.5 transition-colors shadow-lg shadow-emerald-600/20 disabled:bg-slate-300 dark:disabled:bg-slate-800 disabled:text-slate-500 dark:disabled:text-gray-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                                <template x-if="!isSubmittingInvoiceDeduction">
                                    <span class="flex items-center space-x-2">
                                        <i class="fa-solid fa-check-circle text-base"></i>
                                        <span>Bestand jetzt automatisch abziehen & buchen</span>
                                    </span>
                                </template>
                                <template x-if="isSubmittingInvoiceDeduction">
                                    <span class="flex items-center space-x-2">
                                        <i class="fa-solid fa-spinner animate-spin text-base"></i>
                                        <span>Bestand wird abgezogen...</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    
</div>
@endsection
