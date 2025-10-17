# 🏫 academy_board

| PHP + PostgreSQL 기반의 **교사·학생용 게시판 시스템**입니다.  
| 회원 가입 시 역할(teacher / student)에 따라 다른 기능을 이용할 수 있으며,  
| 게시물 작성, 수정, 삭제 등 기본적인 CRUD 기능과 프로필 이미지 업로드 기능을 지원합니다.

---

## 📌 프로젝트 개요

| 항목 | 내용 |
|------|------|
| **프로젝트명** | academy_board |
| **개발 기간** | 2025.10.14 ~ 2025.10.17 (개인 프로젝트) |
| **개발자** | 1인 (기획·프론트엔드·백엔드·DB 전부 직접 구현) |
| **목적** | 학원 또는 학교 환경에서 학생과 교사(선생님)가 함께 사용할 수 있는 간단한 게시판 시스템 구축 |

---

## 🧩 주요 기능

### 👤 회원 관리
- 회원가입 / 로그인 / 로그아웃
- 선생님(teacher), 학생(student) 두 가지 역할
- 프로필 이미지 업로드 및 수정
- 회원 정보 수정 및 탈퇴 기능

### 📝 게시판 기능
- 게시판 타입별 구분 (`notice`, `question`, `assignment` 등)
- 게시글 작성 / 조회 / 수정 / 삭제 (CRUD)
- 첨부파일 업로드 및 다운로드
- 게시물별 작성자·카테고리 표시
- 댓글 작성 및 삭

### 💾 세션 및 인증
- PHP 세션 기반 로그인 유지 (`check_session.php`)
- 역할에 따른 UI 표시 (예: 학생은 공지 게시판에 글 작성 불가능)

---

## ⚙️ 기술 스택

| 구분 | 사용 기술 |
|------|------------|
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **Backend** | PHP 8.x |
| **Database** | PostgreSQL |
| **Web Server** | Nginx + PHP-FPM |
| **OS** | Ubuntu Linux |
| **Version Control** | Git / GitHub |

---

## 🧱 디렉터리 구조

academy_board/
├── api/
│   ├── auth/
│   │   ├── register.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── check_session.php
│   │   └── delete-account.php
│   ├── post/
│   │   ├── create.php
│   │   ├── read.php
│   │   ├── update.php
│   │   ├── delete.php
│   │   └── list.php
│   └── config/
│       └── database.php
├── uploads/              # 프로필 및 첨부파일 저장 경로
├── board.html            # 게시판 목록 페이지
├── board-detail.html     # 게시글 상세 페이지
├── create.html           # 게시글 작성 페이지
├── update.html           # 게시글 수정 페이지
├── login.html            # 로그인 페이지
├── register.html         # 회원가입 페이지
├── forgot-password.html  # 게시글 작성 페이지
├── header.html           # 게시글 작성 페이지
├── index.html            # 게시글 작성 페이지
├── profile.html          # 프로필 조회 페이지
├── profile-edit.html     # 프로필 수정 페이지
└── js/                   # JS
