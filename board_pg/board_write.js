// 미리보기 함수
function showPreview() {
  // 1. 사용자가 입력한 값 가져오기
  let writer = document.querySelector('#writer').value;
  let title = document.querySelector('#title').value;
  let category = document.querySelector('#category').value;
  let content = document.querySelector('#content').value;

  // 2. 미리보기 영역 선택하기
  let previewContent = document.querySelector('.preview_content');

  // 3. 입력값이 비어있을 때 기본 문구 넣기
  if (writer === "" || title === "" || category === "" || content === "") {
    previewContent.innerHTML = `
      <p class="empty_text">작성자, 제목, 상담 유형, 내용을 모두 입력해주세요.</p>
    `;
  } else {
    // 4. 미리보기 영역에 입력한 내용 출력하기
    previewContent.innerHTML = `
      <div class="preview_item">
        <p><strong>작성자</strong> : ${writer}</p>
        <p><strong>제목</strong> : ${title}</p>
        <p><strong>상담 유형</strong> : ${category}</p>
        <p><strong>내용</strong></p>
        <div class="preview_text">${content}</div>
      </div>
    `;
  }
}


// 제출 전 확인 함수
function submitCheck() {
  let writer = document.querySelector('#writer').value;
  let title = document.querySelector('#title').value;
  let category = document.querySelector('#category').value;
  let content = document.querySelector('#content').value;

  if (writer === "") {
    alert("작성자를 입력해주세요.");
    return false;
  }

  if (title === "") {
    alert("제목을 입력해주세요.");
    return false;
  }

  if (category === "") {
    alert("상담 유형을 선택해주세요.");
    return false;
  }

  if (content === "") {
    alert("내용을 입력해주세요.");
    return false;
  }

  // PHP 파일로 실제 제출
  return true;
}