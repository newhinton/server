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

use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;

/**
 * Base controller for interactive public shares
 *
 * It will verify if the user is properly authenticated to the share. If not the
 * user will be redirected to an authentication page.
 *
 * Use this for a controller that is to be called directly by a user. So the
 * normal public share page for files/calendars etc.
 */
abstract class AuthPublicShareController extends PublicShareController {

	/** @var IURLGenerator */
	protected $urlGenerator;

	public function __construct(string $appName,
								IRequest $request,
								ISession $session,
								IURLGenerator $urlGenerator) {
		parent::__construct($appName, $request, $session);

		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Show the authentication page
	 * The form has to submit to the authenticate method route
	 */
	abstract public function showAuthenticate(string $token): TemplateResponse;

	/**
	 * The template to show when authentication failed
	 */
	abstract protected function authFailed(string $token): TemplateResponse;

	/**
	 * Verify the password
	 */
	abstract protected function verifyPassword(string $token, string $password): bool;

	/**
	 * Custom controller post successfull authenticate steps go here
	 */
	protected function postSuccessfullAuthenticate(string $token) {

	}

	/**
	 * @UseSession
	 * @PublicPage
	 * @BruteForceProtection(action=publicLinkAuth)
	 *
	 * @throws NotFoundException
	 *
	 * Authenticate the share
	 */
	final public function authenticate(string $token, string $redirect, string $password = '') {
		if (!$this->isValidToken($token)) {
			throw new NotFoundException('Share not found');
		}

		// Already authenticated
		if ($this->isAuthenticated($token)) {
			return $this->getRedirect();
		}

		if (!$this->verifyPassword($token, $password)) {
			$response = $this->authFailed($token);
			$response->throttle();
			return $response;
		}

		$this->session->regenerateId();
		$this->session->set('public_link_authenticated_token', $token);
		$this->session->set('public_link_authenticated_password_hash', $this->getPasswordHash($token));

		$this->postSuccessfullAuthenticate($token);

		return $this->getRedirect();
	}

	/**
	 * Default landing page
	 *
	 * @param string $token
	 */
	abstract public function show(string $token);

	final public function getAuthenticationRedirect(string $token, string $redirect): RedirectResponse {
		return new RedirectResponse(
			$this->urlGenerator->linkToRoute($this->getRoute('showAuthenticate'), ['token' => $token, 'redirect' => $redirect])
		);
	}

	private function getRoute(string $function): string {
		$app = strtolower($this->appName);
		$class = strtolower((new \ReflectionClass($this))->getShortName());

		return $app . '.' . $class . '.' . $function;
	}

	private function getRedirect(): RedirectResponse {
		//Get all the stored redirect parameters:
		$params = $this->session->get('public_link_authenticate_redirect');

		$route = $this->getRoute('show');

		if ($params === null) {
			$params = [];
		} else {
			$params = json_decode($params, true);
			if (isset($params['_route'])) {
				$route = $params['_route'];
				unset($params['_route']);
			}
		}

		return new RedirectResponse($this->urlGenerator->linkToRoute($route, $params));
	}
}
