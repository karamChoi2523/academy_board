/**
 * 게시물 목록 로딩 함수
 * @param {string} boardType - 게시판 종류 (notice, qna, homework 등)
 * @param {HTMLElement} boardContent - 콘텐츠를 삽입할 HTML 요소
 */
async function loadBoardList(boardType, boardContent) {
  try {
    // 게시물 목록을 가져오기 위한 API 호출
    const response = await fetch(`/api/post/read.php?type=${boardType}`);
    const data = await response.json();

    if (data && data.length > 0) {
      boardContent.innerHTML = "<h2>게시물 목록</h2>";
      const list = document.createElement('ul');
      
      // 게시물 목록을 동적으로 생성
      data.forEach(post => {
        const listItem = document.createElement('li');
        listItem.innerHTML = `
          <a href="board-detail.html?id=${post.id}">${post.title}</a>
          <p>${post.created_at}</p>
        `;
        list.appendChild(listItem);
      });
      
      boardContent.appendChild(list);
    } else {
      boardContent.innerHTML = "<p>게시물이 없습니다.</p>";
    }
  } catch (error) {
    console.error('게시물 목록 로딩 오류:', error);
    boardContent.innerHTML = "<p>게시물 목록을 불러오는 데 오류가 발생했습니다.</p>";
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  const params = new URLSearchParams(window.location.search);
  const boardType = params.get("type"); // 'notice', 'qna', 'homework' 등

  const boardContent = document.getElementById("board-content");

  // 로그인 상태 확인
  if (!sessionStorage.getItem('isLoggedIn')) {
    alert("로그인 후 사용해주세요.");
    window.location.href = "login.html";  // 로그인 페이지로 이동
  }

  // 게시물 목록을 불러오는 함수
  await loadBoardList(boardType, boardContent);
});