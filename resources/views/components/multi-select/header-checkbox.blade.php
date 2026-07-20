<input type="checkbox" @click.stop @change="togglePageSelection()"
    :checked="pageItemIds.length > 0 && pageItemIds.every(id => selectedIds.includes(id))"
    x-effect="$el.indeterminate = pageItemIds.some(id => selectedIds.includes(id)) && !pageItemIds.every(id => selectedIds.includes(id))"
    class="selection-indicator h-4 w-4 cursor-pointer rounded border-gray-400 dark:border-gray-700 bg-white dark:bg-gray-900 text-cyan-500 focus:ring-2 focus:ring-cyan-500/30"
    title="Alle Artikel auf dieser Seite auswählen">
