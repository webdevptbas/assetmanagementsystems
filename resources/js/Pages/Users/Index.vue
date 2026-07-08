<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    users: Object,
    roles: { type: Array, default: () => [] },
    filters: Object,
})

const search = ref(props.filters?.search ?? '')

watch(search, (val) => {
    router.get(route('users.index'), { search: val }, {
        preserveState: true,
        replace: true,
    })
})

const showModal = ref(false)
const isEdit = ref(false)
const selectedUser = ref(null)
const showDeleteModal = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
})

const roleLabel = (role) => {
    const map = { admin: 'Admin', hrd: 'HRD', manager: 'Manager' }
    return map[role] ?? role
}

const roleBadgeClass = (role) => {
    const map = {
        admin:   'bg-[#1A1A1A] text-[#F5C518]',
        hrd:     'bg-blue-50 text-blue-700',
        manager: 'bg-blue-50 text-blue-700',
    }
    return map[role] ?? 'bg-zinc-100 text-zinc-500'
}

const openCreate = () => {
    isEdit.value = false
    form.reset()
    showPassword.value = false
    showConfirmPassword.value = false
    showModal.value = true
}

const openEdit = (user) => {
    isEdit.value = true
    selectedUser.value = user
    form.name     = user.name
    form.email    = user.email
    form.password = ''
    form.password_confirmation = ''
    form.role     = user.roles?.[0]?.name ?? ''
    showPassword.value = false
    showConfirmPassword.value = false
    showModal.value = true
}

const openDelete = (user) => {
    selectedUser.value = user
    showDeleteModal.value = true
}

const submit = () => {
    if (isEdit.value) {
        form.put(route('users.update', selectedUser.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('users.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const confirmDelete = () => {
    router.delete(route('users.destroy', selectedUser.value.id), {
        onSuccess: () => { showDeleteModal.value = false },
    })
}
</script>

<template>
    <AppLayout>
        <template #title>Manajemen Akun</template>

        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <input
                v-model="search"
                type="text"
                placeholder="Cari nama atau email..."
                class="bg-white border border-zinc-200 rounded-lg px-4 py-2 text-sm text-zinc-700 focus:outline-none focus:border-zinc-400 w-64"
            />
            <button
                @click="openCreate"
                class="bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] text-sm font-semibold px-4 py-2 rounded-lg transition-colors"
            >
                + Tambah Akun
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl border border-zinc-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Nama</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Email</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Hak Akses</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-zinc-400 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="users.data.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-zinc-400 text-xs">Belum ada data akun</td>
                    </tr>
                    <tr
                        v-for="(user, index) in users.data"
                        :key="user.id"
                        class="border-t border-zinc-100 hover:bg-zinc-50 transition-colors"
                    >
                        <td class="px-5 py-3 text-zinc-400 text-xs">
                            {{ (users.current_page - 1) * users.per_page + index + 1 }}
                        </td>
                        <td class="px-5 py-3 text-zinc-600">{{ user.name }}</td>
                        <td class="px-5 py-3 text-zinc-500">{{ user.email }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-semibold"
                                :class="roleBadgeClass(user.roles?.[0]?.name)"
                            >
                                {{ roleLabel(user.roles?.[0]?.name) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <button @click="openEdit(user)" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Edit</button>
                                <span class="text-zinc-300">|</span>
                                <button @click="openDelete(user)" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-5 py-3 border-t border-zinc-100 flex items-center justify-between">
                <p class="text-xs text-zinc-400">
                    Menampilkan {{ users.from ?? 0 }}–{{ users.to ?? 0 }} dari {{ users.total }} data
                </p>
                <div class="flex gap-1">
                    <template v-for="link in users.links" :key="link.label">
                        <button
                            v-html="link.label"
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url)"
                            class="px-3 py-1 text-xs rounded-md border transition-colors"
                            :class="link.active
                                ? 'bg-[#1A1A1A] text-[#F5C518] border-[#1A1A1A]'
                                : 'bg-white text-zinc-500 border-zinc-200 hover:bg-zinc-50 disabled:opacity-40 disabled:cursor-not-allowed'"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-md mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-5">
                    {{ isEdit ? 'Edit Akun' : 'Tambah Akun' }}
                </h2>
                <div class="space-y-4">

                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Nama</label>
                        <input v-model="form.name" type="text" placeholder="Nama lengkap"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Email</label>
                        <input v-model="form.email" type="email" placeholder="email@example.com"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400" />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Hak Akses -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Hak Akses</label>
                        <select v-model="form.role"
                            class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400">
                            <option value="" disabled>Pilih role...</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">
                                {{ roleLabel(role.name) }}
                            </option>
                        </select>
                        <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">
                            Password <span v-if="isEdit" class="text-zinc-300">(kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400 pr-10"
                            />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                <svg v-if="showPassword" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                                <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1.5">Konfirmasi Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                class="w-full border border-zinc-200 rounded-lg px-3 py-2.5 text-sm text-zinc-800 focus:outline-none focus:border-zinc-400 pr-10"
                            />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                                <svg v-if="showConfirmPassword" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                                <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button @click="showModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="submit" :disabled="form.processing"
                        class="px-4 py-2 text-sm font-semibold bg-[#F5C518] hover:bg-[#e6b800] text-[#1A1A1A] rounded-lg disabled:opacity-60">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl border border-zinc-200 w-full max-w-sm mx-4 p-6">
                <h2 class="text-base font-semibold text-zinc-900 mb-2">Hapus Akun</h2>
                <p class="text-sm text-zinc-500 mb-6">
                    Yakin ingin menghapus akun <span class="font-medium text-zinc-800">{{ selectedUser?.name }}</span>?
                </p>
                <div class="flex justify-end gap-2">
                    <button @click="showDeleteModal = false" class="px-4 py-2 text-sm text-zinc-600 border border-zinc-200 rounded-lg hover:bg-zinc-50">Batal</button>
                    <button @click="confirmDelete" class="px-4 py-2 text-sm font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg">Hapus</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>