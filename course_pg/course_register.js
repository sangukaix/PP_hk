// 선택된 값들을 저장할 변수
let selectedCourse = "";
let selectedPeriod = "";
let selectedCount = "";
let selectedTimes = [];

// 일반 선택 버튼들
let selectButtons = document.querySelectorAll('.select_btn');

// 시간 버튼들
let timeButtons = document.querySelectorAll('.time_btn');

// 오른쪽 신청내역
let summaryCourse = document.querySelector('#summary_course');
let summaryPeriodCount = document.querySelector('#summary_period_count');
let summaryTimes = document.querySelector('#summary_times');

// 결제금액 표시
let priceTotal = document.querySelector('#price_total');
let priceMonthly = document.querySelector('#price_monthly');

// hidden input
let courseInput = document.querySelector('#course_name');
let periodInput = document.querySelector('#course_period');
let countInput = document.querySelector('#lesson_count');
let firstTimeInput = document.querySelector('#first_time');
let secondTimeInput = document.querySelector('#second_time');

// 희망 수업 시작일 input
let startDateInput = document.querySelector('#start_date');

    // 수업기간 + 수업횟수별 가격표
    let priceTable = {
      "1개월": {
        "주 2회": {
          total: "250,000원",
          monthly: ""
        },
        "주 3회": {
          total: "총 337,000원",
          monthly: ""
        },
        "주 5회": {
          total: "490,000원",
          monthly: ""
        }
      },

      "3개월": {
        "주 2회": {
          total: "646,000원",
          monthly: "(월 215,600원)"
        },
        "주 3회": {
          total: "865,000원",
          monthly: "(월 288,640원)"
        },
        "주 5회": {
          total: "1,309,000원",
          monthly: "(월 436,480원)"
        }
      },

      "6개월": {
        "주 2회": {
          total: "1,122,000원",
          monthly: "(월 187,000원)"
        },
        "주 3회": {
          total: "1,563,000원",
          monthly: "(월 260,525원)"
        },
        "주 5회": {
          total: "2,279,000원",
          monthly: "(월 379,950원)"
        }
      }
    };

// 희망 수업 시작일은 오늘 기준 3일 뒤부터 선택 가능
let today = new Date();
today.setDate(today.getDate() + 3);

// 날짜를 YYYY-MM-DD 형태로 바꾸기
let year = today.getFullYear();
let month = String(today.getMonth() + 1).padStart(2, '0');
let day = String(today.getDate()).padStart(2, '0');

let minStartDate = year + '-' + month + '-' + day;

// date input의 최소 선택 가능 날짜 설정
startDateInput.min = minStartDate;

// 날짜칸이 비어 있으면 기본값도 3일 뒤 날짜로 표시
if(startDateInput.value == ""){
  startDateInput.value = minStartDate;
}


// 선택 버튼 클릭
selectButtons.forEach(function(button){
  button.addEventListener('click', function(){

    let group = this.dataset.group;
    let value = this.dataset.value;

    // 같은 그룹 버튼 active 제거
    let sameGroupButtons = document.querySelectorAll('.select_btn[data-group="' + group + '"]');

    sameGroupButtons.forEach(function(btn){
      btn.classList.remove('active');
    });

    // 클릭한 버튼 active
    this.classList.add('active');

    // 그룹별 값 저장
    if(group == "course"){
      selectedCourse = value;
      courseInput.value = value;
    }

    if(group == "period"){
      selectedPeriod = value;
      periodInput.value = value;
    }

    if(group == "count"){
      selectedCount = value;
      countInput.value = value;
    }

    // 오른쪽 신청내역 업데이트
    updateSummary();
  });
});


// 시간 버튼 클릭
timeButtons.forEach(function(button){
  button.addEventListener('click', function(){

    let time = this.dataset.time;

    // 이미 선택된 시간을 다시 누르면 해제
    if(this.classList.contains('active')){
      this.classList.remove('active');

      selectedTimes = selectedTimes.filter(function(item){
        return item != time;
      });

      updateSummary();
      return;
    }

    // 시간은 최대 2개까지만 선택
    if(selectedTimes.length >= 2){
      alert('희망 수업시간은 총 2개까지 선택할 수 있습니다.');
      return;
    }

    this.classList.add('active');
    selectedTimes.push(time);

    updateSummary();
  });
});


// 오른쪽 신청내역 업데이트
function updateSummary(){

  // 코스명
  summaryCourse.innerText = selectedCourse || "선택 전";

  // 수업기간 / 수업횟수 한 줄로 표시
  if(selectedPeriod != "" && selectedCount != ""){
    summaryPeriodCount.innerText = selectedPeriod + " / " + selectedCount;
  }else if(selectedPeriod != ""){
    summaryPeriodCount.innerText = selectedPeriod;
  }else if(selectedCount != ""){
    summaryPeriodCount.innerText = selectedCount;
  }else{
    summaryPeriodCount.innerText = "선택 전";
  }

  // 희망 수업시간 한 줄로 표시
  if(selectedTimes.length == 2){
    summaryTimes.innerText = selectedTimes[0] + " / " + selectedTimes[1];
  }else if(selectedTimes.length == 1){
    summaryTimes.innerText = selectedTimes[0];
  }else{
    summaryTimes.innerText = "선택 전";
  }

  // 결제금액 표시
  if(selectedPeriod != "" && selectedCount != ""){
    let priceInfo = priceTable[selectedPeriod][selectedCount];

    priceTotal.innerText = priceInfo.total;
    priceMonthly.innerText = priceInfo.monthly;
  }else{
    priceTotal.innerText = "선택 전";
    priceMonthly.innerText = "";
  }

  // hidden input 값 저장
  firstTimeInput.value = selectedTimes[0] || "";
  secondTimeInput.value = selectedTimes[1] || "";
}


// 수강신청 버튼
document.querySelector('#apply_btn').addEventListener('click', function(){

  if(!isLogin){
    alert('로그인 후 수강신청이 가능합니다.');
    location.href = '../member_pg/login.php';
    return;
  }

  if(selectedCourse == ""){
    alert('과정을 선택해주세요.');
    return;
  }

  if(selectedPeriod == ""){
    alert('수업기간을 선택해주세요.');
    return;
  }

  if(selectedCount == ""){
    alert('수업횟수를 선택해주세요.');
    return;
  }

  if(startDateInput.value == ""){
    alert('희망 수업 시작일을 선택해주세요.');
    return;
  }

  if(startDateInput.value < minStartDate){
    alert('희망 수업 시작일은 오늘 기준 3일 뒤부터 선택 가능합니다.');
    return;
  }

  if(selectedTimes.length < 2){
    alert('희망 수업시간을 2개 선택해주세요.');
    return;
  }

  document.querySelector('#course_form').submit();

});