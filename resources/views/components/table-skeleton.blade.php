{{-- resources/views/components/table-skeleton.blade.php --}}
<template x-for="i in rows || 5" :key="i">
    <tr>
        <template x-for="j in cols || 5" :key="j">
            <td class="px-4 py-4">
                <div class="skeleton h-4 rounded" :class="'w-' + (['full','3/4','2/3','1/2','5/6'][(i+j)%5])"></div>
            </td>
        </template>
    </tr>
</template>
