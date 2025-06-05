<script setup lang="ts">
import { ref } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import { Link } from '@inertiajs/vue3'
import { BellIcon } from '@heroicons/vue/24/solid'

const showingNavigationDropdown = ref(false)
</script>

<template>
    <div class="min-h-screen">
        <nav class="app-nav">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <Link :href="route('dashboard')">
                            <ApplicationLogo class="app-nav-logo block h-9 w-auto fill-current" />
                        </Link>
                    </div>

                    <div class="hidden sm:block">
                        <Link :href="route('dashboard')" class="app-nav-link">Dashboard</Link>
                    </div>

                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                        <button type="button" class="app-nav-icon-button">
                            <BellIcon class="h-6 w-6" />
                        </button>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="app-nav-dropdown-trigger inline-flex items-center rounded-md border px-3 py-2 text-sm font-medium leading-4"
                                    >
                                        {{ $page.props.auth.user.name }}
                                        <svg
                                            class="ml-2 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <div class="-mr-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="app-nav-hamburger inline-flex items-center justify-center rounded-md p-2"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="sm:hidden" 
            >
                <div class="app-responsive-nav-section space-y-1 pt-2 pb-3">
                    <Link :href="route('dashboard')" class="app-responsive-nav-link block px-4 py-2 text-sm">
                        Dashboard
                    </Link>
                </div>

                <div class="app-responsive-nav-user-section pt-4 pb-1">
                    <div class="px-4">
                        <div class="app-responsive-nav-user-name">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="app-responsive-nav-user-email">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 px-4">
                        <Link :href="route('profile.edit')" class="app-responsive-nav-action-link block py-1 text-sm">
                            Profile
                        </Link>
                        <form :action="route('logout')" method="post" class="block">
                            @csrf
                            <button type="submit" class="app-responsive-nav-action-link py-1 text-sm w-full text-left">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>