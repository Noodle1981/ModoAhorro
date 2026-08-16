import pluginVue from 'eslint-plugin-vue';

export default [
    ...pluginVue.configs['flat/essential'],
    {
        files: ['resources/js/**/*.{js,mjs,cjs,vue}'],
        rules: {
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'off',
            'no-unused-vars': ['warn', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
        },
    },
    {
        ignores: [
            'public/**',
            'vendor/**',
            'node_modules/**',
            'storage/**',
            'bootstrap/ssr/**',
            'dist/**',
        ],
    },
];
