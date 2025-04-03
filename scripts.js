function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
    document.getElementById('popupOverlay').style.display = 'block';
}

function closePopup() {
    let popups = document.querySelectorAll('.popup');
    popups.forEach(popup => popup.style.display = 'none');
    document.getElementById('popupOverlay').style.display = 'none';
}

async function signup() {
    const username = document.getElementById('signupUsername').value;
    const password = document.getElementById('signupPassword').value;
    const res = await fetch('/signup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    const data = await res.json();
    alert(data.message);
}

async function login() {
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    const res = await fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    const data = await res.json();
    alert(data.message);
}

let isLoggedIn = false; // Change this to true if the user is logged in

if (!isLoggedIn) {
    bookmarkBtn.classList.add("disabled");
    bookmarkBtn.disabled = true;
} else {
    bookmarkBtn.addEventListener("click", () => {
        alert("Recipe bookmarked!");
    });
}