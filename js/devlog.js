document.addEventListener('DOMContentLoaded', () => {
  const viewList = document.getElementById('devlog-list');
  const viewDetails = document.getElementById('devlog-post');
  const btnBack = document.getElementById('backToList');
  const posts = document.querySelectorAll('.devlog-item');

  const dMeta = document.getElementById('detailsMeta');
  const dTitle = document.getElementById('detailsTitle');
  const dBody = document.getElementById('detailsBody');
  const dImg = document.getElementById('detailsImg');
  const dVideo = document.getElementById('detailsVideo');

  let currentPostId = null;
  const commentsList = document.getElementById('commentsList');
  const commentForm = document.getElementById('commentForm');

  const lightbox = document.getElementById('devlog-lightbox');
  const lbImg = document.getElementById('dlb-img');
  const lbVideo = document.getElementById('dlb-video');
  const btnClose = document.getElementById('dlb-close');

  // Ładowanie komentarzy
  async function loadComments(postId) {
    commentsList.innerHTML = '<div style="opacity: 0.5; padding: 10px;">Ładowanie komentarzy...</div>';
    try {
      const res = await fetch(`api/comments.php?post_id=${postId}`);
      const data = await res.json();

      if (data.error) {
        commentsList.innerHTML = `<div style="color: #ff6b6b; padding: 10px;">${data.error}</div>`;
        return;
      }
      if (data.length === 0) {
        commentsList.innerHTML = '<div class="comment-row--empty">Brak komentarzy. Bądź pierwszy!</div>';
        return;
      }

      commentsList.innerHTML = '';
      data.forEach(c => {
        const div = document.createElement('div');
        div.className = 'comment-text';
        div.style.marginBottom = '10px';

        // Opcjonalne zmienne z backendu (user_liked i likes) - jeśli API ich nie zwraca, nie zepsuje to kodu
        const safeContent = (c.content || c.text || '').replace(/</g, "&lt;").replace(/>/g, "&gt;");
        const heartStyle = c.user_liked > 0 ? 'color: #ff4757; opacity: 1;' : 'color: inherit;';

        div.innerHTML = `
                          <div style="display:flex; flex-direction:column; width:100%;">
                              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                                  <span class="comment-who">${c.username}</span>
                                  <div class="comment-meta">
                                      <span class="comment-date">${c.created_at}</span>
                                      <button class="comment-like-btn" data-id="${c.comment_id}" style="${heartStyle}; background: none; border: none; cursor: pointer;">
                                          ❤ <span class="like-count">${c.likes || 0}</span>
                                      </button>
                                  </div>
                              </div>
                              <div class="comment-content">${safeContent}</div>
                          </div>
                      `;
        commentsList.appendChild(div);
      });
    } catch (err) {
      commentsList.innerHTML = '<div style="color: #ff6b6b; padding: 10px;">Błąd łączenia z serwerem.</div>';
    }
  }

  // Dodawanie komentarza - wysyłka w formacie JSON (bardzo ważne!)
  if (commentForm) {
    commentForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!currentPostId) return;

      const input = document.getElementById('commentText');
      const text = input.value.trim();
      if (!text) return;

      const btn = commentForm.querySelector('.comment-btn');
      btn.disabled = true;
      btn.textContent = '...';

      // Zamieniono obiekt FormData na klasyczny JSON, żeby dopasować się do api/comments.php
      const payload = {
        post_id: currentPostId,
        content: text,
        text: text
      };

      try {
        const res = await fetch('api/comments.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
          input.value = '';
          await loadComments(currentPostId);
        } else {
          alert(data.error || 'Błąd dodawania komentarza.');
        }
      } catch (err) {
        alert('Błąd połączenia z serwerem. Sprawdź odpowiedź w narzędziach F12.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'DODAJ';
      }
    });
  }

  // Lajki (korzystające z JSON)
  commentsList.addEventListener('click', async (e) => {
    const btn = e.target.closest('.comment-like-btn');
    if (!btn) return;

    const commentId = btn.getAttribute('data-id');
    try {
      const res = await fetch('api/like_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comment_id: commentId })
      });
      const data = await res.json();
      if (data.success) {
        const countSpan = btn.querySelector('.like-count');
        countSpan.textContent = data.likes;
        if (data.action === 'liked') {
          btn.style.color = '#ff4757';
        } else {
          btn.style.color = 'inherit';
        }
      }
    } catch (err) {}
  });

  // Pokaż detale po kliknięciu w wpis devloga
  posts.forEach(post => {
    post.addEventListener('click', (e) => {
      // Zapobiegnij otwarciu posta jeśli klikasz prosto w zdjęcie do powiększenia
      if (e.target.tagName === 'IMG' || e.target.tagName === 'VIDEO') return;

      currentPostId = post.getAttribute('data-post-id');
      const title = post.getAttribute('data-title');
      const body = post.getAttribute('data-body');
      const media = post.getAttribute('data-media');
      const type = post.getAttribute('data-type');
      const meta = post.getAttribute('data-meta');

      dTitle.textContent = title;
      dBody.innerHTML = body.replace(/\n/g, '<br>');
      dMeta.innerHTML = meta;

      dImg.classList.add('is-hidden');
      if (dVideo) {
        dVideo.classList.add('is-hidden');
        dVideo.pause();
      }

      if (type === 'image' && media && media.trim() !== '') {
        dImg.src = media;
        dImg.setAttribute('data-src', media);
        dImg.classList.remove('is-hidden');
      } else if (type === 'video' && media && media.trim() !== '' && dVideo) {
        dVideo.querySelector('source').src = media;
        dVideo.setAttribute('data-src', media);
        dVideo.load();
        dVideo.classList.remove('is-hidden');
      }

      loadComments(currentPostId);

      viewList.classList.add('is-hidden');
      viewDetails.classList.remove('is-hidden');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // Cofnij do listy wpisów
  if (btnBack) {
    btnBack.addEventListener('click', () => {
      viewDetails.classList.add('is-hidden');
      viewList.classList.remove('is-hidden');
      if (dVideo) dVideo.pause();
      currentPostId = null;
    });
  }

  // Obsługa Lightboxa dla plików multimedialnych
  document.body.addEventListener('click', (e) => {
    if (e.target.classList.contains('media-trigger')) {
      e.stopPropagation();

      lbImg.style.display = 'none';
      lbVideo.style.display = 'none';
      lbVideo.pause();

      const type = e.target.getAttribute('data-type');
      const src = e.target.getAttribute('data-src');

      if (type === 'video') {
        lbVideo.src = src;
        lbVideo.style.display = 'block';
      } else {
        lbImg.src = src;
        lbImg.alt = e.target.alt || 'Powiększenie';
        lbImg.style.display = 'block';
      }

      lightbox.classList.add('active');
    }
  });

  function closeLightbox() {
    lightbox.classList.remove('active');
    lbVideo.pause();
  }

  if (btnClose) btnClose.addEventListener('click', closeLightbox);
  if (lightbox) {
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox || e.target.classList.contains('lightbox-content')) closeLightbox();
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && lightbox && lightbox.classList.contains('active')) closeLightbox();
  });
});