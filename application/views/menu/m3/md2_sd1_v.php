      <div class="main_start">&nbsp;</div>
<!-- 3. 프로젝트 -> 2. 신규 프로젝트 ->1. 신규 등록 -->
      <div class="row" style="margin: 0; padding: 0;">
				<form class="" action="index.html" method="post">
					<fieldset class="font12">
						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">프로젝트 명 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">프로젝트 종류 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">대지위치(주소) <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-10 form-wrap bo-top">
								<div class="col-xs-3 col-sm-2 col-md-1" style="padding-right: 0;">
									<input type="button" class="btn btn-info btn-sm" value="우편번호" onclick="javascript:ZipWindow('/popup/zip_/')">
								</div>
								<div class="col-xs-3 col-sm-5 col-md-1" style="padding-right: 0;">
									<input type="text" class="form-control input-sm" id="zipcode" name="zipcode" maxlength="5" value="" readonly required autofocus>
								</div>
								<div class="col-xs-12 col-sm-6 col-md-4">
									<label for="address1" class="sr-only">회사주소1</label>
									<input type="text" class="form-control input-sm wid-98" id="address1" name="address1" maxlength="100" value="" readonly required autofocus>
								</div>
								<div class="col-xs-12 col-sm-6 col-md-3">
									<label for="address2" class="sr-only">회사주소2</label>
									<input type="text" class="form-control input-sm wid-98" id="address2" maxlength="100" value="" name="address2">
								</div>
								<div class="col-xs-12 col-sm-12 col-md-3 glyphicon-wrap">나머지 주소</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">대지 매입면적 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">계획 대지면적 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">건축규모 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">세대수 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">건축면적 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">총 연면적 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">지상층 연면적 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">지하층 연면적 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">용적율 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">건폐율 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">법정 주차대수 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">계획 주차대수 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-12 col-md-12 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">

								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_name">법정 주차대수 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<input type="text" class="form-control input-sm han" id="co_name" name="co_name" maxlength="30" value="" required autofocus>
								</div>
							</div>
							<div class="col-xs-12 col-sm-4 col-md-2 label-wrap bo-top">
								<label for="co_no1">계획 주차대수 <span class="red">*</span></label>
							</div>
							<div class="col-xs-12 col-sm-8 col-md-4 form-wrap bo-top">
								<div class="col-xs-12 col-sm-8">
									<label for="co_form" class="sr-only">사업자종류 </label>
									<select class="form-control input-sm" id="co_form" name="co_form" required autofocus>
										<option value="">선택</option>
										<option value="1">법인</option>
										<option value="2">개인(일반)</option>
										<option value="3">개인(간이)</option>
									</select>
								</div>
							</div>
						</div>



					</fieldset>
				</form>
      </div>
