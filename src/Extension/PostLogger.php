<?php

/**
 * @package     plg_system_postlogger
 * @copyright   Copyright (C) 2025 Hung Tran
 * @license     GNU General Public License version 2 or later
 */

namespace HungTran\Plugin\System\PostLogger\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * POST logger main class.
 */
final class PostLogger extends CMSPlugin
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since  1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'onAfterRoute',
        ];
    }

    public function onAfterRoute()
    {
        // Only log POST requests.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Check if request is from external site.
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host    = $_SERVER['HTTP_HOST'] ?? '';

        if ($referer && strpos($referer, $host) !== false) {
            // Request is internal, skip.
            return;
        }

        $data = [
            'time'    => date('Y-m-d H:i:s'),
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
            'uri'     => $_SERVER['REQUEST_URI'] ?? '',
            'referer' => $referer,
            'get'     => $_GET,
            'post'    => $_POST,
            'raw'     => file_get_contents('php://input'),
        ];

        // Register logger (will store file in Joomla's configured log_path).
        Log::addLogger(
            ['text_file' => 'plg_system_postlogger.log'],
            Log::ALL,
            ['postlogger']
        );

        Log::add(json_encode($data, JSON_PRETTY_PRINT), Log::INFO, 'postlogger');
    }
}
