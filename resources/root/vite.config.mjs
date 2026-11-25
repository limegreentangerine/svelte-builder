import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import { globSync } from 'glob';
import path from 'path';

// Resolve glob pattern to find all entry files, excluding vendor
const entries = globSync('**/**/ts/index.ts', { ignore: ['vendor/**'] });

// Convert array of files to rollup input object
const input = {};
entries.forEach(file => {
  const dir = path.dirname(file); // blocks/job_list/ts
  const blockPath = path.dirname(dir); // blocks/job_list
  input[blockPath] = file;
});

export default defineConfig({
  plugins: [svelte()],
  build: {
    lib: {
      entry: input,
      formats: ['es']
    },
    outDir: '.',
    target: 'es2019',
    minify: false,
    rollupOptions: {
      output: {
        format: 'es',
        entryFileNames: '[name]/view.js'
      }
    }
  }
});
