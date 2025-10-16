document.addEventListener("DOMContentLoaded", async () => {
  // 로그인 상태 확인
  if (!sessionStorage.getItem('isLoggedIn')) {
    alert("로그인 후 사용해주세요.");
    window.location.href = "login.html";
    return;
  }

  // URL에서 파라미터 가져오기
  const params = new URLSearchParams(window.location.search);
  const boardType = params.get("type") || "notice";
/*
  // 게시판 제목 설정
  const boardTitle = document.getElementById("board-title");
  const titles = {
    "notice": "공지사항 게시판",
    "question": "질문 게시판",
    "assignment": "과제 게시판"
  };
  boardTitle.innerText = titles[boardType] || "게시판";
  */

  // 게시물 목록 로드 (단 1회만 호출)
  const boardContent = document.getElementById("board-content");
  await loadBoardList(boardType, boardContent);
});

/**
 * 게시물 목록 로딩 함수
 */
async function loadBoardList(boardType, boardContent) {
  try {
    const response = await fetch(`./api/post/list.php?type=${boardType}`);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data && data.length > 0) {
      const list = document.createElement('ul');
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
