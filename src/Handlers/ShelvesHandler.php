<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\BookShelf;
use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;

/**
 * Handle server-side virtual shelf actions: toggle, add, remove, switch, create, rename, delete, export, import.
 * URL format: index.php/shelves/{action}
 */
class ShelvesHandler extends BaseHandler
{
    public const HANDLER = "shelves";
    public const PREFIX = "/shelves";
    public const PARAMLIST = ["action"];
    private const ID_PATTERN = '/^\d+$/';
    private const MISSING_SHELF_NAME = 'Missing shelf name';

    public static function getRoutes()
    {
        return [
            "shelves-action" => ["/shelves/{action}", [], ["GET", "POST"]],
        ];
    }

    public function handle($request)
    {
        if (!BookShelf::isServerShelvesEnabled($this->getConfig())) {
            return Response::sendError($request, 'Server shelves are not enabled');
        }

        $this->initializeSession($request);
        return $this->handleEnabledRequest($request);
    }

    private function handleEnabledRequest(Request $request): Response
    {
        $shelf = $this->loadShelf($request);
        $action = $request->get('action') ?? $request->post('action');
        $returnUrl = $request->get('return') ?? $request->post('return');
        if ($action === 'export') {
            return $this->exportShelf($request, $shelf);
        }

        $result = $this->executeAction($action, $request, $shelf);
        if ($request->isAjax() || $request->isJson()) {
            return $this->jsonResponse($result);
        }

        return $this->redirectResponse($returnUrl, $shelf);
    }

    private function initializeSession(Request $request): void
    {
        if (trim((string) $this->config('use_server_shelves')) !== BookShelf::MODE_SESSION) {
            return;
        }
        $session = $this->getContext()->getSession();
        $session->start();
        $request->setSession($session);
    }

    private function loadShelf(Request $request): BookShelf
    {
        $dbContext = new DatabaseContext($request->database(), $this->getConfig());
        $shelf = new BookShelf($request, $dbContext);
        $shelf->load();
        return $shelf;
    }

    /**
     * Summary of executeAction
     * @return array<mixed>
     */
    private function executeAction(string $action, Request $request, BookShelf $shelf): array
    {
        return match ($action) {
            'toggle', 'add', 'remove' => $this->handleBookAction($action, $request, $shelf),
            'switch' => $this->switchShelf($request, $shelf),
            'create' => $this->createShelf($request, $shelf),
            'rename' => $this->renameShelf($request, $shelf),
            'delete' => $this->deleteShelf($request, $shelf),
            'import' => $this->importShelf($request, $shelf),
            default => ['success' => false, 'error' => 'Unknown action: ' . $action],
        };
    }

    /**
     * Summary of handleBookAction
     * @return array{action: string, db: int, id: int, shelf: mixed, success: bool}|array{error: string, success: bool}
     */
    private function handleBookAction(string $action, Request $request, BookShelf $shelf): array
    {
        $id = $request->get('id', null, self::ID_PATTERN) ?? $request->post('id', null, self::ID_PATTERN);
        if ($id === null) {
            return ['success' => false, 'error' => 'Missing book id'];
        }

        $db = $request->get('db', null, self::ID_PATTERN) ?? $request->post('db', null, self::ID_PATTERN) ?? 0;
        $shelfName = $this->getShelfParameter($request, $shelf);
        $inShelf = null;
        if ($action === 'toggle') {
            $inShelf = $shelf->toggleBook((int) $id, (int) $db, $shelfName);
            $success = true;
        } else {
            $success = $action === 'add'
                ? $shelf->addBook((int) $id, (int) $db, $shelfName)
                : $shelf->removeBook((int) $id, (int) $db, $shelfName);
        }

        $result = [
            'success' => $success,
            'action' => $action,
            'id' => (int) $id,
            'db' => (int) $db,
            'shelf' => $shelfName ?? $shelf->getActiveShelfName(),
        ];
        if ($action === 'toggle') {
            $result['success'] = true;
            $result['inShelf'] = $inShelf;
        }
        return $result;
    }

    /**
     * Summary of switchShelf
     * @return array{action: string, shelf: string, success: bool}|array{error: string, success: bool}
     */
    private function switchShelf(Request $request, BookShelf $shelf): array
    {
        $name = $this->getShelfParameter($request, $shelf)
            ?? $request->get('name')
            ?? $request->post('name');
        if (empty($name)) {
            return ['success' => false, 'error' => self::MISSING_SHELF_NAME];
        }
        $name = $shelf->resolveShelfName((string) $name) ?? $name;
        $switched = $shelf->setActiveShelf($name);
        return [
            'success' => $switched,
            'action' => 'switch',
            'shelf' => $shelf->getActiveShelfName(),
        ];
    }

