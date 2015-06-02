<?php

namespace Octower\Util;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{

    public function normalizePath($path)
    {
        $parts = array();// Array to build a new path from the good parts
        $path = str_replace('\\', '/', $path);// Replace backslashes with forwardslashes
        $path = preg_replace('/\/+/', '/', $path);// Combine multiple slashes into a single slash
        $segments = explode('/', $path);// Collect path segments

        foreach ($segments as $segment) {
            if ($segment == '.') {
                continue;
            }

            $test = array_pop($parts);

            if (is_null($test)) {
                $parts[] = $segment;
            }
            elseif ($segment == '..') {
                if ($test == '..') {
                    $parts[] = $test;
                }

                if($test == '..' || $test == '') {
                    $parts[] = $segment;
                }
            }
            else {
                $parts[] = $test;
                $parts[] = $segment;
            }

        }

        return implode('/', $parts);
    }

}