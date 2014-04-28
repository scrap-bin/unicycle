<?php

namespace R2\HTTP;

class Request
{
    private $method;
    private $pathInfo;
    private $requestUri;
    private $baseUrl;

    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($_SERVER['REQUEST_METHOD']);

            if ('POST' === $this->method) {
                if (isset($_SERVER['X-HTTP-METHOD-OVERRIDE'])) {
                    $this->method = strtoupper($_SERVER['X-HTTP-METHOD-OVERRIDE']);
                } elseif (isset($_POST['_method'])) {
                    $this->method = strtoupper($_POST['_method']);
                }
            }
        }

        return $this->method;
    }

    public function getRequestUri()
    {
        if (isset($this->requestUri)) {
            return $this->requestUri;
        }

        $requestUri = '';

        if (isset($_SERVER['X_ORIGINAL_URL'])) {
            // IIS with Microsoft Rewrite Module
            $requestUri = $_SERVER['X_ORIGINAL_URL'];
        } elseif (isset($_SERVER['X_REWRITE_URL'])) {
            // IIS with ISAPI_Rewrite
            $requestUri = $_SERVER['X_REWRITE_URL'];
        } elseif (!empty($_SERVER['IIS_WasUrlRewritten']) && !empty($_SERVER['UNENCODED_URL'])) {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if ('' != $_SERVER['QUERY_STRING']) {
                $requestUri .= '?'.$_SERVER['QUERY_STRING'];
            }
        }

        return $this->requestUri = $requestUri;
    }

    public function getPathInfo()
    {
        if (isset($this->pathInfo)) {
            return $this->pathInfo;
        }

        $baseUrl = $this->getBaseUrl();

        if (null === ($requestUri = $this->getRequestUri())) {
            return $this->pathInfo = '/';
        }

        $pathInfo = '/';

        if (false !== ($pos = strpos($requestUri, '?'))) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (null !== $baseUrl && false === $pathInfo = substr($requestUri, strlen($baseUrl))) {
            return $this->pathInfo = '/';
        } elseif (null === $baseUrl) {
            return $this->pathInfo = $requestUri;
        }

        return $this->pathInfo = (string) $pathInfo;
    }

    public function getBaseUrl()
    {
        if (isset($this->baseUrl)) {
            return $this->baseUrl;
        }

        $filename = basename($_SERVER['SCRIPT_FILENAME']);

        if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            $path    = $_SERVER['PHP_SELF'];
            $file    = $_SERVER['SCRIPT_FILENAME'];
            $segs    = array_reverse(explode('/', trim($file, '/')));
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        $requestUri = $this->getRequestUri();
        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            return $this->baseUrl = $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl))) {
            return $this->baseUrl = rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }
        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            return $this->baseUrl = '';
        }

        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && $pos !== 0) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return $this->baseUrl = rtrim($baseUrl, '/');
    }

    private function getUrlencodedPrefix($string, $prefix)
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }
        $len = strlen($prefix);
        $matches = [];
        if (preg_match("#^(%[[:xdigit:]]{2}|.){{$len}}#x", $string, $matches)) {
            return $matches[0];
        }

        return false;
    }
    public function getSchemeAndHttpHost()
    {
        return $this->getScheme().'://'.$this->getHttpHost();
    }

    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    public function isSecure()
    {
        return isset($_SERVER['HTTPS']) &&
                ('on' == strtolower($_SERVER['HTTPS']) || 1 == $_SERVER['HTTPS']);

    }

    public function getHost()
    {
        $tmp = isset($_SERVER['HOST'])
            ? $_SERVER['HOST']
            : (isset($_SERVER['SERVER_NAME'])
                ? $_SERVER['SERVER_NAME']
                : $_SERVER['SERVER_ADDR']);

        $host = strtolower(preg_replace('/:\d+$/', '', trim($tmp)));
        if ($host && !preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host)) {
            throw new \UnexpectedValueException('Invalid Host "'.$host.'"');
        }

        return $host;
    }

    public function getPort()
    {
        if (isset($_SERVER['HOST'])) {
            $host = $_SERVER['HOST'];
            if (false !== $pos = strrpos($host, ':')) {
                return intval(substr($host, $pos + 1));
            }

            return 'https' === $this->getScheme() ? 443 : 80;
        }

        return $_SERVER['SERVER_PORT'];
    }

    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port   = $this->getPort();

        if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
            return $this->getHost();
        }

        return $this->getHost().':'.$port;
    }
}
