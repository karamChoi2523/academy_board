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
        <p><strong>카테고리:</strong> ${data.category}</p>
        <p><strong>작성자:</strong> ${data.author_nickname}</p> <!-- 작성자 추가 -->
      `;

      // 로그인한 사용자의 user_id를 세션에서 가져옵니다.
      const loggedInUserId = sessionStorage.getItem('user_id');  // 세션에 저장된 사용자 ID
      console.log("Logged in user ID:", loggedInUserId); // 로그로 세션 값 확인

      if (data.user_id == loggedInUserId) { // 비교 시 == 사용하여 타입 일치시킴
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
      body: JSON.stringify({ id: postId }),  // JSON 형식으로 ID 전달
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

// 댓글 작성
document.getElementById("commentForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const content = document.getElementById("commentContent").value;
  const postId = new URLSearchParams(window.location.search).get("id");
  const userId = sessionStorage.getItem("user_id");

  if (!content || !postId || !userId) {
    alert("댓글을 작성할 수 없습니다.");
    return;
  }

  const commentData = {
    post_id: postId,
    user_id: userId,
    content: content,
  };

  try {
    const response = await fetch("/api/comment/create.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(commentData),
    });

    const result = await response.json();
    if (result.success) {
      alert("댓글이 추가되었습니다.");
      loadComments(postId);  // 댓글 목록 다시 불러오기
    } else {
      alert("댓글 작성에 실패했습니다.");
    }
  } catch (error) {
    console.error("댓글 작성 오류:", error);
    alert("댓글 작성 중 오류가 발생했습니다.");
  }
});
// 댓글 조회
async function loadComments(postId) {
  const response = await fetch(`/api/comment/get_comments.php?post_id=${postId}`);
  const comments = await response.json();

  const commentsContainer = document.getElementById("commentsContainer");
  commentsContainer.innerHTML = "";  // 기존 댓글 비우기

  if (comments.length === 0) {
    commentsContainer.innerHTML = "<p>댓글이 없습니다.</p>";
    return;
  }

  comments.forEach((comment) => {
    const commentElement = document.createElement("div");
    commentElement.classList.add("comment");
    commentElement.innerHTML = `
      <p><strong>${comment.author_nickname}</strong> (${comment.created_at}):</p>
      <p>${comment.content}</p>
      ${comment.isAuthor ? `<button class="btn-delete-comment" onclick="deleteComment(${comment.id})">🗑️ 삭제</button>` : ''}
    `;
    commentsContainer.appendChild(commentElement);
  });
}

// 댓글 삭제 함수
async function deleteComment(commentId) {
  if (!confirm('정말로 이 댓글을 삭제하시겠습니까?')) {
    return;
  }

  try {
    const response = await fetch(`/api/comment/delete.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id: commentId }),
    });

    const data = await response.json();
    if (data.success) {
      alert('댓글이 삭제되었습니다.');
      loadComments(postId);  // 댓글 목록 다시 불러오기
    } else {
      alert('댓글 삭제에 실패했습니다.');
    }
  } catch (error) {
    console.error('댓글 삭제 오류:', error);
    alert('댓글 삭제 중 오류가 발생했습니다.');
  }
}

// 페이지 로드 시 댓글 불러오기
//const postId = new URLSearchParams(window.location.search).get("id");
if (postId) {
  loadComments(postId);  // 해당 게시물의 댓글을 불러옵니다.
}
