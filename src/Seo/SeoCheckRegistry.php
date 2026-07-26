<?php

declare(strict_types=1);

namespace LaravelVitals\Seo;

use LaravelVitals\Seo\Checks\Agentic\AccessibleNamesCheck;
use LaravelVitals\Seo\Checks\Agentic\AiBotsAllowedCheck;
use LaravelVitals\Seo\Checks\Agentic\LayoutStabilityCheck;
use LaravelVitals\Seo\Checks\Agentic\LlmsTxtCheck;
use LaravelVitals\Seo\Checks\Agentic\SitemapDeclaredCheck;
use LaravelVitals\Seo\Checks\Agentic\WebMcpAvailableCheck;
use LaravelVitals\Seo\Checks\Agentic\WebMcpFormsCheck;
use LaravelVitals\Seo\Checks\Agentic\WebMcpSchemaValidCheck;
use LaravelVitals\Seo\Checks\Configuration\NoindexCheck;
use LaravelVitals\Seo\Checks\Configuration\NofollowCheck;
use LaravelVitals\Seo\Checks\Configuration\RobotsTxtAllowsIndexingCheck;
use LaravelVitals\Seo\Enums\SeoCheckCategory;
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

            // Agentic (agent-readiness — llms.txt, AI-bot rules, a11y tree, CLS, WebMCP)
            new LlmsTxtCheck(),
            new AiBotsAllowedCheck(),
            new SitemapDeclaredCheck(),
            new AccessibleNamesCheck(),
            new LayoutStabilityCheck(),
            new WebMcpAvailableCheck(),
            new WebMcpFormsCheck(),
            new WebMcpSchemaValidCheck(),
        ];
    }

    /**
     * Returns checks that should run, respecting disabled_checks config.
     *
     * @return list<SeoCheck>
     */
    public function enabled(): array
    {
        $disabledKeys   = (array) config('vitals.seo.disabled_checks', []);
        $agenticEnabled = (bool) config('vitals.seo.agentic.enabled', true);

        return array_values(array_filter(
            $this->all(),
            static function (SeoCheck $check) use ($disabledKeys, $agenticEnabled): bool {
                if (in_array($check->key(), $disabledKeys, true)) {
                    return false;
                }

                // Agent-readiness checks can be switched off as a group.
                return $agenticEnabled || $check->category() !== SeoCheckCategory::Agentic;
            },
        ));
    }
}
