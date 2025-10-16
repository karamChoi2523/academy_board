document.addEventListener("DOMContentLoaded", async () => {
  const params = new URLSearchParams(window.location.search);
  const postId = params.get("id"); // 게시물 ID 가져오기

  const postContent = document.getElementById("post-content");
  const deleteBtn = document.getElementById("delete-btn");
  const editBtn = document.getElementById("edit-btn"); // 수정 버튼

  // 게시물 상세 내용 불러오기
  await loadPostDetails(postId, postContent, deleteBtn, editBtn);
});

/**
 * 게시물 상세 내용 로딩 함수
 * @param {string} postId - 게시물 ID
 * @param {HTMLElement} postContent - 콘텐츠를 삽입할 HTML 요소
 * @param {HTMLElement} deleteBtn - 삭제 버튼
 * @param {HTMLElement} editBtn - 수정 버튼
 */
async function loadPostDetails(postId, postContent, deleteBtn, editBtn) {
  try {
    const response = await fetch(`/api/post/read.php?id=${postId}`);
    const data = await response.json();

    if (data && data.title) {
      postContent.innerHTML = `
        <h2>${data.title}</h2>
        <p>${data.content}</p>
        <p><strong>작성일:</strong> ${data.created_at}</p>
        <p><strong>카테고리:</strong> ${data.category}</p> <!-- 카테고리 추가 -->
        <p><strong>작성자:</strong> ${data.author_nickname}</p> <!-- 작성자 추가 -->
      `;

      // 게시물 작성자와 로그인한 사용자가 일치하는지 확인
      const loggedInUserId = sessionStorage.getItem('userId');  // 로그인한 사용자의 ID
      if (data.user_id === loggedInUserId) {
        // 작성자일 경우 수정/삭제 버튼 활성화
        editBtn.style.display = "inline-block";
        deleteBtn.style.display = "inline-block";

        // 수정 버튼 클릭 시
        editBtn.addEventListener('click', () => {
          window.location.href = `update.html?id=${postId}`;  // 수정 페이지로 리다이렉트
        });

        // 삭제 버튼 클릭 시
        deleteBtn.addEventListener('click', async () => {
          const confirmDelete = confirm("정말로 삭제하시겠습니까?");
          if (confirmDelete) {
            await deletePost(postId);
          }
        });
      }
    } else {
      postContent.innerHTML = "<p>게시물이 존재하지 않거나 오류가 발생했습니다.</p>";
    }
  } catch (error) {
    console.error('게시물 상세 로딩 오류:', error);
    postContent.innerHTML = "<p>게시물 상세 정보를 불러오는 데 오류가 발생했습니다.</p>";
  }
}

/**
 * 게시물 삭제 함수
 * @param {string} postId - 게시물 ID
 */
async function deletePost(postId) {
  try {
    const response = await fetch('/api/post/delete.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id: postId }),
    });

    const data = await response.json();

    if (data.success) {
      alert('게시물이 삭제되었습니다.');
      window.location.href = 'board.html'; // 게시판 목록 페이지로 리다이렉트
    } else {
      alert('게시물 삭제에 실패했습니다.');
    }
  } catch (error) {
    console.error('삭제 오류:', error);
    alert('삭제 처리에 문제가 발생했습니다.');
  }
}
