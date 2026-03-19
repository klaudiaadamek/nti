const listView = document.getElementById("devlog-list");
const postView = document.getElementById("devlog-post");

const backBtn = document.getElementById("backToList");

const detailsTitle = document.getElementById("detailsTitle");
const detailsBody = document.getElementById("detailsBody");
const detailsImg = document.getElementById("detailsImg");

const commentsList = document.getElementById("commentsList");
const commentForm = document.getElementById("commentForm");
const commentText = document.getElementById("commentText");

let activePostId = null;

function showList() {
  postView.classList.add("is-hidden");
  listView.classList.remove("is-hidden");
  activePostId = null;
}

function showPost({ id, title, body, image }) {
  activePostId = id;

  detailsTitle.textContent = title;
  detailsBody.innerHTML = (body || "")
    .split("\n")
    .map((line) => line.trim())
    .join("<br>");

  if (image && image.trim() !== "") {
    detailsImg.src = image;
    detailsImg.style.display = "block";
  } else {
    detailsImg.removeAttribute("src");
    detailsImg.style.display = "none";
  }

  listView.classList.add("is-hidden");
  postView.classList.remove("is-hidden");

  renderComments();
}

async function apiGetComments(postId) {
  const res = await fetch(`api/comments.php?post_id=${encodeURIComponent(postId)}`, {
    credentials: "same-origin",
  });

  const raw = await res.text();
  let data;
  try {
    data = JSON.parse(raw);
  } catch {
    throw new Error("API GET nie zwróciło JSON: " + raw.slice(0, 200));
  }

  if (!res.ok || !data.ok) {
    throw new Error((data && data.error ? data.error : "Błąd API GET") + ` (HTTP ${res.status})`);
  }

  return data.comments || [];
}

async function apiAddComment(postId, content) {
  const res = await fetch("api/comments.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ post_id: postId, content }),
  });

  const raw = await res.text();
  let data;
  try {
    data = JSON.parse(raw);
  } catch {
    throw new Error("API POST nie zwróciło JSON: " + raw.slice(0, 400));
  }

  if (!res.ok || !data.ok) {
    throw new Error((data && data.error ? data.error : "Błąd API POST") + ` (HTTP ${res.status})`);
  }

  return true;
}

async function renderComments() {
  commentsList.innerHTML = "";

  const loading = document.createElement("div");
  loading.className = "comment-row comment-row--empty";
  loading.textContent = "Ładowanie komentarzy...";
  commentsList.appendChild(loading);

  if (!activePostId) return;

  try {
    const comments = await apiGetComments(activePostId);

    commentsList.innerHTML = "";

    if (comments.length === 0) {
      const empty = document.createElement("div");
      empty.className = "comment-row comment-row--empty";
      empty.textContent = "Brak komentarzy. Dodaj pierwszy!";
      commentsList.appendChild(empty);
      return;
    }

    for (const c of comments) {
      const row = document.createElement("div");
      row.className = "comment-row";

      const who = document.createElement("div");
      who.className = "comment-who";
      who.textContent = (c.username || "Anon").toUpperCase();

      const text = document.createElement("div");
      text.className = "comment-text";

      const textContent = document.createElement("span");
      textContent.className = "comment-content";
      textContent.textContent = c.content;

      const metaInfo = document.createElement("div");
      metaInfo.className = "comment-meta";

      const dateSpan = document.createElement("span");
      dateSpan.className = "comment-date";
      dateSpan.textContent = c.created_at ? c.created_at.substring(0, 16) : "Brak daty";

      const likedStr = localStorage.getItem('likedComments');
      const likedComments = likedStr ? JSON.parse(likedStr) : [];

      const currentId = String(c.id || c.comment_id);
      const isLiked = likedComments.includes(currentId);

      const likesCount = parseInt(c.likes);
      const finalLikes = isNaN(likesCount) ? 0 : likesCount;

      const likeBtn = document.createElement("button");
      likeBtn.className = `comment-like-btn ${isLiked ? 'liked' : ''}`;
      likeBtn.innerHTML = `♥ <span class="like-count">${finalLikes}</span>`;
      likeBtn.dataset.id = currentId;

      metaInfo.appendChild(dateSpan);
      metaInfo.appendChild(likeBtn);

      text.appendChild(textContent);
      text.appendChild(metaInfo);

      row.appendChild(who);
      row.appendChild(text);
      commentsList.appendChild(row);
    }
  } catch (err) {
    commentsList.innerHTML = "";
    const box = document.createElement("div");
    box.className = "comment-row comment-row--empty";
    box.textContent = "Błąd ładowania komentarzy: " + (err?.message || err);
    commentsList.appendChild(box);
  }
}

function openFromCard(card) {
  const id = card.dataset.postId;
  const title = card.dataset.title || "POST";
  const body = card.dataset.body || "";
  const image = card.dataset.image || "";
  showPost({ id, title, body, image });
}

listView.addEventListener("click", (e) => {
  const card = e.target.closest(".post-card");
  if (!card) return;
  openFromCard(card);
});

listView.addEventListener("keydown", (e) => {
  if (e.key !== "Enter" && e.key !== " ") return;
  const card = e.target.closest(".post-card");
  if (!card) return;
  e.preventDefault();
  openFromCard(card);
});

backBtn.addEventListener("click", showList);

commentForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  if (!activePostId) return;

  const text = (commentText.value || "").trim();
  if (!text) return;

  const btn = commentForm.querySelector('button[type="submit"]');
  if (btn) btn.disabled = true;

  try {
    await apiAddComment(activePostId, text);
    commentText.value = "";
    await renderComments();
  } catch (err) {
    alert(err?.message || "Błąd połączenia z serwerem.");
  } finally {
    if (btn) btn.disabled = false;
  }
});

commentsList.addEventListener('click', async (e) => {
  const likeBtn = e.target.closest('.comment-like-btn');
  if (!likeBtn) return;

  if (likeBtn.disabled) return;
  likeBtn.disabled = true;

  const commentId = String(likeBtn.dataset.id);
  const isCurrentlyLiked = likeBtn.classList.contains('liked');

  const action = isCurrentlyLiked ? 'unlike' : 'like';

  try {
    const res = await fetch('api/like_comment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: commentId, action: action })
    });

    const data = await res.json();

    if (data.ok) {
      likeBtn.innerHTML = `♥ <span class="like-count">${data.likes}</span>`;

      let likedComments = JSON.parse(localStorage.getItem('likedComments') || '[]');

      if (action === 'like') {
        likeBtn.classList.add('liked');
        if (!likedComments.includes(commentId)) {
          likedComments.push(commentId);
        }
      } else {
        likeBtn.classList.remove('liked');
        likedComments = likedComments.filter(id => id !== commentId);
      }

      localStorage.setItem('likedComments', JSON.stringify(likedComments));
    } else {
      alert(data.error || "Wystąpił błąd przy dawaniu polubienia.");
    }
  } catch (err) {
    alert("Nie udało się połączyć z serwerem.");
  } finally {
    likeBtn.disabled = false;
  }
});

// start
showList();