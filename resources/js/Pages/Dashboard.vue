<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_dipinjam: 0,
            sudah_dikembalikan: 0,
            belum_dikembalikan: 0,
            total_stock: 0,
            stock_menipis: 0,
        }),
    },
    peminjaman_terbaru: {
        type: Array,
        default: () => [],
    },
    top_peminjam: {
        type: Array,
        default: () => [],
    },
    permintaan_terbaru: {
        type: Array,
        default: () => [],
    },
})

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
    })
}
</script>

<template>
    <AppLayout>
        <template #title>Dashboard</template>

        <!-- Stat Cards Asset -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-zinc-200 p-5 border-l-4 border-zinc-200">
                <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Total Dipinjam</p>
                <p class="text-3xl font-semibold text-zinc-900">{{ stats.total_dipinjam }}</p>
                <p class="text-xs text-zinc-400 mt-1">Jumlah Assset Yang Sedang Di Pinjam</p>
            </div>
            <div class="bg-white rounded-xl border border-zinc-200 p-5 border-l-4 border-zinc-200">
                <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Sudah Dikembalikan</p>
                <p class="text-3xl font-semibold text-zinc-900">{{ stats.sudah_dikembalikan }}</p>
                <p class="text-xs text-zinc-400 mt-1">Jumlah Asset Yang Sudah Dikembalikan</p>
            </div>
            <div class="bg-white rounded-xl border border-zinc-200 p-5 border-l-4 border-zinc-200">
                <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Belum Dikembalikan</p>
                <p class="text-3xl font-semibold text-zinc-900">{{ stats.belum_dikembalikan }}</p>
                <p class="text-xs text-zinc-400 mt-1">Jumlah Asset Yang Belum Dikembalikan</p>
            </div>
        </div>

        <!-- Stat Cards Stock -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-zinc-200 p-5 border-l-4 border-zinc-200">
                <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Total Asset</p>
                <p class="text-3xl font-semibold text-zinc-900">{{ stats.total_stock }}</p>
                <p class="text-xs text-zinc-400 mt-1">Total Asset Besar & Asset Kecil</p>
            </div>
            <div class="bg-white rounded-xl border border-zinc-200 p-5 border-l-4 border-zinc-200">
                <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wide mb-2">Stock Menipis</p>
                <p class="text-3xl font-semibold text-zinc-900">{{ stats.stock_menipis }}</p>
                <p class="text-xs text-zinc-400 mt-1">barang dengan stok &lt; 10</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            <!-- Tabel Peminjaman Terbaru -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-zinc-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100">
                    <p class="text-sm font-semibold text-zinc-900">Peminjaman Terbaru</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-zinc-50">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Barang</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Tgl Selesai</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="peminjaman_terbaru.length === 0">
                                <td colspan="4" class="px-5 py-8 text-center text-zinc-400 text-xs">
                                    Belum ada data peminjaman
                                </td>
                            </tr>
                            <tr
                                v-for="item in peminjaman_terbaru"
                                :key="item.id"
                                class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                            >
                                <td class="px-5 py-3 font-medium text-zinc-900">{{ item.employee?.nama }}</td>
                                <td class="px-5 py-3 text-zinc-600">{{ item.large_asset?.nama_barang ?? item.nama_barang }}</td>
                                <td class="px-5 py-3 text-zinc-400 text-xs">{{ formatDate(item.tanggal_selesai) }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700': item.status === 'dikembalikan',
                                            'bg-blue-50 text-blue-700': item.status === 'dipinjam',
                                        }"
                                    >
                                        {{ item.status === 'dikembalikan' ? 'Dikembalikan' : 'Dipinjam' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Peminjam -->
            <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100">
                    <p class="text-sm font-semibold text-zinc-900">Top Peminjam</p>
                </div>
                <div class="p-4">
                    <div v-if="top_peminjam.length === 0" class="text-center text-zinc-400 text-xs py-8">
                        Belum ada data
                    </div>
                    <div
                        v-for="(item, index) in top_peminjam"
                        :key="index"
                        class="flex items-center gap-3 py-2.5 border-b border-zinc-50 last:border-0"
                    >
                        <div class="w-7 h-7 rounded-full bg-[#1A1A1A] text-[#F5C518] text-xs font-semibold flex items-center justify-center flex-shrink-0">
                            {{ index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 truncate">{{ item.employee?.nama }}</p>
                            <p class="text-xs text-zinc-400">{{ item.total }} peminjaman</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>