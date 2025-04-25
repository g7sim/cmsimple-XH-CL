<?php

declare(strict_types=1); 

namespace XH;

class LinkChecker
{
     private const FAILURE_STATUSES = [
        400, 403, 404, 405, 410, 500,
        Link::STATUS_INTERNALFAIL,
        Link::STATUS_EXTERNALFAIL,
        Link::STATUS_CONTENT_NOT_FOUND,
        Link::STATUS_FILE_NOT_FOUND,
        Link::STATUS_ANCHOR_MISSING
    ];

    /**
     * Prepares the link check interface.
     *
     * @return string HTML
     */
    public function prepare(): string
    {
        global $sn, $pth, $tx;

        if (!isset($sn, $pth['folder']['corestyle'], $tx['link']['checking'])) {
             error_log('LinkChecker Error: Missing required global variables in prepare()');
             return '<div id="xh_linkchecker" style="color: red;">Error: Link checker configuration is incomplete.</div>';
        }

        $url = htmlspecialchars($sn . '?&amp;xh_do_validate', ENT_QUOTES, 'UTF-8');
        $loaderImg = htmlspecialchars($pth['folder']['corestyle'] . 'ajax-loader-bar.gif', ENT_QUOTES, 'UTF-8');
        $altText = htmlspecialchars($tx['link']['checking'] ?? 'Checking...', ENT_QUOTES, 'UTF-8');

        $o = '<div id="xh_linkchecker" data-url="' . $url . '">'
            . '<img src="' . $loaderImg . '" width="128" height="15" alt="' . $altText . '">'
            . '</div>';
        return $o;
    }

