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
      // 파일 링크 HTML 미리 준비
      let attachmentHTML = "";
      if (data.attachment_path) {
        const fileUrl = `/uploads/${data.attachment_path}`;
        const fileName = data.attachment_name ?? "첨부파일";
        // 확장자 확인해서 이미지면 미리보기 표시
        const ext = fileName.split('.').pop().toLowerCase();
        if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) {
          attachmentHTML = `
            <div style="margin-top: 1.5rem;">
              <p><strong>첨부 이미지:</strong></p>
              <img src="${fileUrl}" alt="${fileName}" style="max-width: 100%; border-radius: 8px; margin-top: 0.5rem;">
              <p><a href="${fileUrl}" download="${fileName}" style="color:#4c9aff;">📥 ${fileName} 다운로드</a></p>
            </div>
          `;
        } else {
          // 이미지가 아닌 경우
          attachmentHTML = `
            <div style="margin-top: 1.5rem;">
              <p><strong>첨부파일:</strong>
                <a href="${fileUrl}" download="${fileName}" style="color:#4c9aff;">📎 ${fileName}</a>
              </p>
            </div>
          `;
        }
      }

      // 본문 + 첨부파일 표시
      postContent.innerHTML = `
        <h2>${data.title}</h2>
        <p style="margin-top:1.5rem; font-size:0.9rem; color:#666;">
          <strong>작성일:</strong> ${data.created_at} &nbsp;|&nbsp;
          <strong>카테고리:</strong> ${data.category ?? '없음'} &nbsp;|&nbsp;
          <strong>작성자:</strong> ${data.author_nickname ?? '익명'}
        </p>
        <div style="margin:1rem 0; line-height:1.6;">${data.content}</div>
        ${attachmentHTML}
      `;

      // 작성자 확인 후 버튼 표시
      const loggedInUserId = sessionStorage.getItem('user_id');
      if (data.user_id == loggedInUserId) {
        editBtn.style.display = "inline-block";
        deleteBtn.style.display = "inline-block";

        editBtn.addEventListener('click', () => {
          window.location.href = `update.html?id=${postId}`;
        });

        deleteBtn.addEventListener('click', async () => {
          const confirmDelete = confirm("정말로 삭제하시겠습니까?");
          if (confirmDelete) {
            await deletePost(postId, data.boardType);
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
async function deletePost(postId, boardType) {
  console.log("js deletePost", boardType);
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
      // ✅ 게시판 타입에 따라 이동 경로 결정
      window.location.href = `board.html?type=${boardType}`;
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
