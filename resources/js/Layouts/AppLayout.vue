<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.roles?.[0]?.name ?? "admin");

const initials = computed(() => {
    if (!user.value?.name) return "U";
    return user.value.name
        .split(" ")
        .map((n) => n[0])
        .slice(0, 2)
        .join("")
        .toUpperCase();
});

const allMenus = [
    {
        section: "Utama",
        items: [
            {
                label: "Dashboard",
                route: "dashboard",
                roles: ["admin", "hrd", "manager"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>`,
            },
        ],
    },
    {
        section: "Peminjaman Asset",
        roles: ["admin", "hrd", "manager"],
        items: [
            {
                label: "Peminjaman Asset Besar",
                route: "asset-loans.index",
                roles: ["admin", "hrd", "manager"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
                    <path d="M16 3H8l-2 4h12l-2-4z"/>
                </svg>`,
            },
            {
                label: "Permintaan Asset Kecil",
                route: "item-requests.index",
                roles: ["admin", "hrd"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>`,
            },
        ],
    },
    {
        section: "Stock Opname",
        roles: ["admin", "hrd"],
        items: [
            {
                label: "Inventaris Barang",
                route: "inventory.index",
                roles: ["admin", "hrd"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>`,
            },
        ],
    },
    {
        section: "Master Data",
        roles: ["admin", "hrd"],
        items: [
            {
                label: "Data Karyawan",
                route: "employees.index",
                roles: ["admin", "hrd"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>`,
            },
            {
                label: 'Transfer Karyawan',
                route: 'employee-transfers.index',
                roles: ['admin', 'hrd'],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 3l4 4-4 4"/>
                    <path d="M3 7h18"/>
                    <path d="M7 21l-4-4 4-4"/>
                    <path d="M21 17H3"/>
                </svg>`,
            },
            {
                label: "Manajemen Akun",
                route: "users.index",
                roles: ["admin"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M20 21a8 8 0 1 0-16 0"/>
                    <circle cx="19" cy="8" r="3"/>
                    <line x1="19" y1="5" x2="19" y2="11"/>
                    <line x1="16" y1="8" x2="22" y2="8"/>
                </svg>`,
            },
            {
                label: "Departemen",
                route: "departments.index",
                roles: ["admin", "hrd"],
                icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="6" height="14" rx="1"/>
                    <rect x="9" y="3" width="6" height="18" rx="1"/>
                    <rect x="16" y="10" width="6" height="11" rx="1"/>
                </svg>`,
            },
        ],
    },
];

const filteredMenus = computed(() =>
    allMenus
        .map((section) => ({
            ...section,
            items: section.items.filter((item) =>
                item.roles.includes(role.value),
            ),
        }))
        .filter((section) => section.items.length > 0),
);

const isActive = (routeName) => route().current(routeName);
</script>

<template>
    <div class="flex h-screen bg-[#F8FAFC] overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-[220px] bg-[#1A1A1A] flex flex-col flex-shrink-0">
            <!-- Logo -->
            <div class="px-5 py-5 border-b border-zinc-800">
                <img :src="'/Images/logo-bmc.png'" alt="BMC Logo" class="h-12" />
                <p class="text-zinc-500 text-sm mt-1">PT. Bumi Mentari Cemerlang</p>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-3 px-2">
                <template
                    v-for="section in filteredMenus"
                    :key="section.section"
                >
                    <p
                        class="text-[10px] font-semibold text-zinc-600 uppercase tracking-widest px-3 pt-4 pb-1.5"
                    >
                        {{ section.section }}
                    </p>
                    <Link
                        v-for="item in section.items"
                        :key="item.route"
                        :href="route(item.route)"
                        class="flex items-center gap-2.5 px-3 py-2 rounded-lg mx-0 my-0.5 text-zinc-400 hover:text-white hover:bg-zinc-800 transition-all duration-150 text-[13px]"
                        :class="
                            isActive(item.route)
                                ? 'bg-[#F5C518] !text-[#1A1A1A] hover:bg-[#e6b800] hover:!text-[#1A1A1A]'
                                : ''
                        "
                    >
                        <span
                            class="w-[18px] h-[18px] flex-shrink-0"
                            v-html="item.icon"
                        />
                        <span>{{ item.label }}</span>
                    </Link>
                </template>
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-zinc-800">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-zinc-500 hover:text-red-400 hover:bg-zinc-800 transition-all duration-150 text-[13px]"
                >
                    <svg
                        class="w-[18px] h-[18px] flex-shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    <span>Keluar</span>
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Topbar -->
            <header
                class="h-[52px] bg-white border-b border-zinc-200 flex items-center justify-between px-6 flex-shrink-0"
            >
                <h1 class="text-[15px] font-semibold text-zinc-900">
                    <slot name="title">Dashboard</slot>
                </h1>
                <div class="flex items-center gap-3">
                    <span
                        class="bg-[#FEF9C3] text-[#854D0E] text-[11px] font-semibold px-2.5 py-1 rounded-md uppercase"
                    >
                        {{ role }}
                    </span>
                    <div
                        class="w-8 h-8 rounded-full bg-[#1A1A1A] text-[#F5C518] text-xs font-semibold flex items-center justify-center"
                    >
                        {{ initials }}
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
