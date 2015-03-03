<?PHP
function wpsecureops_scan_protect_get_ip_address()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function wpsecureops_scan_protect_normalize_line_endings($s)
{
    // Convert all line-endings to UNIX format
    $s = str_replace(array("\r\n", "\r"), "\n", $s);

    return $s;
}

function wpsecureops_scan_protect_wpsecureops_fnmatch($pattern, $string, $flags = 0)
{
    return function_exists("fnmatch") ? fnmatch($pattern, $string, $flags) : wpsecureops_scan_protect_pcrefnmatch($pattern, $string, $flags);
}

function wpsecureops_scan_protect_pcrefnmatch($pattern, $string, $flags = 0)
{
    $modifiers  = null;
    $transforms = array(
        '\*'      => '.*',
        '\?'      => '.',
        '\[\!'    => '[^',
        '\['      => '[',
        '\]'      => ']',
        '\.'      => '\.',
        '\\'      => '\\\\',
    );

    // Forward slash in string must be in pattern:
    if ($flags & FNM_PATHNAME) {
        $transforms['\*'] = '[^/]*';
    }

    // Back slash should not be escaped:
    if ($flags & FNM_NOESCAPE) {
        unset($transforms['\\']);
    }

    // Perform case insensitive match:
    if ($flags & FNM_CASEFOLD) {
        $modifiers .= 'i';
    }

    // Period at start must be the same as pattern:
    if ($flags & FNM_PERIOD) {
        if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) {
            return false;
        }
    }

    $pattern = '#^'
               . strtr(preg_quote($pattern, '#'), $transforms)
               . '$#'
               . $modifiers;

    return (boolean) preg_match($pattern, $string);
}
