<?php

/**
 * Fallback jika document root belum diarahkan ke folder public/.
 * Akses aplikasi lewat domain tanpa /public — lihat DEPLOY-WINDOWS-LARAGON.md.
 */
require __DIR__.'/public/index.php';
