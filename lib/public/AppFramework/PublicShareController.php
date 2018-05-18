<?php
/**
 * @copyright 2018, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
declare(strict_types=1);

namespace OCP\AppFramework;

use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\ISession;

/**
 * Base controller for public shares
 *
 * It will verify if the user is properly authenticated to the share. If not a 404
 * is thrown by the PublicShareMiddleware.
 *
 * Use this for example for a controller that is not to be called via a webbrowser
 * directly. For example a PublicPreviewController. As this is not meant to be
 * called by a user direclty.
 *
 * To show an auth page extend the AuthPublicShareController
 */
abstract class PublicShareController extends Controller {

	/** @var ISession */
	protected $session;

	public function __construct(string $appName,
								IRequest $request,
								ISession $session) {
		parent::__construct($appName, $request);

		$this->session = $session;
	}

	/**
	 * Get a hash of the password for this share
	 *
	 * To ensure access is blocked when the password to a share is changed we store
	 * a hash of the password for this token.
	 */
	abstract protected function getPasswordHash(string $token): string;

	/**
	 * Is the provided token a valid token
	 */
	abstract protected function isValidToken(string $token): bool;

	/**
	 * Is a share with this token password protected
	 */
	abstract protected function isPasswordProtected(string $token): bool;

	/**
	 * Check if a share is authenticated or not
	 *
	 * @throws NotFoundException
	 */
	final public function isAuthenticated(string $token): bool {
		if (!$this->isValidToken($token)) {
			throw new NotFoundException('Share not found');
		}

		// Always authenticated against non password protected shares
		if (!$this->isPasswordProtected($token)) {
			return true;
		}

		// If we are authenticated properly
		if ($this->session->get('public_link_authenticated_token') === $token &&
			$this->session->get('public_link_authenticated_password_hash') === $this->getPasswordHash($token)) {
			return true;
		}

		// Fail by default if nothing matches
		return false;
	}
}
