<script setup lang="ts">
import { ref } from 'vue'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link } from '@inertiajs/vue3'
import { BellIcon } from '@heroicons/vue/24/solid'

const showingNavigationDropdown = ref(false)
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="border-b border-gray-700 bg-gray-800 text-gray-100">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <!-- Logo -->
          <div class="flex items-center">
            <Link :href="route('dashboard')">
              <ApplicationLogo class="block h-9 w-auto fill-current text-gray-200" />
            </Link>
          </div>

          <!-- Navigation Links -->
          <div class="hidden sm:block">
            <Link :href="route('dashboard')" class="text-sm font-semibold hover:underline text-gray-200">Dashboard</Link>
          </div>

          <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
            <button type="button" class="text-gray-400 hover:text-gray-200 focus:outline-none"><BellIcon class="h-6 w-6" /></button>
            <Dropdown align="right" width="48">
              <template #trigger>
                <span class="inline-flex rounded-md">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-300 hover:text-gray-100"
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
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 
                           011.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 
                           010-1.414z"
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

          <!-- Hamburger -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-gray-200 focus:outline-none"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{
                    'hidden': showingNavigationDropdown,
                    'inline-flex': !showingNavigationDropdown,
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{
                    'hidden': !showingNavigationDropdown,
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

      <!-- Responsive Navigation Menu -->
      <div
        :class="{
          'block': showingNavigationDropdown,
          'hidden': !showingNavigationDropdown,
        }"
        class="sm:hidden"
      >
        <div class="space-y-1 border-b border-gray-700 pt-2 pb-3">
          <Link
            :href="route('dashboard')"
            class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-gray-100"
          >
            Dashboard
          </Link>
        </div>

        <div class="border-t border-gray-700 pt-4 pb-1">
          <div class="px-4">
            <div class="text-base font-medium text-gray-200">
              {{ $page.props.auth.user.name }}
            </div>
            <div class="text-sm font-medium text-gray-400">
              {{ $page.props.auth.user.email }}
            </div>
          </div>
          <div class="mt-3 space-y-1 px-4">
            <Link
              :href="route('profile.edit')"
              class="block py-1 text-sm text-gray-300 hover:text-gray-100 hover:underline"
            >
              Profile
            </Link>
            <form :action="route('logout')" method="post" class="block">
              @csrf
              <button
                type="submit"
                class="py-1 text-sm text-gray-300 hover:text-gray-100 hover:underline"
              >
                Log Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Heading -->
    <header class="bg-white shadow" v-if="$slots.header">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Page Content -->
    <main>
      <slot />
    </main>
  </div>
</template>
