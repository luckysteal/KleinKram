@props(['id'])

<td @click.stop="toggleSelection('{{ $id }}')" class="table-cell !w-12 !px-3">
    <div class="flex items-center justify-center">
        <input type="checkbox" @click.stop @change="toggleSelection('{{ $id }}')" :checked="selectedIds.includes('{{ $id }}')"
            class="selection-indicator h-4 w-4 cursor-pointer rounded border-gray-400 dark:border-gray-700 bg-white dark:bg-gray-900 text-cyan-500 transition-all focus:ring-2 focus:ring-cyan-500/30">
    </div>
</td>
