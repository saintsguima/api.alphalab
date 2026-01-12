<?php
namespace App\Utils;

final class StrTpl
{
    public static function render(string $template, array $vars): string
    {
        $pairs = [];
        foreach ($vars as $k => $v) {
            $pairs['{' . $k . '}'] = (string) $v;
        }
        return strtr($template, $pairs);
    }
}