    /**
     * Handles the actual link check request (synchronous).
     * Outputs the results directly.
     *
     * @return void
     */
    public function doCheck(): void
    {       
		if (function_exists('set_time_limit')) {
             @set_time_limit(300); 
        }

        header('Content-Type: text/plain; charset=utf-8');
        try {
            echo $this->checkLinks();
        } catch (\Throwable $e) {
            error_log("LinkChecker Error during checkLinks: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            echo "An internal error occurred during the link check process. Please check the server logs.";
        }
        exit;
    }

    /**
     * Checks all links gathered from the content and returns the result view.
     *
     * @return string HTML report
     */
    public function checkLinks(): string
    {
        $linksByPage = $this->gatherLinks(); 
        $hints = []; 

        foreach ($linksByPage as $pageIndex => $pageLinks) {
            if (empty($pageLinks)) {
                continue;
            }
            
            foreach ($pageLinks as $link) {
                try {
                    $this->determineLinkStatus($link);

                    $status = $link->getStatus();

                    if (($status !== 200) && ($status !== Link::STATUS_NOT_CHECKED)) {
                        $type = in_array($status, self::FAILURE_STATUSES) ? 'errors' : 'caveats';
                        $hints[$pageIndex][$type][] = $link;
                    }
                } catch (\Throwable $e) {
                     error_log("LinkChecker: Error processing link '{$link->getURL()}' on page index {$pageIndex}: " . $e->getMessage());
                    
                     $link->setStatus(Link::STATUS_INTERNALFAIL); 
                     $hints[$pageIndex]['errors'][] = $link; 
                }
            }
        }

        return $this->message($this->countLinks($linksByPage), $hints);
    }

    /**
     * Gathers all links in the content, grouped by page index.
     *
     * @return array<int, array<Link>> Links grouped by page index [$pageIndex => [Link, Link,...]]
     */
    private function gatherLinks(): array
    {
        global $c, $u, $cl; 

        $linksByPage = [];
        for ($i = 0; $i < $cl; $i++) {
            $linksByPage[$i] = [];
            $content = $c[$i] ?? ''; 
            if (empty($content)) {
                continue;
            }

            $pattern = '/<a\s+[^>]*?href\s*=\s*(["\']?)(.*?)\1[^>]*?>(.*?)<\/a>/is';
            preg_match_all($pattern, $content, $pageLinks, PREG_SET_ORDER);

            if (!empty($pageLinks)) {
                foreach ($pageLinks as $match) {
                   $url = trim($match[2] ?? '');
                    if ($url === '') {
                        continue; 
                    }

                    $url = str_replace('&amp;', '&', $url); 

                    if (strpos($url, '#') === 0) {
                        $pageSlug = $u[$i] ?? ''; 
                        $url = '?' . $pageSlug . $url;
                    }

                    $text = strip_tags($match[3] ?? ''); 
                    $text = trim(preg_replace('/\s+/', ' ', $text)); 
                    $text = $text ?: $url;

                    try {
                        
                        $linksByPage[$i][] = new Link($url, $text);
                    } catch (\Throwable $e) {
                         error_log("LinkChecker: Failed to instantiate Link object for URL '{$url}': " . $e->getMessage());
                         
                    }
                }
            }
        }
        return $linksByPage;
    }

    /**
     * Returns the total number of links found across all pages.
     *
     * @param array<int, array<Link>> $linksByPage An array of page links.
     * @return int Total number of links.
     */
    private function countLinks(array $linksByPage): int
    {
        return array_sum(array_map('count', $linksByPage));
    }

    /**
     * Determines the status of a single link by parsing its URL and dispatching to checkers.
     * Modifies the Link object's status directly.
     *
     * @param Link $link The Link object to check.
     * @return void
     */
    public function determineLinkStatus(Link $link): void
    {
        global $cf; 

        $url = $link->getURL();
        
        if (strlen($url) > 2083) { 
             $link->setStatus(Link::STATUS_INTERNALFAIL); 
             return;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            error_log("LinkChecker: Failed to parse URL: " . $url);
            $link->setStatus(Link::STATUS_INTERNALFAIL); 
            return;
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $status = Link::STATUS_NOT_CHECKED; 

        switch ($scheme) {
            case 'http':
            case 'https':
                $status = $this->checkExternalLink($parts);
                break;
            case 'mailto':
                $status = !empty($cf['link']['mailto'])
                    ? Link::STATUS_MAILTO
                    : Link::STATUS_NOT_CHECKED;
                break;
            case 'tel':
                 
                $status = !empty($cf['link']['tel'])
                    ? Link::STATUS_TEL
                    : Link::STATUS_NOT_CHECKED;
                break;
            case '': 
            case 'file': 
                $status = $this->checkInternalLink($parts);
                break;
            default:
                $status = !empty($cf['link']['unknown_scheme'])
                    ? Link::STATUS_UNKNOWN
                    : Link::STATUS_NOT_CHECKED;
                break;
        }

        $link->setStatus($status);
    }

    /**
     * Checks an internal link (relative path, query string, fragment).
     *
     * @param array $test URL parts from parse_url.
     * @return int Status code (e.g., 200, Link::STATUS_*, 404).
     */
    private function checkInternalLink(array $test): int
    {
        global $c, $u, $cl, $pth, $cf, $plugin_cf; 

        if (isset($test['path']) && !isset($test['query']) && strpos($test['path'], '.') !== false) {
            $filename = urldecode($test['path']);
            if (strpos($filename, '..') === false) {
                 
                 $basePath = $pth['folder']['base'] ?? $_SERVER['DOCUMENT_ROOT'] ?? '';
                 $fullPath = rtrim($basePath, '/') . '/' . ltrim($filename, '/');               

                 if (is_file($fullPath) && is_readable($fullPath) /* && $isPublicPath */) {
                     return 200;
                 } else {
                     
                 }
            }
             
        }
        
        $queryString = $test['query'] ?? null;
        $fragment = $test['fragment'] ?? null;

        if (!$queryString) {
             
             return Link::STATUS_INTERNALFAIL;
        }
        
        parse_str(html_entity_decode($queryString), $queryParams);

        $downloadKey = null;
        if (isset($queryParams['download'])) $downloadKey = 'download';
        elseif (isset($queryParams['&download'])) $downloadKey = '&download'; 

        if ($downloadKey !== null && isset($pth['folder']['downloads'])) {
            $downloadFile = trim($queryParams[$downloadKey] ?? '');
            
            if ($downloadFile !== '' && strpos($downloadFile, '..') === false && strpos($downloadFile, '/') === false) {
                $filePath = rtrim($pth['folder']['downloads'], '/') . '/' . $downloadFile;
                return (is_file($filePath) && is_readable($filePath)) ? 200 : Link::STATUS_FILE_NOT_FOUND;
            } else {
                error_log("LinkChecker: Invalid download path detected: " . ($queryParams[$downloadKey] ?? ''));
                return Link::STATUS_FILE_NOT_FOUND; 
            }
        }
        
        list($pageSlug) = explode('&', $queryString, 2); 
        
        if ($pageSlug === 'sitemap') return 200; 
        if ($pageSlug === 'mailform' && !empty($cf['mailform']['email'])) return 200; 

        $targetPageIndex = array_search($pageSlug, $u);

        if ($targetPageIndex !== false) {
            
            if (!$fragment) {
                return 200; 
            }

            $anchorPattern = '/<[^>]*\s+(?:id|name)\s*=\s*["\']' . preg_quote($fragment, '/') . '["\'][^>]*>/i';
            if (isset($c[$targetPageIndex]) && preg_match($anchorPattern, $c[$targetPageIndex])) {
                return 200; 
            }
            
			static $templateContent = null; 
            if ($templateContent === null) {
                 $templatePath = $pth['file']['template'] ?? null;
                 if ($templatePath && is_file($templatePath) && is_readable($templatePath)) {
                     $templateContent = file_get_contents($templatePath);
                 } else {
                     $templateContent = false; 
                     error_log("LinkChecker: Template file not found or not readable: " . ($templatePath ?? 'N/A'));
                 }
            }
            if ($templateContent && preg_match($anchorPattern, $templateContent)) {
                return 200; 
            }

            return Link::STATUS_ANCHOR_MISSING; 
        }

        if (isset($test['path'])
            && function_exists('XH_isLanguageFolder')
            && function_exists('XH_readContents')
            && preg_match('/\/([a-z]{2})\/?$/i', $test['path'], $matches) 
            && \XH_isLanguageFolder($matches[1])
        ) {
            $lang = $matches[1];
            $langContent = \XH_readContents($lang); 

            if ($langContent && isset($langContent['urls'])) {
                $langTargetPageIndex = array_search($pageSlug, $langContent['urls']);
                if ($langTargetPageIndex !== false) {
                    
                    if (!$fragment) return 200;

                    $langAnchorPattern = '/<[^>]*\s+(?:id|name)\s*=\s*["\']' . preg_quote($fragment, '/') . '["\'][^>]*>/i';
                    if (isset($langContent['pages'][$langTargetPageIndex]) && preg_match($langAnchorPattern, $langContent['pages'][$langTargetPageIndex])) {
                        return 200;
                    }
                    
                    if (isset($templateContent) && $templateContent && preg_match($langAnchorPattern, $templateContent)) {
                        return 200;
                    }
                    return Link::STATUS_ANCHOR_MISSING;
                }
            } else {
                
            }
        }        
        error_log("LinkChecker: Internal link check failed for query '{$queryString}' (fragment: '{$fragment}')");
        return Link::STATUS_INTERNALFAIL;
    }

    /**
     * Checks an external link using a HEAD request.
     *
     * @param array $parts URL parts from parse_url.
     * @return int HTTP Status code or Link::STATUS_EXTERNALFAIL.
     */
    private function checkExternalLink(array $parts): int
    {        
        if (function_exists('set_time_limit')) {
             @set_time_limit(60); 
        }

        $path = $parts['path'] ?? '/';
        if (isset($parts['query'])) {
            $path .= "?" . $parts['query'];
        }
        
        $status = $this->makeHeadRequest(
            $parts['scheme'],
            $parts['host'],
            $path,
            $parts['port'] ?? null 
        );
        
        return ($status !== false) ? $status : Link::STATUS_EXTERNALFAIL;
    }

    /**
     * Makes a HEAD request using cURL or fallback to get_headers.
     * Returns the HTTP status code or false on complete failure.
     *
     * @param string $scheme http or https.
     * @param string $host Host name.
     * @param string $path Absolute path including query string.
     * @param ?int $port Optional port number.
     *
     * @return int|false Status code or false on failure.
     */
    protected function makeHeadRequest(string $scheme, string $host, string $path, ?int $port = null): int|false
    {
        global $cf; 
        
        $timeout = (int) ($cf['link']['timeout'] ?? 10); 
        $connect_timeout = (int) ($cf['link']['connect_timeout'] ?? 5); 
        $maxredir = (int) ($cf['link']['redir'] ?? 5); 
        $agent = $cf['link']['user_agent'] ?? 'CMSimple_XH_LinkChecker/1.0'; 
        $verifySsl = (bool) ($cf['link']['ssl_verify'] ?? true); 
    
        $url = $scheme . '://' . $host . ($port ? ':' . $port : '') . $path;

        if (extension_loaded('curl')) {
            $ch = curl_init();
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,         
                CURLOPT_NOBODY => true,         
                CURLOPT_USERAGENT => $agent,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => $connect_timeout,
                CURLOPT_FRESH_CONNECT => true,  
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0, 
                // CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, 
            ];

            if ($maxredir > 0) {
                $options[CURLOPT_FOLLOWLOCATION] = true; 
                $options[CURLOPT_MAXREDIRS] = $maxredir;
            }

            curl_setopt_array($ch, $options);

            $headerContent = curl_exec($ch); 
            $curlErrorNo = curl_errno($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
            curl_close($ch);

            if ($curlErrorNo !== 0) {
                 error_log("LinkChecker cURL Error for $url: [$curlErrorNo] $curlError");
                 return false;
            }
            
            if ($httpCode > 0) {
                return (int) $httpCode; 
            } else {
                 error_log("LinkChecker cURL Warning for $url: Received HTTP status code 0.");
                }
        }

        if (function_exists('get_headers') && ini_get('allow_url_fopen')) {
            $contextOptions = [
                'http' => [
                    'method' => 'HEAD', 
                    'timeout' => $timeout,
                    'max_redirects' => $maxredir + 1, 
                    'user_agent' => $agent,
                    'ignore_errors' => true, 
                ],
                 'ssl' => [ 
                    'verify_peer' => $verifySsl,
                    'verify_peer_name' => $verifySsl,
                    // 'allow_self_signed' => !$verifySsl, 
                 ]
            ];
            $context = stream_context_create($contextOptions);

            $headers = @get_headers($url, true, $context); 

            if ($headers !== false && !empty($headers)) {
                $statusLine = null;
                
                $lastResponseHeaders = end($headers); 

                if (is_array($lastResponseHeaders) && isset($lastResponseHeaders[0]) && str_starts_with($lastResponseHeaders[0], 'HTTP/')) {
                    
                    $statusLine = $lastResponseHeaders[0];
                } elseif (isset($headers[0]) && is_string($headers[0]) && str_starts_with($headers[0], 'HTTP/')) {
                    
                    $statusLine = $headers[0];
                } else {
                    
                    if(is_array($lastResponseHeaders)) {
                        foreach($lastResponseHeaders as $headerLine) {
                            if (is_string($headerLine) && str_starts_with($headerLine, 'HTTP/')) {
                                $statusLine = $headerLine;
                                break;
                            }
                        }
                    }
                }

                if ($statusLine && preg_match('#^HTTP/\d\.\d\s+(\d{3})#i', $statusLine, $matches)) {
                    return (int) $matches[1]; 
                } else {
                     error_log("LinkChecker get_headers Warning for $url: Could not parse status line from headers: " . print_r($headers, true));
                }
            } else {
                
                 error_log("LinkChecker get_headers failed for $url");
            }
        } elseif (!extension_loaded('curl')) {
             error_log("LinkChecker Error: Neither cURL extension nor allow_url_fopen with get_headers is available.");
        }
        
        return false;
    }

    /**
     * Returns the HTML report for a single error.
     *
     * @param Link $link A link object with an error status.
     * @return string HTML list item.
     */
    public function reportError(Link $link): string
    {
        global $tx; 

        $urlEsc = htmlspecialchars($link->getURL(), ENT_QUOTES, 'UTF-8');
        $textEsc = htmlspecialchars($link->getText(), ENT_QUOTES, 'UTF-8');

        $o = '<li>' . "\n"
            . '<b>' . ($tx['link']['link'] ?? 'Link:') . '</b> '
            . '<a target="_blank" rel="noopener noreferrer" href="' . $urlEsc . '">' . $textEsc . '</a>'
            . '<br>' . "\n"
            . '<b>' . ($tx['link']['linked_page'] ?? 'Source URL:') . '</b> ' . $urlEsc
            . '<br>' . "\n"
            . '<b>' . ($tx['link']['error'] ?? 'Error:') . '</b> ';

        $status = $link->getStatus();
        $errorMessage = match ($status) {
            Link::STATUS_INTERNALFAIL => $tx['link']['int_error'] ?? 'Internal link invalid or page not found.',
            Link::STATUS_CONTENT_NOT_FOUND => $tx['link']['content_not_found'] ?? 'Content file for language not found.',
            Link::STATUS_ANCHOR_MISSING => $tx['link']['int_error_fragment'] ?? 'Internal link valid, but anchor/fragment not found.',
            Link::STATUS_EXTERNALFAIL => $tx['link']['ext_error_domain'] ?? 'Could not reach external domain or connection error.',
            Link::STATUS_FILE_NOT_FOUND => $tx['link']['file_not_found'] ?? 'Download file not found.',
            default => ($tx['link']['ext_error_page'] ?? 'External page error.')
                       . '<br>' . "\n" . '<b>' . ($tx['link']['returned_status'] ?? 'Returned Status:') . '</b> ' . $status,
        };

        $o .= $errorMessage . "\n" . '</li>' . "\n";
        return $o;
    }

    /**
     * Returns the HTML report for a single notice/caveat.
     *
     * @param Link $link A link object with a non-error, non-200 status.
     * @return string HTML list item.
     */
    public function reportNotice(Link $link): string
    {
        global $tx; 

        $urlEsc = htmlspecialchars($link->getURL(), ENT_QUOTES, 'UTF-8');
        $textEsc = htmlspecialchars($link->getText(), ENT_QUOTES, 'UTF-8');

        $o = '<li>' . "\n"
            . '<b>' . ($tx['link']['link'] ?? 'Link:') . '</b> '
            . '<a target="_blank" rel="noopener noreferrer" href="' . $urlEsc . '">' . $textEsc . '</a>'
            . '<br>' . "\n"
            . '<b>' . ($tx['link']['linked_page'] ?? 'Source URL:') . '</b> ' . $urlEsc
            . '<br>' . "\n";

        $status = $link->getStatus();
        $noticeMessage = '';

        switch ($status) {
            case Link::STATUS_MAILTO:
                $noticeMessage = '<b>' . ($tx['link']['hint'] ?? 'Hint:') . '</b> ' . ($tx['link']['email'] ?? 'Mailto link (not automatically checkable).');
                break;
            case Link::STATUS_TEL:
                $noticeMessage = '<b>' . ($tx['link']['hint'] ?? 'Hint:') . '</b> ' . ($tx['link']['tel'] ?? 'Telephone link (not automatically checkable).');
                break;
            case Link::STATUS_UNKNOWN:
                 $noticeMessage = '<b>' . ($tx['link']['hint'] ?? 'Hint:') . '</b> ' . ($tx['link'][Link::STATUS_UNKNOWN] ?? 'Link uses an unknown protocol.');
                 break;
            default:
                
                if ($status >= 300 && $status < 400) {
                    $noticeMessage .= '<b>' . ($tx['link']['hint'] ?? 'Hint:') . '</b> '
                                   . ($tx['link']['redirect'] ?? 'Redirect detected.') . '<br>' . "\n";
                } else {
                     $noticeMessage .= '<b>' . ($tx['link']['hint'] ?? 'Hint:') . '</b> '
                                    . ($tx['link']['other_status'] ?? 'Non-standard status received.') . '<br>' . "\n";
                }
                $noticeMessage .= '<b>' . ($tx['link']['returned_status'] ?? 'Returned Status:') . '</b> ' . $status;
        }

        $o .= $noticeMessage . "\n" . '</li>' . "\n";
        return $o;
    }

    /**
     * Returns the final HTML report summarizing the link check results.
     *
     * @param int $checkedLinks The total number of links gathered.
     * @param array<int, array{'errors'?: Link[], 'caveats'?: Link[]}> $hints Errors/caveats grouped by page index.
     * @return string HTML report.
     */
    public function message(int $checkedLinks, array $hints): string
    {
        global $tx, $h, $u; 
        
        $key = 'checked' . \XH_numberSuffix($checkedLinks); 
        $checkedText = sprintf($tx['link'][$key] ?? '%d links checked.', $checkedLinks);
        $o = "\n" . '<p>' . htmlspecialchars($checkedText, ENT_QUOTES, 'UTF-8') . '</p>' . "\n";

        if (empty($hints)) {
            $o .= '<p><b>' . htmlspecialchars($tx['link']['check_ok'] ?? 'All links seem okay!', ENT_QUOTES, 'UTF-8') . '</b></p>' . "\n";
            return $o;
        }

        $o .= '<p><b>' . htmlspecialchars($tx['link']['check_errors'] ?? 'Link check found issues:', ENT_QUOTES, 'UTF-8') . '</b></p>' . "\n";
             
        ksort($hints);

        foreach ($hints as $pageIndex => $problems) {
            $pageTitle = htmlspecialchars($h[$pageIndex] ?? ('Page Index ' . $pageIndex), ENT_QUOTES, 'UTF-8');
            $pageUrl = htmlspecialchars('?' . ($u[$pageIndex] ?? ''), ENT_QUOTES, 'UTF-8');

            $o .= '<hr>' . "\n\n"
                . '<h2>' . ($tx['link']['page'] ?? 'Page:') . ' '
                . '<a href="' . $pageUrl . '">' . $pageTitle . '</a></h2>' . "\n";

            if (!empty($problems['errors'])) {
                $o .= '<h3>' . htmlspecialchars($tx['link']['errors'] ?? 'Errors', ENT_QUOTES, 'UTF-8') . '</h3>' . "\n"
                    . '<ul style="list-style: disc; margin-left: 25px;">' . "\n";
                foreach ($problems['errors'] as $link) {
                    $o .= $this->reportError($link);
                }
                $o .= '</ul>' . "\n" . "\n";
            }

            if (!empty($problems['caveats'])) {
                $o .= '<h3>' . htmlspecialchars($tx['link']['hints'] ?? 'Hints / Caveats', ENT_QUOTES, 'UTF-8') . '</h3>' . "\n"
                    . '<ul style="list-style: circle; margin-left: 25px;">' . "\n";
                foreach ($problems['caveats'] as $link) {
                    $o .= $this->reportNotice($link);
                }
                $o .= '</ul>' . "\n";
            }
        }
        return $o;
    }
}