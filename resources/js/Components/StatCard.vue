<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    subtitle: {
        type: String,
        default: ''
    },
    icon: {
        type: [Object, Function],
        default: null
    },
    theme: {
        type: String,
        default: 'emerald'
    },
    badge: {
        type: String,
        default: ''
    }
});

const themeStyles = computed(() => {
    switch (props.theme) {
        case 'purple':
            return {
                iconBg: 'bg-purple-100 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400',
                borderHover: 'hover:border-purple-300 dark:hover:border-purple-800'
            };
        case 'blue':
            return {
                iconBg: 'bg-blue-100 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400',
                borderHover: 'hover:border-blue-300 dark:hover:border-blue-800'
            };
        case 'amber':
            return {
                iconBg: 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400',
                borderHover: 'hover:border-amber-300 dark:hover:border-amber-800'
            };
        case 'rose':
            return {
                iconBg: 'bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400',
                borderHover: 'hover:border-rose-300 dark:hover:border-rose-800'
            };
        default:
            return {
                iconBg: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
                borderHover: 'hover:border-emerald-300 dark:hover:border-emerald-800'
            };
    }
});
</script>

<template>
    <div
        :class="[
            'group relative rounded-3xl bg-white p-6 shadow-sm border border-slate-100 transition-all duration-300 hover:shadow-md dark:bg-slate-900 dark:border-slate-800',
            themeStyles.borderHover
        ]"
    >
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                {{ title }}
            </span>
            <div
                v-if="icon"
                :class="[
                    'flex h-10 w-10 items-center justify-center rounded-2xl transition-transform duration-300 group-hover:scale-110',
                    themeStyles.iconBg
                ]"
            >
                <component :is="icon" class="h-5 w-5" />
            </div>
        </div>

        <div class="mt-4 flex items-baseline gap-2">
            <span class="text-3xl font-bold tracking-tight text-slate-800 dark:text-white">
                {{ value }}
            </span>
            <span v-if="badge" class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                {{ badge }}
            </span>
        </div>

        <p v-if="subtitle" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {{ subtitle }}
        </p>
    </div>
</template>
