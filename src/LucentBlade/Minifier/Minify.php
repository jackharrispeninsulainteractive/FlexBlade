<?php

namespace LucentBlade\Minifier;

use LucentBlade\Blade\BladeCompiler;

class Minify
{

    public static final array $regex = [
        '/<(style|script)\s*(.*?)>(.*?)<\/\1>/is',
        "helpers" => [
            "id" => '/id=["\'](.*?)["\']/i'
        ]
    ];

    public static string $STYLE = "style";
    public static string $SCRIPT = "script";

    private array $styles;
    private array $scripts;
    private array $once;
    private array $media;

    public function __construct()
    {
        $this->styles = [
            "root" => []
        ];
        $this->scripts = [
            "root" =>[]
        ];

        $this->once = [];

        $this->media = [
            'mobile' => '(max-width: 767px)',
            'tablet' => '(min-width: 768px) and (max-width: 1199px)',
            'desktop' => '(min-width: 1200px)'
        ];
    }

    public function minifyHtmlDocument(string $content): string {

        $content = preg_replace_callback(self::$regex[0], function ($matches) {
            $props = BladeCompiler::propertiesToKeyValuePair($matches[2]);
            $type = $matches[1];
            $scope = $props[MinifyOption::SCOPE->value] ?? "root";

            if(isset($props[MinifyOption::NO_MINIFY->value])) {
                return $matches[0];
            }

            $code = ($type === self::$SCRIPT) ? self::minifyJS($matches[3]) : self::minifyCSS($matches[3]);

            if(isset($props[MinifyOption::MINIFY_ONCE->value])) {
                $hash = hash('xxh64', $code);

                if(in_array($hash, $this->once)) {
                    return "";
                }

                $this->once[] = $hash;
            }

            if(isset($props[MinifyOption::MEDIA->value]) && $type === self::$STYLE) {
                $input = $props[MinifyOption::MEDIA->value];

                if(!str_contains($input, "|")) {
                    $input = [$input];
                }else{
                    $input = explode("|", $input);
                }

                $mq = [];
                foreach($input as $item) {
                    if(array_key_exists(trim($item), $this->media)) {
                        $mq[] = $this->media[trim($item)];
                    }
                }

                $code = "@media".implode(',',$mq)."{".$code."}";
            }

            $this->{$type === self::$SCRIPT ? 'scripts' : 'styles'}[$scope][] = $code;

            return "";
        }, $content);


        foreach ($this->styles as $name => $style){
            $content = preg_replace('/(<\/head>)/is', "<style id='{$name}'>".implode($style)."</style>", $content, 1);
        }

        foreach ($this->scripts as $name => $script){
            $scriptTag = "<script id='{$name}'>".implode($script)."</script>";
            $content = preg_replace('/(<\/body>)/is', $scriptTag."\n$1", $content, 1);
        }

        return self::minifyHtml($content);
    }

    public static function minifyHtml(string $html): string {
        // Remove HTML comments (but not IE conditional comments)
        $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html);

        // Remove whitespace
        $html = preg_replace('/\s+/', ' ', $html);

        // Remove whitespace around HTML tags
        $html = preg_replace('/\s*(<\/[^>]+>)\s*/', '$1', $html);
        $html = preg_replace('/\s*(<[^>\/]+[^>]*>)\s*/', '$1', $html);

        // Remove trailing whitespace and multiple spaces
        $html = preg_replace('/ {2,}/', ' ', $html);

        return preg_replace('/>\s+</', '><', $html);
    }


    /**
     * Minify CSS content
     */
    public static function minifyCSS(string $css): string {

        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove whitespace around selectors, properties, and values
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        $css = preg_replace('/\s+/', ' ', $css);

        // Remove last semicolon in each rule
        $css = str_replace(';}', '}', $css);

        // Shorter color values
        $css = preg_replace('/#([a-f0-9])\1([a-f0-9])\2([a-f0-9])\3/i', '#$1$2$3', $css);

        // Remove px from 0px
        $css = preg_replace('/(\s|:)0(px|em|ex|pt|pc|%|in|cm|mm|rem|vw|vh|vmin|vmax)/i', '$1' . '0', $css);

        // 0.6 to .6
        $css = preg_replace('/(:|\s)0+\.(\d+)/', '$1.$2', $css);


        return trim($css);
    }

    /**
     * Minify JS content
     */
    public static function minifyJS(string $js): string {
        // Remove comments (both single and multi-line)
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        $js = preg_replace('!//.*!', '', $js);

        // Basic whitespace removal (more advanced processing would require a proper parser)
        $js = preg_replace('/\s+/', ' ', $js);

        // Remove whitespace around operators and punctuation
        $js = preg_replace('/\s*([=+\-*\/,;{}()<>[\]])\s*/', '$1', $js);

        return trim($js);
    }

}