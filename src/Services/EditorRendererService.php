<?php
namespace Ns\Services;

use InvalidArgumentException;

class EditorRendererService
{
    protected array $blocks = [];

    public function __construct(array | null | string $data)
    {
        if ( is_array( $data ) ) {
            if ( isset($data['blocks'] ) ) {
                $this->blocks = $data['blocks'];
            }    
        } elseif ( is_string( $data ) ) {
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException('Invalid JSON string provided.');
            }
            if (!isset($decoded['blocks'])) {
                throw new InvalidArgumentException('Invalid Editor.js data format.');
            }
            $this->blocks = $decoded['blocks'];
        } else {
            $this->blocks = $this->emptyContentBlock();
        }
    }

    public function emptyContentBlock()
    {
        return [
            [
                'type' => 'paragraph',
                'data' => [
                    'text' => __m( 'No content available.', 'SGUniversity' )
                ]
            ]
        ];
    }

    public function render(): string
    {
        $html = '<div class="editor-content">';

        foreach ($this->blocks as $block) {
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];

            $method = 'render' . ucfirst($type);

            if (method_exists($this, $method)) {
                $html .= $this->{$method}($data);
            }
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderParagraph(array $data): string
    {
        return '<p>' . $this->escape($data['text'] ?? '') . '</p>';
    }

    protected function renderMedia( array $data )
    {
        $url = htmlspecialchars($data['url'] ?? '');
        $align = match( $data[ 'align'] ?? 'left' ) {
            'center' => 'sgu:justify-center',
            'right' => 'sgu:justify-end',
            default => 'sgu:justify-start',
        };

        return "<div class=\"sgu:flex $align\">
                    <img src=\"{$url}\" alt=\"Media\" class=\"editor-media\">
                </div>";
    }

    protected function renderHeader(array $data): string
    {
        $level = intval($data['level'] ?? 2);
        $level = max(1, min($level, 6));
        return "<h{$level}>" . $this->escape($data['text'] ?? '') . "</h{$level}>";
    }

    protected function renderList(array $data): string
    {
        $style = $data['style'] ?? 'unordered';
        $items = $data['items'] ?? [];
        $tag = $style === 'ordered' ? 'ol' : 'ul';

        $html = "<{$tag}>";

        foreach ($items as $item) {
            $html .= '<li>' . $this->escape( $item[ 'content' ] ) . '</li>';
        }

        $html .= "</{$tag}>";

        return $html;
    }

    protected function renderQuote(array $data): string
    {
        $text = $this->escape($data['text'] ?? '');
        $caption = $this->escape($data['caption'] ?? '');
        return "<blockquote><p>{$text}</p><cite>{$caption}</cite></blockquote>";
    }

    protected function renderImage(array $data): string
    {
        $url = htmlspecialchars($data['file']['url'] ?? '');
        $caption = htmlspecialchars($data['caption'] ?? '');
        return "<figure><img src=\"{$url}\" alt=\"{$caption}\"><figcaption>{$caption}</figcaption></figure>";
    }

    protected function renderTable(array $data): string
    {
        $content = $data['content'] ?? [];
        $withHeadings = $data['withHeadings'] ?? false;
        
        if (empty($content)) {
            return '';
        }

        $html = '<table class="editor-table sgu:border-collapse sgu:border sgu:border-gray-300 sgu:w-full">';

        foreach ($content as $rowIndex => $row) {
            $html .= '<tr>';
            $isHeaderRow = $withHeadings && $rowIndex === 0;
            $tag = $isHeaderRow ? 'th' : 'td';
            $cellClass = $isHeaderRow 
                ? 'sgu:border sgu:border-gray-300 sgu:px-4 sgu:py-2 sgu:bg-gray-100 sgu:font-semibold sgu:text-left' 
                : 'sgu:border sgu:border-gray-300 sgu:px-4 sgu:py-2';

            foreach ($row as $cell) {
                $html .= "<{$tag} class=\"{$cellClass}\">" . $this->escape($cell) . "</{$tag}>";
            }

            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    protected function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
