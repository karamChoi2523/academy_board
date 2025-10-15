function updateUI(isLoggedIn) {
  const guestView = document.getElementById("guest-view");
  const userView = document.getElementById("user-view");
  const loginLink = document.getElementById("login-link");
  const registerLink = document.getElementById("register-link");
  const logoutLink = document.getElementById("logout-link");

  if (isLoggedIn) {
    // ✅ 로그인 상태
    if (loginLink) loginLink.style.display = "none";
    if (registerLink) registerLink.style.display = "none";
    if (logoutLink) logoutLink.style.display = "inline-block";
    if (guestView) guestView.style.display = "none";
    if (userView) userView.style.display = "block";
  } else {
    // ❌ 비로그인 상태
    if (loginLink) loginLink.style.display = "inline-block";
    if (registerLink) registerLink.style.display = "inline-block";
    if (logoutLink) logoutLink.style.display = "none";
    if (guestView) guestView.style.display = "block";
    if (userView) userView.style.display = "none";
  }
}
