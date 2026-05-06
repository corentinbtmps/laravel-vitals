<?php

declare(strict_types=1);

return [
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
];
