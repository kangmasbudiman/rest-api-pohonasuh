-- MIGRASI DOMAIN GAMBAR (runbook deploy VPS) — jalankan SEKALI di VPS.
--
-- Prasyarat (urutan wajib):
--   1. Folder mirror `assets/images/` sudah tersalin ke
--      restApiPohonasuh/public/assets/images/ (struktur path sama).
--   2. Dump DB produksi sudah di-import ke MySQL VPS.
--   3. https://rest.pohonasuh.io/assets/images/data/foto/rantaukermas/B125_1.jpg
--      balas 200 (uji via curl -I).
--
-- Efek: 3.935 foto pohon + 1.274 galeri lama + 12 foto spesies berpindah
-- dari hosting lama (pohonasuh.org) ke server sendiri (rest.pohonasuh.io).
-- Web dan mobile memakai URL mentah dari kolom ini — tidak ada ubah kode.
--
-- Rollback: jalankan REPLACE yang sama dengan arah dibalik.

UPDATE data_pohon
SET foto_pohon = REPLACE(foto_pohon, 'https://pohonasuh.org/', 'https://rest.pohonasuh.io/')
WHERE foto_pohon LIKE 'https://pohonasuh.org/%';

UPDATE imagepohon
SET urlnya = REPLACE(urlnya, 'https://pohonasuh.org/', 'https://rest.pohonasuh.io/')
WHERE urlnya LIKE 'https://pohonasuh.org/%';

UPDATE species_catalog
SET foto = REPLACE(foto, 'https://pohonasuh.org/', 'https://rest.pohonasuh.io/')
WHERE foto LIKE 'https://pohonasuh.org/%';

-- Verifikasi: ketiganya harus 0 baris.
SELECT 'data_pohon sisa' AS cek, COUNT(*) FROM data_pohon WHERE foto_pohon LIKE 'https://pohonasuh.org/%'
UNION ALL
SELECT 'imagepohon sisa', COUNT(*) FROM imagepohon WHERE urlnya LIKE 'https://pohonasuh.org/%'
UNION ALL
SELECT 'species sisa', COUNT(*) FROM species_catalog WHERE foto LIKE 'https://pohonasuh.org/%';
