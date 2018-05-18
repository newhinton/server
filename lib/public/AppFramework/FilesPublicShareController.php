<?php
declare(strict_types=1);
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

namespace OCP\AppFramework;

use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Share\IManager as ShareManager;

abstract class FilesPublicShareController extends PublicShareController {

	/** @var ShareManager */
	protected $shareManager;

	public function __construct(string $appName,
								IRequest $request,
								ISession $session,
								IURLGenerator $urlGenerator,
								ShareManager $shareManager) {
		parent::__construct($appName, $request, $session, $urlGenerator);

		$this->shareManager = $shareManager;
	}

	protected function verifyPassword(string $token, string $password): bool {
		try {
			$share = $this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			return false;
		}

		return $this->shareManager->checkPassword($share, $password);
	}

	protected function getPasswordHash(string $token): string {
		try {
			$share = $this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			return '';
		}

		return $share->getPassword();
	}

	protected function isValidToken(string $token): bool {
		try {
			$this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			return false;
		}

		return true;
	}

	protected function isPasswordProtected(string $token): bool {
		try {
			$share = $this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			return false;
		}

		return $share->getPassword() !== null;
	}


}
