<?php namespace ProcessWire;

/**
 * TrackingScripts
 * Injects tracking scripts (Google Analytics, Google Ads, Facebook Pixel, custom)
 * into site pages with optional PrivacyWire consent integration.
 */
class TrackingScripts extends WireData implements Module, ConfigurableModule
{
    /**
     * Excluded templates — never inject tracking on these
     */
    protected $excludedTemplates = ['admin', 'form-builder'];

    public function ready()
    {
        $template = $this->wire('page')->template->name;

        // Hook to write txt files when this module's config is saved
        $this->wire('modules')->addHookAfter('saveConfig', $this, 'onSaveConfig');

        if (in_array($template, $this->excludedTemplates)) return;

        $this->wire('page')->addHookAfter('render', $this, 'injectScripts', ['priority' => 100]);
    }

    public function onSaveConfig(HookEvent $event)
    {
        $module = $event->arguments(0);
        if (!($module instanceof TrackingScripts) && $module !== $this->className()) return;

        $data = $event->arguments(1);
        $root = $this->wire('config')->paths->root;
        $files = [
            'robots_txt' => 'robots.txt',
            'llms_txt' => 'llms.txt',
        ];

        foreach ($files as $fieldName => $fileName) {
            $path = $root . $fileName;
            $val = $data[$fieldName] ?? '';
            if (!empty($val)) {
                file_put_contents($path, $val);
                $this->message(sprintf($this->_('Written %s'), $fileName));
            } elseif (is_file($path)) {
                unlink($path);
                $this->message(sprintf($this->_('Removed %s'), $fileName));
            }
        }
    }

    public function injectScripts(HookEvent $event)
    {
        $html = $event->return;
        $head = '';
        $body = '';
        $pw = $this->privacywire_enabled && $this->wire('modules')->isInstalled('PrivacyWire');

        // Google Analytics
        if ($this->ga_enabled && $this->ga_id) {
            $id = $this->wire('sanitizer')->text($this->ga_id);
            if (preg_match('/^G-[A-Z0-9]+$/', $id)) {
                $snippet = $this->buildGASnippet($id, $pw, $this->ga_consent_category);
                $this->appendTo($head, $body, $snippet, $this->ga_position);
            }
        }

        // Google Ads
        if ($this->gads_enabled && $this->gads_id) {
            $id = $this->wire('sanitizer')->text($this->gads_id);
            if (preg_match('/^AW-[A-Z0-9]+$/', $id)) {
                $snippet = $this->buildGAdsSnippet($id, $pw, $this->gads_consent_category);
                $this->appendTo($head, $body, $snippet, $this->gads_position);
            }
        }

        // Facebook Pixel
        if ($this->fbpixel_enabled && $this->fbpixel_id) {
            $id = $this->wire('sanitizer')->text($this->fbpixel_id);
            if (preg_match('/^\d+$/', $id)) {
                $snippet = $this->buildFBPixelSnippet($id, $pw, $this->fbpixel_consent_category);
                $this->appendTo($head, $body, $snippet, $this->fbpixel_position);
            }
        }

        // Custom code (stored as base64)
        if ($this->custom_head) {
            if ($decoded !== false) $head .= $this->custom_head;
        }
        if ($this->custom_body) {
            if ($decoded !== false) $body .= $this->custom_body;
        }

        if ($head) $html = str_replace('</head>', $head . '</head>', $html);
        if ($body) $html = str_replace('</body>', $body . '</body>', $html);

        $event->return = $html;
    }

    // --- Snippet builders ---

    protected function scriptTag(string $content, bool $pw, string $category, string $src = ''): string
    {
        if ($pw) {
            $attrs = "type=\"text/plain\" data-type=\"text/javascript\" data-category=\"{$category}\" class=\"require-consent\"";
        } else {
            $attrs = '';
        }

        if ($src) {
            $srcAttr = $pw ? "data-src=\"{$src}\"" : "src=\"{$src}\"";
            return "<script {$attrs} {$srcAttr} async></script>\n";
        }

        return "<script {$attrs}>{$content}</script>\n";
    }

    protected function buildGASnippet(string $id, bool $pw, string $category): string
    {
        $gtag = "https://www.googletagmanager.com/gtag/js?id={$id}";
        $out = $this->scriptTag('', $pw, $category, $gtag);
        $out .= $this->scriptTag(
            "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$id}');",
            $pw, $category
        );
        return $out;
    }

    protected function buildGAdsSnippet(string $id, bool $pw, string $category): string
    {
        $gtag = "https://www.googletagmanager.com/gtag/js?id={$id}";
        $out = $this->scriptTag('', $pw, $category, $gtag);
        $out .= $this->scriptTag(
            "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$id}');",
            $pw, $category
        );
        return $out;
    }

    protected function buildFBPixelSnippet(string $id, bool $pw, string $category): string
    {
        $code = "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?"
            . "n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;"
            . "n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;"
            . "t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}"
            . "(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');"
            . "fbq('init','{$id}');fbq('track','PageView');";

        $out = $this->scriptTag($code, $pw, $category);
        $out .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" "
            . "src=\"https://www.facebook.com/tr?id={$id}&ev=PageView&noscript=1\"/></noscript>\n";
        return $out;
    }

    protected function appendTo(string &$head, string &$body, string $snippet, string $position): void
    {
        if ($position === 'body') {
            $body .= $snippet;
        } else {
            $head .= $snippet;
        }
    }

}
