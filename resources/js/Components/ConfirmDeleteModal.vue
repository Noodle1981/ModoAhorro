<script setup>
import Modal from '@/Components/Modal.vue';
import { TriangleAlert } from 'lucide-vue-next';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '¿Confirmar eliminación?',
    },
    message: {
        type: String,
        default: 'Esta acción no se puede deshacer.',
    },
    confirmLabel: {
        type: String,
        default: 'Eliminar',
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6 text-center">
            <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <TriangleAlert :size="28" />
            </div>

            <h3 class="text-lg font-black text-slate-900 mb-2">
                {{ title }}
            </h3>

            <p class="text-sm text-slate-500 mb-6 leading-relaxed">
                {{ message }}
            </p>

            <div class="flex items-center justify-center gap-3">
                <button
                    type="button"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider hover:bg-slate-50 transition-colors"
                    :disabled="processing"
                    @click="emit('close')"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-rose-700 transition-colors shadow-sm shadow-rose-200 flex items-center gap-2"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    <span v-if="processing" class="inline-block animate-spin">⏳</span>
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </Modal>
</template>
