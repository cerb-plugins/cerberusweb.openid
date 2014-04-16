<?php
if(class_exists('Extension_LoginAuthenticator',true)):
class ChOpenIdLoginModule extends Extension_LoginAuthenticator {
	function render() {
		@$email = DevblocksPlatform::importGPC($_REQUEST['email'],'string','');
		
		$request = DevblocksPlatform::getHttpRequest();
		$stack = $request->path;
		
		@array_shift($stack); // login
		@array_shift($stack); // openid
		@$page = array_shift($stack);
		
		if(null == ($worker = DAO_Worker::getByEmail($email)))
			return;
		
		// Verify that this is a legitimate login extension for this worker
		if($worker->auth_extension_id != $this->manifest->id)
			return;

		switch($page) {
			case 'setup':
				@$do_submit = DevblocksPlatform::importGPC($_REQUEST['do_submit'], 'integer', 0);
				
				if($do_submit) {
					$this->_processSetupLoginForm($worker);
				} else {
					$this->_renderSetupLoginForm($worker);
				}
				break;

			case 'discover':
				@$openid_url = DevblocksPlatform::importGPC($_POST['openid_url'],'string','');
				
				$openid = DevblocksPlatform::getOpenIDService();
				$url_writer = DevblocksPlatform::getUrlService();
				
				$return_url = $url_writer->writeNoProxy('c=login&ext=openid&a=authenticate', true);
				
				// [TODO] Handle invalid URLs
				$auth_url = $openid->getAuthUrl($openid_url, $return_url . '?email=' . $worker->email);
				DevblocksPlatform::redirectURL($auth_url);
				break;
			
			case 'authenticate':
				$this->_authenticate($worker);
				break;
				
			default:
				$open_ids = DAO_OpenIDToWorker::getWhere(sprintf("%s = %d", DAO_OpenIDToWorker::WORKER_ID, $worker->id));

				// if the worker has no chance of logging in w/ OpenID, set up their account
				if(empty($open_ids)) {
					$query = array(
						'email' => $worker->email,
					);
					
					@$code = DevblocksPlatform::importGPC($_REQUEST['code'], 'string', '');
					
					if(!empty($code))
						$query['code'] = $code;
					
					DevblocksPlatform::redirect(new DevblocksHttpResponse(array('login','openid','setup'), $query));
				}
				
				$this->_renderLoginForm($worker);
				break;
		}
	}

