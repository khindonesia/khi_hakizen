<?php

namespace App\Exceptions;

use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Throwable;

class LaravelExceptionRenderer implements ExceptionRenderer
{
    public function __construct(protected ExceptionRenderer $defaultRenderer)
    {
    }

    /**
     * Renders the given exception as HTML.
     *
     * @param  \Throwable  $throwable
     * @return string
     */
    public function render($throwable)
    {
        $html = $this->defaultRenderer->render($throwable);

        $title = get_class($throwable);
        $message = $throwable->getMessage();
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        $trace = $throwable->getTraceAsString();

        $markdown = "### 🚨 Exception: {$title}\n\n";
        $markdown .= "**Message:** `{$message}`\n";
        $markdown .= "**File:** `{$file}` (Line: {$line})\n\n";
        $markdown .= "```\n{$trace}\n```";

        $escapedMarkdown = json_encode($markdown);

        $copyButtonHtml = <<<HTML
<div id="copy-markdown-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; font-family: ui-sans-serif, system-ui, sans-serif;">
    <button onclick="copyExceptionAsMarkdown()" style="background-color: #df1c24; color: white; border: none; padding: 10px 16px; border-radius: 9999px; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); transition: all 0.2s; display: flex; align-items: center; gap: 8px;">
        <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002 2h2a2 2 0 002-2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
        </svg>
        <span>Copy as Markdown</span>
    </button>
</div>
<script>
function copyExceptionAsMarkdown() {
    const text = {$escapedMarkdown};
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.querySelector('#copy-markdown-widget button');
        const span = btn.querySelector('span');
        const originalText = span.textContent;
        span.textContent = 'Copied!';
        btn.style.backgroundColor = '#107F5B';
        setTimeout(() => {
            span.textContent = originalText;
            btn.style.backgroundColor = '#df1c24';
        }, 2000);
    });
}
</script>
HTML;

        if (str_contains($html, '</body>')) {
            return str_replace('</body>', $copyButtonHtml . '</body>', $html);
        }

        return $html . $copyButtonHtml;
    }
}
