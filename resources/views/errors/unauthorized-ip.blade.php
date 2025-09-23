{{-- <!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Akses Ditolak</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="color-scheme" content="light dark">
</head>
<body class="h-full bg-gray-100 dark:bg-gray-950 text-gray-800 dark:text-gray-100">
  <main class="min-h-screen grid place-items-center p-6">
    <section class="w-full max-w-2xl">
      <div class="relative overflow-hidden rounded-2xl shadow-xl bg-white/80 dark:bg-gray-900/80 backdrop-blur border border-gray-200/60 dark:border-gray-800/60">
        <!-- Top bar gradient -->
        <div class="h-1.5 bg-gradient-to-r from-red-500 via-amber-500 to-blue-600"></div>

        <div class="p-8 md:p-10 flex flex-col items-center text-center gap-6">
          <!-- Cute SVG Illustration -->
          <div class="w-40 h-40 md:w-48 md:h-48">
            <svg viewBox="0 0 200 200" class="w-full h-full" aria-hidden="true">
              <!-- Background blob -->
              <defs>
                <linearGradient id="g1" x1="0" x2="1" y1="0" y2="1">
                  <stop offset="0%" stop-color="#ef4444"/>
                  <stop offset="100%" stop-color="#f59e0b"/>
                </linearGradient>
                <linearGradient id="g2" x1="0" x2="1" y1="1" y2="0">
                  <stop offset="0%" stop-color="#60a5fa"/>
                  <stop offset="100%" stop-color="#22d3ee"/>
                </linearGradient>
                <filter id="soft">
                  <feGaussianBlur in="SourceGraphic" stdDeviation="1.5" />
                </filter>
              </defs>
              <path d="M164 79c0 35-28 75-64 75s-64-40-64-75 28-45 64-45 64 10 64 45Z"
                    fill="url(#g2)" opacity="0.15"/>
              <!-- Shield -->
              <g transform="translate(55,40)">
                <path d="M45 0L90 18v33c0 30-20 51-45 64C20 102 0 81 0 51V18L45 0Z"
                      fill="url(#g1)" />
                <!-- Lock -->
                <g transform="translate(27,34)">
                  <rect x="0" y="18" width="36" height="28" rx="6"
                        fill="white" class="dark:fill-gray-800" />
                  <rect x="14" y="26" width="8" height="12" rx="4"
                        fill="#ef4444" />
                  <path d="M18 0c7 0 12 5 12 12v6h-6v-6a6 6 0 0 0-12 0v6h-6v-6C6 5 11 0 18 0Z"
                        fill="white" class="dark:fill-gray-800"/>
                </g>
              </g>
              <!-- Subtle sparkle -->
              <circle cx="165" cy="45" r="3" fill="#22d3ee">
                <animate attributeName="r" values="2;3;2" dur="2.2s" repeatCount="indefinite"/>
              </circle>
              <circle cx="30" cy="65" r="2.5" fill="#93c5fd">
                <animate attributeName="r" values="2;3;2" dur="2.8s" repeatCount="indefinite"/>
              </circle>
            </svg>
          </div>

          <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
              🚫 Akses Ditolak (403)
            </h1>
            <p class="mt-3 text-base md:text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
              Maaf, alamat IP kamu tidak terdaftar untuk mengakses halaman ini.
              Jika menurutmu ini keliru, hubungi administrator agar IP kamu dimasukkan ke whitelist.
            </p>
          </div>

          <!-- Info card -->
          <div class="w-full grid gap-3 sm:grid-cols-2 text-sm">
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/60 p-3 text-left">
              <div class="text-gray-500 dark:text-gray-400">IP yang terdeteksi</div>
              <div class="mt-0.5 font-mono font-semibold select-all" id="ipText">{{ $ip }}</div>
              <button
                onclick="copyText('#ipText')"
                class="mt-2 inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                Salin IP
              </button>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/60 p-3 text-left">
              <div class="text-gray-500 dark:text-gray-400">Role</div>
              <div class="mt-0.5 font-semibold">{{ ucfirst($role) }}</div>
              <div class="text-xs mt-2 text-gray-500 dark:text-gray-400">
                Waktu: {{ now()->format('d M Y, H:i') }}
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-3 w-full justify-center mt-2">
            <a href="{{ url('/login') }}"
               class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 active:opacity-90 transition">
              🔑 Kembali ke Login
            </a>
            <button onclick="window.location.reload()"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
              🔄 Coba Lagi
            </button>
            <!-- Ganti mailto sesuai kebutuhan -->
            <a href="mailto:admin@example.com?subject=Whitelist%20IP&body=Halo%20Admin,%20mohon%20whitelist%20IP%20saya:%20{{ $ip }}%20untuk%20role%20{{ ucfirst($role) }}."
               class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
              ✉️ Hubungi Admin
            </a>
          </div>

          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            Kode: <span class="font-mono">403-UNAUTHORIZED-IP</span>
          </p>
        </div>

        <!-- Soft glow -->
        <div class="pointer-events-none absolute -top-20 -right-20 w-56 h-56 rounded-full blur-3xl opacity-25"
             style="background: radial-gradient(closest-side, #22d3ee, transparent)"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 w-64 h-64 rounded-full blur-3xl opacity-20"
             style="background: radial-gradient(closest-side, #f59e0b, transparent)"></div>
      </div>
    </section>
  </main>

  <script>
    function copyText(selector) {
      const el = document.querySelector(selector);
      if (!el) return;
      const text = el.textContent.trim();
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
      } else {
        const ta = document.createElement('textarea');
        ta.value = text; document.body.appendChild(ta);
        ta.select(); document.execCommand('copy'); ta.remove();
      }
    }
  </script>
</body>
</html> --}}
