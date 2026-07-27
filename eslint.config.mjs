export default [
  {
    files: ['resources/js/**/*.{js,jsx,mjs}'],
    ignores: ['public/**', 'node_modules/**'],
    rules: {
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      'no-undef': 'error',
    },
  },
];