    /**
     * Summary of createShelf
     * @return array{action: string, shelf: mixed, success: bool}|array{error: string, success: bool}
     */
    private function createShelf(Request $request, BookShelf $shelf): array
    {
        $name = $request->post('name') ?? $request->get('name');
        if (empty($name)) {
            return ['success' => false, 'error' => self::MISSING_SHELF_NAME];
        }
        $created = $shelf->createShelf($name);
        if ($created) {
            $shelf->setActiveShelf($name);
        }
        return ['success' => $created, 'action' => 'create', 'shelf' => $name];
    }

    /**
     * Summary of renameShelf
     * @return array{action: string, new: mixed, old: mixed, success: bool}|array{error: string, success: bool}
     */
    private function renameShelf(Request $request, BookShelf $shelf): array
    {
        $oldName = $this->getShelfParameter($request, $shelf)
            ?? $request->post('old')
            ?? $request->get('old');
        $newName = $request->post('name')
            ?? $request->get('name')
            ?? $request->post('new')
            ?? $request->get('new');
        if (empty($oldName) || empty($newName)) {
            return ['success' => false, 'error' => self::MISSING_SHELF_NAME];
        }
        $oldName = $shelf->resolveShelfName((string) $oldName) ?? $oldName;
        $renamed = $shelf->renameShelf($oldName, $newName);
        return ['success' => $renamed, 'action' => 'rename', 'old' => $oldName, 'new' => $newName];
    }

    /**
     * Summary of deleteShelf
     * @return array{action: string, shelf: mixed, success: bool}|array{error: string, success: bool}
     */
    private function deleteShelf(Request $request, BookShelf $shelf): array
    {
        $name = $this->getShelfParameter($request, $shelf)
            ?? $request->post('name')
            ?? $request->get('name');
        if (empty($name)) {
            return ['success' => false, 'error' => self::MISSING_SHELF_NAME];
        }
        $name = $shelf->resolveShelfName((string) $name) ?? $name;
        $deleted = $shelf->deleteShelf($name);
        return ['success' => $deleted, 'action' => 'delete', 'shelf' => $name];
    }

    private function exportShelf(Request $request, BookShelf $shelf): Response
    {
        $shelfName = $this->getShelfParameter($request, $shelf) ?? $shelf->getActiveShelfName();
        $response = new Response(Response::MIME_TYPE_JSON);
        $response->addHeader('Content-Disposition', 'attachment; filename="cops_shelves_export.json"');
        $response->setContent($shelf->exportData($shelfName));
        return $response;
    }

    /**
     * Summary of importShelf
     * @return array{action: string, mode: mixed, success: bool}|array{error: string, success: bool}
     */
    private function importShelf(Request $request, BookShelf $shelf): array
    {
        $mode = $request->post('mode') ?? $request->get('mode') ?? 'merge';
        $shelfName = $this->getShelfParameter($request, $shelf);
        if ($shelfName !== null) {
            $shelf->setActiveShelf($shelfName);
        }
        $importFile = $request->files('import');
        if (!empty($importFile['tmp_name']) && is_uploaded_file($importFile['tmp_name'])) {
            $payload = file_get_contents($importFile['tmp_name']);
        } else {
            $payload = $request->post('data') ?? $request->get('data');
        }
        if (empty($payload)) {
            return ['success' => false, 'error' => 'Missing import data'];
        }
        $imported = $shelf->importData($payload, $mode);
        return ['success' => $imported, 'action' => 'import', 'mode' => $mode];
    }

    private function getShelfParameter(Request $request, BookShelf $shelf): ?string
    {
        $name = $request->get(BookShelf::URL_PARAM) ?? $request->post(BookShelf::URL_PARAM);
        if (!is_string($name) || $name === '') {
            return null;
        }
        return $shelf->resolveShelfName($name) ?? $name;
    }

    /**
     * Summary of jsonResponse
     * @param array<mixed> $result
     */
    private function jsonResponse(array $result): Response
    {
        $response = new Response(Response::MIME_TYPE_JSON);
        $response->setContent(json_encode($result));
        return $response;
    }

    private function redirectResponse(mixed $returnUrl, BookShelf $shelf): Response
    {
        if ($this->isInternalReturnUrl($returnUrl)) {
            return Response::redirect($returnUrl);
        }
        $redirect = PageHandler::page(PageId::SHELVES, [
            BookShelf::URL_PARAM => $shelf->getActiveShelfName(),
        ]);
        return Response::redirect($redirect);
    }

    private function isInternalReturnUrl(mixed $url): bool
    {
        if (!is_string($url) || $url === '' || !str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return false;
        }
        if (preg_match('/[\x00-\x1F\x7F\\\\]/', $url)) {
            return false;
        }

        $parts = parse_url($url);
        return $parts !== false && !isset($parts['scheme']) && !isset($parts['host']);
    }
}