	function renderWorkerPrefs($worker) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('worker', $worker);
		$tpl->display('devblocks:cerberusweb.openid::login/prefs.tpl');
	}
	
	function saveWorkerPrefs($worker) {
		@$reset_login = DevblocksPlatform::importGPC($_REQUEST['reset_login'], 'integer', 0);
		
		$session = DevblocksPlatform::getSessionService();
		$visit = CerberusApplication::getVisit();
		$worker = CerberusApplication::getActiveWorker();
		
		if($reset_login) {
			$this->resetCredentials($worker);
			
			// If we're not an imposter, go to the login form
			if(!$visit->isImposter()) {
				$session->clear();
				$query = array(
					'email' => $worker->email,
					//'url' => '', // [TODO] This prefs URL
				);
				DevblocksPlatform::redirect(new DevblocksHttpRequest(array('login'), $query));
			}
		}
	}
	
	private function _renderLoginForm($worker) {
		@$error = DevblocksPlatform::importGPC($_REQUEST['error'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->assign('worker', $worker);
		$tpl->assign('error', $error);
		
		$tpl->display('devblocks:cerberusweb.openid::login/login.tpl');
	}
	
	private function _renderSetupLoginForm($worker) {
		$tpl = DevblocksPlatform::getTemplateService();
		
		$tpl->assign('worker', $worker);
		
		@$code = DevblocksPlatform::importGPC($_REQUEST['code'], 'string', null);
		$tpl->assign('code', $code);
		
		if(!isset($_SESSION['recovery_code'])) {
			$recovery_code = CerberusApplication::generatePassword(8);
			
			$_SESSION['recovery_code'] = $worker->email . ':' . $recovery_code;
			
			// [TODO] Email or SMS it through the new recovery platform service
			CerberusMail::quickSend($worker->email, 'Your confirmation code', $recovery_code);
		}
		
		$tpl->display('devblocks:cerberusweb.openid::login/setup.tpl');
	}
	
	private function _processSetupLoginForm($worker) {
		@$openid_url = DevblocksPlatform::importGPC($_POST['openid_url'],'string','');
		
		// [TODO] Confirm auth code, try...catch
		
		if(isset($_GET['openid_mode'])) {
			switch($_GET['openid_mode']) {
				case 'cancel':
					$query = array(
						'email' => $_REQUEST['email'],
					);
					DevblocksPlatform::redirect(new DevblocksHttpRequest(array('login','openid','setup'), $query));
					break;

				default:
					$openid = DevblocksPlatform::getOpenIDService();
	
					// If we failed validation
					if(!$openid->validate($_REQUEST))
						return false;
	
					// Does a worker own this OpenID?
					$openids = DAO_OpenIDToWorker::getWhere(sprintf("%s = %s",
						DAO_OpenIDToWorker::OPENID_CLAIMED_ID,
						Cerb_ORMHelper::qstr($_REQUEST['openid_claimed_id'])
					));
					
					if(!empty($openids))
						return false;
					
					DAO_OpenIDToWorker::create(array(
						DAO_OpenIDToWorker::OPENID_CLAIMED_ID => $_REQUEST['openid_claimed_id'],
						DAO_OpenIDToWorker::OPENID_URL => $_REQUEST['openid_identity'],
						DAO_OpenIDToWorker::WORKER_ID => $worker->id,
					));
					
					$query = array(
						'email' => $worker->email,
					);
					DevblocksPlatform::redirect(new DevblocksHttpRequest(array('login','openid'), $query));
					
					break;
			}
			
		} else {
			$openid = DevblocksPlatform::getOpenIDService();
			$url_writer = DevblocksPlatform::getUrlService();
			
			$return_url = $url_writer->writeNoProxy('c=login&ext=openid&a=setup', true);
			
			// [TODO] Handle invalid URLs
			$auth_url = $openid->getAuthUrl($openid_url, $return_url . '?do_submit=1&email=' . $worker->email);
			DevblocksPlatform::redirectURL($auth_url);
		}
	}

	function resetCredentials($worker) {
		DAO_OpenIDToWorker::deleteByWorkerIds($worker->id);
	}
	
	// This is never called because of OpenID redirects, but it needs to exist for the class interface
	function authenticate() {
		return false;
	}
	
	private function _authenticate() {
		$url_writer = DevblocksPlatform::getUrlService();

		// Mode (Cancel)
		if(isset($_GET['openid_mode']))
		switch($_GET['openid_mode']) {
			case 'cancel':
				header("Location: " . $url_writer->writeNoProxy('c=login&ext=openid', true));
				break;
				
			default:
				$openid = DevblocksPlatform::getOpenIDService();

				try {
					// If we failed validation
					if(!$openid->validate($_REQUEST))
						throw new CerbException("Authentication failed.");
	
					// Get parameters
					$attribs = $openid->getAttributes($_REQUEST);
	
					// Does a worker own this OpenID?
					$openids = DAO_OpenIDToWorker::getWhere(sprintf("%s = %s",
						DAO_OpenIDToWorker::WORKER_ID,
						$worker->id,
						DAO_OpenIDToWorker::OPENID_CLAIMED_ID,
						Cerb_ORMHelper::qstr($_REQUEST['openid_claimed_id'])
					));
					
					if(null == ($openid_owner = array_shift($openids)) || empty($openid_owner->worker_id))
						throw new CerbException("Authentication failed.");
						
					if(null != ($worker = DAO_Worker::get($openid_owner->worker_id)) && !$worker->is_disabled) {
						$_SESSION['login_authenticated_worker'] = $worker;
						DevblocksPlatform::redirect(new DevblocksHttpRequest(array('login','authenticated')));
						
					} else {
						unset($_SESSION['login_authenticated_worker']);
						throw new CerbException("Authentication failed.");
						
					}
					
				} catch (CerbException $e) {
					$query = array(
						'email' => @$_REQUEST['email'],
						'error' => $e->getMessage(),
					);
					DevblocksPlatform::redirect(new DevblocksHttpRequest(array('login','openid'), $query));
					
				}
				
				break;
		}
	}
};
endif;