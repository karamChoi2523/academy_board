document.addEventListener("DOMContentLoaded", async () => {
  // 로그인 상태 확인
  if (!sessionStorage.getItem('isLoggedIn')) {
    alert("로그인 후 사용해주세요.");
    window.location.href = "login.html";
    return;
  }

  // URL에서 파라미터 가져오기
  const params = new URLSearchParams(window.location.search);
  boardType = params.get("type") || "notice";

  // 게시판 제목 설정
  const boardTitle = document.getElementById("board-title");
  const titles = {
    "notice": "📢 공지사항 게시판",
    "question": "❓ 질문 게시판",
    "assignment": "📝 과제 게시판"
  };
  boardTitle.innerText = titles[boardType] || "게시판";

  // 게시물 목록 로드
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
      list.className = 'post-list';
      
      data.forEach(post => {
        const listItem = document.createElement('li');
        listItem.className = 'post-item';
        
        // 날짜 포맷팅
        const date = new Date(post.created_at);
        const formattedDate = formatDate(date);
        
        listItem.innerHTML = `
          <div class="post-content">
            <div class="post-title">${escapeHtml(post.title)}</div>
            <div class="post-meta">
              <span class="post-meta-item author">👤 ${escapeHtml(post.nickname || '익명')}</span>
              <span class="post-meta-item date">📅 ${formattedDate}</span>
            </div>
          </div>
          <div class="post-info">
            <div class="post-views">조회 ${post.views || 0}</div>
            <div class="post-arrow">→</div>
          </div>
        `;
        
        // 클릭 시 상세 페이지로 이동
        listItem.addEventListener('click', () => {
          window.location.href = `board-detail.html?id=${post.id}`;
        });
        
        list.appendChild(listItem);
      });
      
      boardContent.appendChild(list);
    } else {
      boardContent.innerHTML = '<div class="empty-message">게시물이 없습니다.</div>';
    }
  } catch (error) {
    console.error('게시물 목록 로딩 오류:', error);
    boardContent.innerHTML = '<div class="empty-message">게시물을 불러오는 데 오류가 발생했습니다.</div>';
  }
}
function formatDate(date) {
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  const postDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  
  if (postDate.getTime() === today.getTime()) {
    // 오늘: 시간:분 형식
    return date.toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit', hour12: false });
  } else if (postDate.getTime() === yesterday.getTime()) {
    // 어제
    return '어제';
  } else if (now.getFullYear() === date.getFullYear()) {
    // 올해: 월-일 형식
    return date.toLocaleDateString('ko-KR', { month: 'numeric', day: 'numeric' });
  } else {
    // 작년 이전: 년-월-일 형식
    return date.toLocaleDateString('ko-KR', { year: 'numeric', month: 'numeric', day: 'numeric' });
  }
}
/**
 * XSS 방지를 위한 HTML 이스케이프 함수
 */
function escapeHtml(text) {
  if (!text) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}