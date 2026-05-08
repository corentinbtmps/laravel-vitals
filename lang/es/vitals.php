<?php

declare(strict_types=1);

return [
    'empty' => [
        'overview_no_urls' => [
            'title' => 'Añade tu primera URL para comenzar la monitorización',
            'body'  => 'Laravel Vitals rastrea puntuaciones Lighthouse y telemetría de backend para las URLs que configures. Empieza añadiendo las URLs que quieres monitorizar.',
            'cta'   => 'Configurar URLs',
            'docs'  => 'Leer documentación',
        ],
        'overview_no_audits' => [
            'title' => 'Aún no hay auditorías',
            'body'  => 'Ejecuta tu primera auditoría para poblar el panel. Las auditorías pueden ejecutarse mediante artisan, una tarea programada o tu pipeline de CI.',
            'cta'   => 'Abrir URLs',
            'docs'  => 'Leer documentación',
        ],
        'urls_no_urls' => [
            'title' => 'No hay URLs configuradas',
            'body'  => 'Configura las URLs en config/vitals.php bajo la clave urls, o ejecuta el seeder de demostración para datos de ejemplo.',
            'docs'  => 'Leer documentación',
        ],
        'recos_no_recos' => [
            'title' => 'Aún no hay recomendaciones',
            'body'  => 'Ejecuta una auditoría para descubrir oportunidades de optimización. Cada recomendación enlaza con el archivo y la línea exactos de tu aplicación.',
            'cta'   => 'Explorar problemas conocidos',
            'docs'  => 'Leer documentación',
        ],
        'insights_no_history' => [
            'title' => 'Historial de auditorías insuficiente',
            'body'  => 'Los insights comparan auditorías a lo largo del tiempo. Ejecuta al menos 2 auditorías por URL para ver tendencias y regresiones.',
            'docs'  => 'Leer documentación',
        ],
        'budgets_no_budgets' => [
            'title' => 'No hay presupuestos definidos',
            'body'  => 'Los presupuestos de rendimiento hacen fallar tu CI cuando las puntuaciones caen por debajo de un umbral. Defínelos en config/vitals.php bajo budgets.',
            'docs'  => 'Leer documentación',
        ],
    ],
    'onboarding' => [
        'banner_title'    => 'Primeros pasos con Laravel Vitals',
        'banner_subtitle' => ':count de :total pasos completados',
        'dismiss'         => 'Omitir incorporación',
        'dismiss_confirm' => 'Ocultar este banner permanentemente. Todas las funciones siguen accesibles desde la navegación.',
        'steps' => [
            'urls' => [
                'title' => 'Configura tu primera URL',
                'cta'   => 'Añadir URL',
            ],
            'audit' => [
                'title' => 'Ejecuta tu primera auditoría',
                'cta'   => 'Ejecutar auditoría',
            ],
            'notifications' => [
                'title' => 'Configurar notificaciones (opcional)',
                'cta'   => 'Configurar',
            ],
            'budgets' => [
                'title' => 'Establecer presupuestos de rendimiento',
                'cta'   => 'Definir presupuestos',
            ],
        ],
    ],
    'tooltip' => [
        'pin'          => 'Añadir a favoritos',
        'unpin'        => 'Quitar de favoritos',
        'last_audit_at' => 'Última auditoría: :timestamp',
        'metric_score' => 'Puntuación Lighthouse global (0–100)',
        'metric_lcp'   => 'Largest Contentful Paint — tiempo hasta que se renderiza el elemento visible más grande. Bien < 2,5 s',
        'metric_inp'   => 'Interaction to Next Paint — latencia de interacción. Bien < 200 ms',
        'metric_cls'   => 'Cumulative Layout Shift — estabilidad visual. Bien < 0,1',
        'metric_ttfb'  => 'Time to First Byte — tiempo de respuesta del servidor. Bien < 800 ms',
        'cwv_lcp'      => 'Largest Contentful Paint — tiempo hasta que se renderiza el elemento visible más grande. Bien = menos de 2,5 s.',
        'cwv_cls'      => 'Cumulative Layout Shift — desplazamientos visuales inesperados durante la carga. Bien = menos de 0,1.',
        'cwv_inp'      => 'Interaction to Next Paint — latencia entre la entrada del usuario y el siguiente renderizado. Bien = menos de 200 ms.',
        'cwv_ttfb'     => 'Time to First Byte — tiempo que tarda el servidor en responder con el primer byte. Bien = menos de 800 ms.',
        'score_label'  => 'Puntuación Lighthouse :label',
    ],
    'spotlight' => [
        'placeholder'            => 'Buscar URLs, auditorías, recomendaciones…',
        'group_urls'             => 'URLs',
        'group_audits'           => 'Auditorías',
        'group_recommendations'  => 'Recomendaciones',
        'group_learn'            => 'Aprender',
        'empty'                  => 'Sin resultados',
        'hint'                   => 'Escribe al menos 2 caracteres para buscar',
        'kbd_navigate'           => 'Navegar',
        'kbd_open'               => 'Abrir',
        'button_label'           => 'Buscar…',
    ],
    'recommendations' => [
        'unused-javascript' => [
            'title'       => 'Reducir JavaScript no utilizado',
            'description' => 'JavaScript enviado al navegador pero nunca ejecutado desperdicia ancho de banda y tiempo de parsing.',
        ],
        'unused-css-rules' => [
            'title'       => 'Eliminar CSS no utilizado',
            'description' => 'Los bytes de CSS no utilizados aún deben descargarse y ser parseados por el navegador.',
        ],
        'unminified-javascript' => [
            'title'       => 'Minificar JavaScript',
            'description' => 'JavaScript minificado reduce el tamaño de transferencia sin cambio de comportamiento.',
        ],
        'unminified-css' => [
            'title'       => 'Minificar CSS',
            'description' => 'CSS minificado reduce el tamaño de transferencia.',
        ],
        'render-blocking-resources' => [
            'title'       => 'Eliminar recursos que bloquean el renderizado',
            'description' => 'Los recursos en el head bloquean el primer pintado hasta que se descargan. Difiéralos o inline el CSS crítico.',
        ],
        'modern-image-formats' => [
            'title'       => 'Servir imágenes en formatos modernos',
            'description' => 'WebP y AVIF comprimen mejor que JPEG/PNG.',
        ],
        'uses-responsive-images' => [
            'title'       => 'Usar imágenes responsivas',
            'description' => 'Añade srcset y sizes para que el navegador elija la mejor variante por viewport.',
        ],
        'efficient-animated-content' => [
            'title'       => 'Usar video para contenido animado',
            'description' => 'Los GIFs animados son pesados. Codifica la animación como MP4/WebM y usa <video>.',
        ],
        'offscreen-images' => [
            'title'       => 'Lazy-load imágenes fuera de pantalla',
            'description' => 'Añade loading="lazy" para que las imágenes debajo del fold se carguen bajo demanda.',
        ],
        'legacy-javascript' => [
            'title'       => 'Evitar JavaScript legacy a navegadores modernos',
            'description' => 'Los bundles polyfilleados inflan los payloads de navegadores modernos.',
        ],
        'duplicated-javascript' => [
            'title'       => 'Eliminar módulos duplicados',
            'description' => 'Múltiples bundles incluyen el mismo módulo. Ajusta el splitting de Vite.',
        ],
        'color-contrast' => [
            'title'       => 'Mejorar el contraste de colores',
            'description' => 'El contraste del texto está por debajo del umbral WCAG AA.',
        ],
        'image-alt' => [
            'title'       => 'Añadir alt a las imágenes',
            'description' => 'Las imágenes sin atributo alt son inaccesibles para lectores de pantalla.',
        ],
        'document-title' => [
            'title'       => 'Añadir título al documento',
            'description' => 'Las páginas sin <title> son inaccesibles y malas para el SEO.',
        ],
        'html-has-lang' => [
            'title'       => 'Declarar el idioma del documento',
            'description' => 'Añade <html lang="..."> para tecnologías de asistencia.',
        ],
        'errors-in-console' => [
            'title'       => 'Corregir errores de consola',
            'description' => 'Los errores de consola apuntan a bugs en runtime que pueden degradar la UX.',
        ],
        'no-vulnerable-libraries' => [
            'title'       => 'Actualizar librerías JavaScript vulnerables',
            'description' => 'Una librería empaquetada tiene una vulnerabilidad conocida. Actualízala o elimínala.',
        ],
        'meta-description' => [
            'title'       => 'Añadir meta description',
            'description' => 'Los buscadores muestran la meta description en los snippets.',
        ],
        'config-cache-disabled' => [
            'title'       => 'Cachear la configuración Laravel',
            'description' => 'Ejecuta `php artisan config:cache` en deploys de producción.',
        ],
        'route-cache-disabled' => [
            'title'       => 'Cachear la tabla de rutas',
            'description' => 'Ejecuta `php artisan route:cache` en deploys de producción.',
        ],
        'view-cache-disabled' => [
            'title'       => 'Pre-compilar vistas Blade',
            'description' => 'Ejecuta `php artisan view:cache` para evitar compilación en runtime.',
        ],
        'debug-on-prod' => [
            'title'       => 'Desactivar APP_DEBUG en producción',
            'description' => 'El modo debug filtra stack traces y ralentiza requests.',
        ],
        'opcache-disabled' => [
            'title'       => 'Activar OPcache',
            'description' => 'OPcache cachea el bytecode PHP compilado y es esencial en producción.',
        ],
        'missing-php-version' => [
            'title'       => 'Pinear una versión PHP',
            'description' => 'Añade un constraint php explícito en composer.json para rechazar versiones incompatibles.',
        ],
        'session-driver-file' => [
            'title'       => 'Cambiar driver de sesión desde "file"',
            'description' => 'Usa redis o database para sesiones en hosts multi-proceso en producción.',
        ],
        'cache-driver-file' => [
            'title'       => 'Cambiar driver de cache desde "file"',
            'description' => 'Usa redis o memcached en producción.',
        ],
        'queue-driver-sync-prod' => [
            'title'       => 'Configurar una conexión queue real',
            'description' => 'La queue sync ejecuta jobs in-process y bloquea respuestas. Usa redis/database/sqs en producción.',
        ],
        'n-plus-one-detected' => [
            'title'       => 'Patrón N+1 detectado',
            'description' => 'Un patrón de consulta se repitió por encima del umbral configurado durante este audit.',
        ],
        'slow-queries-detected' => [
            'title'       => 'Consultas SQL lentas detectadas',
            'description' => 'Una o más consultas excedieron el umbral de slow-query durante este audit.',
        ],
        'slow-views' => [
            'title'       => 'Vistas Blade lentas detectadas',
            'description' => 'Una vista renderizada tomó más tiempo que el umbral configurado.',
        ],
        'real-world-perf-degraded' => [
            'title'       => 'Performance real peor que synthetic',
            'description' => 'La telemetría de tráfico real (Pulse / Telescope) muestra P95 significativamente por encima del audit Lighthouse synthetic. Investiga condiciones de producción: carga, scripts de terceros, geografía.',
        ],
        'excessive-dom-size' => [
            'title'       => 'Reducir tamaño del DOM',
            'description' => 'La página tiene :count elementos DOM. DOMs grandes ralentizan el renderizado y las interacciones JS. Apunta a menos de 1500 elementos.',
        ],
        'cache-policy-short' => [
            'title'       => 'Mejorar política de cache',
            'description' => ':count recurso(s) tienen TTL menor a 30 días. El cache de larga duración acelera visitas repetidas.',
        ],
        'third-party-blocking' => [
            'title'       => 'Scripts de terceros bloquean el main thread',
            'description' => ':count origen(es) tercero(s) (:entities) bloquean el main thread > 250ms. Difiéralos o auto-aloje cuando sea posible.',
        ],
        'large-payload' => [
            'title'       => 'Reducir peso de la página',
            'description' => 'El peso total es :mb MB. Payloads grandes empeoran el LCP en conexiones lentas. Comprime imágenes y divide bundles de JS.',
        ],
        'bootup-time-high' => [
            'title'       => 'Reducir tiempo de ejecución JavaScript',
            'description' => 'Un script tarda :ms ms en evaluarse. Aplica code-splitting, lazy-load o elimina JS no utilizado.',
        ],
        'unsized-images' => [
            'title'       => 'Imágenes con ancho y alto explícitos',
            'description' => 'Define atributos width/height en imágenes para reservar espacio en el layout. El navegador calcula el ratio antes y evita saltos al cargar.',
        ],
        'font-display' => [
            'title'       => 'Texto visible durante carga de webfonts',
            'description' => 'Usa `font-display: swap` para que el navegador muestre una fuente de respaldo inmediatamente y cambie a la webfont al cargar. Evita FOIT (texto invisible).',
        ],
        'uses-rel-preload' => [
            'title'       => 'Precargar recursos críticos',
            'description' => 'Añade `<link rel="preload">` para recursos descubiertos tarde (módulos Vite, imágenes hero, fuentes críticas). El navegador los obtiene antes.',
        ],
        'uses-http2' => [
            'title'       => 'Usar HTTP/2 (o HTTP/3)',
            'description' => 'HTTP/2 multiplexa requests en una sola conexión — más rápido que HTTP/1.1 en páginas con muchos recursos. La mayoría de hosts modernos lo activan por defecto.',
        ],
        'octane-not-running' => [
            'title'       => 'Considerar Laravel Octane para TTFB más bajo',
            'description' => 'Octane mantiene la aplicación bootstrapped entre requests (Swoole / FrankenPHP / RoadRunner), eliminando el coste de bootstrap por request. Ahorros típicos: 40-200ms.',
        ],
        'assets-not-hashed' => [
            'title'       => 'Nombres de assets sin hash de contenido',
            'description' => 'Sin hashes de contenido (`app-Df8gK3p2.js`) no se puede cachear agresivamente — cada cambio requiere invalidación. Vite genera nombres con hash por defecto; verifica tu build.',
        ],
    ],
];
