<?php

namespace App\Classes;

class HtmlHelper
{
    // See http://www.php.net/manual/en/class.domelement.php#101243
    public static function getInnerHtml($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }
}
