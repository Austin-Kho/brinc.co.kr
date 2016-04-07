<?php
defined('BASEPATH') OR exit ('No direct script access allowed');

class M4_m extends CI_Model {

	//공통 함수 Start//
	/**
	 * [select_data_list 복수 데이터 불러오기]
	 * @param  [String] $table [테이블명]
	 * @param  [Array] $where [필터링 '키'=>값]
	 * @return [Boolean]        [성공 여부]
	 */
	public function select_data_list($table) {
		$qry = $this->db->get($table);
		return $rlt = $qry->result();
	}

	/**
	 * [select_data_row  단수 데이터 불러오기]
	 * @param  [String] $table [테이블명]
	 * @param  [Array] $where [필터링 '키'=>값]
	 * @return [Boolean]        [성공 여부]
	 */
	public function select_data_row($table, $where) {
		$qry = $this->db->get_where($table, $where);
		return $rlt = $qry->row();
	}

	/**
	 * [insert_data 데이터 입력함수]
	 * @param  [String] $table [테이블명]
	 * @param  [Array] $data  [입력할 데이터]
	 * @return [Boolean]        [입력 성공여부]
	 */
	public function insert_data($table, $data) {
		$result = $this->db->insert($table, $data);
		return $result;
	}

	/**
	 * [update_data 데이터 수정함수]
	 * @param  [String] $table [테이블명]
	 * @param  [Array] $data  [수정할 데이터]
	 * @param  [Int] $seq   [데이터 키 값]
	 * @return [Boolean]        [수정 성공여부]
	 */
	public function update_data($table, $data, $where) {
		$result = $this->db->update($table, $data, $where);
		return $result;
	}

	/**
	 * [delete_data 데이터 삭제함수]
	 * @param  [String] $table [테이블명]
	 * @param  [Int] $seq   [데이터 키 값]
	 * @return [Boolean]        [삭제 성공여부]
	 */
	public function delete_data($table, $where) {
		$result = $this->db->delete($table, $where);
		return $result;
	}
	//공통 함수 Start//

	public function cash_book_list($table, $start='', $limit='', $search_con, $n) {
		$this->db->select('seq_num, class1, class2, account, cont, acc, in_acc, inc, out_acc, exp, evidence, cms_capital_cash_book.note, worker, deal_date, name, no');
		$where=" (com_div>0 AND ((in_acc=no AND class2<>7) OR out_acc=no) OR (com_div IS NULL AND in_acc=no AND class2=6)) ";

		//검색어가 있을 경우
		if($search_con['class1']){
			if($search_con['class1']==1) $where.=" AND class1='1' ";
			if($search_con['class1']==2) $where.=" AND class1='2' ";
			if($search_con['class1']==3) $where.=" AND class1='3' ";
		}
		if($search_con['class2']) $where.=" AND class2='".$search_con['class2']."' ";
		if($search_con['s_date']) $where.=" AND deal_date>='".$search_con['s_date']."' ";
		if($search_con['e_date']) {$where.=" AND deal_date<='".$search_con['e_date']."' "; } //$e_add=" AND deal_date<='$search_con['e_date']' ";} else{$e_add="";}

		if($search_con['sh_text']){
			if($search_con['sh_con']==0) $where.=" AND (account like '%".$search_con['sh_text']."%' OR cont like '%".$search_con['sh_text']."%' OR acc like '%".$search_con['sh_text']."%' OR evidence like '%".$search_con['sh_text']."%' OR cms_capital_cash_book.worker like '%".$search_con['sh_text']."%') "; // 통합검색
			if($search_con['sh_con']==1) $where.=" AND cont like '%".$search_con['sh_text']."%' "; // 적 요
			if($search_con['sh_con']==2) $where.=" AND acc like '%".$search_con['sh_text']."%' "; //거래처
			if($search_con['sh_con']==3) $where.=" AND (in_acc like '%".$search_con['sh_text']."%' OR out_acc like '%".$search_con['sh_text']."%') "; // 계정
			if($search_con['sh_con']==4) $where.=" AND evidence like '%".$search_con['sh_text']."%' ";  //증빙서류
		}
		$this->db->where($where);

		$this->db->order_by('deal_date', 'DESC');
		$this->db->order_by('seq_num', 'DESC');
		if($search_con['class1'] =='' && ($start != '' or $limit !=''))	$this->db->limit($limit, $start);
		$qry = $this->db->get($table);

		if($n=='num'){ $result = $qry->num_rows(); }else{ $result = $qry->result(); }
		return $result;
	}
}
// End of this File
