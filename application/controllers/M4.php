<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M4 extends CI_Controller {

	/**
	 * [__construct 이 클래스의 생성자]
	 */
	public function __construct(){
		parent::__construct();
		if(@$this->session->userdata['logged_in'] !== TRUE) {
			redirect(base_url().'member/');
		}
		$this->load->model('main_m'); //모델 파일 로드
	}

	/**
	 * [index 클래스명 생략시 기본 실행 함수]
	 * @return [type] [description]
	 */
	public function index(){
		$this->capital();
	}

	public function _remap($method){
		// 헤더 include
		$this->load->view('cms_main_header');

		if(method_exists($this, $method)){
			$this->{"$method"}();
		}
		// 푸터 include
		$this->load->view('cms_main_footer');
	}

	public function capital($mdi='', $sdi=''){
		//$this->output->enable_profiler(TRUE); //프로파일러 보기//

		if( !$this->uri->segment(3)) $mdi = 1; else $mdi = $this->uri->segment(3);
		if( !$this->uri->segment(4)) $sdi = 1; else $sdi = $this->uri->segment(4);

		$menu['s_di'] = array(
			array('자금 일보', '입출금 내역', '입출금 등록'), // 첫번째 하위 메뉴
			array('분 개 장', '일·월계표', '제무 제표'),                          // 두번째 하위 메뉴
			array('자금 일보', '입출금 내역', '입출금 등록'), // 첫번째 하위 제목
			array('분 개 장', '일·월계표', '주요 제무제표')                                  // 두번째 하위 제목
		);

		$this->load->view('menu/m4/capital_v', $menu);

		// 자금 현황 1. 자금일보 ////////////////////////////////////////////////////////////////////
		if($mdi==1 && $sdi==1 ){
			// 조회 등록 권한 체크
			$auth = $this->main_m->auth_chk('_m4_1_1', $this->session->userdata['user_id']);

			if( !$auth['_m4_1_1'] or $auth['_m4_1_1']==0) {
				$this->load->view('no_auth');
			}else{

				//본 페이지 로딩
				$this->load->view('/menu/m4/md1_sd1_v');
			}






		// 자금 현황 2. 입출금 내역 ////////////////////////////////////////////////////////////////////
		}else if($mdi==1 && $sdi==2) {
			// 조회 등록 권한 체크
			$auth = $this->main_m->auth_chk('_m4_1_2', $this->session->userdata['user_id']);

			if( !$auth['_m4_1_2'] or $auth['_m4_1_2']==0) {
				$this->load->view('no_auth');
			}else{

				//본 페이지 로딩
				$this->load->view('/menu/m4/md1_sd2_v');
			}





		// 자금 현황 3. 입출금 등록 ////////////////////////////////////////////////////////////////////
		}else if($mdi==1 && $sdi==3) {
			// 조회 등록 권한 체크
			$auth = $this->main_m->auth_chk('_m4_1_3', $this->session->userdata['user_id']);

			if( !$auth['_m4_1_3'] or $auth['_m4_1_3']==0) {
				$this->load->view('no_auth');
			}else{

				//본 페이지 로딩
				$this->load->view('/menu/m4/md1_sd3_v');
			}





		// 회계관리 1. 분개장 ////////////////////////////////////////////////////////////////////
		}else if($mdi==2 && $sdi==1) {
			// 조회 등록 권한 체크
			$auth = $this->main_m->auth_chk('_m4_2_1', $this->session->userdata['user_id']);

			if( !$auth['_m4_2_1'] or $auth['_m4_2_1']==0) {
				$this->load->view('no_auth');
			}else{

				//본 페이지 로딩
				$this->load->view('/menu/m4/md2_sd1_v');
			}





		// 회계관리 2. 일월계표 ////////////////////////////////////////////////////////////////////
		}else if($mdi==2 && $sdi==2) {
			// 조회 등록 권한 체크
			$auth = $this->main_m->auth_chk('_m4_2_2', $this->session->userdata['user_id']);

			if( !$auth['_m4_2_2'] or $auth['_m4_2_2']==0) {
				$this->load->view('no_auth');
			}else{

				//본 페이지 로딩
				$this->load->view('/menu/m4/md2_sd2_v');
			}





		// 회계관리 3. 제무제표 ////////////////////////////////////////////////////////////////////
		}else if($mdi==2 && $sdi==3) {
			// 조회 등록 권한 체크
			$auth = $this->main_m->auth_chk('_m4_2_3', $this->session->userdata['user_id']);

			if( !$auth['_m4_2_3'] or $auth['_m4_2_3']==0) {
				$this->load->view('no_auth');
			}else{

				//본 페이지 로딩
				$this->load->view('/menu/m4/md2_sd3_v');
			}
		}
	}
}
// End of this File
