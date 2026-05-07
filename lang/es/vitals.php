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
];
