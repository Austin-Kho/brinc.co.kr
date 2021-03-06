<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cms_m1 extends CB_Controller {

    /**
     * [__construct 이 클래스의 생성자]
     */
    public function __construct(){
        parent::__construct();
        if($this->member->is_member() === false) {
            redirect(site_url('login?url=' . urlencode(current_full_url())));
        }
        $this->load->model('cms_main_model'); //모델 파일 로드
        $this->load->model('cms_m1_model'); //모델 파일 로드
        $this->load->helper('cms_alert'); // 경고창 헬퍼 로딩
        $this->load->helper('cms_cut_string'); // 문자열 자르기 헬퍼 로드
    }

    /**
     * [index 클래스명 생략시 기본 실행 함수]
     * @return [type] [description]
     */
    public function index(){
        $this->sales();
    }

    /**
     * [sales 페이지 메인 함수]
     * @param  string $mdi [2단계 제목]
     * @param  string $sdi [3단계 제목]
     * @return [type]      [description]
     */
    public function sales($mdi='', $sdi=''){
		// $this->output->enable_profiler(TRUE); //프로파일러 보기///

        ///////////////////////////
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_main_index';
        $this->load->event($eventname);

        $view['data'] = $view = array();

        // 이벤트가 존재하면 실행합니다
        $view['data']['event']['before'] = Events::trigger('before', $eventname);

        $view['data']['canonical'] = site_url();

        // 이벤트가 존재하면 실행합니다
        $view['data']['event']['before_layout'] = Events::trigger('before_layout', $eventname);
        ////////////////////////

        $mdi = $this->uri->segment(3, 1);
        $sdi = $this->uri->segment(4, 1);

        $view['top_menu'] = $this->cms_main_model->sql_result("SELECT * FROM cb_menu WHERE men_parent=0 ORDER BY men_order");
        $view['sec_menu'] = $this->cms_main_model->sql_result("SELECT * FROM cb_menu WHERE men_parent={$view['top_menu'][0]->men_id} ORDER BY men_order");

        $view['s_di'] = array(
            array('계약 현황', '계약 등록', '동호수 현황', '계약서 관리'), // 첫번째 하위 메뉴
            array('수납 현황', '수납 등록', '고지서 발행'),	 // 두번째 하위 메뉴
            array('프로젝트별 계약현황', '프로젝트별 계약등록(수정)', '동호수 계약 현황표', '계약서 관련 스캔파일'),  // 첫번째 하위 제목
            array('분양대금 수납 현황', '분양대금 수납 등록', '계약 건별 수납고지서 출력')   // 두번째 하위 제목
        );

        // 등록된 프로젝트 데이터
        $where = "";
        if($this->input->get('yr') !=="") $where=" WHERE biz_start_ym LIKE '{$this->input->get('yr')}%' ";
        $project = $view['project'] = (!$this->input->get('project')) ? '3' : $this->input->get('project'); // 선택한 프로젝트 고유식별 값(아이디)
        $view['pj_list'] = $this->cms_main_model->sql_result(' SELECT * FROM cb_cms_project '.$where.' ORDER BY biz_start_ym DESC '); // 프로젝트 목록
        $view['pj_now'] = $pj_now = $this->cms_main_model->sql_row("SELECT * FROM cb_cms_project WHERE seq={$project}");

        // 프로젝트명, 타입 정보 구하기
        if($pj_now) {
            $view['tp_name'] = explode("-", $pj_now->type_name);
            $view['tp_color'] = explode("-", $pj_now->type_color);
        }


        // 계약현황 1. 계약현황 ////////////////////////////////////////////////////////////////////
        if($mdi==1 && $sdi==1 ){
            // $this->output->enable_profiler(TRUE); //프로파일러 보기//

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_1_1', $this->session->userdata['mem_id']);
            $view['auth11'] = $auth['_m1_1_1']; // 불러올 페이지에 보낼 조회 권한 데이터

            for($i=0; $i<count($view['tp_name']); $i++) {
                $view['summary'][$i] = $this->cms_main_model->sql_row(" SELECT COUNT(type) AS type_num, SUM(is_hold) AS hold, SUM(is_application) AS app, SUM(is_contract) AS cont FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' AND type='".$view['tp_name'][$i]."' ");
                $view['summary_app'][$i] = $this->cms_main_model->sql_num_rows(" SELECT seq FROM cb_cms_sales_application WHERE pj_seq='{$project}' AND unit_type='{$view['tp_name'][$i]}' AND disposal_div='0' ");
                $view['summary_cont'][$i] = $this->cms_main_model->sql_num_rows(" SELECT seq FROM cb_cms_sales_contract WHERE pj_seq='{$project}' AND unit_type='{$view['tp_name'][$i]}' ");
            }

            // 요약 총계 데이터 가져오기
            $view['sum_all'] = $this->cms_main_model->sql_row(" SELECT COUNT(seq) AS unit_num, SUM(is_hold) AS hold, SUM(is_application) AS app, SUM(is_contract) AS cont FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' ");

            // 청약 데이터 가져오기
            $dis_date = date('Y-m-d', strtotime('-3 day'));
            $view['app_data'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_application WHERE pj_seq='$project' AND (disposal_div='0' OR disposal_div='2' OR ((disposal_div='1' OR disposal_div='3') AND disposal_date>='$dis_date')) ORDER BY app_date DESC, seq DESC ");
            $view['app_num'] = $this->cms_main_model->sql_num_rows(" SELECT * FROM cb_cms_sales_application WHERE pj_seq='$project' AND (disposal_div='0' OR disposal_div='2' OR ((disposal_div='1' OR disposal_div='3') AND disposal_date>='$dis_date')) ORDER BY app_date DESC, seq DESC ");

            // 계약 데이터 필터링(타입, 동 별)
            $view['sc_cont_diff'] = $this->cms_main_model->sql_result(" SELECT cont_diff FROM cb_cms_sales_contract GROUP BY cont_diff ORDER BY cont_diff ");
            $view['sc_cont_type'] = $this->cms_main_model->sql_result(" SELECT unit_type FROM cb_cms_sales_contract GROUP BY unit_type ORDER BY unit_type ");
            if($this->input->get('type')) {
                $view['sc_cont_dong'] = $this->cms_main_model->sql_result(" SELECT unit_dong FROM cb_cms_sales_contract WHERE unit_type='".$this->input->get('type')."' GROUP BY unit_dong ORDER BY unit_dong ");
            }else {
                $view['sc_cont_dong'] = $this->cms_main_model->sql_result(" SELECT unit_dong FROM cb_cms_sales_contract GROUP BY unit_dong ORDER BY unit_dong ");
            }

            // 계약 데이터 검색 필터링
            $cont_query = "  SELECT *, cb_cms_sales_contractor.seq AS contractor_seq  ";
            $cont_query .= " FROM cb_cms_sales_contract, cb_cms_sales_contractor  ";
            $cont_query .= " WHERE pj_seq='$project' AND is_transfer='0' AND is_rescission='0' AND cb_cms_sales_contract.seq = cont_seq ";
            if( !empty($this->input->get('diff'))) {$df = $this->input->get('diff'); $cont_query .= " AND cont_diff='$df' ";}
            if( !empty($this->input->get('type'))) {$tp = $this->input->get('type'); $cont_query .= " AND unit_type='$tp' ";}
            if( !empty($this->input->get('dong'))) {$dn = $this->input->get('dong'); $cont_query .= " AND unit_dong='$dn' ";}
            if( !empty($this->input->get('s_date'))) {$sd = $this->input->get('s_date'); $cont_query .= " AND cb_cms_sales_contract.cont_date>='$sd' ";}
            if( !empty($this->input->get('e_date'))) {$ed = $this->input->get('e_date'); $cont_query .= " AND cb_cms_sales_contract.cont_date<='$ed' ";}
            if( !empty($this->input->get('sc_name'))) {$ctor = $this->input->get('sc_name'); $cont_query .= " AND (cb_cms_sales_contractor.contractor='$ctor' OR cb_cms_sales_contract.note LIKE '%$ctor%') ";}

            $view['cont_query'] = $cont_query; // Excel file 로 보낼 쿼리

            //페이지네이션 라이브러리 로딩 추가
            $this->load->library('pagination');

            //페이지네이션 설정/////////////////////////////////
            $config['base_url'] = base_url('cms_m1/sales/1/1');   //페이징 주소
            $config['total_rows'] = $view['total_rows'] = $this->cms_main_model->sql_num_rows($cont_query);  //게시물의 전체 갯수
            if( !$this->input->get('num')) $config['per_page'] = 15;  else $config['per_page'] = $this->input->get('num'); // 한 페이지에 표시할 게시물 수
            $config['num_links'] = 3; // 링크 좌우로 보여질 페이지 수
            // $config['uri_segment'] = 5; //페이지 번호가 위치한 세그먼트
            $config['reuse_query_string'] = TRUE;    //http://example.com/index.php/test/page/20?query=search%term

            // 게시물 목록을 불러오기 위한 start / limit 값 가져오기
            $page = $this->input->get('page'); // get 방식 아닌 경우 $this->uri->segment($config['uri_segment']);
            $start = ($page<=1 or empty($page)) ? 0 : ($page-1) * $config['per_page'];
            $limit = $config['per_page'];

            //페이지네이션 초기화
            $this->pagination->initialize($config);
            //페이징 링크를 생성하여 view에서 사용할 변수에 할당
            $view['pagination'] = $this->pagination->create_links();


            // 계약 데이터 가져오기
            if( !$this->input->get('order')) $cont_query .= " ORDER BY cont_code DESC, cb_cms_sales_contract.cont_date DESC, cb_cms_sales_contract.seq DESC ";
            if($this->input->get('order')=='1') $cont_query .= " ORDER BY cont_code ";
            if($this->input->get('order')=='2') $cont_query .= " ORDER BY cont_code DESC ";
            if($start != '' or $limit !='')	$cont_query .= " LIMIT ".$start.", ".$limit." ";

            $view['cont_data'] = $this->cms_main_model->sql_result($cont_query); // 계약 및 계약자 데이터



            // 계약현황 2. 계약등록 ////////////////////////////////////////////////////////////////////
        }else if($mdi==1 && $sdi==2) {
             // $this->output->enable_profiler(TRUE); //프로파일러 보기//

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_1_2', $this->session->userdata['mem_id']);
            // 불러올 페이지에 보낼 조회 권한 데이터
            $view['auth12'] = $auth['_m1_1_2'];

            if( !$this->input->get('cont_sort1')){
                $msg = "* 등록 구분을 선택하세요.";
            }else if( !$this->input->get('cont_sort2') &&  !$this->input->get('cont_sort3')){
                $msg = "* 세부 등록 구분을 선택하세요.";
            }else if( !$this->input->get('type')){
                $msg = "* 등록(변경)할 타입을 선택 하세요.";
            }else if ($pj_now->data_cr=='1') {
                if (!$this->input->get('dong')) {
                    $msg = "* 등록(변경)할 동을 선택 하세요.";
                } else if (!$this->input->get('ho')) {
                    $msg = "* 등록(변경)할 호수를 선택 하세요.";
                }
            }

            $where_add = " WHERE pj_seq='$project' "; // 프로젝트 지정 쿼리

            // 타입 데이터 불러오기
            $type_name = $this->cms_main_model->sql_result("SELECT type_name FROM cb_cms_project WHERE seq='$project'");
            $view['type_list'] = explode("-", $type_name[0]->type_name);

            if ($pj_now->data_cr=='1'){

                // 동 데이터 불러오기
                $now_type = $this->input->get('type');
                if($this->input->get('type')) $where_add .= " AND type='$now_type' ";
                $view['dong_list'] = $this->cms_main_model->sql_result("SELECT dong FROM cb_cms_project_all_housing_unit $where_add GROUP BY dong ORDER BY dong");

                // 호수 데이터 불러오기
                $now_dong = $this->input->get('dong');
                if($this->input->get('dong')) $where_add .= " AND dong='$now_dong' ";
                $view['ho_list'] = $this->cms_main_model->sql_result("SELECT ho FROM cb_cms_project_all_housing_unit $where_add GROUP BY ho ORDER BY ho");

                // 타입 동호수 텍스트
                $now_ho = $this->input->get('ho');

                $view['dong_ho'] = ($this->input->get('ho'))
                    ? "<font color='#9f0404'><span class='glyphicon glyphicon-fire' aria-hidden='true' style='padding-right: 10px;'></span></font><b>[".$now_type." 타입] &nbsp;".$now_dong ." 동 ". $now_ho." 호</b>"
                    : "<span style='color: #9f0404;'>".$msg."</span>";

                // 청약 또는 계약 체결된 동호수인지 확인
                if($now_ho){
                    $dongho = $view['unit_dong_ho'] = $now_dong."-".$now_ho; // 동호(1005-2002 형식)

                    //  등록할 동호수 유닛 데이터
                    $unit_seq = $view['unit_seq'] =  $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' AND type='$now_type' AND dong='$now_dong' AND ho='$now_ho' ");
                }
            }

            // 청약 또는 계약 상태인지 확인
            if($unit_seq->is_application=='1' or !empty($this->input->get('app_id'))) { // 청약 물건이면
                $app_data = $view['is_reg']['app_data'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_application WHERE seq='".$this->input->get('app_id')."' AND disposal_div<'2' "); // 청약 데이터

            }else if($unit_seq->is_contract=='1' or !empty($this->input->get('cont_id'))){ // 계약 물건이면
                $cont_where = " WHERE cb_cms_sales_contract.seq='{$this->input->get('cont_id')}' AND is_transfer='0' AND is_rescission='0' AND cb_cms_sales_contract.seq=cont_seq  ";
                $cont_query = "  SELECT *, cb_cms_sales_contract.seq AS cont_seq, cb_cms_sales_contractor.seq AS contractor_seq  FROM cb_cms_sales_contract, cb_cms_sales_contractor ".$cont_where;
                $cont_data = $view['is_reg']['cont_data'] = $this->cms_main_model->sql_row($cont_query); // 계약 및 계약자 데이터

                $view['is_app_cont'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_application WHERE pj_seq='$project' AND seq='{$cont_data->app_id}' AND disposal_div='1' "); // 청약->계약전환 물건인지 확인
            }

            // 차수 데이터 불러오기
            $view['diff_no'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_con_diff  WHERE pj_seq='$project' ORDER BY diff_no ");

            // 분양대금 수납 계정
            $view['dep_acc'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_bank_acc WHERE pj_seq='$project' ORDER BY seq ");
            $view['dep_acc_all'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_bank_acc ORDER BY seq ");

            // 계약 등록 시 당회 납부 회차 데이터 가져오기
            if($this->input->get('cont_sort2')=='2'){
                $view['pay_schedule'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' AND pay_code<3 ORDER BY seq ");
            }

            // 수납 관리 테이블 정보 가져오기
            if( !empty($cont_data)) $view['receiv_app'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='1' ");
            if( !empty($cont_data)) $view['received']['1'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='2' ");
            if( !empty($cont_data)) $view['received']['2'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='3' ");
            if( !empty($cont_data)) $view['received']['3'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='4' ");
            if( !empty($cont_data)) $view['received']['4'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='5' ");
            if( !empty($cont_data)) $view['received']['5'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='6' ");
            if( !empty($cont_data)) $view['received']['6'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='7' ");
            if( !empty($cont_data)) $view['received']['7'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code='8' ");
            if( !empty($cont_data)) $view['rec_num'] = $this->cms_main_model->sql_num_rows(" SELECT seq FROM cb_cms_sales_received WHERE cont_seq='$cont_data->cont_seq' AND cont_form_code>='2' ");


            // 라이브러리 로드
            $this->load->library('form_validation'); // 폼 검증

            // 청약, 계약 공통 폼 데이터
            $this->form_validation->set_rules('project', '프로젝트', 'trim|required');
            $this->form_validation->set_rules('cont_sort1', '등록구분1', 'trim|required');
            $this->form_validation->set_rules('type', '타입', 'trim|required');

            if ($pj_now->data_cr=='1') {
                $this->form_validation->set_rules('dong', '동', 'trim|required');
                $this->form_validation->set_rules('ho', '호수', 'trim|required');
            }

            $this->form_validation->set_rules('conclu_date', '처리일자', 'trim|exact_length[10]');
            $this->form_validation->set_rules('due_date', '계약예정일', 'trim|exact_length[10]');
            $this->form_validation->set_rules('custom_name', '청/계약자명', 'trim|required|max_length[20]');

            $this->form_validation->set_rules('tel_1', '연락처[1]', 'trim|max_length[13]|required');
            $this->form_validation->set_rules('tel_2', '연락처[2]', 'trim|max_length[13]');
            $this->form_validation->set_rules('note', '비고', 'trim|max_length[200]');

            // 계약일 경우 폼 데이터
            if($this->input->get('cont_sort2')=='2'){
                $this->form_validation->set_rules('cont_code', '계약일련번호', 'trim|required|max_length[12]');
                $this->form_validation->set_rules('birth_date', '생년월일', 'trim|required|numeric|max_length[6]');
                $this->form_validation->set_rules('cont_gender', '계약자 성별', 'trim|required');

                $this->form_validation->set_rules('app_in_date', '청약금 입금일', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date1', '계약금 입금일1', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date2', '계약금 입금일2', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date3', '계약금 입금일3', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date4', '계약금 입금일4', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date5', '계약금 입금일5', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date6', '계약금 입금일6', 'trim|exact_length[10]');
                $this->form_validation->set_rules('cont_in_date7', '계약금 입금일7', 'trim|exact_length[10]');

                $this->form_validation->set_rules('app_in_mon', '청약금', 'trim|numeric');
                $this->form_validation->set_rules('deposit_1', '계약금1', 'trim|numeric');
                $this->form_validation->set_rules('deposit_2', '계약금2', 'trim|numeric');
                $this->form_validation->set_rules('deposit_3', '계약금3', 'trim|numeric');
                $this->form_validation->set_rules('deposit_4', '계약금4', 'trim|numeric');
                $this->form_validation->set_rules('deposit_5', '계약금5', 'trim|numeric');
                $this->form_validation->set_rules('deposit_6', '계약금6', 'trim|numeric');
                $this->form_validation->set_rules('deposit_7', '계약금7', 'trim|numeric');

                $this->form_validation->set_rules('postcode1', '우편변호1', 'trim|numeric|max_length[5]');
                $this->form_validation->set_rules('address1_1', '메인주소1', 'trim|max_length[100]');
                $this->form_validation->set_rules('address2_1', '세부주소1', 'trim|max_length[50]');
                $this->form_validation->set_rules('postcode2', '우편번호2', 'trim|numeric|max_length[5]');
                $this->form_validation->set_rules('address1_2', '메인주소2', 'trim|max_length[100]');
                $this->form_validation->set_rules('address2_2', '세부주소2', 'trim|max_length[50]');
            }


            if($this->form_validation->run() !== FALSE) {

                // 청약 & 계약 공통
                $pj = $this->input->post('project', TRUE); // 프로젝트 아이디
                if ($pj_now->data_cr=='1') $un = $this->input->post('unit_seq', TRUE); // 동호 아이디

                if($this->input->post('cont_sort2')=='1'){ // 청약일 때

                    // 1. 청약 관리 테이블 입력
                    $app_arr = array(
                        'pj_seq' => $this->input->post('project', TRUE),
                        'applicant' => $this->input->post('custom_name', TRUE),
                        'app_tel1' => $this->input->post('tel_1', TRUE),
                        'app_tel2' => $this->input->post('tel_2', TRUE),
                        'app_date' => $this->input->post('conclu_date', TRUE),
                        'due_date' => $this->input->post('due_date', TRUE),
                        'unit_seq' => $this->input->post('unit_seq', TRUE),
                        'unit_type' => $this->input->post('type', TRUE),
                        'unit_dong_ho' => $this->input->post('unit_dong_ho', TRUE),
                        'app_diff' => $this->input->post('diff_no', TRUE),
                        'app_in_mon' => $this->input->post('app_in_mon', TRUE),
                        'app_in_acc' => $this->input->post('app_in_acc', TRUE),
                        'app_in_date' => $this->input->post('app_in_date', TRUE),
                        'app_in_who' => $this->input->post('app_in_who', TRUE),
                        'note' => $this->input->post('note', TRUE)
                    );

                    if($this->input->post('mode')=='1' && $this->input->post('cont_sort1')=='1'){ // 신규 청약 등록 일 때
                        $add_arr = array('ini_reg_worker' => $this->session->userdata('mem_username'));
                        $app_put = array_merge($app_arr, $add_arr);
                        $result = $this->cms_main_model->insert_data('cb_cms_sales_application', $app_put, 'ini_reg_date'); // 청약관리 테이블 데이터 입력
                        if( !$result){
                            alert('데이터베이스 에러입니다.', base_url(uri_string()));
                        }else{
                            // 2. 동호수 관리 테이블 입력
                            if($pj_now->data_cr=='1') {
                                $where = array('type'=>$this->input->post('type'), 'dong'=>$this->input->post('dong'), 'ho'=>$this->input->post('ho'));
                                $result2 = $this->cms_main_model->update_data('cb_cms_project_all_housing_unit', array('is_application'=>'1', 'modi_date'=>date('Y-m-d'), 'modi_worker'=>$this->session->userdata('mem_username')), $where); // 동호수 테이블 청약상태로 변경
                                if( !$result2) alert('데이터베이스 에러입니다.', base_url(uri_string()));
                            }
                        }
                        $app = $this->cms_main_model->sql_row(" SELECT seq FROM cb_cms_sales_application ORDER BY seq DESC LIMIT 1 ");
                        $app_id = $app->seq;

                    } else if($this->input->post('mode')=='2' && !empty($this->input->get('app_id'))){ // 기존 청약정보 수정일 때
                        $add_arr = array('last_modi_date' => date('Y-m-d'), 'last_modi_worker' => $this->session->userdata('mem_username'));
                        $app_put = array_merge($app_arr, $add_arr);
                        $where = array('pj_seq'=>$pj, 'unit_type' =>$this->input->post('type'), 'unit_dong_ho'=>$this->input->post('unit_dong_ho'));
                        $result = $this->cms_main_model->update_data('cb_cms_sales_application', $app_put, $where); // 청약관리 테이블 데이터 입력
                        if( !$result){
                            alert('데이터베이스 에러입니다.', base_url(uri_string()));
                        }
                        $app_id = $this->input->get('app_id');
                    }
                    $ret_url = ($pj_now->data_cr=='1')
                        ? "?project=".$pj."&mode=2&cont_sort1=".$this->input->post('cont_sort1')."&cont_sort2=".$this->input->post('cont_sort2')."&diff_no=".$this->input->post('diff_no')."&type=".$this->input->post('type')."&dong=".$this->input->post('dong')."&ho=".$this->input->post('ho')
                        : "?project=".$pj."&mode=2&cont_sort1=".$this->input->post('cont_sort1')."&cont_sort2=".$this->input->post('cont_sort2')."&diff_no=".$this->input->post('diff_no')."&type=".$this->input->post('type')."&app_id=".$app_id;
                    alert('청약 정보 입력이 정상 처리되었습니다.', base_url('cms_m1/sales/1/2').$ret_url);



                }else if($this->input->post('cont_sort2')=='2'){ // 계약일 때

                    /******************************계약 테이블 데이터******************************/
                    $con_fl = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_con_floor WHERE pj_seq='$pj' ORDER BY seq "); // 층별 조건 객체배열

                    if($pj_now->data_cr=='1') {
                        if(strlen($this->input->post('ho'))==3) { // 현재 층수 구하기
                            $now_floor = substr($this->input->post('ho'), 0, 1);
                        }else if(strlen($this->input->post('ho'))==4){
                            $now_floor = substr($this->input->post('ho'), 0, 2);
                        }

                        foreach($con_fl as $lt) { // 층수조건 아이디 (con_floor_no) 구하기
                            $a = explode("-", $lt->floor_range);
                            if($now_floor>=$a[0] && $now_floor<=$a[1]) $con_floor_no = $lt->seq;
                        }
                    }else{
                        $con_floor_no = $con_fl[0]->seq;
                    }

                    $pr_where_sql = "pj_seq='{$pj}' 
					                    AND con_diff_no='{$this->input->post('diff_no')}' 
					                    AND con_type='{$this->input->post('type')}' 
					                    AND con_direction_no='1'
					                    AND con_floor_no='{$con_floor_no}' ";

                    // 유닛 해당 가격(분담금) 아이디 추출
                    $price_seq = $this->cms_main_model->sql_row("SELECT * FROM cb_cms_sales_price WHERE {$pr_where_sql}");

                    $cont_arr1 = array( // 계약 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_code' => $this->input->post('cont_code', TRUE),
                        'cont_date' => $this->input->post('conclu_date', TRUE),
                        'app_id' => $this->input->post('app_id', TRUE),
                        'unit_seq' => $this->input->post('unit_seq', TRUE),
                        'unit_type' => $this->input->post('type', TRUE),
                        'unit_dong' => $this->input->post('dong', TRUE),
                        'unit_dong_ho' => $this->input->post('unit_dong_ho', TRUE),
                        'cont_diff' => $this->input->post('diff_no', TRUE),
                        'price_seq' => $price_seq->seq,
                        'note' => $this->input->post('note', TRUE)
                    );
                    /******************************계약 테이블 데이터******************************/

                    if(!$this->input->get('cont_id') AND !$this->input->post('unit_is_cont')) { // // 신규 계약일 때 contract 테이블 데이터 입력

                        //   1. 계약관리 테이블에 해당 데이터를 인서트한다.
                        $add_arr1 = array('ini_reg_worker' => $this->session->userdata('mem_username'));
                        $cont_arr11 = array_merge($cont_arr1, $add_arr1);

                        $result[-1] = $this->cms_main_model->insert_data('cb_cms_sales_contract', $cont_arr11, 'ini_reg_date');
                        if (!$result[-1]) {
                            alert('데이터베이스 에러입니다.0', current_full_url());
                        }
                    }
                    /******************************계약자 테이블 데이터******************************/
                    $cr_cont = $this->cms_main_model->sql_row(" SELECT seq FROM cb_cms_sales_contract ORDER BY seq DESC LIMIT 1 ");
                    $cont_seq = ( $cont_data->cont_seq) ?  $cont_data->cont_seq : $cr_cont->seq;
                    if( !empty($this->input->post('birth_date'))) $birth_gender = $this->input->post('birth_date').'-'.$this->input->post('cont_gender');
                    $is_registered = ($this->input->post('is_registered', TRUE)==='1') ? '1' : '0';
                    $addr_id = $this->input->post('postcode1')."|".$this->input->post('address1_1')."|".$this->input->post('address2_1');
                    $addr_dm = $this->input->post('postcode2')."|".$this->input->post('address1_2')."|".$this->input->post('address2_2');
                    $idoc1 = $this->input->post('incom_doc_1');
                    $idoc2 = $this->input->post('incom_doc_2');
                    $idoc3 = $this->input->post('incom_doc_3');
                    $idoc4 = $this->input->post('incom_doc_4');
                    $idoc5 = $this->input->post('incom_doc_5');
                    $idoc6 = $this->input->post('incom_doc_6');
                    $idoc7 = $this->input->post('incom_doc_7');
                    $idoc8 = $this->input->post('incom_doc_8');
                    $incom_doc = $idoc1."-".$idoc2."-".$idoc3."-".$idoc4."-".$idoc5."-".$idoc6."-".$idoc7."-".$idoc8;

                    $cont_arr2 = array( // 계약자 (contractor) 테이블 입력 데이터
                        'contractor' => $this->input->post('custom_name', TRUE),
                        'cont_birth_id' => $birth_gender,
                        'is_registered' =>  $is_registered,
                        'cont_tel1' =>  $this->input->post('tel_1', TRUE),
                        'cont_tel2' =>  $this->input->post('tel_2', TRUE),
                        'cont_addr1' =>  $addr_id,
                        'cont_addr2' =>  $addr_dm,
                        'cont_date' =>  $this->input->post('conclu_date', TRUE),
                        'incom_doc' =>  $incom_doc
                    );
                    /******************************계약자 테이블 데이터******************************/

                    /******************************계약금 1 폼 데이터******************************/
                    $cont_arr3 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche1', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_1', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_1', TRUE),
                        'paid_date' => $this->input->post('cont_in_date1', TRUE),
                        'paid_who' => $this->input->post('cont_in_who1', TRUE),
                        'cont_form_code' => '2',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 1 폼 데이터******************************/
                    /******************************계약금 2 폼 데이터******************************/
                    $cont_arr4 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche2', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_2', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_2', TRUE),
                        'paid_date' => $this->input->post('cont_in_date2', TRUE),
                        'paid_who' => $this->input->post('cont_in_who2', TRUE),
                        'cont_form_code' => '3',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 2 폼 데이터******************************/
                    /******************************계약금 3 폼 데이터******************************/
                    $cont_arr5 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche3', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_3', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_3', TRUE),
                        'paid_date' => $this->input->post('cont_in_date3', TRUE),
                        'paid_who' => $this->input->post('cont_in_who3', TRUE),
                        'cont_form_code' => '4',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 3 폼 데이터******************************/
                    /******************************계약금 4 폼 데이터******************************/
                    $cont_arr6 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche4', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_4', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_4', TRUE),
                        'paid_date' => $this->input->post('cont_in_date4', TRUE),
                        'paid_who' => $this->input->post('cont_in_who4', TRUE),
                        'cont_form_code' => '5',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 4 폼 데이터******************************/
                    /******************************계약금 5 폼 데이터******************************/
                    $cont_arr7 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche5', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_5', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_5', TRUE),
                        'paid_date' => $this->input->post('cont_in_date5', TRUE),
                        'paid_who' => $this->input->post('cont_in_who5', TRUE),
                        'cont_form_code' => '6',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 5 폼 데이터******************************/
                    /******************************계약금 6 폼 데이터******************************/
                    $cont_arr8 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche6', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_6', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_6', TRUE),
                        'paid_date' => $this->input->post('cont_in_date6', TRUE),
                        'paid_who' => $this->input->post('cont_in_who6', TRUE),
                        'cont_form_code' => '7',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 6 폼 데이터******************************/
                    /******************************계약금 7 폼 데이터******************************/
                    $cont_arr9 = array( // 수납 테이블 입력 데이터
                        'pj_seq' => $this->input->post('project', TRUE),
                        'cont_seq' => $cont_seq,
                        'pay_sche_code' => $this->input->post('cont_pay_sche7', TRUE), // 당회 납부 회차
                        'paid_amount' => $this->input->post('deposit_7', TRUE), // 납부한 금액
                        'paid_acc' => $this->input->post('dep_acc_7', TRUE),
                        'paid_date' => $this->input->post('cont_in_date7', TRUE),
                        'paid_who' => $this->input->post('cont_in_who7', TRUE),
                        'cont_form_code' => '8',
                        'reg_worker' => $this->session->userdata('mem_username')
                    );
                    /******************************계약금 7 폼 데이터******************************/


                    /////////////////////////////////////////////////////////////////////////////신규 계약 인서트

                    if(!$this->input->get('cont_id') AND !$this->input->post('unit_is_cont')){ // // 신규 계약일 때 contractor 테이블 데이터 입력

                        //   2. 계약자관리 테이블에 해당 데이터를 인서트한다.
                        $add_arr2 = array('cont_seq' => $cont_seq, 'ini_reg_worker' => $this->session->userdata('mem_username'));
                        $cont_arr22 = array_merge($cont_arr2, $add_arr2);

                        $result[1] = $this->cms_main_model->insert_data('cb_cms_sales_contractor', $cont_arr22, 'ini_reg_date');

                        if( !$result[1]) {
                            alert('데이터베이스 에러입니다.2', '');
                        }

                        // 3. 청약 테이블 해당 데이터에 계약 전환 업데이트
                        if(!empty($this->input->get('app_id')) OR $this->input->post('unit_is_app')=='1'){ // 청약 상태인 데이터 이면
                            // 청약 테이블 계약전환 처리
                            $dis_data = array(
                                'disposal_div'=> '1',
                                'disposal_date' => date('Y-m-d'),
                                'last_modi_date'=> date('Y-m-d'),
                                'last_modi_worker' =>$this->session->userdata('mem_username')
                            );
                            $result[2] = $this->cms_main_model->update_data('cb_cms_sales_application', $dis_data, array('unit_seq'=>$this->input->post('unit_seq'))); // 청약 테이블 계약전환 처리
                            if( !$result[2]) {
                                alert('데이터베이스 에러입니다.3', base_url(uri_string()));
                            }

                            // 4. 동호수 관리 테이블 입력 청약->OFF
                            if($pj_now->data_cr=='1') {
                                $result[3] = $this->cms_main_model->update_data('cb_cms_project_all_housing_unit', array('is_application'=>'0'), array('seq'=>$un)); // 동호수 테이블 계약상태로 변경
                                if( !$result[3]) {
                                    alert('데이터베이스 에러입니다.4', base_url(uri_string()));
                                }
                            }

                            // 5. 청약금 데이터 -> 수납 데이터로 입력
                            if( !empty($this->input->post('app_in_mon', TRUE))){
                                $app_mon = array( // 청약금 -> 수납 테이블 입력 데이터
                                    'pj_seq' => $this->input->post('project', TRUE),
                                    'cont_seq' => $cont_seq,
                                    'pay_sche_code' => $this->input->post('app_pay_sche', TRUE), // 당회 납부 회차
                                    'paid_amount' => $this->input->post('app_in_mon', TRUE), // 납부한 금액
                                    'paid_acc' => $this->input->post('app_in_acc', TRUE),
                                    'paid_date' => $this->input->post('app_in_date', TRUE),
                                    'paid_who' => $this->input->post('app_in_who', TRUE),
                                    'cont_form_code' => '1',
                                    'reg_worker' => $this->session->userdata('mem_username')
                                );
                                $result[4] = $this->cms_main_model->insert_data('cb_cms_sales_received', $app_mon, 'reg_date');
                                if( !$result[4]) {
                                    alert('데이터베이스 에러입니다.5', base_url(uri_string()));
                                }
                            }
                        }
                        // 6. 동호수 관리 테이블 입력 계약->On
                        if($pj_now->data_cr=='1') {
                            $result[5] = $this->cms_main_model->update_data('cb_cms_project_all_housing_unit', array('is_contract'=>'1', 'modi_date'=>date('Y-m-d'), 'modi_worker'=>$this->session->userdata('mem_username')), array('seq'=>$un)); // 동호수 테이블 계약상태로 변경
                            if( !$result[5]) {
                                alert('데이터베이스 에러입니다.6', base_url(uri_string()));
                            }
                        }

                        // 7. 계약금 데이터1 -> 수납 데이터로 입력
                        if($this->input->post('deposit_1') && $this->input->post('deposit_1')!='0'){ // 계약금 1 (분담금 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[6] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr3, 'reg_date');
                            if( !$result[6]) {
                                alert('데이터베이스 에러입니다.7', base_url(uri_string()));
                            }
                        }

                        // 8. 계약금 데이터2 -> 수납 데이터로 입력
                        if($this->input->post('deposit_2') && $this->input->post('deposit_2')!='0'){ // 계약금 2 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[7] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr4, 'reg_date');
                            if( !$result[7]) {
                                alert('데이터베이스 에러입니다.8', base_url(uri_string()));
                            }
                        }

                        // 9. 계약금 데이터3 -> 수납 데이터로 입력
                        if($this->input->post('deposit_3') && $this->input->post('deposit_3')!='0'){ // 계약금 3 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[8] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr5, 'reg_date');
                            if( !$result[8]) {
                                alert('데이터베이스 에러입니다.9', base_url(uri_string()));
                            }
                        }

                        // 10. 계약금 데이터4 -> 수납 데이터로 입력
                        if($this->input->post('deposit_4') && $this->input->post('deposit_4')!='0'){ // 계약금 3 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[9] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr6, 'reg_date');
                            if( !$result[9]) {
                                alert('데이터베이스 에러입니다.10', base_url(uri_string()));
                            }
                        }

                        // 11. 계약금 데이터5 -> 수납 데이터로 입력
                        if($this->input->post('deposit_5') && $this->input->post('deposit_5')!='0'){ // 계약금 3 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[10] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr7, 'reg_date');
                            if( !$result[10]) {
                                alert('데이터베이스 에러입니다.11', base_url(uri_string()));
                            }
                        }

                        // 12. 계약금 데이터6 -> 수납 데이터로 입력
                        if($this->input->post('deposit_6') && $this->input->post('deposit_6')!='0'){ // 계약금 3 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[11] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr8, 'reg_date');
                            if( !$result[11]) {
                                alert('데이터베이스 에러입니다.12', base_url(uri_string()));
                            }
                        }

                        // 13. 계약금 데이터7 -> 수납 데이터로 입력
                        if($this->input->post('deposit_7') && $this->input->post('deposit_7')!='0'){ // 계약금 3 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            $result[12] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr9, 'reg_date');
                            if( !$result[12]) {
                                alert('데이터베이스 에러입니다.13', base_url(uri_string()));
                            }
                        }
                        $cont_case = $this->input->post('unit_dong_ho', TRUE) ? $this->input->post('unit_dong_ho', TRUE) : $this->input->post('custom_name', TRUE);
                        alert($cont_case.'의 계약 정보입력이 정상처리되었습니다.', base_url('cms_m1/sales/1/1'));

                    }else if(!empty($this->input->get('cont_id')) OR $this->input->post('unit_is_cont')=='1'){ // 기존 계약정보 수정일 때
                    	
                    	if ($pj_now->data_cr=='1') {
                            //   0. unit 테이블 업데이트
                            $unit_data = array('is_contract' => '1');
                            $unit_where = array(
                                'pj_seq' => $pj,
                                'type' => $this->input->post('type', TRUE),
                                'dong' => $this->input->post('dong', TRUE),
                                'ho' => $this->input->post('ho', TRUE)
                            );
                            $rlt[0] = $this->cms_main_model->update_data('cb_cms_project_all_housing_unit', $unit_data, $unit_where);
                            if( !$rlt[0]){
                                alert('데이터베이스 에러입니다.0', current_full_url());
                            }
                        }

                        //   1. 계약관리 테이블(contract)에 해당 데이터를 업데이트한다.
                        $add_arr1 = array('last_modi_date' => date('Y-m-d'), 'last_modi_worker' => $this->session->userdata('mem_username'));

                    	$cont_arr11 = array_merge($cont_arr1, $add_arr1);
                        $result[0] = $this->cms_main_model->update_data('cb_cms_sales_contract', $cont_arr11, array('seq' => $cont_seq));
                        if( !$result[0]){
                            alert('데이터베이스 에러입니다.1', base_url(uri_string()));
                        }

                        //   2. 계약자관리 테이블에 해당 데이터를 업데이트한다.
                        $cont_arr22 = array_merge($cont_arr2, $add_arr1);

                        $result[1] = $this->cms_main_model->update_data('cb_cms_sales_contractor', $cont_arr22, array('seq'=>$cont_data->contractor_seq, 'cont_seq'=>$cont_data->cont_seq));
                        if( !$result[1]) {
                            alert('데이터베이스 에러입니다.2', '');
                        }

                        // 3. 계약금 데이터1 -> 수납 데이터로 수정
                        if($this->input->post('deposit_1') && $this->input->post('deposit_1')!='0'){ // 계약금 1 (분담금 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_1_')=='1'){
                                $result[5] = $this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr3, array('seq'=>$this->input->post('received1')));
                            }else{
                                $result[5] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr3, 'reg_date');
                            }
                            if( !$result[5]) {
                                alert('데이터베이스 에러입니다.6', base_url(uri_string()));
                            }
                        }

                        // 4. 계약금 데이터2 -> 수납 데이터로 수정
                        if($this->input->post('deposit_2') && $this->input->post('deposit_2')!='0'){ // 계약금 2 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_2_')=='1'){
                                $result[6] =$this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr4, array('seq'=>$this->input->post('received2')));
                            }else{
                                $result[6] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr4, 'reg_date');
                            }
                            if( !$result[6]) {
                                alert('데이터베이스 에러입니다.7', base_url(uri_string()));
                            }
                        }

                        // 5. 계약금 데이터3 -> 수납 데이터로 수정
                        if($this->input->post('deposit_3') && $this->input->post('deposit_3')!='0'){ // 계약금 3 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_3_')=='1'){
                                $result[7] =$this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr5, array('seq'=>$this->input->post('received3')));
                            }else{
                                $result[7] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr5, 'reg_date');
                            }
                            if( !$result[7]) {
                                alert('데이터베이스 에러입니다.8', base_url(uri_string()));
                            }
                        }

                        // 6. 계약금 데이터4 -> 수납 데이터로 수정
                        if($this->input->post('deposit_4') && $this->input->post('deposit_4')!='0'){ // 계약금 4 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_4_')=='1'){
                                $result[8] =$this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr6, array('seq'=>$this->input->post('received4')));
                            }else{
                                $result[8] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr6, 'reg_date');
                            }
                            if( !$result[8]) {
                                alert('데이터베이스 에러입니다.9', base_url(uri_string()));
                            }
                        }

                        // 7. 계약금 데이터5 -> 수납 데이터로 수정
                        if($this->input->post('deposit_5') && $this->input->post('deposit_5')!='0'){ // 계약금 5 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_5_')=='1'){
                                $result[9] =$this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr7, array('seq'=>$this->input->post('received5')));
                            }else{
                                $result[9] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr7, 'reg_date');
                            }
                            if( !$result[9]) {
                                alert('데이터베이스 에러입니다.10', base_url(uri_string()));
                            }
                        }

                        // 8. 계약금 데이터6 -> 수납 데이터로 수정
                        if($this->input->post('deposit_6') && $this->input->post('deposit_6')!='0'){ // 계약금 6 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_6_')=='1'){
                                $result[10] =$this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr8, array('seq'=>$this->input->post('received6')));
                            }else{
                                $result[10] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr8, 'reg_date');
                            }
                            if( !$result[10]) {
                                alert('데이터베이스 에러입니다.11', base_url(uri_string()));
                            }
                        }

                        // 9. 계약금 데이터7 -> 수납 데이터로 수정
                        if($this->input->post('deposit_7') && $this->input->post('deposit_7')!='0'){ // 계약금 7 (대행비 // 또는 일반 분양대금) 입력정보 있을때 처리
                            if($this->input->post('deposit_7_')=='1'){
                                $result[11] =$this->cms_main_model->update_data('cb_cms_sales_received', $cont_arr9, array('seq'=>$this->input->post('received7')));
                            }else{
                                $result[11] = $this->cms_main_model->insert_data('cb_cms_sales_received', $cont_arr9, 'reg_date');
                            }
                            if( !$result[11]) {
                                alert('데이터베이스 에러입니다.12', base_url(uri_string()));
                            }
                        }
                        $cont_case = $this->input->post('unit_dong_ho', TRUE) ? $this->input->post('unit_dong_ho', TRUE) : $this->input->post('custom_name', TRUE)."님";
                        alert($cont_case.'의 계약 정보수정이 정상처리되었습니다.', current_full_url());
                    }

                }else if($this->input->post('cont_sort3')=='3'){ // 청약 해지일 때
                    if($this->input->post('is_cancel')=='1' && $this->input->post('is_refund')!='1') {
                        $cancel_data = array(
                            'disposal_div'=>'2',
                            'disposal_date'=>$this->input->post('conclu_date'),
                            'last_modi_date'=>date('Y-m-d'),
                            'last_modi_worker'=>$this->session->userdata('mem_username')
                        );
                        $result[0] = $this->cms_main_model->update_data('cb_cms_sales_application', $cancel_data, array('pj_seq'=>$pj, 'unit_seq'=>$un)); // 해지처리
                        if( !$result[0]) alert('데이터베이스 에러입니다.', '');
                        $ret_url = base_url("cms_m1/sales/1/2?project=".$pj."&mode=2&cont_sort1=2&cont_sort3=3&diff_no=".$this->input->post('diff_no')."&type=".$this->input->post('type')."&dong=".$this->input->post('dong')."&ho=".$this->input->post('ho'));
                        alert('청약 해지가 정상처리 되었습니다.', $ret_url);
                    }
                    if($this->input->post('is_cancel')=='1' && $this->input->post('is_refund')=='1') {
                        $cancel_data = array(
                            'refund_amount' => $this->input->post('app_in_mon'),
                            'disposal_div' => '3',
                            'disposal_date' => $this->input->post('conclu_date'),
                            'last_modi_date' => date('Y-m-d'),
                            'last_modi_worker' => $this->session->userdata('mem_username')
                        );
                        $result[0] = $this->cms_main_model->update_data('cb_cms_sales_application', $cancel_data, array('pj_seq'=>$pj, 'unit_seq'=>$un)); // 해지 환불 처리
                        if( !$result[0]) alert('데이터베이스 에러입니다.', '');
                        $result[1] = $this->cms_main_model->update_data('cb_cms_project_all_housing_unit', array('is_application'=>'0', 'modi_date'=>date('Y-m-d'), 'modi_worker'=>$this->session->userdata('mem_username')), array('seq'=>$un));
                        if( !$result[1])  alert('데이터베이스 에러입니다.', '');
                        $ret_url = base_url("cms_m1/sales/1/2?project=".$pj."&mode=2&cont_sort1=2&cont_sort3=3&diff_no=".$this->input->post('diff_no')."&type=".$this->input->post('type')."&dong=".$this->input->post('dong')."&ho=".$this->input->post('ho'));
                        alert('해지 환불이 정상처리 되었습니다.', $ret_url);
                    }

                }else if($this->input->post('cont_sort3')=='4'){ // 계약 해지일 때
                    if($this->input->post('is_cont_cancel')=='1' && $this->input->post('is_cont_refund')!='1') {
                        $cancel_data = array(
                            'is_rescission'=>'1', // 해지 처리
                            'rescission_date'=>$this->input->post('conclu_date'),
                            'last_modi_date'=>date('Y-m-d'),
                            'last_modi_worker'=>$this->session->userdata('mem_username')
                        );
                        $result[0] = $this->cms_main_model->update_data('cb_cms_sales_contract', $cancel_data, array('seq'=>$this->input->post('cont_seq'))); // 해지 처리
                        if( !$result[0]) alert('데이터베이스 에러입니다.', '');
                        $cancel_data2 = array(
                            'is_transfer'=>'2', // 1.매도, 2. 해약
                            'transfer_date'=>$this->input->post('conclu_date'),
                            'last_modi_date'=>date('Y-m-d'),
                            'last_modi_worker'=>$this->session->userdata('mem_username')
                        );
                        $result[1] = $this->cms_main_model->update_data('cb_cms_sales_contractor', $cancel_data2, array('cont_seq'=>$this->input->post('cont_seq'))); // 해지 처리
                        if( !$result[1]) alert('데이터베이스 에러입니다.', '');
                        $ret_url = base_url("cms_m1/sales/1/2?project=".$pj."&mode=2&cont_sort1=2&cont_sort3=4&diff_no=".$this->input->post('diff_no')."&type=".$this->input->post('type')."&dong=".$this->input->post('dong')."&ho=".$this->input->post('ho'));
                        alert('계약 해지가 정상처리 되었습니다.', $ret_url);
                    }
                    if($this->input->post('is_cont_cancel')=='1' && $this->input->post('is_cont_refund')=='1') { // 계약 해지 환불일 때
                        $cancel_data = array(
                            'is_rescission'=>'2', // 환불 처리
                            'rescission_date'=>$this->input->post('conclu_date'),
                            'last_modi_date'=>date('Y-m-d'),
                            'last_modi_worker'=>$this->session->userdata('mem_username')
                        );
                        $result[0] = $this->cms_main_model->update_data('cb_cms_sales_contract', $cancel_data, array('seq'=>$this->input->post('cont_seq'))); // 해지 환불 처리
                        if( !$result[0]) alert('데이터베이스 에러입니다.', '');
                        $cancel_data2 = array(
                            'is_transfer'=>'2', // 1.매도, 2. 해약
                            'transfer_date'=>$this->input->post('conclu_date'),
                            'last_modi_date'=>date('Y-m-d'),
                            'last_modi_worker'=>$this->session->userdata('mem_username')
                        );
                        $result[1] = $this->cms_main_model->update_data('cb_cms_sales_contractor', $cancel_data2, array('cont_seq'=>$this->input->post('cont_seq'))); // 해지 처리
                        if( !$result[1]) alert('데이터베이스 에러입니다.', '');
                        $result[2] = $this->cms_main_model->update_data('cb_cms_sales_received', array('is_refund'=>'1'), array('cont_seq'=>$this->input->post('cont_seq'))); // 해지 환불 처리
                        if( !$result[2]) alert('데이터베이스 에러입니다.', '');
                        $result[3] = $this->cms_main_model->update_data('cb_cms_project_all_housing_unit', array('is_contract'=>'0', 'modi_date'=>date('Y-m-d'), 'modi_worker'=>$this->session->userdata('mem_username')), array('seq'=>$un));
                        if( !$result[3])  alert('데이터베이스 에러입니다.', '');
                        $ret_url = base_url("cms_m1/sales/1/2?project=".$pj."&mode=2&cont_sort1=2&cont_sort3=4&diff_no=".$this->input->post('diff_no')."&type=".$this->input->post('type')."&dong=".$this->input->post('dong')."&ho=".$this->input->post('ho'));
                        alert('해약 환불이 정상처리 되었습니다.', $ret_url);
                    }
                }
            }



            // 계약현황 3. 동호수현황 ////////////////////////////////////////////////////////////////////
        }else if($mdi==1 && $sdi==3) {
            // $this->output->enable_profiler(TRUE); //프로파일러 보기

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_1_3', $this->session->userdata['mem_id']);
            $view['auth13'] = $auth['_m1_1_3']; // 불러올 페이지에 보낼 조회 권한 데이터

            // 공급세대 및 유보세대 청약 계약세대 구하기
            $view['summary_tb'] = $this->cms_main_model->sql_row(" SELECT COUNT(*) AS total, SUM(is_hold) AS hold, SUM(is_application) AS acn, SUM(is_contract) AS cont FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project'  ");

            // 타입 관련 데이터 구하기
            $type = $this->cms_main_model->sql_row(" SELECT type_name, type_color FROM cb_cms_project WHERE seq='$project' ");
            if($type) {
                $view['type'] = array(
                    'name' => explode("-", $type->type_name),
                    'color' => explode("-", $type->type_color)
                );
            }

            // 해당 단지 최 고층 구하기
            $max_fl = $this->cms_main_model->sql_row(" SELECT MAX(ho) AS max_ho FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' ");
            if(strlen($max_fl->max_ho)==3) $view['max_floor'] = substr($max_fl->max_ho, -3,1);
            if(strlen($max_fl->max_ho)==4) $view['max_floor'] = substr($max_fl->max_ho, -4,2);

            // 해당 단지 동 수 및 리스트 구하기
            $dong_data = $view['dong_data'] = $this->cms_main_model->sql_result(" SELECT dong FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' GROUP BY dong ");

            // 각 동별 라인 수 구하기   //$line_num[6]->to_line
            for($j=0; $j<count($view['dong_data']); $j++) :
                $d = $dong_data[$j]->dong;
                $line_num = $view['line_num'][$j] = $this->cms_main_model->sql_row(" SELECT MIN(RIGHT(ho,2)) AS from_line, MAX(RIGHT(ho,2)) AS to_line FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' AND dong='$d' ");
            endfor;



            // 계약현황 4. 계약서 [스캔파일] ////////////////////////////////////////////////////////////////////
        }else if($mdi==1 && $sdi==4) {
            // $this->output->enable_profiler(TRUE); //프로파일러 보기

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_1_4', $this->session->userdata['mem_id']);
            $view['auth14'] = $auth['_m1_1_4']; // 불러올 페이지에 보낼 조회 권한 데이터



            //2. 수납관리 1. 수납현황 ////////////////////////////////////////////////////////////////////
        }else if($mdi==2 && $sdi==1) {
            // $this->output->enable_profiler(TRUE); //프로파일러 보기//

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_2_1', $this->session->userdata['mem_id']);
            // 불러올 페이지에 보낼 조회 권한 데이터
            $view['auth21'] = $auth['_m1_2_1'];

            // 총 약정금액
            $view['total_pmt'] = $this->cms_main_model->sql_row(" SELECT SUM(unit_price * unit_num) AS total FROM cb_cms_sales_price WHERE pj_seq='$project' ");
            // 분양 금액 구하기
            $view['sell_data'] = $this->cms_main_model->sql_row(" SELECT SUM(unit_price) AS sell_total FROM cb_cms_sales_contract, cb_cms_sales_price WHERE cb_cms_sales_contract.pj_seq='$project' AND price_seq=cb_cms_sales_price.seq ");

            // 필터링 위한 데이터
            $view['pay_sche'] = $this->cms_main_model->sql_result(" SELECT pay_code, pay_name FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' ORDER BY pay_code ");
            $view['paid_acc'] = $this->cms_main_model->sql_result(" SELECT seq, acc_nick FROM cb_cms_sales_bank_acc WHERE pj_seq='$project' ORDER BY seq ");

            // 수납데이터
            $view['rec_data'] = $this->cms_main_model->sql_row(" SELECT SUM(paid_amount) AS rec_total FROM cb_cms_sales_received WHERE pj_seq='$project' ");


            // 수납 데이터 검색 필터링
            $rec_query = " SELECT cb_cms_sales_received.seq, cont_seq, paid_amount, paid_date, paid_who, acc_nick, pay_name, unit_type, unit_dong_ho, cont_code, cont_diff ";

            $rec_query .= " FROM cb_cms_sales_received, cb_cms_sales_pay_sche, cb_cms_sales_bank_acc, cb_cms_sales_contract ";
            $rec_query .= " WHERE is_refund='0' AND cb_cms_sales_received.pj_seq='$project' AND cb_cms_sales_pay_sche.pj_seq='$project'  AND pay_sche_code=cb_cms_sales_pay_sche.pay_code AND paid_acc=cb_cms_sales_bank_acc.seq AND cont_seq=cb_cms_sales_contract.seq ";
            if( !empty($this->input->get('con_pay_sche'))) { $rec_query .= " AND pay_sche_code='".$this->input->get('con_pay_sche')."' ";}
            if( !empty($this->input->get('con_paid_acc'))) { $rec_query .= " AND paid_acc='".$this->input->get('con_paid_acc')."' ";}
            if( !empty($this->input->get('s_date'))) { $rec_query .= " AND paid_date>='".$this->input->get('s_date')."' ";}
            if( !empty($this->input->get('e_date'))) { $rec_query .= " AND paid_date<='".$this->input->get('e_date')."' ";}

//			$view['rec_query'] = $rec_query; // Excel 출력 데이터로 보낼 쿼리

            $amount_qry = " SELECT SUM(paid_amount) AS total_amount FROM cb_cms_sales_received WHERE pj_seq='$project'  ";

            $w_qry = "";
            if( !empty($this->input->get('con_pay_sche'))) { $w_qry = " AND pay_sche_code='".$this->input->get('con_pay_sche')."' ";}
            if( !empty($this->input->get('con_paid_acc'))) { $w_qry .= " AND paid_acc='".$this->input->get('con_paid_acc')."' ";}
            if( !empty($this->input->get('s_date'))) { $w_qry .= " AND paid_date>='".$this->input->get('s_date')."' ";}
            if( !empty($this->input->get('e_date'))) { $w_qry .= " AND paid_date<='".$this->input->get('e_date')."' ";}


            //페이지네이션 라이브러리 로딩 추가
            $this->load->library('pagination');

            //페이지네이션 설정/////////////////////////////////
            $config['base_url'] = base_url('cms_m1/sales/2/1');   //페이징 주소
            $config['total_rows'] = $view['total_rows'] = $this->cms_main_model->sql_num_rows($rec_query);  //게시물의 전체 갯수
            if( !$this->input->get('num')) $config['per_page'] = 15;  else $config['per_page'] = $this->input->get('num'); // 한 페이지에 표시할 게시물 수
            $config['num_links'] = 3; // 링크 좌우로 보여질 페이지 수
            $config['uri_segment'] =5; //페이지 번호가 위치한 세그먼트
            $config['reuse_query_string'] = TRUE;    //http://example.com/index.php/test/page/20?query=search%term

            // 게시물 목록을 불러오기 위한 start / limit 값 가져오기
            $page = $this->input->get('page'); // get 방식 아닌 경우 $this->uri->segment($config['uri_segment']);
            $start = ($page<=1 or empty($page)) ? 0 : ($page-1) * $config['per_page'];
            $limit = $config['per_page'];

            //페이지네이션 초기화
            $this->pagination->initialize($config);
            //페이징 링크를 생성하여 view에서 사용할 변수에 할당
            $view['pagination'] = $this->pagination->create_links();


            // 수납 데이터 가져오기
            $rec_query .= "ORDER BY paid_date DESC, cb_cms_sales_received.seq DESC ";
            if($start != '' or $limit !='')	$rec_query .= " LIMIT ".$start.", ".$limit." ";
            $view['rec_list'] = $this->cms_main_model->sql_result($rec_query); // 수납 및 계약자 데이터
            $view['rec'] = $this->cms_main_model->sql_row($amount_qry.$w_qry); // 총 수납액 구하기



            // 2. 수납관리 2. 수납등록 ////////////////////////////////////////////////////////////////////
        }else if($mdi==2 && $sdi==2) {
            // $this->output->enable_profiler(TRUE); //프로파일러 보기//

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_2_2', $this->session->userdata['mem_id']);
            // 불러올 페이지에 보낼 조회 권한 데이터
            $view['auth22'] = $auth['_m1_2_2'];
            
            if(!empty($this->input->get('payer'))){
                $now_payer = $this->input->get('payer');
                $paid_who = $this->cms_main_model->sql_row(" SELECT seq FROM cb_cms_sales_received WHERE paid_who LIKE '%$now_payer%' ");

                $view['now_payer'] = $this->cms_main_model->sql_result(
                    " SELECT paid_who, cb_cms_sales_received.cont_seq AS r_cont_seq, cont_code, unit_type, contractor, unit_dong_ho, is_rescission
					  FROM cb_cms_sales_received, cb_cms_sales_contract, cb_cms_sales_contractor
						WHERE cb_cms_sales_received.pj_seq='$project'
						AND cb_cms_sales_received.cont_seq=cb_cms_sales_contract.seq
						AND cb_cms_sales_contractor.cont_seq=cb_cms_sales_contract.seq
						AND (cont_code LIKE '%$now_payer%' OR paid_who LIKE '%$now_payer%' OR contractor LIKE '%$now_payer%')
						GROUP BY cb_cms_sales_received.cont_seq "
                );
            }

            $now_dong = $this->input->get('dong');
            $now_ho = $this->input->get('ho');
            
            if ($pj_now->data_cr=='1') {
				$view['dong_list'] = $this->cms_main_model->sql_result(" SELECT dong FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' GROUP BY dong ORDER BY dong "); // 동 리스트
				$view['ho_list'] = $this->cms_main_model->sql_result(" SELECT ho FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' AND dong='$now_dong' AND is_contract='1' GROUP BY ho ORDER BY ho "); // 호 리스트
				$unit = $view['unit'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_project_all_housing_unit WHERE pj_seq='$project' AND dong='$now_dong' AND ho='$now_ho' ");  // 선택한 동호수 유닛
			} else {
				// 타입 데이터 불러오기
				$type_name = $this->cms_main_model->sql_result("SELECT type_name FROM cb_cms_project WHERE seq='$project'");
				$view['type_list'] = explode("-", $type_name[0]->type_name);
				
            	$cont_code_list = $view['cont_code_list'] = $this->cms_main_model->sql_result("
 						SELECT cb_cms_sales_contract.seq AS cont_seq, cont_code, contractor
						FROM cb_cms_sales_contract, cb_cms_sales_contractor
						WHERE pj_seq='{$this->input->get('project')}'
						AND cb_cms_sales_contractor.cont_seq=cb_cms_sales_contract.seq
						AND unit_type='{$this->input->get('type')}' ");
			}

            if( !empty($this->input->get('cont_code')) OR !empty($unit->seq)){
                $cont_where = (!empty($unit->seq))
					? " WHERE unit_seq='$unit->seq' AND cb_cms_sales_contract.seq=cont_seq "
					: " WHERE cont_code='{$this->input->get('cont_code')}' AND unit_type='{$this->input->get('type')}' AND cb_cms_sales_contract.seq=cont_seq ";
                
                $cont_query = "
 						SELECT *, cb_cms_sales_contract.seq AS cont_seq, cb_cms_sales_contractor.seq AS contractor_seq
 						FROM cb_cms_sales_contract, cb_cms_sales_contractor ".$cont_where."
 						ORDER BY is_rescission ";
                
                $cont_data = $view['cont_data'] = $this->cms_main_model->sql_row($cont_query); // 계약 및 계약자 데이터
				
                // 수납 데이터
                $view['received'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_received WHERE pj_seq='$project' AND cont_seq='$cont_data->seq' ORDER BY paid_date, seq "); // 계약자별 수납데이터
                $view['total_paid'] = $this->cms_main_model->sql_row(" SELECT SUM(paid_amount) AS total_paid FROM cb_cms_sales_received WHERE pj_seq='$project' AND cont_seq='$cont_data->seq' "); // 계약자별 총 수납액
            }
            
            // 수납 약정
            $pay_sche = $view['pay_sche'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' "); // 전체 약정 회차
			
			$style_str = "<font color='#5c6a9a'><span class='glyphicon glyphicon-user' aria-hidden='true' style='padding-right: 10px;'></span></font>";
			
            if($pj_now->data_cr=='1' AND !empty($this->input->get('ho'))){
				$view['contractor_info'] = $style_str."<b>[ 일련번호 : ".$cont_data->cont_code." ] &nbsp;".$now_dong."동 ".$now_ho."호 ( ".$unit->type." 타입 / 계약자 : ".$cont_data->contractor." )</b>";
			}elseif(!empty($this->input->get('cont_code'))) {
				$view['contractor_info'] = $style_str."<b>[ 일련번호 : ".$cont_data->cont_code." ] &nbsp;( ".$cont_data->unit_type." 타입 / 계약자 : ".$cont_data->contractor." )</b>";
			}else{
				$view['contractor_info'] = "";
			}

            // 수납 계좌
            $view['paid_acc'] = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_bank_acc WHERE pj_seq='{$project}' ");
            // 수정 시 수납 데이터
            $view['modi_rec'] = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_received WHERE seq='{$this->input->get('rec_seq')}' ");
            // 데이터 삭제
            if( !empty($this->input->get('del_code'))){
                $result = $this->cms_main_model->delete_data('cb_cms_sales_received', array('seq' => $this->input->get('del_code')));
                if($result) {
                    alert('삭제 되었습니다.', current_full_url());
                }else{
                    alert('다시 시도하여 주십시요.', current_full_url());
                }
            }

            // 라이브러리 로드
            $this->load->library('form_validation'); // 폼 검증

            $this->form_validation->set_rules('paid_date', '수납일자', 'trim|exact_length[10]|required');
            $this->form_validation->set_rules('pay_sche_code', '수납회차', 'trim|required');
            $this->form_validation->set_rules('paid_amount', '수납금액', 'trim|numeric|required');
            $this->form_validation->set_rules('paid_acc', '수납계좌', 'trim|required');
            $this->form_validation->set_rules('paid_who', '입금자', 'trim|required|max_length[20]');
            $this->form_validation->set_rules('note', '비 고', 'trim|max_length[200]');


            if($this->form_validation->run() !== FALSE) { // 폼 데이터가 있는 경우

                $ins_data = array(
                    'pj_seq' => $project,
                    'cont_seq' => $this->input->post('cont_seq'),
                    'pay_sche_code' => $this->input->post('pay_sche_code'),
                    'paid_amount' => $this->input->post('paid_amount'),
                    'paid_acc' => $this->input->post('paid_acc'),
                    'paid_date' => $this->input->post('paid_date'),
                    'paid_who' => $this->input->post('paid_who'),
                    'note' => $this->input->post('note'),
                    'reg_date' => date('Y-m-d'),
                    'reg_worker' => $this->session->userdata('mem_username')
                );
                if($this->input->post('modi')=='1'){
                    $result = $this->cms_main_model->update_data('cb_cms_sales_received', $ins_data, array('seq' => $this->input->post('rec_seq'))); // 수정 모드일 경우
                }else{
                    $result = $this->cms_main_model->insert_data('cb_cms_sales_received', $ins_data); // 입력 모드일 경우
                }

                if( !$result) alert("데이터베이스 에러입니다.", current_full_url());

                alert("수납내역이 정상 입력 되었습니다.", current_full_url());
            }



            // 1. 수납관리 3. 수납 고지서 관리 ////////////////////////////////////////////////////////////////////
        }else if($mdi==2 && $sdi==3) {
             $this->output->enable_profiler(TRUE); //프로파일러 보기//

            // 조회 등록 권한 체크
            $auth = $this->cms_main_model->auth_chk('_m1_2_3', $this->session->userdata['mem_id']);
            // 불러올 페이지에 보낼 조회 권한 데이터
            $view['auth23'] = $auth['_m1_2_3'];

            // view page로 보낼 데이터 구하기
            $view['view']['bill_issue'] = $bill_issue = $this->cms_main_model->sql_row(" SELECT * FROM cb_cms_sales_bill_issue WHERE pj_seq='$project' ");
            $view['view']['addr'] = explode("|", $bill_issue->address);
            $view['view']['pay_sche'] = $this->cms_main_model->sql_result(" SELECT *, MAX(pay_code) AS pay_code FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' GROUP BY pay_time ORDER BY pay_code ");
            // 실제 납부회차
            $view['view']['real_sche'] = $this->cms_main_model->sql_result( " SELECT MAX(pay_code) AS pay_code FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' GROUP BY pay_time ");

            // 2차 계약금 이후 회차의 납부기한 데이터
            $view['due_sche'] = $this->cms_main_model->sql_row(" SELECT MIN(pay_code) AS start FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' AND pay_time='3' ");
            $pay_code = $bill_issue->pay_code;
            $ddate = $this->cms_main_model->sql_row(" SELECT pay_due_date FROM cb_cms_sales_pay_sche WHERE pj_seq='$project' AND pay_code='$pay_code' ");
            $view['due_date'] = ($ddate->pay_due_date == "0000-00-00") ? "" : $ddate->pay_due_date;

            // 계약 데이터 필터링(타입, 동 별)
            $view['sc_cont_diff'] = $this->cms_main_model->sql_result(" SELECT cont_diff FROM cb_cms_sales_contract GROUP BY cont_diff ORDER BY cont_diff ");
            $view['sc_cont_type'] = $this->cms_main_model->sql_result(" SELECT unit_type FROM cb_cms_sales_contract GROUP BY unit_type ORDER BY unit_type ");
            if($this->input->get('type')) {
                $view['sc_cont_dong'] = $this->cms_main_model->sql_result(" SELECT unit_dong FROM cb_cms_sales_contract WHERE unit_type='".$this->input->get('type')."' GROUP BY unit_dong ORDER BY unit_dong ");
            }else {
                $view['sc_cont_dong'] = $this->cms_main_model->sql_result(" SELECT unit_dong FROM cb_cms_sales_contract GROUP BY unit_dong ORDER BY unit_dong ");
            }

            // 계약자 데이터 구하기	// 계약 데이터 검색 필터링
            $cont_query = "  SELECT *, cb_cms_sales_contract.seq AS cont_seq, cb_cms_sales_contractor.seq AS contractor_seq  ";
            $cont_query .= " FROM cb_cms_sales_contract, cb_cms_sales_contractor  ";
            $cont_query .= " WHERE pj_seq='$project' AND is_transfer='0' AND is_rescission='0' AND cb_cms_sales_contract.seq = cont_seq ";
            if( !empty($this->input->get('diff'))) {$df = $this->input->get('diff'); $cont_query .= " AND cont_diff='$df' ";}
            if( !empty($this->input->get('type'))) {$tp = $this->input->get('type'); $cont_query .= " AND unit_type='$tp' ";}
            if( !empty($this->input->get('dong'))) {$dn = $this->input->get('dong'); $cont_query .= " AND unit_dong='$dn' ";}
            if( !empty($this->input->get('filter'))) $cont_query .= "  ";
            if( !empty($this->input->get('sc_name'))) {$ctor = $this->input->get('sc_name'); $cont_query .= " AND (cb_cms_sales_contractor.contractor='$ctor' OR cb_cms_sales_contract.note LIKE '%$ctor%') ";}

            // $view['cont_query'] = $cont_query; // Excel file 로 보낼 쿼리

            //페이지네이션 라이브러리 로딩 추가
            $this->load->library('pagination');

            //페이지네이션 설정/////////////////////////////////
            $config['base_url'] = base_url('cms_m1/sales/2/3');   //페이징 주소
            $config['total_rows'] = $view['total_rows'] = $this->cms_main_model->sql_num_rows($cont_query);  //게시물의 전체 갯수
            if( !$this->input->get('num')) $config['per_page'] = 10;  else $config['per_page'] = $this->input->get('num'); // 한 페이지에 표시할 게시물 수
            $config['num_links'] = 3; // 링크 좌우로 보여질 페이지 수
            // $config['uri_segment'] = 5; //페이지 번호가 위치한 세그먼트
            $config['reuse_query_string'] = TRUE;    //http://example.com/index.php/test/page/20?query=search%term

            // 게시물 목록을 불러오기 위한 start / limit 값 가져오기
            $page = $this->input->get('page'); // get 방식 아닌 경우 $this->uri->segment($config['uri_segment']);
            $start = ($page<=1 or empty($page)) ? 0 : ($page-1) * $config['per_page'];
            $limit = $config['per_page'];

            $this->pagination->initialize($config); //페이지네이션 초기화
            $view['pagination'] = $this->pagination->create_links(); //페이징 링크를 생성하여 view에서 사용할 변수에 할당

            // 계약 데이터 가져오기
            if( !$this->input->get('order')) $cont_query .= " ORDER BY cb_cms_sales_contract.cont_date DESC, cb_cms_sales_contract.seq DESC ";
            if($this->input->get('order')=='1') $cont_query .= " ORDER BY cont_code ";
            if($this->input->get('order')=='2') $cont_query .= " ORDER BY cont_code DESC ";
            if($start != '' or $limit !='')	$cont_query .= " LIMIT ".$start.", ".$limit." ";

            $view['cont_data'] = $this->cms_main_model->sql_result($cont_query); // 계약 및 계약자 데이터


            // 라이브러리 로드
            $this->load->library('form_validation'); // 폼 검증

            // 고지서 기본 내용 폼(bill_set)
            $this->form_validation->set_rules('published_date', '발행일자', 'trim|exact_length[10]');
            $this->form_validation->set_rules('pay_sche_code', '회차구분', 'trim|numeric');
            $this->form_validation->set_rules('sche_due_date', '당회 납부기한', 'trim|exact_length[10]');
            $this->form_validation->set_rules('host_name_1', '시행자(조합)', 'trim|max_length[30]');
            $this->form_validation->set_rules('tell_1', '시행자(조합) 연락처', 'trim|max_length[13]');
            $this->form_validation->set_rules('host_name_2', '시행자(대행사)', 'trim|max_length[30]');
            $this->form_validation->set_rules('tell_2', '시행자(대행사) 연락처', 'trim|max_length[13]');
            $this->form_validation->set_rules('bank_acc_1', '은행계좌1', 'trim|max_length[30]');
            $this->form_validation->set_rules('acc_host_1', '예금주1', 'trim|max_length[20]');
            $this->form_validation->set_rules('bank_acc_2', '은행계좌2', 'trim|max_length[30]');
            $this->form_validation->set_rules('acc_host_2', '예금주2', 'trim|max_length[20]');
            $this->form_validation->set_rules('postcode1', '우편변호1', 'trim|numeric|max_length[5]');
            $this->form_validation->set_rules('address1_1', '메인주소1', 'trim|max_length[100]');
            $this->form_validation->set_rules('address2_1', '세부주소1', 'trim|max_length[50]');
            $this->form_validation->set_rules('title', '고지서 제목', 'trim|max_length[100]');
            $this->form_validation->set_rules('content', '고지서 내용', 'trim');


            if($this->form_validation->run() !== FALSE) : // 폼검증 통과 했을 경우, Post 데이타 있을 경우

                // 데이터 가공
                $address = $this->input->post('postcode1', TRUE)."|".$this->input->post('address1_1', TRUE)."|".$this->input->post('address2_1', TRUE);

                // 고지서 기본 내용 폼(bill_set)
                $bill_set_data = array(
                    'pay_code' => $this->input->post('pay_sche_code', TRUE),
                    'host_name_1' => $this->input->post('host_name_1', TRUE),
                    'tell_1' => $this->input->post('tell_1', TRUE),
                    'host_name_2' => $this->input->post('host_name_2', TRUE),
                    'tell_2' => $this->input->post('tell_2', TRUE),
                    'bank_acc_1' => $this->input->post('bank_acc_1', TRUE),
                    'acc_host_1' => $this->input->post('acc_host_1', TRUE),
                    'bank_acc_2' => $this->input->post('bank_acc_2', TRUE),
                    'acc_host_2' => $this->input->post('acc_host_2', TRUE),
                    'address' => $address,
                    'title' => $this->input->post('title', TRUE),
                    'content' => $this->input->post('content', TRUE),
                    'last_update_user' => $this->session->userdata('mem_username'),
                    'last_update_time' => date("Y-m-d h:i:s")
                );
                $due_date_data = array('pay_due_date' => $this->input->post('sche_due_date', TRUE));
                
                if(!empty($bill_set_data)) {
                	if ($bill_issue!==NULL) {
						$result = $this->cms_main_model->update_data('cb_cms_sales_bill_issue', $bill_set_data, array('pj_seq' => $project));
					} else {
                		$pj_arr = array('pj_seq' => $project);
						$put_data = array_merge($pj_arr, $bill_set_data);
						$result = $this->cms_main_model->insert_data('cb_cms_sales_bill_issue', $put_data);
					}
                	
                    if(!empty($due_date_data)) {$result1 = $this->cms_main_model->update_data('cb_cms_sales_pay_sche', $due_date_data, array('pj_seq' => $project, 'pay_code' => $pay_code));}
                    
                    if($result) alert('정상적으로 설정 되었습니다.', current_full_url());
                }
            endif; // 폼검증 통과 시 종료
        }



        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_main');
        $meta_description = $this->cbconfig->item('site_meta_description_main');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_main');
        $meta_author = $this->cbconfig->item('site_meta_author_main');
        $page_name = $this->cbconfig->item('site_page_name_main');



        $layoutconfig = array(
            'path' => 'cms_m1',
            'layout' => 'layout',
            'skin' => 'm1_header',
            'layout_dir' => 'bootstrap',
            'mobile_layout_dir' => 'bootstrap',
            'use_sidebar' => 0,
            'use_mobile_sidebar' => 0,
            'skin_dir' => 'bootstrap',
            'mobile_skin_dir' => 'bootstrap',
            'page_title' => $page_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'meta_author' => $meta_author,
            'page_name' => $page_name,
        );
        $view['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        $this->data = $view;
        $this->layout = element('layout_skin_file', element('layout', $view));
        $this->view = element('view_skin_file', element('layout', $view));
    }
}
// End of this File
