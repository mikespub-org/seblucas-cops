<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Data;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;

/**
 * Summary of Response
 * @todo class Response extends \Symfony\Component\HttpFoundation\Response ?
 */
class Response
{
    public const SYMFONY_RESPONSE = '\Symfony\Component\HttpFoundation\Response';

    /** @var class-string */
    public static $handler = HtmlHandler::class;

    protected int $statusCode = 200;
    protected ?string $mimetype = null;
    protected ?int $expires = null;
    protected ?string $filename = null;
    protected ?string $content = null;
    protected ?\Closure $callback = null;
    protected bool $sent = false;
    /** @var array<string, mixed> */
    protected array $headers = [];

    /**
     * Summary of getMimeType
     * @param string $filepath
     * @return ?string mimetype for known extension or existing file, or null if undefined
     */
    public static function getMimeType($filepath)
    {
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);
        if (array_key_exists($extension, Data::$mimetypes)) {
            $mimetype = Data::$mimetypes[$extension];
        } elseif (file_exists($filepath)) {
            $mimetype = mime_content_type($filepath);
            if (!$mimetype) {
                $mimetype = 'application/octet-stream';
            }
        } else {
            // undefined mimetype - do not set Content-Type
            $mimetype = null;
        }
        return $mimetype;
    }

    /**
     * Summary of __construct
     * @param ?string $mimetype with null = no mimetype, '...' = actual mimetype for Content-Type
     * @param ?int $expires with null = no cache control, 0 = default expiration, > 0 actual expiration
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @return void
     */
    public function __construct($mimetype = null, $expires = null, $filename = null)
    {
        $this->setHeaders($mimetype, $expires, $filename);
        $this->sent = false;
    }

    /**
     * Summary of setHeaders
     * @param ?string $mimetype with null = no mimetype, '...' = actual mimetype for Content-Type
     * @param ?int $expires with null = no cache control, 0 = default expiration, > 0 actual expiration
     * @param ?string $filename with null = no disposition, '' = inline, '...' = attachment filename
     * @return static
     */
    public function setHeaders($mimetype = null, $expires = null, $filename = null): static
    {
        $this->mimetype = $mimetype;
        $this->expires = $expires;
        $this->filename = $filename;
        return $this;
    }

    /**
     * Summary of addHeader
     * @param string $name
     * @param mixed $value
     * @return Response
     */
    public function addHeader($name, $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Summary of setStatusCode
     * @param int $statusCode
     * @return Response
     */
    public function setStatusCode($statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Summary of setCallback
     * @todo possibly use to send file or zipstream later in response handler?
     * @param \Closure|callable $callback
     * @return static
     */
    public function setCallback($callback): static
    {
        if ($callback instanceof \Closure) {
            $this->callback = $callback;
        } else {
            $this->callback = \Closure::fromCallable($callback);
        }
        return $this;
    }

    /**
     * Summary of sendHeaders
     * @return static
     */
    public function sendHeaders(?int $statusCode = null): static
    {
        if (headers_sent()) {
            return $this;
        }

        if (is_null($this->expires)) {
            // no cache control
        } elseif (empty($this->expires)) {
            // use default expiration (14 days)
            $this->expires = 60 * 60 * 24 * 14;
        }
        if (!empty($this->expires)) {
            $this->addHeader('Pragma', 'public');
            $this->addHeader('Cache-Control', 'max-age=' . (string) $this->expires);
            $this->addHeader('Expires', gmdate('D, d M Y H:i:s', time() + $this->expires) . ' GMT');
        }

        if (!empty($this->mimetype)) {
            $this->addHeader('Content-Type', $this->mimetype);
        }

        if (is_null($this->filename)) {
            // no content disposition
        } elseif (empty($this->filename)) {
            $this->addHeader('Content-Disposition', 'inline');
        } else {
            $this->addHeader('Content-Disposition', 'attachment; filename="' . basename($this->filename) . '"');
        }

        foreach ($this->headers as $name => $value) {
            if (isset($value)) {
                header($name . ': ' . (string) $value);
            } else {
                header($name);
            }
        }

        // let PHP handle RFC 2616 (HTTP/1.1) vs RFC 3875 (CGI/1.1)
        // @see https://www.php.net/manual/en/ini.core.php#ini.cgi.rfc2616-headers
        $statusCode ??= $this->statusCode;
        if ($statusCode !== 200) {
            http_response_code($statusCode);
        }

        return $this;
    }

    /**
     * Summary of setContent
     * @param ?string $content actual data
     * @return static
     */
    public function setContent($content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Summary of getContent
     * @return string|null
     */
    public function getContent(): string|null
    {
        return $this->content;
    }

    /**
     * Summary of sendContent
     * @return static
     */
    public function sendContent(): static
    {
        // @todo check callback
        if (isset($this->content)) {
            echo $this->content;
        }

        return $this;
    }

    /**
     * Summary of prepare
     * @todo dummy method for now
     * @see https://symfony.com/doc/current/components/http_foundation.html#sending-the-response
     *
     * @param Request $request
     * @return static
     */
    public function prepare($request): static
    {
        return $this;
    }

    /**
     * Summary of send
     * @return static
     */
    public function send(bool $flush = true): static
    {
        if ($this->sent) {
            return $this;
        }
        $this->sent = true;

        $this->sendHeaders();
        // @todo check callback
        $this->sendContent();

        return $this;
    }

    /**
     * Summary of isSent
     * @param ?bool $sent
     * @return bool
     */
    public function isSent($sent = null): bool
    {
        // set sent for Zipper etc.
        if (!is_null($sent)) {
            $this->sent = $sent;
        }
        return $this->sent;
    }

    /**
     * Summary of notFound
     * @param ?Request $request
     * @param string|null $error
     * @return self
     */
    public static function notFound($request = null, $error = null): self
    {
        $response = new self();
        $response->setStatusCode(404);

        $data = ['link' => self::$handler::link()];
        $data['error'] = htmlspecialchars($error ?? "I'm sorry Dave, I'm afraid I can't do that");
        $template = 'templates/notfound.html';
        $response->setContent(Format::template($data, $template));
        return $response;
    }

    /**
     * Summary of sendError
     * @param ?Request $request
     * @param string|null $error
     * @param array<string, mixed> $params
     * @return self
     */
    public static function sendError($request = null, $error = null, $params = ['page' => 'index', 'db' => 0, 'vl' => 0]): self
    {
        $response = new self();
        $response->setStatusCode(404);

        $data = ['link' => self::$handler::route(PageId::ROUTE_INDEX, $params)];
        $data['error'] = htmlspecialchars($error ?? 'Unknown Error');
        $template = 'templates/error.html';
        $response->setContent(Format::template($data, $template));
        return $response;
    }

    /**
     * Summary of redirect
     * @param string $location
     * @return self
     */
    public static function redirect($location): self
    {
        $response = new self();
        $response->addHeader('Location', $location);
        return $response;
    }
}
