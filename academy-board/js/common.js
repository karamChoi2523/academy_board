document.addEventListener("DOMContentLoaded", async () => {
  const headerContainer = document.createElement("div");
  document.body.prepend(headerContainer);

  let loginLink, registerLink, logoutLink, menuToggle, nav;

  try {
    // 1️⃣ header.html 불러오기
    const res = await fetch("header.html");
    if (!res.ok) throw new Error(`헤더 로드 실패: ${res.status}`);
   
    const html = await res.text();
    headerContainer.innerHTML = html;

    // 2️⃣ 요소 참조
    loginLink = document.getElementById("login-link");
    registerLink = document.getElementById("register-link");
    logoutLink = document.getElementById("logout-link");
    menuToggle = document.getElementById("menu-toggle");
    nav = document.getElementById("main-nav");

    // 홈 버튼
    window.goHome = () => (window.location.href = "index.html");

    // 메뉴 토글
    if (menuToggle && nav) {
      menuToggle.addEventListener("click", () => {
        nav.classList.toggle("open");
      });
    }

    // 3️⃣ 세션 확인
    const sessionRes = await fetch("/api/auth/check_session.php", {
      method: "GET",
      credentials: "include",
      cache: "no-store"
    });
   
    if (!sessionRes.ok) {
      throw new Error(`세션 확인 실패: ${sessionRes.status}`);
    }
   
    const result = await sessionRes.json();

    // UI 업데이트 함수
    const updateUI = (isLoggedIn) => {
      const guestView = document.getElementById("guest-view");
      const userView = document.getElementById("user-view");

      if (isLoggedIn) {
        loginLink?.style.setProperty('display', 'none');
        registerLink?.style.setProperty('display', 'none');
        logoutLink?.style.setProperty('display', 'inline-block');
        guestView?.style.setProperty('display', 'none');
        userView?.style.setProperty('display', 'block');
      } else {
        loginLink?.style.setProperty('display', 'inline-block');
        registerLink?.style.setProperty('display', 'inline-block');
        logoutLink?.style.setProperty('display', 'none');
        guestView?.style.setProperty('display', 'block');
        userView?.style.setProperty('display', 'none');
      }
    };

    // 초기 UI 설정
    updateUI(result.logged_in);

    if (result.logged_in) {
      console.log(`🔹 로그인됨: ${result.user.nickname} (${result.user.role})`);
    }

    // 4️⃣ 공개 페이지 목록
    const publicPages = ['/login.html', '/index.html', '/register.html', '/'];
    const currentPath = window.location.pathname;
   
    // 로그인되지 않았고, 공개 페이지가 아닌 경우만 리다이렉트
    if (!result.logged_in && !publicPages.some(page => currentPath.endsWith(page))) {
      alert("로그인 후 사용해주세요.");
      window.location.href = "login.html";
    }

    // 5️⃣ 로그아웃 이벤트
    if (logoutLink) {
      logoutLink.addEventListener("click", async (e) => {
        e.preventDefault();

        try {
          const res = await fetch("/api/auth/logout.php", {
            method: "POST",
            credentials: "include",
            headers: { "Content-Type": "application/json" }
          });

          if (!res.ok) {
            throw new Error(`로그아웃 요청 실패: ${res.status}`);
          }

          const result = await res.json();

          if (result.success) {
            alert("로그아웃 되었습니다.");
            updateUI(false);
           
            setTimeout(() => {
              window.location.href = "index.html";
            }, 300);
          } else {
            alert(result.message || "로그아웃 중 오류가 발생했습니다.");
          }
        } catch (err) {
          console.error("로그아웃 실패:", err);
          alert("로그아웃 처리에 문제가 발생했습니다.");
        }
      });
    }

  } catch (err) {
    console.error("헤더 로딩 또는 세션 확인 실패:", err);
    // 에러 발생 시에도 기본 UI는 표시되도록
    if (loginLink) loginLink.style.display = "inline-block";
    if (registerLink) registerLink.style.display = "inline-block";
  }
});

