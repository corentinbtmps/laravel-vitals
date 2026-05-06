<?php

declare(strict_types=1);

return [
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
