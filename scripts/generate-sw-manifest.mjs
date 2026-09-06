// Genere public/build-precache.json a partir du manifest Vite (public/build/manifest.json)
// afin que le Service Worker puisse precacher les bundles hashes du build courant.
// Lance automatiquement apres "npm run build" (voir package.json > scripts.postbuild).
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const viteManifestPath = join(root, 'public/build/manifest.json');
const outPath = join(root, 'public/build-precache.json');

if (!existsSync(viteManifestPath)) {
    console.warn('[generate-sw-manifest] public/build/manifest.json introuvable, build-precache.json non genere.');
    process.exit(0);
}

const raw = readFileSync(viteManifestPath, 'utf-8');
const manifest = JSON.parse(raw);

const files = new Set();
for (const entry of Object.values(manifest)) {
    if (entry.file) files.add(`/build/${entry.file}`);
    if (Array.isArray(entry.css)) {
        for (const css of entry.css) files.add(`/build/${css}`);
    }
    if (Array.isArray(entry.assets)) {
        for (const asset of entry.assets) files.add(`/build/${asset}`);
    }
}

const urls = Array.from(files).sort();
const version = createHash('sha256').update(raw).digest('hex').slice(0, 12);

writeFileSync(outPath, JSON.stringify({ version, urls }, null, 2));
console.log(`[generate-sw-manifest] build-precache.json genere (version ${version}, ${urls.length} fichiers).`);
