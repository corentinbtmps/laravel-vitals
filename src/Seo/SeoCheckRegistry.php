<?php

declare(strict_types=1);

namespace LaravelVitals\Seo;

use LaravelVitals\Seo\Checks\Configuration\NoindexCheck;
use LaravelVitals\Seo\Checks\Configuration\NofollowCheck;
use LaravelVitals\Seo\Checks\Configuration\RobotsTxtAllowsIndexingCheck;
use LaravelVitals\Seo\Checks\Content\BrokenImagesCheck;
use LaravelVitals\Seo\Checks\Content\BrokenLinksCheck;
use LaravelVitals\Seo\Checks\Content\H1Check;
use LaravelVitals\Seo\Checks\Content\HttpsLinksCheck;
use LaravelVitals\Seo\Checks\Content\ImageAltCheck;
use LaravelVitals\Seo\Checks\Meta\CanonicalCheck;
use LaravelVitals\Seo\Checks\Meta\HtmlLangCheck;
use LaravelVitals\Seo\Checks\Meta\InvalidHeadElementsCheck;
use LaravelVitals\Seo\Checks\Meta\MetaDescriptionCheck;
use LaravelVitals\Seo\Checks\Meta\OpenGraphImageCheck;
use LaravelVitals\Seo\Checks\Meta\StructuredDataCheck;
use LaravelVitals\Seo\Checks\Meta\TitleLengthCheck;
use LaravelVitals\Seo\Checks\Performance\CompressionCheck;
use LaravelVitals\Seo\Checks\Performance\CssSizeCheck;
use LaravelVitals\Seo\Checks\Performance\HtmlSizeCheck;
use LaravelVitals\Seo\Checks\Performance\ImageSizeCheck;
use LaravelVitals\Seo\Checks\Performance\JavaScriptSizeCheck;
use LaravelVitals\Seo\Checks\Performance\StatusCodeCheck;
use LaravelVitals\Seo\Checks\Performance\TtfbCheck;
use LaravelVitals\Seo\Contracts\SeoCheck;

final class SeoCheckRegistry
{
    /**
     * Returns all registered check instances in category + weight order.
     *
     * @return list<SeoCheck>
     */
    public function all(): array
    {
        return [
            // Configuration
            new NoindexCheck(),
            new NofollowCheck(),
            new RobotsTxtAllowsIndexingCheck(),

            // Content
            new H1Check(),
            new HttpsLinksCheck(),
            new ImageAltCheck(),
            new BrokenLinksCheck(),
            new BrokenImagesCheck(),

            // Meta
            new MetaDescriptionCheck(),
            new TitleLengthCheck(),
            new OpenGraphImageCheck(),
            new HtmlLangCheck(),
            new CanonicalCheck(),
            new StructuredDataCheck(),
            new InvalidHeadElementsCheck(),

            // Performance
            new TtfbCheck(),
            new StatusCodeCheck(),
            new HtmlSizeCheck(),
            new ImageSizeCheck(),
            new JavaScriptSizeCheck(),
            new CssSizeCheck(),
            new CompressionCheck(),
        ];
    }

    /**
     * Returns checks that should run, respecting disabled_checks config.
     *
     * @return list<SeoCheck>
     */
    public function enabled(): array
    {
        $disabledKeys = (array) config('vitals.seo.disabled_checks', []);

        return array_values(array_filter(
            $this->all(),
            static fn (SeoCheck $check): bool => ! in_array($check->key(), $disabledKeys, true),
        ));
    }
}
