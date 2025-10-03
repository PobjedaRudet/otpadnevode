<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-6 py-4 text-sm text-gray-900 border-b border-gray-100">
        {{ $record->datum->format('d.m.Y') }}
    </td>
    <td class="px-6 py-4 text-sm text-gray-900 border-b border-gray-100">
        {{ $record->vrijeme->format('H:i:s') }}
    </td>
    <td class="px-6 py-4 text-sm font-medium text-gray-900 border-b border-gray-100">
        {{ number_format($record->vrijednost, 2) }}
    </td>
</tr>
