document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('messageInput');

    // 保存
    input.addEventListener('input', function () {
        localStorage.setItem('draft_message', input.value);
    });

    // 復元
    const saved = localStorage.getItem('draft_message');
    if (saved) {
        input.value = saved;
    }

    // 送信時に削除
    input.closest('form').addEventListener('submit', function () {
        localStorage.removeItem('draft_message');
    });
});

// モーダル表示
const completeButton = document.querySelector('.transaction-button');
const modal = document.getElementById('ratingModal');

if (completeButton) {
    completeButton.addEventListener('click', function (e) {
        e.preventDefault(); // ← これ重要（送信止める）
        modal.classList.remove('hidden');
    });
}

// 星クリック
const stars = document.querySelectorAll('.star-rating-modal span');
const ratingInput = document.getElementById('ratingValue');

stars.forEach(star => {
    star.addEventListener('click', () => {
        const value = star.getAttribute('data-value');
        ratingInput.value = value;

        stars.forEach(s => s.classList.remove('active'));

        for (let i = 0; i < value; i++) {
            stars[i].classList.add('active');
        }
    });
});