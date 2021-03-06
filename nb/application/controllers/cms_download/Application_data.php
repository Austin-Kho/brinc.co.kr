<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Application_data extends CB_Controller {
	/**
	 * [__construct 이 클래스의 생성자]
	 */
	public function __construct(){
		parent::__construct();
		$this->load->model('cms_main_model'); //모델 파일 로드
	}

	public function download(){

		/** 데이터 가져오기 시작 **/
		//----------------------------------------------------------//
		$project = urldecode($this->input->get('pj'));
		$app_data = $this->cms_main_model->sql_result(" SELECT * FROM cb_cms_sales_application WHERE pj_seq='$project' AND disposal_div='0' ORDER BY app_date ASC, seq ASC ");
		$pj_title = $this->cms_main_model->sql_row(" SELECT pj_name FROM cb_cms_project WHERE seq='$project' ");

		//----------------------------------------------------------//
		/** 데이터 가져오기 종료 **/


		/** 엑셀 시트만들기 시작 **/
		//----------------------------------------------------------//
    require_once APPPATH . '/third_party/Phpexcel/Bootstrap.php';

    // Create new Spreadsheet object
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    // Set document properties
    $spreadsheet->getProperties()->setCreator(site_url())
      ->setLastModifiedBy($this->session->userdata('mem_username'))
      ->setTitle('Application_data')
      ->setSubject('청약자_데이터')
      ->setDescription('청약자 데이터');
		//----------------------------------------------------------//

		$spreadsheet->setActiveSheetIndex(0); // 워크시트에서 1번째는 활성화
		$spreadsheet->getActiveSheet()->setTitle('청약자_데이터'); // 워크시트 이름 지정

		// 본문 내용 ---------------------------------------------------------------//

		// 전체 글꼴 및 정렬
		$spreadsheet->getActiveSheet()->duplicateStyleArray( // 전체 글꼴 및 정렬
			array(
				'font' => array('size' => 9),
				'alignment' => array(
					'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
					'horizontal'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				)
			),
			'A:K'
		);

		// 헤더 스타일 생성 -- add style to the header
    $styleArray = array(
      'font' => array(
        'bold' => true,
      ),
      'alignment' => array(
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
      ),
      'borders' => array(
        'top' => array(
          'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ),
      ),
    );
    $spreadsheet->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleArray);

		$outBorder = array(
      'borders' => array(
        'outline' => array(
          'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ),
      ),
    );
		// $spreadsheet->getActiveSheet()->getStyle('A2:'.toAlpha(count($row_opt)-1).'2')->applyFromArray($outBorder);

		$allBorder = array(
      'borders' => array(
        'allborders' => array(
          'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ),
      ),
    );
		$spreadsheet->getActiveSheet()->getStyle('A3:K'.(count($app_data)+3))->applyFromArray($allBorder);

		$spreadsheet->getActiveSheet()->getColumnDimension("A")->setWidth(6); // A열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("B")->setWidth(8); // B열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("C")->setWidth(10); // C열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("D")->setWidth(10); // D열의 셀 넓이 설정
        $spreadsheet->getActiveSheet()->getColumnDimension("E")->setWidth(10); // E열의 셀 넓이 설정
        $spreadsheet->getActiveSheet()->getColumnDimension("F")->setWidth(10); // F열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("G")->setWidth(12); // G열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("H")->setWidth(12); // H열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("I")->setWidth(12); // I열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("J")->setWidth(10); // J열의 셀 넓이 설정
		$spreadsheet->getActiveSheet()->getColumnDimension("K")->setWidth(50); // K열의 셀 넓이 설정

		$spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(19.5); // 전체 기본 셀 높이 설정
		$spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(37.5); // 1행의 셀 높이 설정
		// $spreadsheet->getActiveSheet()->getRowDimension(2)->setRowHeight(9.5); // 2행의 셀 높이 설정

		$spreadsheet->getActiveSheet()->mergeCells('A1:K1');// A1부터 D1까지 셀을 합칩니다.

		$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);// A1의 폰트를 변경 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('A1', $pj_title->pj_name.' 청약자 데이터');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->getStyle('K2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
		$spreadsheet->getActiveSheet()->setCellValue('K2', date('Y-m-d')." 현재");// 해당 셀의 내용을 입력 합니다.

		$spreadsheet->getActiveSheet()->getStyle('A3:K3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAEAEA');
		$spreadsheet->getActiveSheet()->setCellValue('A3', 'no.');
		$spreadsheet->getActiveSheet()->setCellValue('B3', '타 입');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('C3', '동 호 수');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('D3', '청 약 자');// 해당 셀의 내용을 입력 합니다.
        $spreadsheet->getActiveSheet()->setCellValue('E3', '전화번호1');// 해당 셀의 내용을 입력 합니다.
        $spreadsheet->getActiveSheet()->setCellValue('F3', '전화번호2');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('G3', '차 수');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('H3', '청 약 금');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('I3', '청약 일자');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('J3', '상 태');// 해당 셀의 내용을 입력 합니다.
		$spreadsheet->getActiveSheet()->setCellValue('K3', '비 고');// 해당 셀의 내용을 입력 합니다.

		$i=1;
		foreach ($app_data as $lt) {

			switch ($lt->disposal_div) :
				case '1': $condi = $col="#0D069F"; $con="계약전환"; break;
				case '2': $condi = $col="#8C1024"; $con="해지신청"; break;
				case '3': $condi = $col="#354E62"; $con="환불완료"; break;
				default: $condi = $col="#05980F"; $con="정상청약"; break;
			endswitch;

			$spreadsheet->getActiveSheet()->setCellValue('A'.(3+$i), $i);
			$spreadsheet->getActiveSheet()->setCellValue('B'.(3+$i), $lt->unit_type);
			$spreadsheet->getActiveSheet()->setCellValue('C'.(3+$i), $lt->unit_dong_ho);
			$spreadsheet->getActiveSheet()->setCellValue('D'.(3+$i), $lt->applicant);
            $spreadsheet->getActiveSheet()->setCellValue('E'.(3+$i), $lt->app_tel1);
            $spreadsheet->getActiveSheet()->setCellValue('F'.(3+$i), $lt->app_tel2);
			$diff = $this->cms_main_model->sql_row(" SELECT diff_name FROM cb_cms_sales_con_diff WHERE pj_seq='$project' AND diff_no = '$lt->app_diff' ");
			$spreadsheet->getActiveSheet()->setCellValue('G'.(3+$i), $diff->diff_name);
			$spreadsheet->getActiveSheet()->getStyle('H'.(3+$i))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->setCellValue('H'.(3+$i), $lt->app_in_mon);
			$spreadsheet->getActiveSheet()->getStyle('H4:H'.(count($app_data)+3))->getNumberFormat()->setFormatCode('#,##0');
			$spreadsheet->getActiveSheet()->setCellValue('I'.(3+$i), $lt->app_date);
			$spreadsheet->getActiveSheet()->setCellValue('J'.(3+$i), $con);
			$spreadsheet->getActiveSheet()->getStyle('K'.(3+$i))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
			$spreadsheet->getActiveSheet()->setCellValue('K'.(3+$i), $lt->note);

			$i++;
		}

	// set right to left direction
    // $spreadsheet->getActiveSheet()->setRightToLeft(true);

	// 본문 내용 ---------------------------------------------------------------//

	$filename="청약자_데이터(".date('Y-m-d').").xlsx"; // 엑셀 파일 이름

    // Redirect output to a client's web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // mime 타입
	Header('Content-Disposition: attachment; filename='.iconv('UTF-8','CP949',$filename)); // 브라우저에서 받을 파일 이름
    header('Cache-Control: max-age=0'); // no cache
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

		// Excel5 포맷으로 저장 -> 엑셀 2007 포맷으로 저장하고 싶은 경우 'Excel2007'로 변경합니다.
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Excel2007');
		// 서버에 파일을 쓰지 않고 바로 다운로드 받습니다.
    $writer->save('php://output');
    exit;

    // create new file and remove Compatibility mode from word title
	}
}
// End of File
