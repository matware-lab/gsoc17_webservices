<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Api tester CLI.
 *
 * This is a command-line script to help with api tests.
 *
 * Called with no arguments: php api.php
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Http\Http;
use Joomla\CMS\Application\CliApplication;

/**
 * A command line script for api testing.
 *
 * @since  4.0
 */
class ApiCli extends CliApplication
{
	/**
	 * The url to test api.
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $url = 'http://localhost/api/index.php/article/1';

	/**
	 * The http method to use
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $method = 'get';

	/**
	 * The Joomla! username
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $username = 'admin';

	/**
	 * The Joomla! password
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $password = '';

	/**
	 * Entry point for API CLI script
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function doExecute()
	{
		// Http instance
		$this->http = new Http();

		// Print a blank line.
		$this->out(JText::_('API_CLI'));
		$this->out('============================');

		// Send request
		switch ($this->method) {
			case 'post':
			case 'put':
				$response = $this->http->post($this->url, $resource, $this->getRestHeaders());
				break;

			case 'get':
			case 'patch':
			case 'delete':
			default:
				$response = $this->http->get($this->url, $this->getRestHeaders());
				break;
		}

		$this->out($response->body);

		// Print a blank line at the end.
		$this->out();
	}

	/**
	 * Get the rest headers to send
	 *
	 * @param   bool    $form  True if we like to use POST
	 *
	 * @return  array   The RESTful headers
	 *
	 * @since   4.0
	 */
	protected function getRestHeaders($form = false)
	{
		// Encode the headers for REST
		$authorization = base64_encode($this->username . ":" . $this->password);

		$headers = array(
			'Authorization' => 'Basic ' . $authorization
		);

		$headers['Content-Type'] = "application/vnd.api+json";
		$headers['Accept'] = "text/*, text/html, text/html;level=1, */*";

		if ($form === true)
		{
			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		return $headers;
	}
}

// Set up the container
JFactory::getContainer()->share(
	'ApiCli',
	function (\Joomla\DI\Container $container)
	{
		return new ApiCli(
			null,
			null,
			null,
			null,
			$container->get(\Joomla\Event\DispatcherInterface::class),
			$container
		);
	},
	true
);

$app = JFactory::getContainer()->get('ApiCli');
JFactory::$application = $app;
$app->execute();
