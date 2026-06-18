let confirmPayBtn = document.querySelector('#confirm_pay_btn');

let bankModalBg = document.querySelector('#bank_modal_bg');
let bankModalClose = document.querySelector('#bank_modal_close');
let bankSubmitBtn = document.querySelector('#bank_submit_btn');

// 결제하기 버튼 클릭
confirmPayBtn.addEventListener('click', function(){

  let agreeTerms = document.querySelector('#agree_terms');
  let agreePrivacy = document.querySelector('#agree_privacy');
  let paymentMethod = document.querySelector('input[name="payment_method"]:checked');

  if(!agreeTerms.checked){
    alert('이용약관에 동의해주세요.');
    return;
  }

  if(!agreePrivacy.checked){
    alert('개인정보 수집 및 이용에 동의해주세요.');
    return;
  }

  if(!paymentMethod){
    alert('결제수단을 선택해주세요.');
    return;
  }

  // 무통장입금만 팝업 연결
  if(paymentMethod.value == 'bank'){
    bankModalBg.style.display = 'flex';
    return;
  }

  alert('현재는 무통장입금만 연결되어 있습니다.');
});

// 팝업 닫기
bankModalClose.addEventListener('click', function(){
  bankModalBg.style.display = 'none';
});

// 팝업 안 신청 완료하기
bankSubmitBtn.addEventListener('click', function(){

  let depositorName = document.querySelector('#depositor_name');
  let depositDate = document.querySelector('#deposit_date');

  if(depositorName.value == ''){
    alert('입금자명을 입력해주세요.');
    return;
  }

  if(depositDate.value == ''){
    alert('입금예정일을 선택해주세요.');
    return;
  }

  document.querySelector('#confirm_form').submit();
});