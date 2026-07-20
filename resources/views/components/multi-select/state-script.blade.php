{{-- Shared Alpine state for SCK mass selection. --}}
@once
    <script>
        window.massSelectionState = (config = {}) => ({
            selectedIds: (config.selected || []).map(String),
            pageItemIds: (config.page || []).map(String),
            matchingItemIds: (config.all || []).map(String),
            panelNeedsSpace: false,
            initMassSelection() {
                this.updatePanelLayout();
                this.$watch('selectedIds', () => this.updatePanelLayout());
                window.addEventListener('resize', () => this.updatePanelLayout());
            },
            updatePanelLayout() {
                this.panelNeedsSpace = this.selectedIds.length > 0 && window.innerWidth < 1700;
            },
            toggleSelection(id) {
                id = String(id);
                this.selectedIds = this.selectedIds.includes(id)
                    ? this.selectedIds.filter(selectedId => selectedId !== id)
                    : [...this.selectedIds, id];
                this.persistSelection();
            },
            togglePageSelection() {
                const pageSelected = this.pageItemIds.length > 0 && this.pageItemIds.every(id => this.selectedIds.includes(id));
                this.selectedIds = pageSelected
                    ? this.selectedIds.filter(id => !this.pageItemIds.includes(id))
                    : [...new Set([...this.selectedIds, ...this.pageItemIds])];
                this.persistSelection();
            },
            selectAllMatching() {
                this.selectedIds = [...this.matchingItemIds];
                this.persistSelection();
            },
            clearSelection() {
                this.selectedIds = [];
                this.persistSelection();
            },
            persistSelection() {
                if (!config.selectionUrl) return;
                fetch(config.selectionUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrf || '{{ csrf_token() }}'},
                    body: JSON.stringify({ids: this.selectedIds}),
                }).catch(() => {});
            },
            submitBulk(action, values = {}) {
                if (!this.selectedIds.length) return;
                if (action === 'delete' && !confirm(`Möchten Sie ${this.selectedIds.length} markierte Artikel wirklich löschen?`)) return;
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = (config.actions && config.actions[action]) || action;
                
                const field = (name, value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                };
                
                field('_token', config.csrf || '{{ csrf_token() }}');
                if (action === 'delete') field('_method', 'DELETE');
                this.selectedIds.forEach(id => field('ids[]', id));
                Object.entries(values).forEach(([name, value]) => field(name, value));
                
                document.body.appendChild(form);
                form.submit();
            },
        });
    </script>
@endonce
